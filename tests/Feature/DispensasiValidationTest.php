<?php

use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

it('rejects a dispensation for a lesson that has already ended', function () {
    $student = dispensasiValidationStudent();
    $pastLesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $currentLesson = JamPelajaran::create(['jam_ke' => 2, 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 08:00:00', 'Asia/Jakarta'));

    $this->actingAs($student->user)->post(route('dispensasi.store'), [
        'jam_mulai_id' => $pastLesson->id,
        'jam_selesai_id' => $currentLesson->id,
        'alasan' => 'Kegiatan sekolah',
    ])->assertSessionHasErrors('jam_mulai_id');

    $this->assertDatabaseCount('dispensasis', 0);
});

it('rejects a non-image file as dispensation evidence', function () {
    $student = dispensasiValidationStudent();
    $lesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));

    $this->actingAs($student->user)->post(route('dispensasi.store'), [
        'jam_mulai_id' => $lesson->id,
        'jam_selesai_id' => $lesson->id,
        'alasan' => 'Kegiatan sekolah',
        'bukti' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('bukti');

    $this->assertDatabaseCount('dispensasis', 0);
});

it('allows a dispensation to end in the same lesson that it starts', function () {
    $student = dispensasiValidationStudent();
    $lesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));

    $this->actingAs($student->user)->post(route('dispensasi.store'), [
        'jam_mulai_id' => $lesson->id,
        'jam_selesai_id' => $lesson->id,
        'alasan' => 'Kegiatan sekolah',
    ])->assertRedirect(route('dispensasi.index'));

    $this->assertDatabaseHas('dispensasis', [
        'siswa_id' => $student->id,
        'jam_mulai_id' => $lesson->id,
        'jam_selesai_id' => $lesson->id,
    ]);
});

it('rejects an ending lesson before the dispensation starts', function () {
    $student = dispensasiValidationStudent();
    $firstLesson = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $secondLesson = JamPelajaran::create(['jam_ke' => 2, 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));

    $this->actingAs($student->user)->post(route('dispensasi.store'), [
        'jam_mulai_id' => $secondLesson->id,
        'jam_selesai_id' => $firstLesson->id,
        'alasan' => 'Kegiatan sekolah',
    ])->assertSessionHasErrors('jam_selesai_id');

    $this->assertDatabaseCount('dispensasis', 0);
});

function dispensasiValidationStudent(): Siswa
{
    $user = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);

    return Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '10001',
        'nama_siswa' => 'Siswa Uji',
        'jenis_kelamin' => 'L',
    ]);
}
