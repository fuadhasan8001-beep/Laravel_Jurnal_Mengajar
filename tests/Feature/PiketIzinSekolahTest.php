<?php

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\IzinSekolah;
use App\Models\IzinMasuk;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\ClassAbsenceRecorded;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
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
        ->assertSee('Catat Izin atau Sakit Seharian')
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

it('notifies the assigned class secretary when piket records a parent permission', function (): void {
    Notification::fake();
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $secretary->kelasSekretaris()->attach($this->kelas);

    $this->actingAs($this->piket)->post(route('piket.izin-sekolah.store'), [
        'siswa_ids' => [$this->student->id],
        'status' => 'I',
        'alasan' => 'Kontrol kesehatan',
        'surat_izin' => UploadedFile::fake()->image('surat-orang-tua.jpg'),
    ])->assertRedirect(route('piket.izin-sekolah.index'));

    Notification::assertSentTo($secretary, ClassAbsenceRecorded::class, fn (ClassAbsenceRecorded $notification): bool => str_contains($notification->message, $this->student->nama_siswa)
        && str_contains($notification->message, 'izin')
        && str_contains($notification->url, 'kelas_id='.$this->kelas->id)
    );
});

it('records izin masuk, updates eligible lesson attendance, and notifies class recipients', function (): void {
    Notification::fake();
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $secretary->kelasSekretaris()->attach($this->kelas);
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

    $this->actingAs($this->piket)->get(route('piket.izin-masuk.create'))
        ->assertOk()
        ->assertSee('Surat izin masuk');

    $this->post(route('piket.izin-masuk.store'), [
        'siswa_id' => $this->student->id,
        'jam_masuk_ke' => 1,
        'alasan' => 'Terlambat karena kendaraan umum.',
    ])->assertRedirect(route('piket.izin-masuk.create'));

    $permission = IzinMasuk::firstOrFail();
    $attendance = Absensi::where('jurnal_id', $journal->id)->where('siswa_id', $this->student->id)->firstOrFail();
    expect($permission->jam_masuk_ke)->toBe(1)
        ->and($attendance->status)->toBe('H')
        ->and($attendance->catatan)->toContain('Terlambat, izin masuk jam ke-1')
        ->and($attendance->catatan)->toContain('Alasan: Terlambat karena kendaraan umum.');

    $this->actingAs($this->teacher)->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Terlambat, izin masuk jam ke-1')
        ->assertSee('Alasan: Terlambat karena kendaraan umum.');

    $this->post(route('absensi.store'), [
        'jurnal_id' => $journal->id,
        'absensis' => [$this->student->id => [
            'siswa_id' => $this->student->id,
            'status' => 'H',
            'catatan' => 'Catatan lain',
        ]],
    ])->assertRedirect();
    expect($attendance->fresh()->catatan)->toContain('Alasan: Terlambat karena kendaraan umum.');

    foreach ([$this->teacher, $secretary] as $recipient) {
        Notification::assertSentTo($recipient, ClassAbsenceRecorded::class, fn (ClassAbsenceRecorded $notification): bool => str_contains($notification->message, $this->student->nama_siswa)
            && str_contains($notification->message, 'jam ke-1')
            && str_contains($notification->url, 'kelas_id='.$this->kelas->id));
    }
});

it('shows izin masuk on the attendance page even when the class has no journal yet', function (): void {
    $this->actingAs($this->piket)->post(route('piket.izin-masuk.store'), [
        'siswa_id' => $this->student->id,
        'jam_masuk_ke' => 1,
        'alasan' => 'Terlambat karena kendaraan umum.',
    ])->assertRedirect(route('piket.izin-masuk.create'));

    $this->actingAs($this->teacher)->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Surat izin masuk')
        ->assertSee($this->student->nama_siswa)
        ->assertSee('Hadir mulai jam ke-1')
        ->assertSee('Terlambat. Alasan: Terlambat karena kendaraan umum.');
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

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $response = $this->actingAs($admin)->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Izin · dari guru piket')
        ->assertSee(route('piket.izin-sekolah.surat', IzinSekolah::firstOrFail()));

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $izinRadio = $xpath->query('//input[@type="radio" and @value="I" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    $hadirRadio = $xpath->query('//input[@type="radio" and @value="H" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    expect($izinRadio)->not->toBeNull()
        ->and($izinRadio->hasAttribute('checked'))->toBeTrue()
        ->and($izinRadio->hasAttribute('disabled'))->toBeTrue()
        ->and($hadirRadio->hasAttribute('disabled'))->toBeTrue();
});

it('synchronizes approved dispensasi to Dispen even when the saved attendance is still Hadir', function (): void {
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
    Absensi::create(['jurnal_id' => $journal->id, 'siswa_id' => $this->student->id, 'status' => 'H']);
    $dispensasi = Dispensasi::create([
        'siswa_id' => $this->student->id,
        'tanggal' => today(),
        'jam_mulai_id' => $this->jam->id,
        'jam_selesai_id' => $this->jam->id,
        'alasan' => 'Keperluan keluarga',
        'status_piket' => 'Disetujui',
        'status_admin' => 'Disetujui',
        'status_akhir' => 'Disetujui',
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $response = $this->actingAs($admin)->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Dispen · disetujui')
        ->assertSee(route('dispensasi.show', $dispensasi));

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $dispenRadio = $xpath->query('//input[@type="radio" and @value="D" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    $hadirRadio = $xpath->query('//input[@type="radio" and @value="H" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    expect($dispenRadio)->not->toBeNull()
        ->and($dispenRadio->hasAttribute('checked'))->toBeTrue()
        ->and($dispenRadio->hasAttribute('disabled'))->toBeTrue()
        ->and($hadirRadio->hasAttribute('disabled'))->toBeTrue();

    $this->post(route('absensi.store'), [
        'jurnal_id' => $journal->id,
        'absensis' => [$this->student->id => ['siswa_id' => $this->student->id, 'status' => 'H']],
    ])->assertRedirect();
    expect(Absensi::where('jurnal_id', $journal->id)->where('siswa_id', $this->student->id)->value('status'))->toBe('D');
});

it('shows saved Sakit status in sync while allowing a per-journal correction', function (): void {
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
    Absensi::create(['jurnal_id' => $journal->id, 'siswa_id' => $this->student->id, 'status' => 'S', 'catatan' => 'Demam']);

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $response = $this->actingAs($admin)->get(route('absensi.index'))->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $sakitRadio = $xpath->query('//input[@type="radio" and @value="S" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    expect($sakitRadio)->not->toBeNull()
        ->and($sakitRadio->hasAttribute('checked'))->toBeTrue()
        ->and($sakitRadio->hasAttribute('disabled'))->toBeFalse();

    $this->post(route('absensi.store'), [
        'jurnal_id' => $journal->id,
        'absensis' => [$this->student->id => ['siswa_id' => $this->student->id, 'status' => 'H']],
    ])->assertRedirect();
    expect(Absensi::where('jurnal_id', $journal->id)->where('siswa_id', $this->student->id)->value('status'))->toBe('H');
});

it('synchronizes a full-day Sakit letter to current and future journal attendance', function (): void {
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
        'status' => 'S',
        'alasan' => 'Demam',
        'surat_izin' => UploadedFile::fake()->image('surat-sakit.jpg'),
    ])->assertRedirect(route('piket.izin-sekolah.index'));

    $izin = IzinSekolah::firstOrFail();
    expect($izin->status)->toBe('S');
    $this->assertDatabaseHas('absensis', [
        'jurnal_id' => $journal->id,
        'siswa_id' => $this->student->id,
        'status' => 'S',
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $response = $this->actingAs($admin)->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Sakit · dari guru piket')
        ->assertSee(route('piket.izin-sekolah.surat', $izin));
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $sakitRadio = $xpath->query('//input[@type="radio" and @value="S" and contains(@name, "['.$this->student->id.'][status]")]')->item(0);
    expect($sakitRadio)->not->toBeNull()
        ->and($sakitRadio->hasAttribute('checked'))->toBeTrue()
        ->and($sakitRadio->hasAttribute('disabled'))->toBeTrue();

    $secondPeriod = JamPelajaran::create([
        'jam_ke' => 3,
        'jam_mulai' => '09:00:00',
        'jam_selesai' => '10:00:00',
        'is_active' => true,
    ]);
    Jadwal::create([
        'guru_id' => $this->guru->id,
        'kelas_id' => $this->kelas->id,
        'mapel_id' => $this->mapel->id,
        'jam_pelajaran_id' => $secondPeriod->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);
    $this->travelTo(Carbon::parse('2026-09-28 09:15:00', 'Asia/Jakarta'));
    $this->actingAs($this->teacher)->post(route('jurnal.store'), [
        'status_guru' => 'Hadir',
        'materi' => 'Geometri',
        'latitude' => 0,
        'longitude' => 0.00005,
        'location_accuracy' => 10,
    ])->assertSessionHasNoErrors();

    $futureJournal = Jurnal::where('jam_mulai_id', $secondPeriod->id)->firstOrFail();
    $this->assertDatabaseHas('absensis', [
        'jurnal_id' => $futureJournal->id,
        'siswa_id' => $this->student->id,
        'status' => 'S',
    ]);
});

it('keeps a full-day Sakit status when a lesson dispensasi is approved later', function (): void {
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
    IzinSekolah::create([
        'siswa_id' => $this->student->id,
        'tanggal' => today(),
        'status' => 'S',
        'alasan' => 'Demam',
        'surat_izin_path' => 'izin-sekolah/surat/sakit.jpg',
        'piket_id' => $this->piket->id,
    ]);
    Absensi::create(['jurnal_id' => $journal->id, 'siswa_id' => $this->student->id, 'status' => 'S']);
    $dispensasi = Dispensasi::create([
        'siswa_id' => $this->student->id,
        'tanggal' => today(),
        'jam_mulai_id' => $this->jam->id,
        'jam_selesai_id' => $this->jam->id,
        'alasan' => 'Keperluan keluarga',
        'status_piket' => 'Disetujui',
        'status_admin' => 'Menunggu',
        'status_akhir' => 'Menunggu',
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
        ->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])
        ->assertRedirect();

    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Disetujui']);
    $this->assertDatabaseHas('absensis', [
        'jurnal_id' => $journal->id,
        'siswa_id' => $this->student->id,
        'status' => 'S',
        'surat_izin_path' => 'izin-sekolah/surat/sakit.jpg',
    ]);
});

it('rejects an existing same-day permission when Piket uploads the batch again', function (): void {
    $payload = [
        'siswa_ids' => [$this->student->id],
        'status' => 'I',
        'alasan' => 'Izin pertama',
        'surat_izin' => UploadedFile::fake()->image('surat-pertama.jpg'),
    ];
    $this->actingAs($this->piket)->post(route('piket.izin-sekolah.store'), $payload)->assertRedirect();
    $first = IzinSekolah::firstOrFail();

    $this->post(route('piket.izin-sekolah.store'), [
        'siswa_ids' => [$this->student->id],
        'status' => 'S',
        'alasan' => 'Berubah menjadi sakit',
        'surat_izin' => UploadedFile::fake()->image('surat-kedua.jpg'),
    ])->assertSessionHasErrors('siswa_ids');

    $updated = IzinSekolah::firstOrFail();
    expect($updated->id)->toBe($first->id)
        ->and($updated->status)->toBe('I')
        ->and($updated->alasan)->toBe('Izin pertama')
        ->and($updated->surat_izin_path)->toBe($first->surat_izin_path);
    $this->assertDatabaseCount('izin_sekolahs', 1);
});
