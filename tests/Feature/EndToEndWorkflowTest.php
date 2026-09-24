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
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('completes the journal verification and approved dispensation attendance flow', function () {
    $this->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $studentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $guru = Guru::create([
        'user_id' => $teacher->id,
        'nip' => 'E2E-GURU',
        'nama_guru' => 'Guru End to End',
        'status_kepegawaian' => 'Honorer',
    ]);
    $kelas = Kelas::create(['nama_kelas' => 'Kelas End to End', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'E2E', 'nama_mapel' => 'Mapel End to End']);
    $jam = JamPelajaran::create([
        'jam_ke' => 1,
        'jam_mulai' => '07:00:00',
        'jam_selesai' => '07:45:00',
        'is_active' => true,
    ]);
    $student = Siswa::create([
        'user_id' => $studentUser->id,
        'kelas_id' => $kelas->id,
        'nis' => 'E2E-SISWA',
        'nama_siswa' => 'Siswa End to End',
        'jenis_kelamin' => 'L',
    ]);
    $kelas->sekretarisUsers()->attach($secretary);
    Jadwal::create([
        'guru_id' => $guru->id,
        'kelas_id' => $kelas->id,
        'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $jam->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);

    $this->actingAs($teacher)->post(route('jurnal.store'), [
        'status_guru' => 'Hadir',
        'materi' => 'Materi uji end to end',
    ])->assertRedirect();
    $journal = Jurnal::firstOrFail();

    $this->actingAs($secretary)->post(route('jurnal.verify', $journal), [
        'status' => 'Disetujui',
        'catatan' => 'Terverifikasi',
    ])->assertRedirect();
    $this->assertDatabaseHas('jurnals', ['id' => $journal->id, 'status_verifikasi' => 'Disetujui']);

    $this->travelTo(Carbon::parse('2026-09-14 06:00:00', 'Asia/Jakarta'));
    $dispensationData = [
        'tanggal' => '2026-09-14',
        'jam_mulai_id' => $jam->id,
        'jam_selesai_id' => $jam->id,
        'alasan' => 'Kegiatan sekolah',
    ];
    $this->actingAs($studentUser)->post(route('dispensasi.store'), $dispensationData)
        ->assertRedirect(route('dispensasi.index'));
    $dispensasi = Dispensasi::firstOrFail();
    Notification::assertSentTo($piket, DispensasiNotification::class, fn ($notification) => $notification->event === 'submitted');

    $this->actingAs($piket)->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensasi));
    Notification::assertSentTo($admin, DispensasiApprovalMail::class);

    $this->actingAs($admin)->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensasi));
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Disetujui']);
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);
    Notification::assertSentTo($studentUser, DispensasiNotification::class, fn ($notification) => $notification->event === 'admin_approved');
    Notification::assertSentTo($teacher, DispensasiNotification::class, fn ($notification) => $notification->event === 'teacher_approved');

    $export = $this->get(route('laporan.absensi.export', ['status' => 'D']));
    $export->assertDownload('rekap-absensi.csv');
    expect($export->streamedContent())->toContain('Siswa End to End', 'Dispensasi');
});
