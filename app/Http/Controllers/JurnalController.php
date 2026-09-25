<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJurnalRequest;
use App\Http\Requests\UpdateJurnalRequest;
use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Services\SchoolLocationVerifier;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JurnalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai'])
            ->latest('tanggal');

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', $this->currentGuru()->id);
        }

        if (auth()->user()->role === 'sekretaris') {
            $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'));
        }

        $query
            ->when($request->filled('tanggal_mulai'), fn ($builder) => $builder->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($builder) => $builder->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('mapel_id'), fn ($builder) => $builder->where('mapel_id', $request->integer('mapel_id')))
            ->when($request->filled('status_verifikasi'), fn ($builder) => $builder->where('status_verifikasi', $request->string('status_verifikasi')));

        return view('jurnal.index', [
            'jurnals' => $query->paginate(15)->withQueryString(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
        ]);
    }

    public function create(): View
    {
        return view('jurnal.create', $this->formData());
    }

    public function store(StoreJurnalRequest $request, SchoolLocationVerifier $locationVerifier): RedirectResponse
    {
        $data = $request->validated();
        $absensis = $data['absensi'] ?? [];
        unset($data['absensi'], $data['tanda_tangan'], $data['jadwal_id']);
        $data = [...$data, ...$this->locationData($data, $locationVerifier)];
        $this->validatePeriodOrder($data);

        $jurnal = DB::transaction(function () use ($data, $absensis): Jurnal {
            $guru = Guru::where('user_id', auth()->id())->lockForUpdate()->firstOrFail();
            $existingJournal = Jurnal::query()
                ->where('guru_id', $guru->id)
                ->whereDate('tanggal', $data['tanggal'])
                ->where('kelas_id', $data['kelas_id'])
                ->where('mapel_id', $data['mapel_id'])
                ->where('jam_mulai_id', $data['jam_mulai_id'])
                ->where('jam_selesai_id', $data['jam_selesai_id'])
                ->lockForUpdate()->first();

            if ($existingJournal) {
                return $existingJournal;
            }

            $jurnal = Jurnal::create([
                ...$data,
                'guru_id' => $guru->id,
            ]);
            $this->syncAbsensis($jurnal, $absensis);

            return $jurnal;
        }, 5);

        return redirect()->route('jurnal.show', $jurnal)
            ->with('success', $jurnal->wasRecentlyCreated ? 'Jurnal berhasil disimpan.' : 'Jurnal untuk sesi ini sudah diisi. Data sebelumnya tetap tersimpan.');
    }

    public function show(Jurnal $jurnal): View
    {
        $this->authorizeJournal($jurnal);

        return view('jurnal.show', [
            'jurnal' => $jurnal->load([
                'guru',
                'kelas',
                'mapel',
                'jamMulai',
                'jamSelesai',
                'absensis.siswa',
            ]),
        ]);
    }

    public function signature(Jurnal $jurnal): StreamedResponse
    {
        $this->authorizeJournal($jurnal);

        abort_unless($jurnal->tanda_tangan && Storage::exists($jurnal->tanda_tangan), 404);

        return Storage::response($jurnal->tanda_tangan, 'tanda-tangan-jurnal.png', [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="tanda-tangan-jurnal.png"',
        ]);
    }

    public function edit(Jurnal $jurnal): View
    {
        $this->authorizeJournal($jurnal, true);
        $jurnal->load('absensis');

        return view('jurnal.edit', [
            ...$this->formData($jurnal),
            'jurnal' => $jurnal,
        ]);
    }

    public function update(UpdateJurnalRequest $request, Jurnal $jurnal, SchoolLocationVerifier $locationVerifier): RedirectResponse
    {
        $this->authorizeJournal($jurnal, true);
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat diubah.');

        $data = $request->validated();
        $absensis = $data['absensi'] ?? [];
        unset($data['absensi'], $data['tanda_tangan']);
        $data = [...$data, ...$this->locationData($data, $locationVerifier)];
        unset($data['tanggal']);
        $this->validatePeriodOrder($data, $jurnal->tanggal->toDateString());
        DB::transaction(function () use ($data, $absensis, $jurnal): void {
            $jurnal = Jurnal::whereKey($jurnal->id)->lockForUpdate()->firstOrFail();
            abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat diubah.');
            $jurnal->update($data);
            $this->syncAbsensis($jurnal, $absensis);
        });

        return redirect()->route('jurnal.show', $jurnal)
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Jurnal $jurnal): RedirectResponse
    {
        $this->authorizeJournal($jurnal, true);
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat dihapus.');

        DB::transaction(function () use ($jurnal): void {
            $jurnal = Jurnal::whereKey($jurnal->id)->lockForUpdate()->firstOrFail();
            abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat dihapus.');
            $signature = $jurnal->tanda_tangan;
            $jurnal->delete();
            if ($signature) {
                DB::afterCommit(fn () => Storage::delete($signature));
            }
        });

        return redirect()->route('jurnal.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    public function verify(Request $request, Jurnal $jurnal): RedirectResponse
    {
        $this->authorizeJournal($jurnal);
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal sudah diverifikasi.');

        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($jurnal, $data): void {
            $jurnal = Jurnal::whereKey($jurnal->id)->lockForUpdate()->firstOrFail();
            abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal sudah diverifikasi.');
            $jurnal->update(['status_verifikasi' => $data['status']]);
            $jurnal->verifikasiJurnals()->create([
                'verifikator_id' => auth()->id(),
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
                'verified_at' => now(),
            ]);
        });

        return redirect()->route('jurnal.show', $jurnal)->with('success', 'Verifikasi jurnal berhasil disimpan.');
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function locationData(array $data, SchoolLocationVerifier $locationVerifier): array
    {
        if ($data['status_guru'] !== 'Hadir') {
            return [
                'latitude' => null,
                'longitude' => null,
                'location_accuracy' => null,
                'location_distance' => null,
                'location_verified_at' => null,
            ];
        }

        $verifiedLocation = $locationVerifier->verify(
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['location_accuracy'] ?? null,
        );

        return [
            'latitude' => $verifiedLocation['latitude'],
            'longitude' => $verifiedLocation['longitude'],
            'location_accuracy' => $verifiedLocation['accuracy'],
            'location_distance' => $verifiedLocation['distance'],
            'location_verified_at' => $verifiedLocation['verified_at'],
        ];
    }

    private function authorizeJournal(Jurnal $jurnal, bool $mustOwn = false): void
    {
        abort_unless(in_array(auth()->user()->role, ['guru', 'admin', 'sekretaris'], true), 403);

        if ($mustOwn || auth()->user()->role === 'guru') {
            abort_unless($jurnal->guru_id === $this->currentGuru()->id, 403);
        }

        if (auth()->user()->role === 'sekretaris') {
            abort_unless(auth()->user()->kelasSekretaris()->whereKey($jurnal->kelas_id)->exists(), 403);
        }
    }

    private function validatePeriodOrder(array $data, ?string $tanggal = null): void
    {
        $start = JamPelajaran::findOrFail($data['jam_mulai_id']);
        $end = JamPelajaran::findOrFail($data['jam_selesai_id']);
        $hari = Carbon::parse($tanggal ?? $data['tanggal'])->locale('id')->translatedFormat('l');
        [$startTime] = $start->timesForDay($hari);
        [, $endTime] = $end->timesForDay($hari);

        abort_if($startTime >= $endTime, 422, 'Jam selesai harus setelah jam mulai.');
    }

    /**
     * @param  array<int, array{siswa_id: int, status: string, catatan?: string|null}>  $absensis
     */
    private function syncAbsensis(Jurnal $jurnal, array $absensis): void
    {
        $students = Siswa::query()
            ->where('kelas_id', $jurnal->kelas_id)
            ->orderBy('nama_siswa')
            ->get(['id']);
        $studentIds = $students->pluck('id');
        $submittedAbsensis = collect($absensis)->keyBy('siswa_id');
        $dispensedStudentIds = Dispensasi::approvedForJournal($jurnal)->pluck('siswa_id');

        abort_unless($submittedAbsensis->keys()->diff($studentIds)->isEmpty(), 422, 'Siswa tidak termasuk dalam kelas jurnal ini.');
        abort_if(
            $submittedAbsensis->where('status', 'D')->keys()->diff($dispensedStudentIds)->isNotEmpty(),
            422,
            'Status dispensasi memerlukan persetujuan admin.'
        );

        $timestamp = now();
        $records = $students->map(function (Siswa $student) use ($jurnal, $submittedAbsensis, $timestamp, $dispensedStudentIds): array {
            $absensi = $submittedAbsensis->get($student->id, []);

            if ($dispensedStudentIds->contains($student->id)) {
                $absensi = ['status' => 'D', 'catatan' => 'Dispensasi disetujui.'];
            }

            return [
                'jurnal_id' => $jurnal->id,
                'siswa_id' => $student->id,
                'status' => $absensi['status'] ?? 'H',
                'catatan' => $absensi['catatan'] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->all();

        Absensi::where('jurnal_id', $jurnal->id)
            ->whereNotIn('siswa_id', $studentIds)
            ->delete();

        if ($records !== []) {
            Absensi::upsert($records, ['jurnal_id', 'siswa_id'], ['status', 'catatan', 'updated_at']);
        }

        $jurnal->update([
            'attendance_witnesses' => Absensi::query()
                ->where('jurnal_id', $jurnal->id)
                ->where('status', 'H')
                ->inRandomOrder()
                ->limit(3)
                ->pluck('siswa_id')
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Jurnal $jurnal = null): array
    {
        $sessions = Jadwal::sessionsForGuru($this->currentGuru(), now());
        $activeSessions = $sessions->where('active', true);
        $requestedSession = request()->integer('jadwal_id');
        $activeSession = $requestedSession && $activeSessions->count() > 1
            ? $activeSessions->firstWhere('id', $requestedSession)
            : ($activeSessions->count() === 1 ? $activeSessions->first() : $activeSessions->first());
        $kelasId = $jurnal?->kelas_id ?? $activeSession['kelas_id'] ?? null;
        $tanggal = $jurnal?->tanggal ?? today();

        return [
            'sessions' => $sessions,
            'activeSession' => $jurnal ? null : $activeSession,
            'existingJournal' => $activeSession
                ? Jurnal::query()
                    ->where('guru_id', $this->currentGuru()->id)
                    ->whereDate('tanggal', today())
                    ->where('kelas_id', $activeSession['kelas_id'])
                    ->where('mapel_id', $activeSession['mapel_id'])
                    ->where('jam_mulai_id', $activeSession['jam_mulai_id'])
                    ->where('jam_selesai_id', $activeSession['jam_selesai_id'])
                    ->first()
                : null,
            'scheduleConflict' => $activeSessions->count() > 1,
            'approvedDispensasis' => Dispensasi::with(['jamMulai', 'jamSelesai'])
                ->whereDate('tanggal', $tanggal)
                ->whereHas('siswa', fn ($query) => $query->where('kelas_id', $kelasId))
                ->where('status_akhir', 'Disetujui')
                ->get(['id', 'siswa_id', 'jam_mulai_id', 'jam_selesai_id']),
            'kelas' => Kelas::with(['siswas' => fn ($query) => $query->select(['id', 'kelas_id', 'nama_siswa', 'nis'])->orderBy('nama_siswa')])
                ->whereKey($kelasId)
                ->get(),
            'jamPelajarans' => JamPelajaran::where('is_active', true)->orderBy('jam_ke')->get(),
        ];
    }
}
