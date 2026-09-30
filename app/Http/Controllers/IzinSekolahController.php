<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\IzinSekolah;
use App\Models\IzinMasuk;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\ClassAbsenceRecorded;
use App\Services\ClassAbsenceNotifier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IzinSekolahController extends Controller
{
    public function createArrival(Request $request): View
    {
        $this->authorizePiketAccess($request);

        return view('izin-sekolah.arrival', [
            'siswas' => Siswa::with('kelas')->orderBy('nama_siswa')->get(),
            'jamPelajarans' => JamPelajaran::where('is_active', true)->orderBy('jam_ke')->get(),
        ]);
    }

    public function storeArrival(Request $request): RedirectResponse
    {
        $this->authorizePiketAccess($request);
        $data = $request->validate([
            'siswa_id' => ['required', 'integer', 'exists:siswas,id'],
            'jam_masuk_ke' => ['required', 'integer', 'exists:jam_pelajarans,jam_ke'],
            'alasan' => ['nullable', 'string', 'max:1000'],
        ]);
        $date = today()->toDateString();
        $arrivalTime = now()->format('H:i:s');
        $student = Siswa::with('kelas')->findOrFail($data['siswa_id']);

        if (IzinSekolah::where('siswa_id', $student->id)->whereDate('tanggal', $date)->exists()) {
            return back()->withInput()->withErrors(['siswa_id' => 'Siswa ini sudah tercatat izin atau sakit seharian.']);
        }

        $permission = IzinMasuk::updateOrCreate(
            ['siswa_id' => $student->id, 'tanggal' => $date],
            [
                'waktu_masuk' => $arrivalTime,
                'jam_masuk_ke' => $data['jam_masuk_ke'],
                'alasan' => $data['alasan'] ?? null,
                'piket_id' => $request->user()->id,
            ],
        );

        $day = Carbon::parse($date)->locale('id')->translatedFormat('l');
        $jurnals = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->whereDate('tanggal', $date)
            ->where('kelas_id', $student->kelas_id)
            ->get()
            ->filter(fn (Jurnal $jurnal): bool => $jurnal->jamSelesai->timesForDay($day)[1] > $arrivalTime);

        foreach ($jurnals as $jurnal) {
            Absensi::updateOrCreate(
                ['jurnal_id' => $jurnal->id, 'siswa_id' => $student->id],
                ['status' => 'H', 'catatan' => $this->arrivalNote($permission)],
            );
        }

        $this->notifyArrivalRecipients($student, $permission, $day);

        return redirect()->route('piket.izin-masuk.create')
            ->with('success', 'Surat izin masuk berhasil dicatat. Absensi jam berjalan dan berikutnya diperbarui.');
    }

    public function index(Request $request): View
    {
        $this->authorizePiketAccess($request);

        return view('izin-sekolah.index', [
            'izinSekolahs' => IzinSekolah::with(['siswa.kelas', 'piket'])
                ->latest('tanggal')->latest('id')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePiketAccess($request);

        return view('izin-sekolah.create', [
            'siswas' => Siswa::with('kelas')->orderBy('nama_siswa')->get(),
        ]);
    }

    public function store(Request $request, ClassAbsenceNotifier $absenceNotifier): RedirectResponse
    {
        $this->authorizePiketAccess($request);
        $data = $request->validate([
            'siswa_ids' => ['required', 'array', 'min:1', 'max:100'],
            'siswa_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'status' => ['nullable', 'in:I,S'],
            'alasan' => ['nullable', 'string', 'max:5000'],
            'surat_izin' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['tanggal'] = today()->toDateString();
        $data['status'] = $data['status'] ?? 'I';

        $existingStudents = Siswa::whereIn('id', $data['siswa_ids'])
            ->whereHas('izinSekolahs', fn ($query) => $query->whereDate('tanggal', $data['tanggal']))
            ->orderBy('nama_siswa')->pluck('nama_siswa');
        if ($existingStudents->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'siswa_ids' => 'Siswa berikut sudah tercatat pada tanggal tersebut: '.$existingStudents->join(', ').'. Hapus dari pilihan atau gunakan tanggal lain.',
            ]);
        }

        $students = Siswa::whereIn('id', $data['siswa_ids'])->get();
        $path = $request->file('surat_izin')->store('izin-sekolah/surat');

        try {
            DB::transaction(function () use ($data, $students, $path): void {
                foreach ($students as $student) {
                    $attributes = [
                        'status' => $data['status'],
                        'alasan' => $data['alasan'] ?? null,
                        'surat_izin_path' => $path,
                        'piket_id' => auth()->id(),
                    ];
                    IzinSekolah::create(['siswa_id' => $student->id, 'tanggal' => $data['tanggal'], ...$attributes]);

                    $jurnals = Jurnal::whereDate('tanggal', $data['tanggal'])
                        ->where('kelas_id', $student->kelas_id)->get();
                    foreach ($jurnals as $jurnal) {
                        Absensi::updateOrCreate(
                            ['jurnal_id' => $jurnal->id, 'siswa_id' => $student->id],
                            [
                                'status' => $data['status'],
                                'catatan' => $data['status'] === 'S'
                                    ? 'Sakit seharian berdasarkan surat orang tua.'
                                    : 'Izin sekolah seharian berdasarkan surat orang tua.',
                                'surat_izin_path' => $path,
                            ]
                        );
                    }
                }
            });
        } catch (\Throwable $exception) {
            Storage::delete($path);
            throw $exception;
        }

        $absenceNotifier->notify($students, $data['status'] === 'S' ? 'sakit' : 'izin', $data['tanggal']);

        return redirect()->route('piket.izin-sekolah.index')
            ->with('success', ($data['status'] === 'S' ? 'Surat sakit seharian' : 'Surat izin seharian').' berhasil dicatat. Absensi jurnal hari tersebut sudah diperbarui.');
    }

    private function arrivalNote(IzinMasuk $permission): string
    {
        $note = 'Terlambat, izin masuk jam ke-'.$permission->jam_masuk_ke.' pukul '.substr($permission->waktu_masuk, 0, 5).'.';

        return $permission->alasan ? $note.' Alasan: '.$permission->alasan : $note;
    }

    private function notifyArrivalRecipients(Siswa $student, IzinMasuk $permission, string $day): void
    {
        $kelas = Kelas::with('sekretarisUsers')->find($student->kelas_id);
        if (! $kelas) {
            return;
        }

        $scheduledGuruIds = Jadwal::with(['jamPelajaran', 'guru'])
            ->where('kelas_id', $kelas->id)
            ->where('hari', $day)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Jadwal $jadwal): bool => $jadwal->jamPelajaran->is_active
                && $jadwal->jamPelajaran->timesForDay($day)[1] > $permission->waktu_masuk)
            ->pluck('guru.user_id')
            ->filter()
            ->unique();

        $recipients = $kelas->sekretarisUsers->where('is_active', true)
            ->merge(User::whereIn('id', $scheduledGuruIds)->where('is_active', true)->get())
            ->unique('id');
        $message = $student->nama_siswa.' dari kelas '.$kelas->nama_kelas.' terlambat dan masuk pada jam ke-'.$permission->jam_masuk_ke.' pukul '.substr($permission->waktu_masuk, 0, 5).'.';
        $url = route('laporan.absensi', [
            'tanggal_mulai' => $permission->tanggal->toDateString(),
            'tanggal_selesai' => $permission->tanggal->toDateString(),
            'kelas_id' => $kelas->id,
        ]);

        $recipients->each(fn (User $recipient) => $recipient->notify(new ClassAbsenceRecorded($message, $url)));
    }

    private function authorizePiketAccess(Request $request): void
    {
        if ($request->user()->role === 'guru') {
            abort_unless($request->user()->isPiketHariIni(), 403);
        }
    }
}
