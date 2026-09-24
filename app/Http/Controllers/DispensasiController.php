<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\DispensasiNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DispensasiController extends Controller
{
    public function index(): View
    {
        $this->authorizePiketAccess();
        $query = Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])
            ->latest();

        if (auth()->user()->role === 'siswa') {
            $query->where('siswa_id', $this->student()->id);
        }

        $dispensasis = $query->get();

        return view('dispensasi.index', [
            'dispensasis' => $dispensasis->groupBy(fn (Dispensasi $dispensasi): string => $dispensasi->group_key ?? (string) $dispensasi->id)
                ->map(fn ($group) => $group->first())
                ->values(),
        ]);
    }

    public function create(): View
    {
        $this->authorizePiketAccess();
        $hari = today()->locale('id')->translatedFormat('l');
        $waktuSekarang = now()->format('H:i:s');
        $jamPelajarans = JamPelajaran::where('is_active', true)->orderBy('jam_ke')->get();

        return view('dispensasi.create', [
            'hariIni' => $hari,
            'siswas' => auth()->user()->isPiketHariIni() ? Siswa::with('kelas')->orderBy('nama_siswa')->get() : collect(),
            'jamPelajarans' => $jamPelajarans,
            'jamTidakTersediaIds' => $jamPelajarans
                ->filter(fn (JamPelajaran $jam): bool => $jam->timesForDay($hari)[1] <= $waktuSekarang)
                ->pluck('id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePiketAccess();
        $isPiket = auth()->user()->isPiketHariIni();
        abort_if(! $isPiket && ($request->filled('siswa_ids') || $request->hasFile('surat_izin') || $request->filled('attendance_status')), 403);
        $request->merge(['tanggal' => today()->toDateString()]);

        $data = $request->validate([
            'siswa_ids' => [$isPiket ? 'required' : 'exclude', 'array', 'min:1', 'max:100'],
            'siswa_ids.*' => ['integer', 'distinct', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai_id' => ['required', 'exists:jam_pelajarans,id'],
            'jam_selesai_id' => ['required', 'exists:jam_pelajarans,id'],
            'alasan' => ['required', 'string', 'max:5000'],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'surat_izin' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'attendance_status' => [$isPiket ? 'required' : 'exclude', 'in:H,S,I,A'],
        ]);

        $jamMulai = JamPelajaran::findOrFail($data['jam_mulai_id']);
        $jamSelesai = JamPelajaran::findOrFail($data['jam_selesai_id']);

        $hari = today()->locale('id')->translatedFormat('l');
        [$mulai, $akhirPelajaranMulai] = $jamMulai->timesForDay($hari);
        [, $selesai] = $jamSelesai->timesForDay($hari);

        if ($mulai >= $selesai) {
            return back()->withInput()->withErrors([
                'jam_selesai_id' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        if (! $jamMulai->is_active || $akhirPelajaranMulai <= now()->format('H:i:s')) {
            return back()->withInput()->withErrors([
                'jam_mulai_id' => 'Jam dispensasi harus untuk pelajaran yang belum berakhir.',
            ]);
        }

        if (! $jamSelesai->is_active) {
            return back()->withInput()->withErrors([
                'jam_selesai_id' => 'Jam selesai harus merupakan jam pelajaran aktif.',
            ]);
        }

        $studentIds = $isPiket ? $data['siswa_ids'] : [$this->student()->id];

        abort_if(
            Dispensasi::query()
                ->whereIn('siswa_id', $studentIds)
                ->whereDate('tanggal', $data['tanggal'])
                ->whereIn('status_akhir', ['Menunggu', 'Disetujui'])
                ->exists(),
            422,
            'Siswa sudah memiliki pengajuan dispensasi aktif pada tanggal yang sama.'
        );

        unset($data['siswa_ids'], $data['bukti'], $data['surat_izin']);
        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('dispensasi/bukti');
        }
        if ($request->hasFile('surat_izin')) {
            $data['surat_izin_path'] = $request->file('surat_izin')->store('dispensasi/surat');
        }

        $groupKey = $isPiket ? (string) Str::uuid() : null;
        try {
            $first = DB::transaction(function () use ($studentIds, $data, $isPiket, $groupKey): ?Dispensasi {
                $first = null;
                foreach ($studentIds as $studentId) {
                    $dispensasi = Dispensasi::create([
                        ...$data, 'group_key' => $groupKey, 'siswa_id' => $studentId,
                        ...($isPiket ? ['status_piket' => 'Disetujui', 'piket_id' => auth()->id(), 'verified_piket_at' => now()] : []),
                    ]);
                    $first ??= $dispensasi;
                }

                return $first;
            });
        } catch (\Throwable $exception) {
            Storage::delete(array_filter([$data['bukti'] ?? null, $data['surat_izin_path'] ?? null]));
            throw $exception;
        }

        if ($first && ! $isPiket) {
            User::where('role', 'piket')->where('is_active', true)->get()
                ->each->notify((new DispensasiNotification($first, 'submitted'))->afterCommit());
        }

        return redirect()->route('dispensasi.index')
            ->with('success', 'Pengajuan dispensasi berhasil dikirim.');
    }

    public function show(Dispensasi $dispensasi): View
    {
        $this->authorizePiketAccess();
        $this->authorizeView($dispensasi);

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi->load([
                'siswa',
                'groupStudents.siswa',
                'jamMulai',
                'jamSelesai',
                'piket',
                'admin',
                'waka',
            ]),
            'proofUrl' => $dispensasi->status_akhir === 'Disetujui'
                ? URL::temporarySignedRoute('dispensasi.public-proof', now()->addYear(), ['dispensasi' => $dispensasi->id])
                : null,
        ]);
    }

    public function publicProof(Dispensasi $dispensasi): View
    {
        abort_unless($dispensasi->status_akhir === 'Disetujui', 404);

        return view('dispensasi.public-proof', [
            'dispensasi' => $dispensasi->load(['siswa.kelas', 'jamMulai', 'jamSelesai', 'piket', 'waka']),
        ]);
    }

    public function downloadEvidence(Dispensasi $dispensasi): BinaryFileResponse
    {
        $this->authorizeView($dispensasi);

        abort_unless($dispensasi->bukti && Storage::exists($dispensasi->bukti), 404);

        return response()->download(Storage::path($dispensasi->bukti));
    }

    public function downloadParentLetter(Dispensasi $dispensasi): BinaryFileResponse
    {
        $this->authorizeView($dispensasi);

        abort_unless($dispensasi->surat_izin_path && Storage::exists($dispensasi->surat_izin_path), 404);

        return response()->download(Storage::path($dispensasi->surat_izin_path));
    }

    public function verify(Request $request, Dispensasi $dispensasi): RedirectResponse
    {
        $this->authorizePiketAccess();
        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($dispensasi, $data): void {
            $dispensasi = Dispensasi::whereKey($dispensasi->id)->lockForUpdate()->firstOrFail();
            $alreadyApprovedWithStatus = $dispensasi->status_akhir === 'Disetujui'
                && $dispensasi->status_piket === 'Disetujui'
                && $dispensasi->status_admin === 'Disetujui'
                && filled($dispensasi->attendance_status);

            abort_if($dispensasi->status_akhir !== 'Menunggu' && ! $alreadyApprovedWithStatus, 422, 'Dispensasi sudah memiliki keputusan akhir.');
            $user = auth()->user();
            $isPiket = $user->isPiketHariIni();

            if (! $isPiket && $dispensasi->status_piket !== 'Disetujui') {
                abort(422, 'Dispensasi harus disetujui piket terlebih dahulu.');
            }

            if ($isPiket) {
                abort_if($dispensasi->status_piket !== 'Menunggu', 422, 'Pernyataan sudah diteruskan kepada admin.');
                $dispensasi->status_piket = $data['status'];
                $dispensasi->piket_id = $user->id;
                $dispensasi->verified_piket_at = Carbon::now();
            } else {
                $dispensasi->status_admin = $data['status'];
                $dispensasi->admin_id = $user->id;
                $dispensasi->verified_admin_at = Carbon::now();
                $dispensasi->waka_id = $user->id;
                $dispensasi->verified_waka_at = Carbon::now();
            }

            $dispensasi->catatan_verifikasi = $data['catatan_verifikasi'] ?? null;
            $dispensasi->status_akhir = $this->finalStatus($dispensasi);
            $dispensasi->save();

            $group = $dispensasi->group_key
                ? Dispensasi::where('group_key', $dispensasi->group_key)->lockForUpdate()->get()
                : collect([$dispensasi]);
            $group->each(function (Dispensasi $item) use ($dispensasi): void {
                if ($item->id === $dispensasi->id) {
                    return;
                }
                $item->status_piket = $dispensasi->status_piket;
                $item->piket_id = $dispensasi->piket_id;
                $item->verified_piket_at = $dispensasi->verified_piket_at;
                $item->status_admin = $dispensasi->status_admin;
                $item->admin_id = $dispensasi->admin_id;
                $item->verified_admin_at = $dispensasi->verified_admin_at;
                $item->waka_id = $dispensasi->waka_id;
                $item->verified_waka_at = $dispensasi->verified_waka_at;
                $item->status_akhir = $dispensasi->status_akhir;
                $item->catatan_verifikasi = $dispensasi->catatan_verifikasi;
                $item->save();
            });

            if ($isPiket) {
                $event = $data['status'] === 'Disetujui' ? 'piket_approved' : 'piket_rejected';
                if ($data['status'] !== 'Disetujui') {
                    $dispensasi->siswa->user?->notify(new DispensasiNotification($dispensasi, $event));
                }
            } else {
                $event = $data['status'] === 'Disetujui' ? 'admin_approved' : 'admin_rejected';
                $dispensasi->siswa->user?->notify(new DispensasiNotification($dispensasi, $event));
            }

            if ($dispensasi->status_akhir === 'Disetujui') {
                $group->each(fn (Dispensasi $item): mixed => $this->markAttendanceAsDispensed($item));
                $this->notifyTeachers($dispensasi);
            }
        });

        return redirect()->route('dispensasi.show', $dispensasi)
            ->with('success', 'Verifikasi dispensasi berhasil disimpan.');
    }

    private function notifyTeachers(Dispensasi $dispensasi): void
    {
        $dispensasi->loadMissing(['siswa', 'jamMulai', 'jamSelesai']);
        $start = $dispensasi->jamMulai->jam_mulai;
        $end = $dispensasi->jamSelesai->jam_selesai;
        $scheduledGuruIds = Jadwal::where('kelas_id', $dispensasi->siswa->kelas_id)
            ->where('hari', $dispensasi->tanggal->locale('id')->translatedFormat('l'))->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('jam_mulai', '<', $end)->where('jam_selesai', '>', $start))
            ->pluck('guru_id');
        $journalGuruIds = Jurnal::where('kelas_id', $dispensasi->siswa->kelas_id)->whereDate('tanggal', $dispensasi->tanggal)
            ->whereHas('jamMulai', fn ($query) => $query->where('jam_mulai', '<', $end))
            ->whereHas('jamSelesai', fn ($query) => $query->where('jam_selesai', '>', $start))->pluck('guru_id');
        User::whereIn('id', Guru::whereIn('id', $scheduledGuruIds->merge($journalGuruIds)->unique())->select('user_id'))
            ->where('is_active', true)->where('role', 'guru')->get()
            ->each->notify(new DispensasiNotification($dispensasi, 'teacher_approved'));
    }

    private function student(): Siswa
    {
        return Siswa::where('user_id', auth()->id())->firstOrFail();
    }

    private function authorizeView(Dispensasi $dispensasi): void
    {
        if (auth()->user()->role === 'siswa' && $dispensasi->siswa_id !== $this->student()->id) {
            abort(403);
        }
    }

    private function authorizePiketAccess(): void
    {
        if (auth()->user()->role === 'guru') {
            abort_unless(auth()->user()->isPiketHariIni(), 403);
        }
    }

    private function finalStatus(Dispensasi $dispensasi): string
    {
        if ($dispensasi->status_piket === 'Ditolak' || $dispensasi->status_admin === 'Ditolak') {
            return 'Ditolak';
        }

        if ($dispensasi->status_piket === 'Disetujui' && $dispensasi->status_admin === 'Disetujui') {
            return 'Disetujui';
        }

        return 'Menunggu';
    }

    private function markAttendanceAsDispensed(Dispensasi $dispensasi): void
    {
        $dispensasi->loadMissing(['siswa', 'jamMulai', 'jamSelesai']);

        $start = Carbon::parse($dispensasi->jamMulai->jam_mulai);
        $end = Carbon::parse($dispensasi->jamSelesai->jam_selesai);

        $jurnals = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->whereDate('tanggal', $dispensasi->tanggal)
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->get()
            ->filter(function (Jurnal $jurnal) use ($start, $end): bool {
                if (! $jurnal->jamMulai || ! $jurnal->jamSelesai) {
                    return false;
                }

                $jurnalStart = Carbon::parse($jurnal->jamMulai->jam_mulai);
                $jurnalEnd = Carbon::parse($jurnal->jamSelesai->jam_selesai);

                return $jurnalStart < $end && $jurnalEnd > $start;
            });

        foreach ($jurnals as $jurnal) {
            Absensi::updateOrCreate(
                ['jurnal_id' => $jurnal->id, 'siswa_id' => $dispensasi->siswa_id],
                [
                    'status' => $dispensasi->attendance_status ?? 'D',
                    'catatan' => $dispensasi->surat_izin_path ? 'Izin orang tua / surat dispensasi terlampir.' : 'Dispensasi disetujui.',
                    'surat_izin_path' => $dispensasi->surat_izin_path,
                ]
            );
        }
    }
}
