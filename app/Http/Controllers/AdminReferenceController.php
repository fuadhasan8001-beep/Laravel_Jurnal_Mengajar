<?php

namespace App\Http\Controllers;

use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminReferenceController extends Controller
{
    public function kelas(Request $request): View
    {
        return view('admin.reference.index', [
            'type' => 'kelas',
            'title' => 'Data Kelas',
            'items' => Kelas::when($request->filled('q'), fn ($query) => $query->where('nama_kelas', 'like', '%'.$request->string('q').'%'))->withCount('siswas')->orderBy('nama_kelas')->paginate(20)->withQueryString(),
        ]);
    }

    public function storeKelas(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', 'unique:kelas,nama_kelas'],
            'tingkat' => ['required', 'string', 'max:10'],
        ]);
        Kelas::create($data);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function editKelas(Kelas $kelas): View
    {
        return view('admin.reference.form', ['type' => 'kelas', 'title' => 'Edit Kelas', 'item' => $kelas]);
    }

    public function updateKelas(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:100', Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id)],
            'tingkat' => ['required', 'string', 'max:10'],
        ]);
        $kelas->update($data);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroyKelas(Kelas $kelas): RedirectResponse
    {
        if ($kelas->siswas()->exists() || $kelas->jurnals()->exists()) {
            throw ValidationException::withMessages(['kelas' => 'Kelas yang sudah dipakai tidak dapat dihapus.']);
        }
        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function mapel(Request $request): View
    {
        return view('admin.reference.index', [
            'type' => 'mapel',
            'title' => 'Data Mata Pelajaran',
            'items' => Mapel::when($request->filled('q'), fn ($query) => $query->where('nama_mapel', 'like', '%'.$request->string('q').'%')->orWhere('kode_mapel', 'like', '%'.$request->string('q').'%'))->withCount('jurnals')->orderBy('nama_mapel')->paginate(20)->withQueryString(),
        ]);
    }

    public function storeMapel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', 'unique:mapels,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);
        Mapel::create($data);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function editMapel(Mapel $mapel): View
    {
        return view('admin.reference.form', ['type' => 'mapel', 'title' => 'Edit Mata Pelajaran', 'item' => $mapel]);
    }

    public function updateMapel(Request $request, Mapel $mapel): RedirectResponse
    {
        $data = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', Rule::unique('mapels', 'kode_mapel')->ignore($mapel->id)],
            'nama_mapel' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);
        $mapel->update($data);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroyMapel(Mapel $mapel): RedirectResponse
    {
        if ($mapel->jurnals()->exists()) {
            throw ValidationException::withMessages(['mapel' => 'Mata pelajaran yang sudah dipakai tidak dapat dihapus.']);
        }
        $mapel->delete();

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function jam(Request $request): View
    {
        return view('admin.reference.index', [
            'type' => 'jam',
            'title' => 'Data Jam Pelajaran',
            'items' => JamPelajaran::when($request->filled('q'), fn ($query) => $query->where('jam_ke', $request->integer('q')))->orderBy('jam_ke')->paginate(20)->withQueryString(),
        ]);
    }

    public function storeJam(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jam_ke' => ['required', 'integer', 'min:1', 'unique:jam_pelajarans,jam_ke'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'is_active' => ['required', 'boolean'],
        ]);
        JamPelajaran::create($data);

        return redirect()->route('admin.jam.index')->with('success', 'Jam pelajaran berhasil dibuat.');
    }

    public function editJam(JamPelajaran $jam): View
    {
        return view('admin.reference.form', ['type' => 'jam', 'title' => 'Edit Jam Pelajaran', 'item' => $jam]);
    }

    public function updateJam(Request $request, JamPelajaran $jam): RedirectResponse
    {
        $data = $request->validate([
            'jam_ke' => ['required', 'integer', 'min:1', Rule::unique('jam_pelajarans', 'jam_ke')->ignore($jam->id)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'is_active' => ['required', 'boolean'],
        ]);
        $jam->update($data);

        return redirect()->route('admin.jam.index')->with('success', 'Jam pelajaran berhasil diperbarui.');
    }

    public function destroyJam(JamPelajaran $jam): RedirectResponse
    {
        if ($jam->jurnalsMulai()->exists() || $jam->jurnalsSelesai()->exists()) {
            throw ValidationException::withMessages(['jam' => 'Jam yang sudah dipakai tidak dapat dihapus.']);
        }
        $jam->delete();

        return redirect()->route('admin.jam.index')->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
