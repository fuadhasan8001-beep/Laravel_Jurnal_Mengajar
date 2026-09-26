<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits teachers to their own schedules in the list and detail pages', function () {
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $teacherProfile = Guru::create([
        'user_id' => $teacher->id,
        'nip' => 'SCHEDULE-OWNER',
        'nama_guru' => 'Guru Pemilik',
        'status_kepegawaian' => 'Honorer',
    ]);
    $otherTeacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherProfile = Guru::create([
        'user_id' => $otherTeacher->id,
        'nip' => 'SCHEDULE-OTHER',
        'nama_guru' => 'Guru Lain',
        'status_kepegawaian' => 'Honorer',
    ]);
    $ownClass = Kelas::create(['nama_kelas' => 'Kelas Pemilik', 'tingkat' => 'X']);
    $otherClass = Kelas::create(['nama_kelas' => 'Kelas Lain', 'tingkat' => 'XI']);
    $mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45']);
    $ownSchedule = Jadwal::create([
        'guru_id' => $teacherProfile->id,
        'kelas_id' => $ownClass->id,
        'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $period->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);
    $otherSchedule = Jadwal::create([
        'guru_id' => $otherProfile->id,
        'kelas_id' => $otherClass->id,
        'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $period->id,
        'hari' => 'Selasa',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)->get(route('jadwal.index'))
        ->assertSee('Kelas Pemilik')
        ->assertDontSee('Kelas Lain');

    $this->get(route('jadwal.show', $otherSchedule))->assertForbidden();
    $this->get(route('jadwal.show', $ownSchedule))->assertSee('Kelas Pemilik');
});

it('rejects schedules whose clock times overlap for the same teacher and class', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $teacher = Guru::create([
        'user_id' => $teacherUser->id,
        'nip' => 'SCHEDULE-CONFLICT',
        'nama_guru' => 'Guru Konflik',
        'status_kepegawaian' => 'Honorer',
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'Kelas Konflik', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'BIND', 'nama_mapel' => 'Bahasa Indonesia']);
    $periodTen = JamPelajaran::create([
        'jam_ke' => 10,
        'jam_mulai' => '14:20:00',
        'jam_selesai' => '15:00:00',
        'is_active' => true,
    ]);
    $periodEleven = JamPelajaran::create([
        'jam_ke' => 11,
        'jam_mulai' => '14:00:00',
        'jam_selesai' => '14:30:00',
        'is_active' => true,
    ]);
    Jadwal::create([
        'guru_id' => $teacher->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $periodTen->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);

    $this->actingAs($admin)->post(route('jadwal.store'), [
        'guru_id' => $teacher->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $periodEleven->id,
        'hari' => 'Senin',
        'is_active' => true,
    ])->assertSessionHasErrors('jam_pelajaran_id');

    $this->assertDatabaseCount('jadwals', 1);
});
