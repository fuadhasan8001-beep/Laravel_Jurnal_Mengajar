<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an admin to create a teacher account and profile', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->post(route('admin.gurus.store'), [
        'nama_guru' => 'Guru Baru',
        'nip' => '19800101202601099',
        'status_kepegawaian' => 'Honorer',
        'email' => 'guru.baru@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.gurus.index'));

    expect(Guru::where('nip', '19800101202601099')->exists())->toBeTrue();
    expect(User::where('email', 'guru.baru@example.com')->value('role'))->toBe('guru');
});

it('allows an admin to create a student account and profile', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1', 'tingkat' => 'XII']);

    $this->actingAs($admin)->post(route('admin.siswas.store'), [
        'nama_siswa' => 'Siswa Baru',
        'nis' => '987654',
        'jenis_kelamin' => 'P',
        'kelas_id' => $kelas->id,
        'email' => 'siswa.baru@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.siswas.index'));

    expect(Siswa::where('nis', '987654')->exists())->toBeTrue();
    expect(User::where('email', 'siswa.baru@example.com')->value('role'))->toBe('siswa');
});
