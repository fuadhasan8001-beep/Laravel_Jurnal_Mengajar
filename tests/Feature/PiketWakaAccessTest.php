<?php

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\DispensasiApprovalMail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->travelTo(Carbon::parse('2026-09-28 07:15:00', 'Asia/Jakarta'));
    $this->teacher = User::factory()->create(['name' => 'Badrus Sulaiman', 'role' => 'guru', 'is_active' => true]);
    $this->guru = Guru::create(['user_id' => $this->teacher->id, 'nip' => 'PIKET-1', 'nama_guru' => $this->teacher->name, 'status_kepegawaian' => 'Honorer']);
    $this->waka = User::factory()->create(['name' => 'Setiyo Winarko', 'username' => 'waka.test', 'role' => 'waka', 'is_active' => true]);
    $kelas = Kelas::create(['nama_kelas' => 'XI RPL 2', 'tingkat' => 'XI']);
    $this->student = Siswa::create(['user_id' => User::factory()->create(['role' => 'siswa'])->id, 'kelas_id' => $kelas->id, 'nis' => 'TEST-1', 'nama_siswa' => 'Siswa Uji', 'jenis_kelamin' => 'L']);
    $jam = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'is_active' => true]);
    $this->payload = ['siswa_ids' => [$this->student->id], 'jam_mulai_id' => $jam->id, 'jam_selesai_id' => $jam->id, 'alasan' => 'Kegiatan sekolah'];
    $this->dispensasi = Dispensasi::create(['siswa_id' => $this->student->id, 'tanggal' => today(), 'jam_mulai_id' => $jam->id, 'jam_selesai_id' => $jam->id, 'alasan' => 'Kegiatan sekolah']);
});

it('allows duty teacher access only on the assigned date including direct URLs', function () {
    JadwalPiket::create(['guru_id' => $this->guru->id, 'tanggal' => today()->subDay()]);
    $this->actingAs($this->teacher);
    foreach (['/piket', route('dispensasi.index'), route('dispensasi.create'), route('dispensasi.show', $this->dispensasi), route('dispensasi.evidence', $this->dispensasi), route('dispensasi.parent-letter', $this->dispensasi)] as $url) {
        $this->get($url)->assertForbidden();
    }
    $this->post(route('dispensasi.store'), $this->payload)->assertForbidden();
    $this->post(route('dispensasi.verify', $this->dispensasi), ['status' => 'Disetujui'])->assertForbidden();
    JadwalPiket::create(['guru_id' => $this->guru->id, 'tanggal' => today()]);
    $this->get('/piket')->assertOk();
    $this->get(route('dispensasi.create'))->assertOk();
    $this->travelTo(now()->addDay());
    $this->get('/piket')->assertForbidden();
});

it('lets waka use the admin workflow and admin-like navigation without being able to submit student dispensasi', function () {
    $this->post('/login', ['login' => 'waka.test', 'password' => 'password'])->assertRedirect('/admin');
    $this->get('/admin')->assertOk()->assertSee('Buka verifikasi');
    $this->get('/admin/data/guru')->assertOk();
    $this->get('/waka')->assertRedirect('/admin');
    $this->get(route('dispensasi.index'))->assertOk()->assertSee('Verifikasi dispensasi');
    $this->post(route('dispensasi.store'), $this->payload)->assertForbidden();
    $this->post(route('dispensasi.verify', $this->dispensasi), ['status' => 'Disetujui'])->assertUnprocessable();
});

it('records the real submitter and waka verifier and prevents a second decision', function () {
    $this->dispensasi->delete();
    JadwalPiket::create(['guru_id' => $this->guru->id, 'tanggal' => today()]);
    $this->actingAs($this->teacher)->post(route('dispensasi.store'), [...$this->payload, 'piket_id' => $this->waka->id, 'waka_id' => $this->teacher->id])->assertRedirect();
    $record = Dispensasi::firstOrFail();
    expect($record->piket_id)->toBe($this->teacher->id)->and($record->waka_id)->toBeNull();
    Notification::assertSentTo($this->waka, DispensasiApprovalMail::class);
    $this->actingAs($this->waka)->get(route('dispensasi.show', $record))->assertOk()->assertSee('Ambil keputusan');
    $this->post(route('dispensasi.verify', $record), ['status' => 'Disetujui'])->assertRedirect();
    $record->refresh();
    expect($record->waka_id)->toBe($this->waka->id)->and($record->admin_id)->toBeNull()->and($record->verified_waka_at)->not->toBeNull()->and($record->status_akhir)->toBe('Disetujui');
    $this->get(URL::temporarySignedRoute('dispensasi.public-proof', now()->addHour(), $record))->assertOk()->assertSee('Badrus Sulaiman')->assertSee('Setiyo Winarko');
    $this->post(route('dispensasi.verify', $record), ['status' => 'Ditolak'])->assertUnprocessable();
});

it('does not label admin approvals as waka approvals', function () {
    $this->dispensasi->update(['status_piket' => 'Disetujui', 'piket_id' => $this->teacher->id]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->actingAs($admin)->post(route('dispensasi.verify', $this->dispensasi), ['status' => 'Disetujui'])->assertRedirect();
    expect($this->dispensasi->fresh()->admin_id)->toBe($admin->id)->and($this->dispensasi->fresh()->waka_id)->toBeNull();
});
