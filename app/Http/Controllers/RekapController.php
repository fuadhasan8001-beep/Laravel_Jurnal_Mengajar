<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapController extends Controller
{
    private function dataFilter()
    {
        return [
            'gurus'  => Guru::orderBy('nama')->get(),
            'kelass' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
        ];
    }

    private function terapkanFilter($query, Request $request)
    {
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }
        return $query;
    }

    public function harian(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $jurnals = $this->terapkanFilter(Jurnal::with(['guru', 'kelas', 'mapel']), $request)
            ->whereDate('tanggal', $tanggal)
            ->get();

        $statistik = [
            'total' => $jurnals->count(),
            'guru'  => $jurnals->pluck('guru_id')->unique()->count(),
            'kelas' => $jurnals->pluck('kelas_id')->unique()->count(),
            'mapel' => $jurnals->pluck('mapel_id')->unique()->count(),
        ];

        return view('rekap.harian', array_merge(
            compact('jurnals', 'tanggal', 'statistik'),
            $this->dataFilter()
        ));
    }

    public function mingguan(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $senin  = Carbon::parse($tanggal)->startOfWeek();
        $minggu = $senin->copy()->endOfWeek();

        Carbon::setLocale('id');

        $jurnals = $this->terapkanFilter(Jurnal::with(['guru', 'kelas', 'mapel']), $request)
            ->whereBetween('tanggal', [$senin->format('Y-m-d'), $minggu->format('Y-m-d')])
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');

        $semua = $jurnals->flatten();
        $statistik = [
            'total' => $semua->count(),
            'guru'  => $semua->pluck('guru_id')->unique()->count(),
            'kelas' => $semua->pluck('kelas_id')->unique()->count(),
            'mapel' => $semua->pluck('mapel_id')->unique()->count(),
        ];

        return view('rekap.mingguan', array_merge(
            compact('jurnals', 'tanggal', 'senin', 'minggu', 'statistik'),
            $this->dataFilter()
        ));
    }

    public function bulanan(Request $request)
    {
        $bulan = $request->input('bulan', date('Y-m'));
        [$tahun, $nomorBulan] = explode('-', $bulan);

        Carbon::setLocale('id');

        $jurnals = $this->terapkanFilter(Jurnal::with(['guru', 'kelas', 'mapel']), $request)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan)
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');

        $semua = $jurnals->flatten();
        $statistik = [
            'total' => $semua->count(),
            'guru'  => $semua->pluck('guru_id')->unique()->count(),
            'kelas' => $semua->pluck('kelas_id')->unique()->count(),
            'mapel' => $semua->pluck('mapel_id')->unique()->count(),
        ];

        return view('rekap.bulanan', array_merge(
            compact('jurnals', 'bulan', 'statistik'),
            $this->dataFilter()
        ));
    }

    public function tahunan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        Carbon::setLocale('id');

        $jurnals = $this->terapkanFilter(Jurnal::with(['guru', 'kelas', 'mapel']), $request)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        $perBulan = $jurnals->groupBy(function ($jurnal) {
            return Carbon::parse($jurnal->tanggal)->month;
        });

        $perGuru = $jurnals->groupBy('guru_id')->map(function ($group) {
            return [
                'nama'  => $group->first()->guru->nama ?? '-',
                'total' => $group->count(),
            ];
        })->sortByDesc('total');

        $statistik = [
            'total' => $jurnals->count(),
            'guru'  => $jurnals->pluck('guru_id')->unique()->count(),
            'kelas' => $jurnals->pluck('kelas_id')->unique()->count(),
            'mapel' => $jurnals->pluck('mapel_id')->unique()->count(),
        ];

        return view('rekap.tahunan', array_merge(
            compact('tahun', 'perBulan', 'perGuru', 'statistik'),
            $this->dataFilter()
        ));
    }
}