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
use App\Notifications\DispensasiApprovalMail;
use App\Notifications\DispensasiNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('completes the full admin, teacher, secretary, student, and duty-teacher workflow over HTTP', function () {
    config(['school.latitude' => 0, 'school.longitude' => 0, 'school.radius_meters' => 100, 'school.max_gps_accuracy' => 25]);
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
    Notification::fake();

    $admin = User::factory()->create([
        'username' => 'admin.e2e', 'email' => 'admin.e2e@example.test',
        'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true,
    ]);
    $piket = User::factory()->create([
        'username' => 'piket.e2e', 'password' => Hash::make('password'),
        'role' => 'piket', 'is_active' => true,
    ]);

    // Admin creates all operational data through the same endpoints used by the UI.
    $this->post('/login', ['login' => $admin->username, 'password' => 'password'])->assertRedirect('/admin');
    $this->get('/admin')->assertOk();
    $this->post(route('admin.kelas.store'), ['nama_kelas' => 'Kelas End to End', 'tingkat' => 'X'])
        ->assertRedirect(route('admin.kelas.index'));
    $kelas = Kelas::where('nama_kelas', 'Kelas End to End')->firstOrFail();
    $this->post(route('admin.gurus.store'), [
        'nama_guru' => 'Guru End to End', 'nip' => 'E2E-GURU', 'status_kepegawaian' => 'Honorer',
        'email' => 'guru.e2e@example.test', 'password' => 'password',
    ])->assertRedirect(route('admin.gurus.index'));
    $guru = Guru::where('nip', 'E2E-GURU')->firstOrFail();
    $this->post(route('admin.siswas.store'), [
        'nama_siswa' => 'Siswa End to End', 'nis' => 'E2E-SISWA', 'jenis_kelamin' => 'L',
        'kelas_id' => $kelas->id, 'email' => 'siswa.e2e@example.test', 'password' => 'password',
    ])->assertRedirect(route('admin.siswas.index'));
    $student = Siswa::where('nis', 'E2E-SISWA')->firstOrFail();
    $this->post(route('admin.mapel.store'), ['kode_mapel' => 'E2E', 'nama_mapel' => 'Mapel End to End'])
        ->assertRedirect(route('admin.mapel.index'));
    $mapel = Mapel::where('kode_mapel', 'E2E')->firstOrFail();
    $this->post(route('admin.jam.store'), [
        'jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'is_active' => 1,
    ])->assertRedirect(route('admin.jam.index'));
    $jam = JamPelajaran::where('jam_ke', 1)->firstOrFail();
    $this->post(route('jadwal.store'), [
        'guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $jam->id, 'hari' => 'Senin', 'is_active' => 1,
    ])->assertRedirect(route('jadwal.index'));
    $jadwal = Jadwal::firstOrFail();
    $this->post(route('logout'))->assertRedirect('/login');
    $this->assertGuest();

    // Provision the class secretary using the production command.
    expect(Artisan::call('app:provision-class-secretary-accounts'))->toBe(0);
    $secretary = $kelas->fresh()->sekretarisUsers()->firstOrFail();

    $teacherUser = $guru->user;
    $this->post('/login', ['login' => $teacherUser->username, 'password' => 'password'])->assertRedirect('/guru');
    $this->get('/guru')->assertOk();
    $this->get(route('jurnal.create'))->assertOk();
    $this->post(route('jurnal.store'), ['status_guru' => 'Hadir', 'materi' => 'Materi uji end to end',
        'latitude' => 0, 'longitude' => 0.00005, 'location_accuracy' => 10])
        ->assertRedirect();
    $journal = Jurnal::firstOrFail();
    $this->post(route('logout'))->assertRedirect('/login');

    $this->post('/login', ['login' => $secretary->username, 'password' => 'Jurnal-KELASENDTOEND-2026'])->assertRedirect('/sekretaris');
    $this->get('/sekretaris')->assertOk();
    $this->get(route('jadwal.show', $jadwal))->assertOk();
    $this->post(route('jurnal.verify', $journal), ['status' => 'Disetujui', 'catatan' => 'Terverifikasi'])
        ->assertRedirect();
    $this->assertDatabaseHas('jurnals', ['id' => $journal->id, 'status_verifikasi' => 'Disetujui']);
    $this->post(route('logout'))->assertRedirect('/login');

    // Submit the student request before the lesson starts.
    $this->travelTo(Carbon::parse('2026-09-14 06:00:00', 'Asia/Jakarta'));
    $this->post('/login', ['login' => $student->nis, 'password' => 'password'])->assertRedirect('/siswa');
    $this->get('/siswa')->assertOk();
    $this->get(route('dispensasi.create'))->assertOk();
    $this->post(route('dispensasi.store'), [
        'jam_mulai_id' => $jam->id, 'jam_selesai_id' => $jam->id, 'alasan' => 'Kegiatan sekolah',
    ])->assertRedirect(route('dispensasi.index'));
    $dispensasi = Dispensasi::firstOrFail();
    Notification::assertSentTo($piket, DispensasiNotification::class, fn ($notification) => $notification->event === 'submitted');
    $this->post(route('logout'))->assertRedirect('/login');

    $this->post('/login', ['login' => $piket->username, 'password' => 'password'])->assertRedirect('/piket');
    $this->get('/piket')->assertOk();
    $this->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensasi));
    Notification::assertSentTo($admin, DispensasiApprovalMail::class);
    $this->post(route('logout'))->assertRedirect('/login');

    $this->post('/login', ['login' => $admin->username, 'password' => 'password'])->assertRedirect('/admin');
    $this->get('/admin')->assertOk();
    $this->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensasi));
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Disetujui']);
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);
    Notification::assertSentTo($student->user, DispensasiNotification::class, fn ($notification) => $notification->event === 'admin_approved');
    Notification::assertSentTo($teacherUser, DispensasiNotification::class, fn ($notification) => $notification->event === 'teacher_approved');

    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
    $export = $this->get(route('laporan.absensi.export', ['status' => 'D']));
    $export->assertDownload('rekap-absensi.csv');
    expect($export->streamedContent())->toContain('Siswa End to End', 'Dispensasi');
    $this->post(route('logout'))->assertRedirect('/login');
    $this->assertGuest();
});
