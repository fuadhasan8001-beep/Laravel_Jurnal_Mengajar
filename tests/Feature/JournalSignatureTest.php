<?php

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('continues to serve signatures from historical journals', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '19800101202601002', 'nama_guru' => 'Guru Satu', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $lesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $signaturePath = 'jurnal/tanda-tangan/legacy.png';
    Storage::put($signaturePath, 'legacy signature');
    $journal = Jurnal::create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'jam_mulai_id' => $lesson->id,
        'jam_selesai_id' => $lesson->id,
        'tanggal' => today(),
        'status_guru' => 'Hadir',
        'materi' => 'Materi lama',
        'tanda_tangan' => $signaturePath,
    ]);

    $this->actingAs($user)->get(route('jurnal.signature', $journal))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
    Storage::disk('local')->assertExists($signaturePath);
});
