<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders each role dashboard with its required view data', function (string $role, string $path) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->actingAs($user)->get($path)->assertOk();
})->with([
    'admin' => ['admin', '/admin'],
    'guru' => ['guru', '/guru'],
    'siswa' => ['siswa', '/siswa'],
    'piket' => ['piket', '/piket'],
    'sekretaris' => ['sekretaris', '/sekretaris'],
]);

it('denies a role access to another role dashboard', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('redirects guests from dashboards to login', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/admin', '/guru', '/siswa', '/piket', '/sekretaris']);

it('returns a not found response for an unknown page', function () {
    $this->get('/page-that-does-not-exist')->assertNotFound();
});

it('uses Friday lesson times in the teacher dashboard active schedule indicator', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => 'DASH-FRIDAY', 'nama_guru' => 'Guru Jumat', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X DASH FRIDAY', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'DFRI', 'nama_mapel' => 'Mapel Jumat']);
    $period = JamPelajaran::create([
        'jam_ke' => 1, 'jam_mulai' => '13:00', 'jam_selesai' => '13:45',
        'jam_mulai_jumat' => '11:00', 'jam_selesai_jumat' => '11:30', 'is_active' => true,
    ]);
    $schedule = Jadwal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $period->id, 'hari' => 'Jumat', 'is_active' => true]);

    $this->travelTo(Carbon::parse('2026-09-18 11:15:00', 'Asia/Jakarta'));
    $this->actingAs($user)->get('/guru')->assertOk()->assertViewHas('activeJadwalIds', fn ($ids) => $ids->contains($schedule->id));

    $this->travelTo(Carbon::parse('2026-09-18 13:15:00', 'Asia/Jakarta'));
    $this->get('/guru')->assertViewHas('activeJadwalIds', fn ($ids) => $ids->isEmpty());
});
