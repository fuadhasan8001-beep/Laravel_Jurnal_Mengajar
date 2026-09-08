<?php

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function journalSetup(): array
{
    $user = User::factory()->create([
        'role' => 'guru',
        'is_active' => true,
    ]);
    $guru = Guru::create([
        'user_id' => $user->id,
        'nip' => '19800101202601002',
        'nama_guru' => 'Guru Satu',
        'status_kepegawaian' => 'Honorer',
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $jamMulai = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45']);
    $jamSelesai = JamPelajaran::create(['jam_ke' => 2, 'jam_mulai' => '07:45', 'jam_selesai' => '08:30']);

    return compact('user', 'guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai');
}

it('allows a teacher to create a journal', function () {
    $data = journalSetup();

    $response = $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'tanggal' => '2026-09-08',
        'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_mulai_id' => $data['jamMulai']->id,
        'jam_selesai_id' => $data['jamSelesai']->id,
        'status_guru' => 'Hadir',
        'materi' => 'Pengantar matematika',
        'tujuan_pembelajaran' => 'Siswa memahami konsep dasar.',
    ]);

    $response->assertRedirect();
    expect(Jurnal::where('guru_id', $data['guru']->id)->count())->toBe(1);
});

it('prevents a teacher from viewing another teachers journal', function () {
    $owner = journalSetup();
    $otherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherGuru = Guru::create([
        'user_id' => $otherUser->id,
        'nip' => '19800101202601003',
        'nama_guru' => 'Guru Dua',
        'status_kepegawaian' => 'Honorer',
    ]);
    $journal = Jurnal::create([
        'guru_id' => $otherGuru->id,
        'kelas_id' => $owner['kelas']->id,
        'mapel_id' => $owner['mapel']->id,
        'jam_mulai_id' => $owner['jamMulai']->id,
        'jam_selesai_id' => $owner['jamSelesai']->id,
        'tanggal' => '2026-09-08',
        'status_guru' => 'Hadir',
        'materi' => 'Materi privat',
    ]);

    $this->actingAs($owner['user'])
        ->get(route('jurnal.show', $journal))
        ->assertForbidden();
});
