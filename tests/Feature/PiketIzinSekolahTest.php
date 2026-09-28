<?php

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\IzinSekolah;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->travelTo(Carbon::parse('2026-09-28 07:15:00', 'Asia/Jakarta'));
    config([
        'school.latitude' => 0,
        'school.longitude' => 0,
        'school.radius_meters' => 100,
        'school.max_gps_accuracy' => 25,
    ]);
    Storage::fake();

    $this->piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);
    $this->teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->guru = Guru::create([
        'user_id' => $this->teacher->id,
        'nip' => 'IZIN-001',
        'nama_guru' => 'Guru Uji',
        'status_kepegawaian' => 'Honorer',
    ]);
    $this->kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $this->mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $this->jam = JamPelajaran::create([
        'jam_ke' => 1,
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '08:00:00',
        'is_active' => true,
    ]);
    $this->student = Siswa::create([
        'user_id' => User::factory()->create(['role' => 'siswa', 'is_active' => true])->id,
        'kelas_id' => $this->kelas->id,
        'nis' => 'IZIN-001',
        'nama_siswa' => 'Siswa Izin',
        'jenis_kelamin' => 'L',
    ]);
    Jadwal::create([
        'guru_id' => $this->guru->id,
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'jam_pelajaran_id' => $this->jam->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);
});

it('records a full-day parent permission without lesson hours and applies it to a later journal', function (): void {
    $this->actingAs($this->piket)->get(route('piket.izin-sekolah.create'))
        ->assertOk()
        ->assertSee('Catat izin sekolah seharian')
        ->assertDontSee('jam_mulai_id');

    $this->post(route('piket.izin-sekolah.store'), [
        'siswa_ids' => [$this->student->id],
        'alasan' => 'Demam',
        'surat_izin' => UploadedFile::fake()->image('surat.jpg'),
    ])->assertRedirect(route('piket.izin-sekolah.index'));

    $izin = IzinSekolah::firstOrFail();
    expect($izin->tanggal->toDateString())->toBe('2026-09-28');
    $this->assertDatabaseCount('dispensasis', 0);
    Storage::assertExists($izin->surat_izin_path);

    $this->actingAs($this->teacher)->post(route('jurnal.store'), [
        'status_guru' => 'Hadir',
        'materi' => 'Bilangan',
        'latitude' => 0,
        'longitude' => 0.00005,
        'location_accuracy' => 10,
    ])->assertSessionHasNoErrors();

    $journal = Jurnal::firstOrFail();
    $attendance = Absensi::where('jurnal_id', $journal->id)->where('siswa_id', $this->student->id)->firstOrFail();
    expect($attendance->status)->toBe('I')
        ->and($attendance->surat_izin_path)->toBe($izin->surat_izin_path);

    $this->post(route('absensi.store'), [
        'jurnal_id' => $journal->id,
        'absensis' => [$this->student->id => [
            'siswa_id' => $this->student->id,
            'status' => 'H',
        ]],
    ])->assertRedirect();
    expect($attendance->fresh()->status)->toBe('I');

    $this->get(route('piket.izin-sekolah.surat', $izin))->assertOk();
});

it('updates existing journals for the whole class when Piket records an all-day permission', function (): void {
    $journal = Jurnal::create([
        'guru_id' => $this->guru->id,
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'jam_mulai_id' => $this->jam->id,
        'jam_selesai_id' => $this->jam->id,
        'tanggal' => today(),
        'status_guru' => 'Hadir',
        'materi' => 'Bilangan',
    ]);

    $this->actingAs($this->piket)->post(route('piket.izin-sekolah.store'), [
        'siswa_ids' => [$this->student->id],
        'surat_izin' => UploadedFile::fake()->image('surat.jpg'),
    ])->assertRedirect(route('piket.izin-sekolah.index'));

    $attendance = Absensi::where('jurnal_id', $journal->id)->where('siswa_id', $this->student->id)->firstOrFail();
    expect($attendance->status)->toBe('I')->and($attendance->surat_izin_path)->not->toBeNull();
});