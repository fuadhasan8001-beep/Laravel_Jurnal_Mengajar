<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function journalSetup(): array
{
    test()->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
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
    Jadwal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $jamMulai->id, 'hari' => 'Senin', 'is_active' => true]);

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

it('records teacher absence tasks and student attendance when creating a journal', function () {
    $data = journalSetup();
    $studentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $student = Siswa::create([
        'user_id' => $studentUser->id,
        'kelas_id' => $data['kelas']->id,
        'nis' => '0105292765',
        'nama_siswa' => 'Siswa Sakit',
        'jenis_kelamin' => 'P',
    ]);

    $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'tanggal' => '2020-01-01',
        'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_mulai_id' => $data['jamMulai']->id,
        'jam_selesai_id' => $data['jamMulai']->id,
        'status_guru' => 'Izin',
        'materi' => 'Belajar mandiri',
        'tugas' => 'Kerjakan latihan halaman 10.',
        'absensi' => [
            $student->id => [
                'siswa_id' => $student->id,
                'status' => 'S',
                'catatan' => 'Demam',
            ],
        ],
    ])->assertRedirect();

    $jurnal = Jurnal::firstOrFail();

    expect($jurnal->tanggal->toDateString())->toBe(today()->toDateString());
    $this->assertDatabaseHas('absensis', [
        'jurnal_id' => $jurnal->id,
        'siswa_id' => $student->id,
        'status' => 'S',
        'catatan' => 'Demam',
    ]);
});

it('allows an absent teacher to record only their status', function () {
    $data = journalSetup();

    $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_mulai_id' => $data['jamMulai']->id,
        'jam_selesai_id' => $data['jamMulai']->id,
        'status_guru' => 'Sakit',
        'materi' => 'Belajar mandiri',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('jurnals', ['guru_id' => $data['guru']->id, 'status_guru' => 'Sakit']);
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
