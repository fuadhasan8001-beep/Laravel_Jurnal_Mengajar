<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NationalHoliday;
use App\Models\SchoolEvent;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function jurnal(Request $request): View
    {
        return $this->journalReport($request);
    }

    public function jurnalPiket(Request $request): View
    {
        abort_unless($request->user()->isPiketHariIni(), 403);

        return $this->journalReport($request, true);
    }

    private function journalReport(Request $request, bool $includeAllTeachers = false): View
    {
        $request->validate(['tampilan' => ['nullable', 'in:kelas,guru']]);
        $jurnals = $this->jurnalQuery($request, $includeAllTeachers)->paginate(20)->withQueryString();
        $groupBy = $request->input('tampilan', 'kelas') === 'guru' ? 'guru_id' : 'kelas_id';
        $groupLabel = $groupBy === 'guru_id' ? 'guru' : 'kelas';
        $journalGroups = $jurnals->getCollection()->groupBy($groupBy);

        return view('laporan.jurnal', [
            'jurnals' => $jurnals,
            'journalGroups' => $journalGroups,
            'groupLabel' => $groupLabel,
            'monitoringDate' => Carbon::parse($request->input('monitoring_date', today()->toDateString())),
            'monitoring' => $this->journalMonitoring($request, $includeAllTeachers),
            ...$this->filterData(),
        ]);
    }

    public function jurnalExport(Request $request): StreamedResponse
    {
        return $this->exportJurnals($request);
    }

    public function jurnalPiketExport(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isPiketHariIni(), 403);

        return $this->exportJurnals($request, true);
    }

    private function exportJurnals(Request $request, bool $includeAllTeachers = false): StreamedResponse
    {
        return $this->csv('rekap-jurnal.csv', [
            'Tanggal', 'Guru', 'Kelas', 'Mata Pelajaran', 'Jam', 'Materi', 'Status',
        ], $this->jurnalQuery($request, $includeAllTeachers)->lazy(500)->map(fn (Jurnal $jurnal) => [
            Carbon::parse($jurnal->tanggal)->toDateString(),
            $jurnal->guru->nama_guru,
            $jurnal->kelas->nama_kelas,
            $jurnal->mapel->nama_mapel,
            $jurnal->jamMulai->jam_ke.' - '.$jurnal->jamSelesai->jam_ke,
            $jurnal->materi,
            $jurnal->status_verifikasi,
        ]));
    }

    public function absensi(Request $request): View
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        if (! $request->filled('tanggal_mulai') && ! $request->filled('tanggal_selesai')) {
            $request->merge([
                'tanggal_mulai' => today()->startOfMonth()->toDateString(),
                'tanggal_selesai' => today()->endOfMonth()->toDateString(),
            ]);
        } elseif (! $request->filled('tanggal_mulai')) {
            $request->merge(['tanggal_mulai' => Carbon::parse($request->input('tanggal_selesai'))->startOfMonth()->toDateString()]);
        } elseif (! $request->filled('tanggal_selesai')) {
            $request->merge(['tanggal_selesai' => Carbon::parse($request->input('tanggal_mulai'))->endOfMonth()->toDateString()]);
        }

        $query = $this->absensiQuery($request);
        $summary = (clone $query)
            ->reorder()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $studentAttendance = (clone $query)
            ->reorder()
            ->join('jurnals as report_jurnals', 'report_jurnals.id', '=', 'absensis.jurnal_id')
            ->selectRaw('report_jurnals.kelas_id as kelas_id, absensis.status as status, count(*) as total')
            ->groupBy('report_jurnals.kelas_id', 'absensis.status')
            ->get();
        $teacherAttendance = $this->statisticsJournalQuery($request)
            ->selectRaw('kelas_id, status_guru as status, count(*) as total')
            ->groupBy('kelas_id', 'status_guru')
            ->get();
        $classIds = $studentAttendance->pluck('kelas_id')->merge($teacherAttendance->pluck('kelas_id'))->unique();
        $classes = Kelas::query()->whereIn('id', $classIds)->orderBy('nama_kelas')->get();
        $attendanceGroups = $classes->groupBy(fn (Kelas $class): string => $this->jurusanFromClassName($class))
            ->map(function (Collection $jurusanClasses) use ($studentAttendance, $teacherAttendance): array {
                $classStats = $jurusanClasses->map(function (Kelas $class) use ($studentAttendance, $teacherAttendance): array {
                    $studentCounts = $this->attendanceCounts($studentAttendance->where('kelas_id', $class->id));
                    $teacherCounts = $this->attendanceCounts($teacherAttendance->where('kelas_id', $class->id));

                    return [
                        'kelas' => $class,
                        'siswa' => $studentCounts,
                        'siswa_persen' => $this->attendancePercentages($studentCounts),
                        'siswa_hadir_persen' => $this->studentAttendanceRate($studentCounts),
                        'guru' => $teacherCounts,
                        'guru_persen' => $this->attendancePercentages($teacherCounts),
                    ];
                });
                $studentCounts = $this->attendanceCounts($studentAttendance->whereIn('kelas_id', $jurusanClasses->modelKeys()));
                $teacherCounts = $this->attendanceCounts($teacherAttendance->whereIn('kelas_id', $jurusanClasses->modelKeys()));

                return [
                    'classes' => $classStats,
                    'siswa' => $studentCounts,
                    'siswa_persen' => $this->attendancePercentages($studentCounts),
                    'siswa_hadir_persen' => $this->studentAttendanceRate($studentCounts),
                    'guru' => $teacherCounts,
                    'guru_persen' => $this->attendancePercentages($teacherCounts),
                ];
            });

        $studentSummary = $this->attendanceCounts($summary->map(fn ($total, $status) => (object) ['status' => $status, 'total' => $total])->values());
        $teacherSummary = $this->attendanceCounts($teacherAttendance);
        $rankingRequest = clone $request;
        $rankingRequest->query->remove('status');
        $rankingAbsensi = $this->absensiQuery($rankingRequest);
        $studentAbsenceLeaders = (clone $rankingAbsensi)
            ->join('siswas as statistics_siswas', 'statistics_siswas.id', '=', 'absensis.siswa_id')
            ->select('absensis.siswa_id', 'statistics_siswas.nama_siswa')
            ->selectRaw("SUM(CASE WHEN absensis.status IN ('S', 'I', 'A') THEN 1 ELSE 0 END) as absence_count")
            ->selectRaw("SUM(CASE WHEN absensis.status IN ('H', 'S', 'I', 'A') THEN 1 ELSE 0 END) as eligible_sessions")
            ->groupBy('absensis.siswa_id', 'statistics_siswas.nama_siswa')
            ->havingRaw("SUM(CASE WHEN absensis.status IN ('S', 'I', 'A') THEN 1 ELSE 0 END) > 0")
            ->reorder()->orderByDesc('absence_count')->orderBy('statistics_siswas.nama_siswa')->limit(10)->get();
        $teacherAbsenceLeaders = (clone $this->statisticsJournalQuery($request))
            ->join('gurus as statistics_gurus', 'statistics_gurus.id', '=', 'jurnals.guru_id')
            ->select('jurnals.guru_id', 'statistics_gurus.nama_guru')
            ->selectRaw("SUM(CASE WHEN jurnals.status_guru IN ('Izin', 'Sakit', 'Tanpa Keterangan') THEN 1 ELSE 0 END) as absence_count")
            ->selectRaw('COUNT(*) as recorded_journals')
            ->groupBy('jurnals.guru_id', 'statistics_gurus.nama_guru')
            ->havingRaw("SUM(CASE WHEN jurnals.status_guru IN ('Izin', 'Sakit', 'Tanpa Keterangan') THEN 1 ELSE 0 END) > 0")
            ->reorder()->orderByDesc('absence_count')->orderBy('statistics_gurus.nama_guru')->limit(10)->get();
        $selectedClass = $request->filled('kelas_id')
            ? $this->statisticsClassQuery()->whereKey($request->integer('kelas_id'))->first()
            : null;
        $selectedProgram = $request->string('program')->toString();
        $statisticsScopeLabel = match (auth()->user()->role) {
            'admin', 'waka' => 'Statistik sekolah',
            'guru' => auth()->user()->kelasWali()->exists() ? 'Statistik kelas yang diampu dan kelas wali' : 'Statistik kelas yang diampu',
            'sekretaris' => 'Statistik kelas penugasan sekretaris',
            default => 'Statistik kelas yang dapat diakses',
        };
        if ($selectedProgram !== '') {
            $statisticsScopeLabel = 'Statistik program '.$selectedProgram;
        }
        if ($selectedClass && ($selectedProgram === '' || $this->jurusanFromClassName($selectedClass) === $selectedProgram)) {
            $statisticsScopeLabel = 'Statistik kelas '.$selectedClass->nama_kelas;
        }
        $periodStart = Carbon::parse($request->input('tanggal_mulai'));
        $periodEnd = Carbon::parse($request->input('tanggal_selesai'));

        return view('laporan.absensi', [
            'absensis' => $query->paginate(30)->withQueryString(),
            'summary' => $summary,
            'studentSummary' => $studentSummary,
            'studentSummaryPercentages' => $this->attendancePercentages($studentSummary),
            'teacherSummary' => $teacherSummary,
            'teacherSummaryPercentages' => $this->attendancePercentages($teacherSummary),
            'studentAttendanceRate' => $this->studentAttendanceRate($studentSummary),
            'studentAbsenceLeaders' => $studentAbsenceLeaders,
            'teacherAbsenceLeaders' => $teacherAbsenceLeaders,
            'studentStatusList' => collect($studentSummary)->keys()->map(fn (string $status) => $this->studentStatusLabel($status))->filter()->values()->all(),
            'teacherStatusList' => collect($teacherSummary)->keys()->map(fn (string $status) => $this->teacherStatusLabel($status))->filter()->values()->all(),
            'attendanceGroups' => $attendanceGroups,
            'selectedProgram' => $selectedProgram,
            'selectedClass' => $selectedClass,
            'statisticsScopeLabel' => $statisticsScopeLabel,
            'periodLabel' => $periodStart->locale('id')->translatedFormat('d F Y').' - '.$periodEnd->locale('id')->translatedFormat('d F Y'),
            'hasAttendanceData' => $studentAttendance->isNotEmpty() || $teacherAttendance->isNotEmpty(),
            'hasStudentAttendance' => $studentAttendance->isNotEmpty(),
            'hasTeacherAttendance' => $teacherAttendance->isNotEmpty(),
            ...$this->filterData(),
            'siswas' => Siswa::when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id')))
                ->when(auth()->user()->role === 'guru', fn ($query) => $query->whereIn('kelas_id', Jadwal::where('guru_id', $this->currentGuru()->id)->select('kelas_id')->distinct()))
                ->orderBy('nama_siswa')->get(),
        ]);
    }

    public function absensiExport(Request $request): StreamedResponse
    {
        return $this->csv('rekap-absensi.csv', [
            'Tanggal', 'Siswa', 'Kelas', 'Guru', 'Mata Pelajaran', 'Status', 'Catatan',
        ], $this->absensiQuery($request)->lazy(500)->map(fn (Absensi $absensi) => [
            $absensi->jurnal->tanggal->format('Y-m-d'),
            $absensi->siswa->nama_siswa,
            $absensi->jurnal->kelas->nama_kelas,
            $absensi->jurnal->guru->nama_guru,
            $absensi->jurnal->mapel->nama_mapel,
            ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'][$absensi->status] ?? $absensi->status,
            $absensi->catatan,
        ]));
    }

    public function dispensasi(Request $request): View
    {
        $query = $this->dispensasiQuery($request);
        $summary = (clone $query)
            ->selectRaw('status_akhir, count(*) as total')
            ->groupBy('status_akhir')
            ->pluck('total', 'status_akhir');

        return view('laporan.dispensasi', [
            'dispensasis' => $query->paginate(20)->withQueryString(),
            'summary' => $summary,
            ...$this->filterData(),
        ]);
    }

    public function dispensasiExport(Request $request): StreamedResponse
    {
        return $this->csv('rekap-dispensasi.csv', [
            'Tanggal', 'Siswa', 'Kelas', 'Jam', 'Status', 'Alasan',
        ], $this->dispensasiQuery($request)->lazy(500)->map(fn (Dispensasi $dispensasi) => [
            Carbon::parse($dispensasi->tanggal)->toDateString(),
            $dispensasi->siswa->nama_siswa,
            $dispensasi->siswa->kelas->nama_kelas,
            $dispensasi->jamMulai->jam_ke.' - '.$dispensasi->jamSelesai->jam_ke,
            $dispensasi->status_akhir,
            $dispensasi->alasan,
        ]));
    }

    private function jurnalQuery(Request $request, bool $includeAllTeachers = false)
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'program' => ['nullable', 'string', 'max:100'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'status_verifikasi' => ['nullable', 'in:Menunggu,Disetujui,Ditolak'],
        ]);

        $programClassIds = $this->classIdsForProgram($request->input('program'));

        return Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai', 'absensis.siswa', 'schoolEvent'])
            ->when(auth()->user()->role === 'guru' && ! $includeAllTeachers, fn ($query) => $query->where('guru_id', $this->currentGuru()->id))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id')))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->where('kelas_id', $request->integer('kelas_id')))
            ->when($programClassIds !== null, fn ($query) => $query->whereIn('kelas_id', $programClassIds))
            ->when($request->filled('mapel_id'), fn ($query) => $query->where('mapel_id', $request->integer('mapel_id')))
            ->when($request->filled('status_verifikasi'), fn ($query) => $query->where('status_verifikasi', $request->string('status_verifikasi')))
            ->latest('tanggal');
    }

    private function absensiQuery(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'program' => ['nullable', 'string', 'max:100'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'siswa_id' => ['nullable', 'exists:siswas,id'],
            'status' => ['nullable', 'in:H,S,I,A,D'],
        ]);

        $programClassIds = $this->classIdsForProgram($request->input('program'));

        return Absensi::with(['siswa', 'jurnal.guru', 'jurnal.kelas', 'jurnal.mapel'])
            ->when(auth()->user()->role === 'guru', fn ($query) => $query->whereHas('jurnal', function ($journal): void {
                $user = auth()->user();
                $journal->where('guru_id', $this->currentGuru()->id)
                    ->orWhere(function ($waliJournals) use ($user): void {
                        $waliJournals->whereIn('kelas_id', $user->kelasWali()->select('kelas.id'))
                            ->where('status_verifikasi', 'Disetujui')
                            ->whereHas('verifikasiJurnals', fn ($verification) => $verification
                                ->where('status', 'Disetujui')
                                ->whereHas('verifikator', fn ($verifikator) => $verifikator->where('role', 'sekretaris')));
                    });
            }))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'))))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '>=', $request->date('tanggal_mulai'))))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '<=', $request->date('tanggal_selesai'))))
            ->when($request->filled('guru_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('guru_id', $request->integer('guru_id'))))
            ->when($request->filled('kelas_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('kelas_id', $request->integer('kelas_id'))))
            ->when($programClassIds !== null, fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereIn('kelas_id', $programClassIds)))
            ->when($request->filled('mapel_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('mapel_id', $request->integer('mapel_id'))))
            ->when($request->filled('siswa_id'), fn ($query) => $query->where('siswa_id', $request->integer('siswa_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('absensis.created_at');
    }

    private function dispensasiQuery(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'status_akhir' => ['nullable', 'in:Menunggu,Disetujui,Ditolak'],
        ]);

        return Dispensasi::with(['siswa.kelas', 'jamMulai', 'jamSelesai'])
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereHas('siswa', fn ($student) => $student->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'))))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->whereHas('siswa', fn ($student) => $student->where('kelas_id', $request->integer('kelas_id'))))
            ->when($request->filled('status_akhir'), fn ($query) => $query->where('status_akhir', $request->string('status_akhir')))
            ->latest('tanggal');
    }

    /**
     * @return array<string, mixed>
     */
    private function filterData(): array
    {
        $user = auth()->user();
        $guruId = $user->role === 'guru' ? $this->currentGuru()->id : null;

        return [
            'gurus' => Guru::when($guruId, fn ($query) => $query->whereKey($guruId))->orderBy('nama_guru')->get(),
            'kelas' => $this->statisticsClassQuery()->orderBy('nama_kelas')->get(),
            'mapels' => Mapel::when($guruId, fn ($query) => $query->whereHas('gurus', fn ($teacher) => $teacher->whereKey($guruId)))->orderBy('nama_mapel')->get(),
            'programs' => $this->statisticsClassQuery()->get()->map(fn (Kelas $class): string => $this->jurusanFromClassName($class))->unique()->sort()->values(),
        ];
    }

    private function statisticsJournalQuery(Request $request): Builder
    {
        $user = auth()->user();
        $programClassIds = $this->classIdsForProgram($request->input('program'));

        return Jurnal::query()
            ->when($user->role === 'guru', function ($query) use ($user): void {
                $query->where(function ($scope) use ($user): void {
                    $scope->where('guru_id', $this->currentGuru()->id)
                        ->orWhere(function ($waliJournals) use ($user): void {
                            $waliJournals->whereIn('kelas_id', $user->kelasWali()->select('kelas.id'))
                                ->where('status_verifikasi', 'Disetujui')
                                ->whereHas('verifikasiJurnals', fn ($verification) => $verification
                                    ->where('status', 'Disetujui')
                                    ->whereHas('verifikator', fn ($verifikator) => $verifikator->where('role', 'sekretaris')));
                        });
                });
            })
            ->when($user->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', $user->kelasSekretaris()->select('kelas.id')))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->where('kelas_id', $request->integer('kelas_id')))
            ->when($programClassIds !== null, fn ($query) => $query->whereIn('kelas_id', $programClassIds))
            ->when($request->filled('mapel_id'), fn ($query) => $query->where('mapel_id', $request->integer('mapel_id')));
    }

    private function statisticsClassQuery(): Builder
    {
        $user = auth()->user();
        $guruId = $user->role === 'guru' ? $this->currentGuru()->id : null;

        return Kelas::query()
            ->when($user->role === 'sekretaris', fn ($query) => $query->whereIn('id', $user->kelasSekretaris()->select('kelas.id')))
            ->when($guruId, fn ($query) => $query->where(function ($classes) use ($guruId, $user): void {
                $classes->whereHas('jadwals', fn ($schedule) => $schedule->where('guru_id', $guruId))
                    ->orWhereIn('id', $user->kelasWali()->select('kelas.id'));
            }));
    }

    /** @param array<string, int> $counts */
    private function studentAttendanceRate(array $counts): ?float
    {
        $eligibleSessions = array_sum($counts) - ($counts['Dispensasi'] ?? 0);
        if ($eligibleSessions === 0) {
            return null;
        }

        return round(($counts['Hadir'] ?? 0) * 100 / $eligibleSessions, 1);
    }

    /** @return array<int, int>|null */
    private function classIdsForProgram(?string $program): ?array
    {
        if ($program === null || $program === '') {
            return null;
        }

        return Kelas::query()->get()->filter(fn (Kelas $class): bool => $this->jurusanFromClassName($class) === $program)->modelKeys();
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /** @param Collection<int, object> $rows
     * @return array<string, int>
     */
    private function attendanceCounts(Collection $rows): array
    {
        $counts = [];

        foreach ($rows as $row) {
            $label = $this->normalizeStatusKey($row->status, $row->status ?? '');
            $counts[$label] = ($counts[$label] ?? 0) + (int) $row->total;
        }

        return $counts;
    }

    private function normalizeStatusKey(mixed $status, mixed $fallback = null): string
    {
        return match (strval($status)) {
            'H' => 'Hadir',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpa',
            'D' => 'Dispensasi',
            'Hadir', 'Izin', 'Sakit', 'Dinas', 'Tanpa Keterangan', 'Alpa', 'Dispensasi' => strval($status),
            default => is_string($fallback) && $fallback !== '' ? $fallback : 'Lainnya',
        };
    }

    private function studentStatusLabel(string $status): string
    {
        return match ($status) {
            'H', 'Hadir' => 'Hadir',
            'S', 'Sakit' => 'Sakit',
            'I', 'Izin' => 'Izin',
            'A', 'Alpa' => 'Alpa',
            'D', 'Dispensasi' => 'Dispensasi',
            default => $status,
        };
    }

    private function teacherStatusLabel(string $status): string
    {
        return match ($status) {
            'Hadir' => 'Hadir',
            'Izin' => 'Izin',
            'Sakit' => 'Sakit',
            'Dinas' => 'Dinas',
            'Tanpa Keterangan' => 'Tanpa Keterangan',
            default => $status,
        };
    }

    /** @param array<string, int> $counts
     * @return array<string, float>
     */
    private function attendancePercentages(array $counts): array
    {
        $total = array_sum($counts);

        return collect($counts)->map(fn (int $count): float => $total > 0 ? round($count * 100 / $total, 1) : 0.0)->all();
    }

    private function jurusanFromClassName(Kelas $class): string
    {
        $parts = preg_split('/\s+/', trim($class->nama_kelas), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($parts !== [] && Str::lower($parts[0]) === Str::lower($class->tingkat)) {
            array_shift($parts);
        }
        if ($parts !== [] && preg_match('/^(\d+|[A-Z])$/i', $parts[array_key_last($parts)]) === 1) {
            array_pop($parts);
        }

        return $parts === [] ? 'Umum' : implode(' ', $parts);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function journalMonitoring(Request $request, bool $includeAllTeachers = false): Collection
    {
        $date = Carbon::parse($request->input('monitoring_date', today()->toDateString()));
        if (NationalHoliday::whereDate('holiday_date', $date)->exists()) {
            return collect();
        }

        $weekday = $date->copy()->locale('id')->translatedFormat('l');
        $schedules = Jadwal::query()
            ->with(['guru', 'kelas', 'mapel', 'jamPelajaran'])
            ->where('hari', $weekday)
            ->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->when(auth()->user()->role === 'guru' && ! $includeAllTeachers, fn ($query) => $query->where('guru_id', $this->currentGuru()->id))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id')))
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('mapel_id'), fn ($query) => $query->where('mapel_id', $request->integer('mapel_id')))
            ->get();

        $overrideUserIds = SchoolEvent::whereDate('event_date', $date)
            ->whereIn('attendance_mode', ['morning_evening', 'once', 'none'])
            ->get()
            ->flatMap(fn (SchoolEvent $event) => $event->targetUsers()->pluck('users.id'))
            ->unique();
        $dismissalEvents = SchoolEvent::whereDate('event_date', $date)
            ->whereNotNull('early_dismissal_at')
            ->where('attendance_mode', 'normal')
            ->get();
        $dismissalByUser = [];
        foreach ($dismissalEvents as $event) {
            foreach ($event->targetUsers()->pluck('users.id') as $userId) {
                $dismissalByUser[$userId] = min($dismissalByUser[$userId] ?? '23:59:59', $event->early_dismissal_at);
            }
        }
        $schedules = $schedules->reject(function (Jadwal $schedule) use ($weekday, $overrideUserIds, $dismissalByUser): bool {
            $userId = $schedule->guru->user_id;
            if ($overrideUserIds->contains($userId)) {
                return true;
            }

            [$start] = $schedule->jamPelajaran->timesForDay($weekday);

            return isset($dismissalByUser[$userId]) && $start >= $dismissalByUser[$userId];
        });
        $journals = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->whereDate('tanggal', $date)
            ->whereIn('guru_id', $schedules->pluck('guru_id')->unique())
            ->get()
            ->groupBy(fn (Jurnal $jurnal): string => $jurnal->guru_id.'-'.$jurnal->kelas_id.'-'.$jurnal->mapel_id);

        return $schedules->map(function (Jadwal $schedule) use ($journals, $weekday): array {
            $journal = $journals->get($schedule->guru_id.'-'.$schedule->kelas_id.'-'.$schedule->mapel_id, collect())
                ->first(fn (Jurnal $candidate): bool => $candidate->jamMulai->jam_ke <= $schedule->jamPelajaran->jam_ke
                    && $candidate->jamSelesai->jam_ke >= $schedule->jamPelajaran->jam_ke);
            [$start, $end] = $schedule->jamPelajaran->timesForDay($weekday);

            return [
                'guru' => $schedule->guru->nama_guru,
                'kelas' => $schedule->kelas->nama_kelas,
                'mapel' => $schedule->mapel->nama_mapel,
                'jam' => substr($start, 0, 5).' - '.substr($end, 0, 5),
                'status' => match ($journal?->status_guru) {
                    'Hadir' => 'Guru hadir',
                    'Izin' => 'Guru izin',
                    'Sakit' => 'Guru sakit',
                    default => $journal ? 'Jurnal sudah dibuat' : 'Belum mengisi jurnal',
                },
            ];
        })->values();
    }

    /**
     * @param  array<int, string>  $header
     * @param  iterable<array<int, mixed>>  $rows
     */
    private function csv(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array_map($this->safeCsvCell(...), $header));

            foreach ($rows as $row) {
                fputcsv($handle, array_map($this->safeCsvCell(...), $row));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvCell(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^[\t\r\n ]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
