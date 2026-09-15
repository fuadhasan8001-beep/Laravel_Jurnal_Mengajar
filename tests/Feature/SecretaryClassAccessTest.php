<?php

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

    $this->actingAs($data['secretary'])->post(route('jurnal.verify', $assignedJournal), ['status' => 'Disetujui'])->assertRedirect();
    $this->assertDatabaseHas('jurnals', ['id' => $assignedJournal->id, 'status_verifikasi' => 'Disetujui']);

    $this->actingAs($data['secretary'])->post(route('absensi.store'), [
        'jurnal_id' => $assignedJournal->id,
        'siswa_id' => $data['student']->id,
        'status' => 'H',
    ])->assertRedirect();

    $this->assertDatabaseHas('absensis', ['jurnal_id' => $assignedJournal->id, 'siswa_id' => $data['student']->id, 'status' => 'H']);
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
