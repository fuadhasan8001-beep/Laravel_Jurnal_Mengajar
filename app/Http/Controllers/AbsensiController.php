<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\IzinSekolah;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $isAdmin = $request->user()->role === 'admin';
        $request->validate([
            'tanggal' => ['nullable', 'date'],
            'guru_search' => ['nullable', 'string', 'max:100'],
        ]);
        $selectedDate = $isAdmin
            ? ($request->input('tanggal') ?: today()->toDateString())
            : $request->input('tanggal');

        $query = Jurnal::with(['guru', 'absensis.siswa', 'kelas.siswas'])
            ->orderByDesc('tanggal');

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', $this->currentGuru()->id);
        }

        if (auth()->user()->role === 'sekretaris') {
            $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'));
        }

        $query
            ->when($selectedDate, fn ($builder) => $builder->whereDate('tanggal', $selectedDate))
            ->when($isAdmin && $request->filled('guru_search'), fn ($builder) => $builder->whereHas(
                'guru',
                fn ($guru) => $guru->where('nama_guru', 'like', '%'.$request->string('guru_search')->trim().'%')
            ))
            ->when($request->filled('tanggal_mulai'), fn ($builder) => $builder->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($builder) => $builder->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('guru_id'), fn ($builder) => $builder->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('mapel_id'), fn ($builder) => $builder->where('mapel_id', $request->integer('mapel_id')))
            ->when($request->filled('siswa_id'), fn ($builder) => $builder->whereHas('absensis', fn ($attendance) => $attendance->where('siswa_id', $request->integer('siswa_id'))));

        return view('absensi.index', [
            'jurnals' => $query->paginate(15)->withQueryString(),
            'gurus' => Guru::orderBy('nama_guru')->get(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
            'siswas' => Siswa::orderBy('nama_siswa')->get(),
            'isAdmin' => $isAdmin,
            'selectedDate' => $selectedDate,
            'guruSearch' => $request->string('guru_search')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('absensis')) {
            return $this->storeMultiple($request);
        }

        $data = $request->validate([
            'jurnal_id' => ['required', 'exists:jurnals,id'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:H,S,I,A,D'],
            'catatan' => ['nullable', 'string'],
        ]);

        $jurnal = Jurnal::with(['jamMulai', 'jamSelesai'])->findOrFail($data['jurnal_id']);
        $this->authorizeJurnal($jurnal);

        abort_unless(
            Siswa::whereKey($data['siswa_id'])->where('kelas_id', $jurnal->kelas_id)->exists(),
            422,
            'Siswa tidak termasuk dalam kelas jurnal ini.'
        );

        $hasApprovedDispensasi = Dispensasi::approvedForJournal($jurnal)->contains('siswa_id', $data['siswa_id']);
        abort_if($data['status'] === 'D' && ! $hasApprovedDispensasi, 422, 'Status dispensasi memerlukan persetujuan admin.');
        if ($hasApprovedDispensasi) {
            $data['status'] = 'D';
            $data['catatan'] = 'Dispensasi disetujui.';
        }
        $allDayIzin = IzinSekolah::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal', $jurnal->tanggal)->first();
        if ($allDayIzin) {
            $data['status'] = 'I';
            $data['catatan'] = 'Izin sekolah seharian berdasarkan surat orang tua.';
        }

        Absensi::updateOrCreate(
            [
                'jurnal_id' => $data['jurnal_id'],
                'siswa_id' => $data['siswa_id'],
            ],
            [
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
                'surat_izin_path' => $allDayIzin?->surat_izin_path,
            ]
        );

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    private function storeMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jurnal_id' => ['required', 'exists:jurnals,id'],
            'absensis' => ['required', 'array', 'min:1'],
            'absensis.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'absensis.*.status' => ['required', 'in:H,S,I,A,D'],
            'absensis.*.catatan' => ['nullable', 'string'],
        ]);

        $jurnal = Jurnal::with(['jamMulai', 'jamSelesai'])->findOrFail($data['jurnal_id']);
        $this->authorizeJurnal($jurnal);

        $records = collect($data['absensis']);
        $studentIds = $records->pluck('siswa_id');
        $studentsInClass = Siswa::where('kelas_id', $jurnal->kelas_id)
            ->whereIn('id', $studentIds)
            ->pluck('id');
        abort_unless($studentIds->diff($studentsInClass)->isEmpty(), 422, 'Siswa tidak termasuk dalam kelas jurnal ini.');

        $approvedDispensations = Dispensasi::approvedForJournal($jurnal)->pluck('siswa_id');
        $allDayIzin = IzinSekolah::whereDate('tanggal', $jurnal->tanggal)
            ->whereIn('siswa_id', $studentIds)->get()->keyBy('siswa_id');
        DB::transaction(function () use ($records, $jurnal, $approvedDispensations, $allDayIzin): void {
            foreach ($records as $record) {
                $studentId = (int) $record['siswa_id'];
                $hasApprovedDispensation = $approvedDispensations->contains($studentId);
                abort_if($record['status'] === 'D' && ! $hasApprovedDispensation, 422, 'Status dispensasi memerlukan persetujuan admin.');
                $allDayLeave = $allDayIzin->get($studentId);

                Absensi::updateOrCreate(
                    ['jurnal_id' => $jurnal->id, 'siswa_id' => $studentId],
                    [
                        'status' => $allDayLeave ? 'I' : ($hasApprovedDispensation ? 'D' : $record['status']),
                        'catatan' => $allDayLeave ? 'Izin sekolah seharian berdasarkan surat orang tua.' : ($hasApprovedDispensation ? 'Dispensasi disetujui.' : ($record['catatan'] ?? null)),
                        'surat_izin_path' => $allDayLeave?->surat_izin_path,
                    ]
                );
            }
        });

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function downloadParentLetter(Absensi $absensi): BinaryFileResponse
    {
        $absensi->load('jurnal');
        $this->authorizeJurnal($absensi->jurnal);

        abort_unless($absensi->surat_izin_path && Storage::exists($absensi->surat_izin_path), 404);

        return response()->download(Storage::path($absensi->surat_izin_path));
    }

    public function downloadSchoolPermissionLetter(Request $request, IzinSekolah $izinSekolah): BinaryFileResponse
    {
        $user = $request->user();
        $student = $izinSekolah->siswa;

        if ($user->role === 'guru') {
            $guruId = $this->currentGuru()->id;
            $hasJournalAccess = Jurnal::whereDate('tanggal', $izinSekolah->tanggal)
                ->where('kelas_id', $student->kelas_id)->where('guru_id', $guruId)->exists();
            $hasScheduleAccess = Jadwal::where('kelas_id', $student->kelas_id)
                ->where('guru_id', $guruId)
                ->where('hari', $izinSekolah->tanggal->locale('id')->translatedFormat('l'))
                ->where('is_active', true)->exists();
            abort_unless($hasJournalAccess || $hasScheduleAccess, 403);
        } elseif ($user->role === 'sekretaris') {
            abort_unless($user->kelasSekretaris()->whereKey($student->kelas_id)->exists(), 403);
        } elseif ($user->role === 'piket') {
            abort_unless($user->is_active, 403);
        } else {
            abort_unless(in_array($user->role, ['admin', 'waka'], true), 403);
        }

        abort_unless(Storage::exists($izinSekolah->surat_izin_path), 404);

        return response()->download(Storage::path($izinSekolah->surat_izin_path));
    }

    private function authorizeJurnal(Jurnal $jurnal): void
    {
        if (auth()->user()->role === 'guru') {
            abort_unless($jurnal->guru_id === $this->currentGuru()->id, 403);
        }

        if (auth()->user()->role === 'sekretaris') {
            abort_unless(auth()->user()->kelasSekretaris()->whereKey($jurnal->kelas_id)->exists(), 403);
        }
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }
}
