<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\DispensasiApprovalMail;
use App\Notifications\DispensasiNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DispensasiController extends Controller
{
    public function index(): View
    {
        $query = Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])
            ->latest();

        if (auth()->user()->role === 'siswa') {
            $query->where('siswa_id', $this->student()->id);
        }

        return view('dispensasi.index', [
            'dispensasis' => $query->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->role === 'piket', 403);

        $hari = today()->locale('id')->translatedFormat('l');
        $waktuSekarang = now()->format('H:i:s');
        $jamPelajarans = JamPelajaran::query()
            ->where('is_active', true)
            ->orderBy('jam_ke')
            ->get();

        return view('dispensasi.create', [
            'siswas' => auth()->user()->role === 'piket' ? Siswa::with('kelas')->orderBy('nama_siswa')->get() : collect(),
            'jamPelajarans' => $jamPelajarans,
            'jamTidakTersediaIds' => $jamPelajarans
                ->filter(fn (JamPelajaran $jam): bool => $jam->timesForDay($hari)[1] <= $waktuSekarang)
                ->pluck('id')
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isPiket = auth()->user()->role === 'piket';
        abort_if(! $isPiket && $request->filled('siswa_ids'), 403);
        $request->merge(['tanggal' => today()->toDateString()]);

        $data = $request->validate([
            'siswa_ids' => $isPiket ? ['required', 'array', 'min:1'] : ['prohibited'],
            'siswa_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai_id' => ['required', 'exists:jam_pelajarans,id'],
            'jam_selesai_id' => ['required', 'exists:jam_pelajarans,id'],
            'alasan' => ['required', 'string', 'max:5000'],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $jamMulai = JamPelajaran::findOrFail($data['jam_mulai_id']);
        $jamSelesai = JamPelajaran::findOrFail($data['jam_selesai_id']);

        if ($jamSelesai->jam_ke < $jamMulai->jam_ke) {
            return back()->withInput()->withErrors([
                'jam_selesai_id' => 'Jam selesai tidak boleh sebelum jam dispensasi.',
            ]);
        }

        $hari = today()->locale('id')->translatedFormat('l');
        $lessonEnd = Carbon::parse(today()->toDateString().' '.$jamMulai->timesForDay($hari)[1]);
        if ($lessonEnd->lessThanOrEqualTo(now())) {
            return back()->withInput()->withErrors([
                'jam_mulai_id' => 'Jam dispensasi harus untuk pelajaran yang belum berakhir.',
            ]);
        }

        $evidencePath = null;
        if ($request->hasFile('bukti')) {
            $evidencePath = $request->file('bukti')->store('dispensasi/bukti');
        }

        $groupKey = (string) Str::uuid();
        $studentIds = $isPiket ? $data['siswa_ids'] : [$this->student()->id];
        $dispensasis = DB::transaction(function () use ($data, $evidencePath, $groupKey, $studentIds, $isPiket): array {
            return collect($studentIds)->map(function (int $siswaId) use ($data, $evidencePath, $groupKey, $isPiket): Dispensasi {
                return Dispensasi::create([
                    ...collect($data)->except('siswa_ids')->all(),
                    'siswa_id' => $siswaId,
                    'bukti' => $evidencePath,
                    'group_key' => $groupKey,
                    ...($isPiket ? [
                        'piket_id' => auth()->id(),
                        'status_piket' => 'Disetujui',
                        'verified_piket_at' => now(),
                    ] : []),
                ]);
            })->all();
        });

        if ($isPiket) {
            User::where('role', 'admin')->where('is_active', true)->get()
                ->each->notify(new DispensasiApprovalMail($dispensasis[0]));
        } else {
            User::where('role', 'piket')->where('is_active', true)->get()
                ->each->notify(new DispensasiNotification($dispensasis[0], 'submitted'));
        }

        return redirect()->route('dispensasi.index')
            ->with('success', 'Pengajuan dispensasi berhasil dikirim.');
    }

    public function show(Dispensasi $dispensasi): View
    {
        $this->authorizeView($dispensasi);

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi->load([
                'siswa',
                'jamMulai',
                'jamSelesai',
                'piket',
                'admin',
            ]),
        ]);
    }

    public function downloadEvidence(Dispensasi $dispensasi): BinaryFileResponse
    {
        $this->authorizeView($dispensasi);

        abort_unless($dispensasi->bukti && Storage::exists($dispensasi->bukti), 404);

        return response()->download(Storage::path($dispensasi->bukti));
    }

    public function verify(Request $request, Dispensasi $dispensasi): RedirectResponse
    {
        abort_if($dispensasi->status_akhir !== 'Menunggu', 422, 'Dispensasi sudah memiliki keputusan akhir.');

        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $isPiket = $user->role === 'piket';

        if (! $isPiket && $dispensasi->status_piket !== 'Disetujui') {
            abort(422, 'Dispensasi harus disetujui piket terlebih dahulu.');
        }

        if ($isPiket) {
            $dispensasi->status_piket = $data['status'];
            $dispensasi->piket_id = $user->id;
            $dispensasi->verified_piket_at = Carbon::now();
        } else {
            $dispensasi->status_admin = $data['status'];
            $dispensasi->admin_id = $user->id;
            $dispensasi->verified_admin_at = Carbon::now();
        }

        $dispensasi->catatan_verifikasi = $data['catatan_verifikasi'] ?? null;
        $dispensasi->status_akhir = $this->finalStatus($dispensasi);
        $dispensasi->save();

        if ($isPiket) {
            $event = $data['status'] === 'Disetujui' ? 'piket_approved' : 'piket_rejected';
            User::where('role', 'admin')->where('is_active', true)->get()
                ->each->notify(new DispensasiApprovalMail($dispensasi));
        } else {
            $event = $data['status'] === 'Disetujui' ? 'admin_approved' : 'admin_rejected';
            $dispensasi->siswa->user?->notify(new DispensasiNotification($dispensasi, $event));
        }

        if ($dispensasi->status_akhir === 'Disetujui') {
            $this->markAttendanceAsDispensed($dispensasi);
            $this->notifyScheduledTeachers($dispensasi);
        }

        return redirect()->route('dispensasi.show', $dispensasi)
            ->with('success', 'Verifikasi dispensasi berhasil disimpan.');
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

        $jurnals = Jurnal::query()
            ->whereDate('tanggal', $dispensasi->tanggal)
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->where('jam_mulai_id', '<=', $dispensasi->jam_selesai_id)
            ->where('jam_selesai_id', '>=', $dispensasi->jam_mulai_id)
            ->get();

        foreach ($jurnals as $jurnal) {
            Absensi::updateOrCreate(
                ['jurnal_id' => $jurnal->id, 'siswa_id' => $dispensasi->siswa_id],
                [
                    'status' => 'D',
                    'catatan' => 'Dispensasi disetujui.',
                ]
            );
        }
    }

    private function notifyScheduledTeachers(Dispensasi $dispensasi): void
    {
        $dispensasi->loadMissing('siswa');
        $hari = $dispensasi->tanggal->copy()->locale('id')->translatedFormat('l');

        Jadwal::query()
            ->with('guru.user')
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->where('hari', $hari)
            ->where('is_active', true)
            ->get()
            ->pluck('guru.user')
            ->filter()
            ->unique('id')
            ->each->notify(new DispensasiNotification($dispensasi, 'teacher_approved'));
    }
}
