<?php

use App\Models\Guru;
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

it('recognizes a students NISN on the forgot password form', function () {
    $user = User::factory()->create([
        'role' => 'siswa',
        'is_active' => true,
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'X TKI 2', 'tingkat' => 'X']);
    Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '0105292766',
        'nama_siswa' => 'Siswa Lupa Password',
        'jenis_kelamin' => 'L',
    ]);

    $this->post('/forgot-password', ['login' => '0105292766'])
        ->assertSessionHas('status', 'Silakan hubungi admin untuk menyiapkan ulang password akun Anda.');
});

it('describes account recovery as admin assistance instead of an automated password reset', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Bantuan Akses Akun')
        ->assertSee('password tidak diatur ulang otomatis');
});

it('blocks a role from another role dashboard', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('limits repeated login attempts by account identity and client address', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['login' => 'unknown-user', 'password' => 'invalid'])
            ->assertSessionHasErrors('login');
    }

    $this->post('/login', ['login' => 'unknown-user', 'password' => 'invalid'])
        ->assertTooManyRequests();
});

it('allows each active role to log in to its dashboard', function (string $role, string $path) {
    $user = User::factory()->create([
        'username' => 'akun.'.$role,
        'password' => Hash::make('password'),
        'role' => $role,
        'is_active' => true,
    ]);
    $login = $user->username;

    if ($role === 'guru') {
        Guru::create([
            'user_id' => $user->id,
            'nip' => 'LOGIN-GURU',
            'nama_guru' => 'Guru Login',
            'status_kepegawaian' => 'Honorer',
        ]);
    }

    if ($role === 'siswa') {
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Login', 'tingkat' => 'X']);
        $siswa = Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $kelas->id,
            'nis' => 'LOGIN-NIS',
            'nama_siswa' => 'Siswa Login',
            'jenis_kelamin' => 'L',
        ]);
        $login = $siswa->nis;
    }

    $this->post('/login', ['login' => $login, 'password' => 'password'])->assertRedirect($path);
    expect(auth()->id())->toBe($user->id);
})->with([
    'admin' => ['admin', '/admin'],
    'guru' => ['guru', '/guru'],
    'siswa' => ['siswa', '/siswa'],
    'piket' => ['piket', '/piket'],
    'sekretaris' => ['sekretaris', '/sekretaris'],
]);

it('invalidates the session when a user logs out', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);

    $this->actingAs($user)->post(route('logout'))->assertRedirect('/login');

    $this->assertGuest();
});
