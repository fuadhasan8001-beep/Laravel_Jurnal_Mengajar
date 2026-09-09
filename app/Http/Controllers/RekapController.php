<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function harian(Request $request)
    {
        // kalau user pilih tanggal, pakai tanggal itu
        // kalau tidak, pakai tanggal hari ini
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        // ambil jurnal sesuai tanggal, sekalian data guru/kelas/mapel
        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereDate('tanggal', $tanggal)
            ->get();

    return view('rekap.harian', compact('jurnals', 'tanggal'));
    }

    public function mingguan(Request $request)
    {
         // tanggal yang dipilih user (default: hari ini)
          $tanggal = $request->input('tanggal', date('Y-m-d'));

         // cari Senin & Minggu dari minggu itu
         // startOfWeek() = otomatis mundur ke hari Senin
        $senin = \Carbon\Carbon::parse($tanggal)->startOfWeek();
        $minggu = $senin->copy()->endOfWeek();

         // bikin nama hari pakai bahasa Indonesia
         \Carbon\Carbon::setLocale('id');

         // ambil jurnal dari Senin sampai Minggu
        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereBetween('tanggal', [$senin->format('Y-m-d'), $minggu->format('Y-m-d')])
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');   // dikelompokkan per tanggal

    return view('rekap.mingguan', compact('jurnals', 'tanggal', 'senin', 'minggu'));
    }

    public function bulanan(Request $request)
    {
         // input bulan formatnya "2026-09" (tahun-bulan)
         // kalau user belum pilih, pakai bulan sekarang
         $bulan = $request->input('bulan', date('Y-m'));

         // pisahkan jadi tahun dan nomor bulan
        [$tahun, $nomorBulan] = explode('-', $bulan);

         \Carbon\Carbon::setLocale('id');

         // ambil semua jurnal di bulan itu
        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $nomorBulan)
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');   // dikelompokkan per tanggal

         // hitung total jurnal di bulan itu
         $totalJurnal = $jurnals->flatten()->count();

    return view('rekap.bulanan', compact('jurnals', 'bulan', 'totalJurnal'));
    }

    public function tahunan(Request $request)
    {
        // tahun yang dipilih user (default: tahun sekarang)
        $tahun = $request->input('tahun', date('Y'));

        \Carbon\Carbon::setLocale('id');

        // ambil semua jurnal di tahun itu
        $jurnals = Jurnal::with(['guru', 'kelas', 'mapel'])
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        // kelompokkan per bulan (hasilnya: key = 1 sampai 12)
        $perBulan = $jurnals->groupBy(function ($jurnal) {
        return \Carbon\Carbon::parse($jurnal->tanggal)->month;
    });

        $totalJurnal = $jurnals->count();

    return view('rekap.tahunan', compact('tahun', 'perBulan', 'totalJurnal'));
    }
}

