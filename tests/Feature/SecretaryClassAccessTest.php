<?php

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('limits secretary journal and attendance access to assigned classes', function () {
    $data = secretaryClassSetup();
    $assignedJournal = Jurnal::create([...$data['journal'], 'kelas_id' => $data['assigned']->id, 'materi' => 'Materi kelas sendiri']);
    $otherJournal = Jurnal::create([...$data['journal'], 'kelas_id' => $data['other']->id, 'materi' => 'Materi kelas lain']);

    $this->actingAs($data['secretary'])->get(route('jurnal.index'))
        ->assertOk()
        ->assertSee('Materi kelas sendiri')
        ->assertDontSee('Materi kelas lain');

    $this->actingAs($data['secretary'])->get(route('jurnal.show', $otherJournal))->assertForbidden();
    $this->actingAs($data['secretary'])->post(route('jurnal.verify', $otherJournal), ['status' => 'Disetujui'])->assertForbidden();
    $this->actingAs($data['secretary'])->post(route('absensi.store'), [
        'jurnal_id' => $otherJournal->id, 'siswa_id' => $data['student']->id, 'status' => 'H',
    ])->assertForbidden();

    $this->actingAs($data['secretary'])->post(route('jurnal.verify', $assignedJournal), ['status' => 'Disetujui'])->assertRedirect();
    $this->assertDatabaseHas('jurnals', ['id' => $assignedJournal->id, 'status_verifikasi' => 'Disetujui']);

    $this->actingAs($data['secretary'])->post(route('absensi.store'), [
        'jurnal_id' => $assignedJournal->id,
        'siswa_id' => $data['student']->id,
        'status' => 'H',
    ])->assertRedirect();

    $this->assertDatabaseHas('absensis', ['jurnal_id' => $assignedJournal->id, 'siswa_id' => $data['student']->id, 'status' => 'H']);
});

it('denies piket general attendance access and teachers access to another teachers attendance', function () {
    $data = secretaryClassSetup();
    $otherTeacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherTeacher = Guru::create(['user_id' => $otherTeacherUser->id, 'nip' => 'SECRETARY-OTHER', 'nama_guru' => 'Guru Lain', 'status_kepegawaian' => 'Honorer']);
    $otherJournal = Jurnal::create([...$data['journal'], 'guru_id' => $otherTeacher->id, 'materi' => 'Jurnal guru lain']);
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);

    $this->actingAs($data['teacherUser'])->post(route('absensi.store'), [
        'jurnal_id' => $otherJournal->id, 'siswa_id' => $data['student']->id, 'status' => 'H',
    ])->assertForbidden();
    $this->actingAs($piket)->get(route('absensi.index'))->assertForbidden();
    $this->post(route('absensi.store'), [
        'jurnal_id' => $otherJournal->id, 'siswa_id' => $data['student']->id, 'status' => 'H',
    ])->assertForbidden();

    $this->assertDatabaseCount('absensis', 0);
});

it('provisions one secretary account for each scheduled class', function () {
    $data = secretaryClassSetup();

    Jadwal::create([
        'guru_id' => $data['guru']->id,
        'kelas_id' => $data['assigned']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['period']->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);

    expect(Artisan::call('app:provision-class-secretary-accounts'))->toBe(0);

    $email = 'pengurus.'.Str::slug($data['assigned']->nama_kelas).'.'.$data['assigned']->id.'@sekolah.local';
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    $this->assertDatabaseHas('sekretaris_kelas', ['user_id' => $user->id, 'kelas_id' => $data['assigned']->id]);
});

it('scopes schedule details and dispensation exports to the secretary class', function () {
    $data = secretaryClassSetup();
    $schedule = Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $data['other']->id,
        'mapel_id' => $data['mapel']->id, 'jam_pelajaran_id' => $data['period']->id, 'hari' => 'Senin', 'is_active' => true]);
    $otherStudent = Siswa::create(['user_id' => User::factory()->create(['role' => 'siswa'])->id, 'kelas_id' => $data['other']->id, 'nis' => 'OTHER-SECRETARY', 'nama_siswa' => 'Rahasia kelas lain', 'jenis_kelamin' => 'L']);
    Dispensasi::create(['siswa_id' => $otherStudent->id, 'tanggal' => '2026-09-14',
        'jam_mulai_id' => $data['period']->id, 'jam_selesai_id' => $data['period']->id, 'alasan' => 'Kegiatan']);
    $this->actingAs($data['secretary'])->get(route('jadwal.show', $schedule))->assertForbidden();
    $this->get(route('jadwal.index'))->assertViewHas('jadwals', fn ($items) => $items->isEmpty());
    $this->get(route('laporan.dispensasi', ['kelas_id' => $data['other']->id]))->assertOk()->assertDontSee('Rahasia kelas lain');
    $this->get(route('laporan.dispensasi.export', ['kelas_id' => $data['other']->id]))->assertOk()->assertDontSee('Rahasia kelas lain');
});

it('updates one schedule without changing global lesson period times', function () {
    $data = secretaryClassSetup();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $later = JamPelajaran::create(['jam_ke' => 2, 'jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'is_active' => true]);
    $schedule = Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $data['assigned']->id,
        'mapel_id' => $data['mapel']->id, 'jam_pelajaran_id' => $data['period']->id, 'hari' => 'Senin', 'is_active' => true]);
    $this->actingAs($admin)->put(route('jadwal.update', $schedule), [
        'guru_id' => $data['guru']->id, 'kelas_id' => $data['assigned']->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['period']->id, 'hari' => 'Senin', 'is_active' => 1,
        'jam_mulai' => '09:00', 'jam_selesai' => '09:50',
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($data['period']->fresh()->jam_mulai)->toBe('07:00');
    expect($data['period']->fresh()->jam_selesai)->toBe('07:45');
    expect($later->fresh()->jam_mulai)->toBe('08:00');
    expect($later->fresh()->jam_selesai)->toBe('08:45');
});

function secretaryClassSetup(): array
{
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $teacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacherUser->id, 'nip' => 'SECRETARY-1', 'nama_guru' => 'Guru Uji', 'status_kepegawaian' => 'Honorer']);
    $assigned = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $other = Kelas::create(['nama_kelas' => 'X RPL 2', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'RPL', 'nama_mapel' => 'Rekayasa Perangkat Lunak']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'is_active' => true]);
    $student = Siswa::create([
        'user_id' => User::factory()->create(['role' => 'siswa'])->id,
        'kelas_id' => $assigned->id,
        'nis' => 'SECRETARY-1',
        'nama_siswa' => 'Siswa Uji',
        'jenis_kelamin' => 'L',
    ]);
    $secretary->kelasSekretaris()->attach($assigned);
    $journal = [
        'guru_id' => $guru->id,
        'kelas_id' => $assigned->id,
        'mapel_id' => $mapel->id,
        'jam_mulai_id' => $period->id,
        'jam_selesai_id' => $period->id,
        'tanggal' => '2026-09-14',
        'status_guru' => 'Hadir',
        'status_verifikasi' => 'Menunggu',
    ];

    return compact('secretary', 'teacherUser', 'guru', 'assigned', 'other', 'mapel', 'period', 'student', 'journal');
}
