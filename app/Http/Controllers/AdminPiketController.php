<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPiketController extends Controller
{
    public function index(): View
    {
        return view('admin.piket.index', [
            'gurus' => Guru::with('user')->orderBy('nama_guru')->get(),
            'jadwals' => JadwalPiket::with('guru')->whereDate('tanggal', '>=', today())->orderBy('tanggal')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guru_id' => ['required', 'exists:gurus,id'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
        ]);

        JadwalPiket::updateOrCreate(
            ['guru_id' => $data['guru_id'], 'tanggal' => $data['tanggal']],
            ['dibuat_oleh' => $request->user()->id],
        );

        return back()->with('success', 'Jadwal piket guru berhasil disimpan.');
    }

    public function destroy(JadwalPiket $jadwalPiket): RedirectResponse
    {
        $jadwalPiket->delete();

        return back()->with('success', 'Jadwal piket berhasil dihapus.');
    }
}
