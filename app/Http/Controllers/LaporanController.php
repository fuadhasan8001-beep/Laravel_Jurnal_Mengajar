<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function jurnal(Request $request): View
    {
        $jurnals = $this->jurnalQuery($request)->paginate(20)->withQueryString();

        return view('laporan.jurnal', [
            'jurnals' => $jurnals,
            ...$this->filterData(),
        ]);
    }

    public function jurnalExport(Request $request): Response
    {
        $jurnals = $this->jurnalQuery($request)->get();

        return $this->csv('rekap-jurnal.csv', [
            ['Tanggal', 'Guru', 'Kelas', 'Mata Pelajaran', 'Jam', 'Materi', 'Status'],
            ...$jurnals->map(fn (Jurnal $jurnal) => [
                Carbon::parse($jurnal->tanggal)->toDateString(),
                $jurnal->guru->nama_guru,
                $jurnal->kelas->nama_kelas,
                $jurnal->mapel->nama_mapel,
                $jurnal->jamMulai->jam_ke.' - '.$jurnal->jamSelesai->jam_ke,
                $jurnal->materi,
                $jurnal->status_verifikasi,
            ])->all(),
        ]);
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

    public function absensiExport(Request $request): Response
    {
        $absensis = $this->absensiQuery($request)->get();

        return $this->csv('rekap-absensi.csv', [
            ['Tanggal', 'Siswa', 'Kelas', 'Guru', 'Mata Pelajaran', 'Status', 'Catatan'],
            ...$absensis->map(fn (Absensi $absensi) => [
                $absensi->jurnal->tanggal->format('Y-m-d'),
                $absensi->siswa->nama_siswa,
                $absensi->jurnal->kelas->nama_kelas,
                $absensi->jurnal->guru->nama_guru,
                $absensi->jurnal->mapel->nama_mapel,
                $absensi->status,
                $absensi->catatan,
            ])->all(),
        ]);
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

    public function dispensasiExport(Request $request): Response
    {
        $dispensasis = $this->dispensasiQuery($request)->get();

        return $this->csv('rekap-dispensasi.csv', [
            ['Tanggal', 'Siswa', 'Kelas', 'Jam', 'Status', 'Alasan'],
            ...$dispensasis->map(fn (Dispensasi $dispensasi) => [
                Carbon::parse($dispensasi->tanggal)->toDateString(),
                $dispensasi->siswa->nama_siswa,
                $dispensasi->siswa->kelas->nama_kelas,
                $dispensasi->jamMulai->jam_ke.' - '.$dispensasi->jamSelesai->jam_ke,
                $dispensasi->status_akhir,
                $dispensasi->alasan,
            ])->all(),
        ]);
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
        ]);

        return Absensi::with(['siswa', 'jurnal.guru', 'jurnal.kelas', 'jurnal.mapel'])
            ->when(auth()->user()->role === 'guru', fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('guru_id', $this->currentGuru()->id)))
            ->when($request->filled('tanggal_mulai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '>=', $request->date('tanggal_mulai'))))
            ->when($request->filled('tanggal_selesai'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->whereDate('tanggal', '<=', $request->date('tanggal_selesai'))))
            ->when($request->filled('guru_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('guru_id', $request->integer('guru_id'))))
            ->when($request->filled('kelas_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('kelas_id', $request->integer('kelas_id'))))
            ->when($request->filled('mapel_id'), fn ($query) => $query->whereHas('jurnal', fn ($journal) => $journal->where('mapel_id', $request->integer('mapel_id'))))
            ->when($request->filled('siswa_id'), fn ($query) => $query->where('siswa_id', $request->integer('siswa_id')))
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
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
        ];
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function csv(string $filename, array $rows): Response
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return response()->make($contents, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
