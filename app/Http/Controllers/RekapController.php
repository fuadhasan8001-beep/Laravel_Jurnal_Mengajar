<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function harian(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereDate('tanggal', $tanggal)
            ->get();

    return view('rekap.harian', compact('jurnals', 'tanggal'));
    }

    public function mingguan(Request $request)
    {
          $tanggal = $request->input('tanggal', date('Y-m-d'));

        $senin = \Carbon\Carbon::parse($tanggal)->startOfWeek();
        $minggu = $senin->copy()->endOfWeek();

         \Carbon\Carbon::setLocale('id');

        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereBetween('tanggal', [$senin->format('Y-m-d'), $minggu->format('Y-m-d')])
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');   

    return view('rekap.mingguan', compact('jurnals', 'tanggal', 'senin', 'minggu'));
    }

    public function bulanan(Request $request)
    {
         $bulan = $request->input('bulan', date('Y-m'));

        [$tahun, $nomorBulan] = explode('-', $bulan);

         \Carbon\Carbon::setLocale('id');

        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan)
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');   

         $totalJurnal = $jurnals->flatten()->count();

    return view('rekap.bulanan', compact('jurnals', 'bulan', 'totalJurnal'));
    }

    public function tahunan(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        \Carbon\Carbon::setLocale('id');

        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        $perBulan = $jurnals->groupBy(function ($jurnal) {
        return \Carbon\Carbon::parse($jurnal->tanggal)->month;
    });

        $totalJurnal = $jurnals->count();

    return view('rekap.tahunan', compact('tahun', 'perBulan', 'totalJurnal'));
    }
}

