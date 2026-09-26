<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPiketController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        return view('admin.piket.index', [
            'allGurus' => Guru::with('user')->orderBy('nama_guru')->get(),
            'gurus' => Guru::with(['user', 'jadwalPikets' => fn ($query) => $query
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')])
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('nama_guru', 'like', '%'.$search.'%')
                        ->orWhere('nip', 'like', '%'.$search.'%');
                }))
                ->orderBy('nama_guru')
                ->paginate(20)
                ->withQueryString(),
            'jadwals' => JadwalPiket::with('guru')
                ->whereDate('tanggal', '>=', today())
                ->when($search !== '', fn ($query) => $query->whereHas('guru', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('nama_guru', 'like', '%'.$search.'%')
                        ->orWhere('nip', 'like', '%'.$search.'%');
                })))
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get(),
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

    public function update(Request $request, JadwalPiket $jadwalPiket): RedirectResponse
    {
        $data = $request->validate([
            'guru_id' => [
                'required',
                'exists:gurus,id',
                Rule::unique('jadwal_pikets', 'guru_id')
                    ->where('tanggal', $request->input('tanggal'))
                    ->ignore($jadwalPiket->id),
            ],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $jadwalPiket->update($data);

        return back()->with('success', 'Jadwal piket guru berhasil diperbarui.');
    }

    public function destroy(JadwalPiket $jadwalPiket): RedirectResponse
    {
        $jadwalPiket->delete();

        return back()->with('success', 'Jadwal piket berhasil dihapus.');
    }
}
