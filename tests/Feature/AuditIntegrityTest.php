<?php

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('refuses duplicate NIS while keeping both accounts intact', function () {
    $kelas = Kelas::create(['nama_kelas' => 'X AUDIT', 'tingkat' => 'X']);
    $first = User::factory()->create(['role' => 'siswa']);
    $second = User::factory()->create(['role' => 'siswa']);
    $data = ['kelas_id' => $kelas->id, 'nis' => 'AUDIT-1', 'nama_siswa' => 'Siswa', 'jenis_kelamin' => 'L'];
    Siswa::create([...$data, 'user_id' => $first->id]);
    expect(fn () => Siswa::create([...$data, 'user_id' => $second->id]))
        ->toThrow(QueryException::class);
    $this->assertDatabaseCount('siswas', 1);
    $this->assertDatabaseCount('users', 2);
});

it('validates duplicate NIS in the admin student creation flow', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $kelas = Kelas::create(['nama_kelas' => 'X AUDIT FORM', 'tingkat' => 'X']);
    $existingUser = User::factory()->create(['role' => 'siswa']);
    Siswa::create(['user_id' => $existingUser->id, 'kelas_id' => $kelas->id,
        'nis' => 'AUDIT-FORM', 'nama_siswa' => 'Siswa Lama', 'jenis_kelamin' => 'L']);

    $this->actingAs($admin)->post(route('admin.siswas.store'), [
        'nama_siswa' => 'Siswa Baru', 'nis' => 'AUDIT-FORM', 'jenis_kelamin' => 'P',
        'kelas_id' => $kelas->id, 'email' => 'audit.form@example.test', 'password' => 'password123',
    ])->assertSessionHasErrors('nis');

    $this->assertDatabaseCount('siswas', 1);
});

it('keeps the database unique index for one attendance per student and journal', function () {
    $user = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => 'ATT-UNIQUE', 'nama_guru' => 'Guru', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X ATT UNIQUE', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'ATTU', 'nama_mapel' => 'Mapel']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45']);
    $studentUser = User::factory()->create(['role' => 'siswa']);
    $student = Siswa::create(['user_id' => $studentUser->id, 'kelas_id' => $kelas->id,
        'nis' => 'ATT-UNIQUE', 'nama_siswa' => 'Siswa', 'jenis_kelamin' => 'L']);
    $journal = Jurnal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'tanggal' => '2026-09-14', 'materi' => '']);
    Absensi::create(['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'H']);

    expect(fn () => Absensi::create(['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'S']))
        ->toThrow(QueryException::class);
    $this->assertDatabaseCount('absensis', 1);
});

it('cleans up uploaded evidence when dispensation persistence fails', function () {
    Storage::fake('local');
    config(['filesystems.default' => 'local']);
    $kelas = Kelas::create(['nama_kelas' => 'X EVIDENCE', 'tingkat' => 'X']);
    $studentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $student = Siswa::create(['user_id' => $studentUser->id, 'kelas_id' => $kelas->id,
        'nis' => 'EVIDENCE-1', 'nama_siswa' => 'Siswa', 'jenis_kelamin' => 'L']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45']);
    $this->travelTo(Carbon::parse('2026-09-14 06:00:00', 'Asia/Jakarta'));
    Event::listen('eloquent.creating: App\\Models\\Dispensasi', function (): void {
        throw new RuntimeException('Test persistence failure.');
    });
    $this->withoutExceptionHandling();

    try {
        $this->actingAs($studentUser)->post(route('dispensasi.store'), [
            'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'alasan' => 'Kegiatan',
            'bukti' => UploadedFile::fake()->createWithContent('bukti.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL9+wAAAABJRU5ErkJggg==')),
        ]);
        test()->fail('Expected persistence exception was not thrown.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Test persistence failure.');
    }

    Storage::disk('local')->assertDirectoryEmpty('dispensasi/bukti');
    $this->assertDatabaseCount('dispensasis', 0);
});
