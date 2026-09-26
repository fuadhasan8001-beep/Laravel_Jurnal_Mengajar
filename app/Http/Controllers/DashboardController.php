<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'totalGuru' => Guru::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'totalMapel' => Mapel::count(),
            'totalJurnal' => Jurnal::count(),
            'totalJadwal' => Jadwal::where('is_active', true)->count(),
            'jurnalHariIni' => Jurnal::whereDate('tanggal', today())->count(),
            'jurnalBulanIni' => Jurnal::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'jurnalTanpaTujuan' => Jurnal::where(fn ($query) => $query->whereNull('tujuan_pembelajaran')->orWhere('tujuan_pembelajaran', ''))->count(),
            'jurnalMenunggu' => Jurnal::where('status_verifikasi', 'Menunggu')->count(),
            'menungguVerifikasi' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
            'disetujuiBulanIni' => Dispensasi::where('status_akhir', 'Disetujui')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
            'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
        ]);
    }

    public function guru(): View
    {
        $now = now();
        $hariIni = $now->copy()->locale('id')->translatedFormat('l');
        $guru = Guru::where('user_id', auth()->id())->first();
        $jadwalHariIni = Jadwal::with(['kelas', 'mapel', 'jamPelajaran'])
            ->where('guru_id', $guru?->id)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->where('hari', $hariIni)
            ->where('is_active', true)
            ->get()
            ->sortBy('jamPelajaran.jam_ke')
            ->values();
        $sessions = $guru ? Jadwal::sessionsForGuru($guru, $now) : collect();
        $activeJadwalIds = $sessions->where('active', true)
            ->flatMap(fn (array $session): array => $session['jadwal_ids'])
            ->unique()
            ->values();

        return view('dashboard.guru', [
            'jurnalMingguIni' => Jurnal::where('guru_id', $guru?->id)->whereBetween('tanggal', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count(),
            'kelasAktif' => Jurnal::where('guru_id', $guru?->id)->distinct('kelas_id')->count('kelas_id'),
            'siswaTerpantau' => $guru
                ? Siswa::whereIn('kelas_id', Jadwal::query()
                    ->where('guru_id', $guru->id)
                    ->where('is_active', true)
                    ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
                    ->select('kelas_id')
                    ->distinct())
                    ->count()
                : 0,
            'jadwalHariIni' => $jadwalHariIni,
            'jurnalHariIni' => Jurnal::with(['jamMulai', 'jamSelesai'])->where('guru_id', $guru?->id)->whereDate('tanggal', $now->toDateString())->get(),
            'jurnalTerbaru' => Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai', 'absensis.siswa'])
                ->where('guru_id', $guru?->id)->latest('tanggal')->first(),
            'activeJadwalIds' => $activeJadwalIds,
        ]);
    }

    public function siswa(): View
    {
        $siswa = Siswa::where('user_id', auth()->id())->first();
        $dispensasi = $siswa ? Dispensasi::where('siswa_id', $siswa->id) : Dispensasi::whereRaw('1 = 0');

        return view('dashboard.siswa', [
            'menunggu' => (clone $dispensasi)->where('status_akhir', 'Menunggu')->count(),
            'disetujui' => (clone $dispensasi)->where('status_akhir', 'Disetujui')->count(),
            'ditolak' => (clone $dispensasi)->where('status_akhir', 'Ditolak')->count(),
            'totalPengajuan' => $dispensasi->count(),
        ]);
    }

    public function sekretaris(): View
    {
        $kelasSekretaris = auth()->user()->kelasSekretaris()->orderBy('nama_kelas')->get();
        $kelasIds = $kelasSekretaris->modelKeys();

        return view('dashboard.sekretaris', [
            'kelasSekretaris' => $kelasSekretaris,
            'jurnalTercatat' => Jurnal::whereIn('kelas_id', $kelasIds)->count(),
            'jurnalLengkap' => Jurnal::whereIn('kelas_id', $kelasIds)->whereNotNull('materi')->where('materi', '!=', '')->count(),
            'jurnalMenunggu' => Jurnal::whereIn('kelas_id', $kelasIds)->where('status_verifikasi', 'Menunggu')->count(),
            'kelasAktif' => Jurnal::whereIn('kelas_id', $kelasIds)->distinct('kelas_id')->count('kelas_id'),
            'jurnalPerluVerifikasi' => Jurnal::with(['guru', 'kelas', 'mapel'])->whereIn('kelas_id', $kelasIds)
                ->where('status_verifikasi', 'Menunggu')->latest('tanggal')->limit(5)->get(),
        ]);
    }

    public function piket(): View
    {
        abort_unless(auth()->user()->isPiketHariIni(), 403);

        return view('dashboard.piket', [
            'antrianBaru' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
            'diverifikasiHariIni' => Dispensasi::whereDate('verified_piket_at', today())->count(),
            'totalBulanIni' => Dispensasi::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
            'pengajuanMenunggu' => Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])->where('status_akhir', 'Menunggu')->latest()->limit(5)->get(),
        ]);
    }
}
