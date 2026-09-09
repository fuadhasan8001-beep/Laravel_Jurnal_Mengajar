<?php

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('rejects inactive users during login', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password'),
        'role' => 'guru',
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('allows active users to log in with their username', function () {
    $user = User::factory()->create([
        'username' => 'guru.contoh',
        'password' => Hash::make('password'),
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->post('/login', [
        'login' => $user->username,
        'password' => 'password',
    ])->assertRedirect('/guru');

    expect(auth()->id())->toBe($user->id);
});

it('allows active students to log in with their NISN', function () {
    $user = User::factory()->create([
        'username' => 'student.username',
        'password' => Hash::make('password'),
        'role' => 'siswa',
        'is_active' => true,
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'X TKI 1', 'tingkat' => 'X']);
    $siswa = Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '0105292765',
        'nama_siswa' => 'Siswa Contoh',
        'jenis_kelamin' => 'P',
    ]);

    $this->post('/login', [
        'login' => $siswa->nis,
        'password' => 'password',
    ])->assertRedirect('/siswa');

    expect(auth()->id())->toBe($user->id);
});

it('blocks a role from another role dashboard', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
