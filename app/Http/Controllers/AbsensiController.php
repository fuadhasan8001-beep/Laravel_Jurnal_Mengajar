<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $jurnals = Jurnal::with('absensis.siswa')
            ->orderByDesc('tanggal')
            ->get();

        return view('absensi.index', compact('jurnals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jurnal_id' => ['required', 'exists:jurnals,id'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:H,S,I,A,D'],
            'catatan' => ['nullable', 'string'],
        ]);

        Absensi::updateOrCreate(
            [
                'jurnal_id' => $data['jurnal_id'],
                'siswa_id' => $data['siswa_id'],
            ],
            [
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]
        );

        return back()->with('success', 'Absensi berhasil disimpan.');
    }
}