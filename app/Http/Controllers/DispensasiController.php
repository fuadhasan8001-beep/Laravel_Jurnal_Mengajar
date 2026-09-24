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
use Throwable;

class DispensasiController extends Controller
{
    public function index(): View
    {
        $query = Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai', 'groupStudents.siswa'])
            ->where(function ($query): void {
                $query->whereNull('group_key')
                    ->orWhereRaw('dispensasis.id = (select MIN(grouped.id) from dispensasis as grouped where grouped.group_key = dispensasis.group_key)');
            })
            ->latest();

        if (auth()->user()->role === 'siswa') {
            $query->where('siswa_id', $this->student()->id);
        }

        return view('dispensasi.index', [
            'dispensasis' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $hari = today()->locale('id')->translatedFormat('l');
        $waktuSekarang = now()->format('H:i:s');
        $jamPelajarans = JamPelajaran::query()
            ->where('is_active', true)
            ->orderBy('jam_ke')
            ->get();

        return view('dispensasi.create', [
            'hariIni' => $hari,
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

        $hari = Carbon::parse($data['tanggal'])->locale('id')->translatedFormat('l');
        [$jamMulaiWaktu] = $jamMulai->timesForDay($hari);
        [, $jamSelesaiWaktu] = $jamSelesai->timesForDay($hari);

        if ($jamMulaiWaktu >= $jamSelesaiWaktu) {
            return back()->withInput()->withErrors([
                'jam_selesai_id' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        $lessonEnd = Carbon::parse($data['tanggal'].' '.$jamMulaiWaktu);
        if ($lessonEnd->lessThanOrEqualTo(now())) {
            return back()->withInput()->withErrors([
                'jam_mulai_id' => 'Jam dispensasi harus untuk pelajaran yang belum berakhir.',
            ]);
        }

        $evidencePath = null;
        if ($request->hasFile('bukti')) {
            $evidencePath = $request->file('bukti')->store('dispensasi/bukti');
        }

        $groupKey = $isPiket ? (string) Str::uuid() : null;
        $studentIds = $isPiket ? $data['siswa_ids'] : [$this->student()->id];
        try {
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
        } catch (Throwable $exception) {
            if ($evidencePath !== null) {
                Storage::delete($evidencePath);
            }

            throw $exception;
        }

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
                'groupStudents.siswa',
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
        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($dispensasi, $data): void {
            $dispensasi = Dispensasi::whereKey($dispensasi->id)->lockForUpdate()->firstOrFail();
            abort_if($dispensasi->status_akhir !== 'Menunggu', 422, 'Dispensasi sudah memiliki keputusan akhir.');

            $user = auth()->user();
            $isPiket = $user->role === 'piket';
            if (! $isPiket && $dispensasi->status_piket !== 'Disetujui') {
                abort(422, 'Dispensasi harus disetujui piket terlebih dahulu.');
            }
            if ($isPiket) {
                abort_if($dispensasi->status_piket !== 'Menunggu', 422, 'Pernyataan sudah diteruskan kepada admin.');
                $dispensasi->status_piket = $data['status'];
                $dispensasi->piket_id = $user->id;
                $dispensasi->verified_piket_at = now();
            } else {
                $dispensasi->status_admin = $data['status'];
                $dispensasi->admin_id = $user->id;
                $dispensasi->verified_admin_at = now();
            }

            $dispensasi->catatan_verifikasi = $data['catatan_verifikasi'] ?? null;
            $dispensasi->status_akhir = $this->finalStatus($dispensasi);
            $dispensasi->save();

            $group = $dispensasi->group_key
                ? Dispensasi::where('group_key', $dispensasi->group_key)->lockForUpdate()->get()
                : collect([$dispensasi]);
            $group->each(function (Dispensasi $item) use ($dispensasi): void {
                if ($item->is($dispensasi)) {
                    return;
                }

                $item->status_piket = $dispensasi->status_piket;
                $item->piket_id = $dispensasi->piket_id;
                $item->verified_piket_at = $dispensasi->verified_piket_at;
                $item->status_admin = $dispensasi->status_admin;
                $item->admin_id = $dispensasi->admin_id;
                $item->verified_admin_at = $dispensasi->verified_admin_at;
                $item->status_akhir = $dispensasi->status_akhir;
                $item->catatan_verifikasi = $dispensasi->catatan_verifikasi;
                $item->save();
            });

            if ($isPiket) {
                if ($data['status'] === 'Disetujui') {
                    User::where('role', 'admin')->where('is_active', true)->get()->each(function (User $admin) use ($dispensasi): void {
                        $admin->notify((new DispensasiNotification($dispensasi, 'piket_approved'))->afterCommit());
                        $admin->notify((new DispensasiApprovalMail($dispensasi))->afterCommit());
                    });
                } else {
                    $dispensasi->siswa->user?->notify((new DispensasiNotification($dispensasi, 'piket_rejected'))->afterCommit());
                }
            } else {
                $event = $data['status'] === 'Disetujui' ? 'admin_approved' : 'admin_rejected';
                $dispensasi->siswa->user?->notify((new DispensasiNotification($dispensasi, $event))->afterCommit());
            }

            if ($dispensasi->status_akhir === 'Disetujui') {
                $group->each(fn (Dispensasi $item): mixed => $this->markAttendanceAsDispensed($item));
                $this->notifyScheduledTeachers($dispensasi);
            }
        });

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

        $hari = $dispensasi->tanggal->locale('id')->translatedFormat('l');
        [$start] = $dispensasi->jamMulai->timesForDay($hari);
        [, $end] = $dispensasi->jamSelesai->timesForDay($hari);
        $jurnals = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->whereDate('tanggal', $dispensasi->tanggal)
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->get()
            ->filter(fn (Jurnal $jurnal): bool => $this->periodsOverlap($jurnal->jamMulai, $jurnal->jamSelesai, $hari, $start, $end));

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
        $dispensasi->loadMissing(['siswa', 'jamMulai', 'jamSelesai']);
        $hari = $dispensasi->tanggal->locale('id')->translatedFormat('l');
        [$start] = $dispensasi->jamMulai->timesForDay($hari);
        [, $end] = $dispensasi->jamSelesai->timesForDay($hari);
        $scheduledGuruIds = Jadwal::with('jamPelajaran')
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->where('hari', $hari)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Jadwal $jadwal): bool => $this->periodsOverlap($jadwal->jamPelajaran, $jadwal->jamPelajaran, $hari, $start, $end))
            ->pluck('guru_id');
        $journalGuruIds = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->whereDate('tanggal', $dispensasi->tanggal)
            ->get()
            ->filter(fn (Jurnal $journal): bool => $this->periodsOverlap($journal->jamMulai, $journal->jamSelesai, $hari, $start, $end))
            ->pluck('guru_id');

        User::whereIn('id', Guru::whereIn('id', $scheduledGuruIds->merge($journalGuruIds)->unique())->select('user_id'))
            ->where('is_active', true)
            ->where('role', 'guru')
            ->get()
            ->each(fn (User $user) => $user->notify((new DispensasiNotification($dispensasi, 'teacher_approved'))->afterCommit()));
    }

    private function periodsOverlap(JamPelajaran $lessonStart, JamPelajaran $lessonEnd, string $hari, string $start, string $end): bool
    {
        [$lessonStartTime] = $lessonStart->timesForDay($hari);
        [, $lessonEndTime] = $lessonEnd->timesForDay($hari);

        return $lessonStartTime < $end && $lessonEndTime > $start;
    }
}
