<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDataController extends Controller
{
    public function gurus(Request $request): View
    {
        $query = Guru::with('user')->latest();

        $query
            ->when($request->filled('q'), fn ($builder) => $builder->where(function ($search) use ($request): void {
                $search->where('nama_guru', 'like', '%'.$request->string('q').'%')
                    ->orWhere('nip', 'like', '%'.$request->string('q').'%');
            }))
            ->when($request->filled('status'), fn ($builder) => $builder->whereHas('user', fn ($user) => $user->where('is_active', $request->boolean('status'))));

        return view('admin.gurus.index', ['gurus' => $query->paginate(20)->withQueryString()]);
    }

    public function createGuru(): View
    {
        return view('admin.gurus.create');
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:30', 'unique:gurus,nip'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'status_kepegawaian' => ['required', 'in:PNS,PPPK,Honorer'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['nama_guru'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'guru',
                'is_active' => true,
            ]);

            Guru::create([
                ...$data,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('admin.gurus.index')->with('success', 'Data guru berhasil dibuat.');
    }

    public function editGuru(Guru $guru): View
    {
        return view('admin.gurus.edit', ['guru' => $guru->load('user')]);
    }

    public function updateGuru(Request $request, Guru $guru): RedirectResponse
    {
        $data = $request->validate([
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:30', Rule::unique('gurus', 'nip')->ignore($guru->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'status_kepegawaian' => ['required', 'in:PNS,PPPK,Honorer'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($guru->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $guru): void {
            $guru->update($data);
            $guru->user->update([
                'name' => $data['nama_guru'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
                ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
            ]);
        });

        return redirect()->route('admin.gurus.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroyGuru(Guru $guru): RedirectResponse
    {
        $guru->user->update(['is_active' => false]);

        return redirect()->route('admin.gurus.index')->with('success', 'Akun guru dinonaktifkan.');
    }

    public function siswas(Request $request): View
    {
        $query = Siswa::with(['user', 'kelas'])->latest();

        $query
            ->when($request->filled('q'), fn ($builder) => $builder->where(function ($search) use ($request): void {
                $search->where('nama_siswa', 'like', '%'.$request->string('q').'%')
                    ->orWhere('nis', 'like', '%'.$request->string('q').'%');
            }))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('status'), fn ($builder) => $builder->whereHas('user', fn ($user) => $user->where('is_active', $request->boolean('status'))));

        return view('admin.siswas.index', [
            'siswas' => $query->paginate(20)->withQueryString(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
        ]);
    }

    public function createSiswa(): View
    {
        return view('admin.siswas.create', ['kelas' => Kelas::orderBy('nama_kelas')->get()]);
    }

    public function storeSiswa(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_siswa' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'string', 'max:30', 'unique:siswas,nis'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['nama_siswa'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Siswa::create([
                ...$data,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('admin.siswas.index')->with('success', 'Data siswa berhasil dibuat.');
    }

    public function editSiswa(Siswa $siswa): View
    {
        return view('admin.siswas.edit', [
            'siswa' => $siswa->load('user'),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
        ]);
    }

    public function updateSiswa(Request $request, Siswa $siswa): RedirectResponse
    {
        $data = $request->validate([
            'nama_siswa' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswas', 'nis')->ignore($siswa->id)],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($siswa->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $siswa): void {
            $siswa->update($data);
            $siswa->user->update([
                'name' => $data['nama_siswa'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
                ...($data['password'] ? ['password' => Hash::make($data['password'])] : []),
            ]);
        });

        return redirect()->route('admin.siswas.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroySiswa(Siswa $siswa): RedirectResponse
    {
        $siswa->user->update(['is_active' => false]);

        return redirect()->route('admin.siswas.index')->with('success', 'Akun siswa dinonaktifkan.');
    }
}
