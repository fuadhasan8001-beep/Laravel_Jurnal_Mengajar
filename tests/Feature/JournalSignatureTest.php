<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('stores and displays a teachers signature with a journal', function () {
    Storage::fake('local');
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => '19800101202601002', 'nama_guru' => 'Guru Satu', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $lesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $schedule = Jadwal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'jam_pelajaran_id' => $lesson->id, 'hari' => 'Senin', 'is_active' => true]);

    $this->actingAs($user)->post(route('jurnal.store'), [
        'jadwal_id' => $schedule->id,
        'status_guru' => 'Hadir',
        'materi' => 'Pengantar matematika',
        'tanda_tangan' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL9+wAAAABJRU5ErkJggg==',
    ])->assertRedirect();

    $journal = $guru->jurnals()->sole();
    expect($journal->tanda_tangan)->not->toBeNull();
    Storage::disk('local')->assertExists($journal->tanda_tangan);

    $this->actingAs($user)->get(route('jurnal.show', $journal))
        ->assertOk()
        ->assertSee('Tanda tangan guru');
});
