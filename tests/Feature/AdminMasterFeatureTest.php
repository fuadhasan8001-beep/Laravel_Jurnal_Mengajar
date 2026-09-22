<?php

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
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

it('allows an admin to manage lesson periods and blocks deletion when they are used', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.jam.index'))
        ->assertOk()
        ->assertSee('Data Jam Pelajaran');

    $this->post(route('admin.jam.store'), [
        'jam_ke' => 1,
        'jam_mulai' => '07:00',
        'jam_selesai' => '07:45',
        'is_active' => 1,
    ])->assertRedirect(route('admin.jam.index'));

    $period = JamPelajaran::sole();
    $this->get(route('admin.jam.edit', $period))->assertOk()->assertSee('Edit Jam Pelajaran');

    $this->put(route('admin.jam.update', $period), [
        'jam_ke' => 1,
        'jam_mulai' => '07:05',
        'jam_selesai' => '07:50',
        'is_active' => 1,
    ])->assertRedirect(route('admin.jam.index'));

    $period->refresh();
    expect($period->jam_mulai)->toBe('07:05');

    $guru = Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru', 'is_active' => true])->id,
        'nip' => 'JAM-1',
        'nama_guru' => 'Guru Jam',
        'status_kepegawaian' => 'Honorer',
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'X JAM', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'JAM', 'nama_mapel' => 'Mapel Jam']);
    Jurnal::create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'jam_mulai_id' => $period->id,
        'jam_selesai_id' => $period->id,
        'tanggal' => '2026-09-22',
        'materi' => '',
        'status_guru' => 'Hadir',
    ]);

    $this->delete(route('admin.jam.destroy', $period))
        ->assertSessionHasErrors('jam');
    expect(JamPelajaran::find($period->id))->not->toBeNull();
});

it('forbids non-admin users from managing lesson periods', function () {
    $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

    $this->actingAs($guru)->get(route('admin.jam.index'))->assertForbidden();
    $this->actingAs($guru)->get(route('admin.jam.create'))->assertForbidden();
    $this->actingAs($guru)->post(route('admin.jam.store'), [
        'jam_ke' => 1,
        'jam_mulai' => '07:00',
        'jam_selesai' => '07:45',
        'is_active' => 1,
    ])->assertForbidden();
});
