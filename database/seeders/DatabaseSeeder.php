<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Testing',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Akun Guru
        User::updateOrCreate(
            ['email' => 'guru@test.com'],
            [
                'name' => 'Guru Testing',
                'password' => Hash::make('password123'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );

        // Akun Siswa
        $userSiswa = User::updateOrCreate(
            ['email' => 'siswa@test.com'],
            [
                'name' => 'Siswa Testing',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'is_active' => true,
            ]
        );

        // Data Kelas
        $kelas = Kelas::updateOrCreate(
            ['nama_kelas' => 'XI RPL 2'],
            [
                'tingkat' => 'XI',
            ]
        );

        // Data Siswa
        Siswa::updateOrCreate(
            ['user_id' => $userSiswa->id],
            [
                'kelas_id' => $kelas->id,
                'nis' => '123456',
                'nama_siswa' => 'Siswa Testing',
                'jenis_kelamin' => 'L',
            ]
        );
    }
}
