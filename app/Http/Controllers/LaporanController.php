<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function jurnal(Request $request): View
    {
        $jurnals = $this->jurnalQuery($request)->paginate(20)->withQueryString();

        return view('laporan.jurnal', [
            'jurnals' => $jurnals,
            'monitoringDate' => Carbon::parse($request->input('monitoring_date', today()->toDateString())),
            'monitoring' => $this->journalMonitoring($request),
            ...$this->filterData(),
        ]);
    }

    public function jurnalExport(Request $request): StreamedResponse
    {
        return $this->csv('rekap-jurnal.csv', [
            'Tanggal', 'Guru', 'Kelas', 'Mata Pelajaran', 'Jam', 'Materi', 'Status',
        ], $this->jurnalQuery($request)->lazy(500)->map(fn (Jurnal $jurnal) => [
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
        $query = $this->absensiQuery($request);
        $summary = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('laporan.absensi', [
            'absensis' => $query->paginate(30)->withQueryString(),
            'summary' => $summary,
            ...$this->filterData(),
            'siswas' => Siswa::orderBy('nama_siswa')->get(),
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

    private function jurnalQuery(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'status_verifikasi' => ['nullable', 'in:Menunggu,Disetujui,Ditolak'],
        ]);

        return Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai'])
            ->when(auth()->user()->role === 'guru', fn ($query) => $query->where('guru_id', $this->currentGuru()->id))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id')))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->where('kelas_id', $request->integer('kelas_id')))
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
            'mapel_id' => ['nullable', 'exists:mapels,id'],
            'siswa_id' => ['nullable', 'exists:siswas,id'],
            'status' => ['nullable', 'in:H,S,I,A,D'],
        ]);

        return Absensi::with(['siswa', 'jurnal.guru', 'jurnal.kelas', 'jurnal.mapel'])
            ->when(auth()->user()->role === 'guru', fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('guru_id', $this->currentGuru()->id)))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'))))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '>=', $request->date('tanggal_mulai'))))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '<=', $request->date('tanggal_selesai'))))
            ->when($request->filled('guru_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('guru_id', $request->integer('guru_id'))))
            ->when($request->filled('kelas_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('kelas_id', $request->integer('kelas_id'))))
            ->when($request->filled('mapel_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('mapel_id', $request->integer('mapel_id'))))
            ->when($request->filled('siswa_id'), fn ($query) => $query->where('siswa_id', $request->integer('siswa_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest();
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
        return [
            'gurus' => Guru::orderBy('nama_guru')->get(),
            'kelas' => Kelas::when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('id', auth()->user()->kelasSekretaris()->select('kelas.id')))
                ->orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
        ];
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function journalMonitoring(Request $request): Collection
    {
        $date = Carbon::parse($request->input('monitoring_date', today()->toDateString()));
        $weekday = $date->copy()->locale('id')->translatedFormat('l');
        $schedules = Jadwal::query()
            ->with(['guru', 'kelas', 'mapel', 'jamPelajaran'])
            ->where('hari', $weekday)
            ->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->when(auth()->user()->role === 'guru', fn ($query) => $query->where('guru_id', $this->currentGuru()->id))
            ->when(auth()->user()->role === 'sekretaris', fn ($query) => $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id')))
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($query) => $query->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('mapel_id'), fn ($query) => $query->where('mapel_id', $request->integer('mapel_id')))
            ->get();
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
