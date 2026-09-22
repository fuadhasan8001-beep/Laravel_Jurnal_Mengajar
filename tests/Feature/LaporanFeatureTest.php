<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows operational roles to view report pages', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('laporan.jurnal'))->assertOk();
    $this->actingAs($user)->get(route('laporan.absensi'))->assertOk();
    $this->actingAs($user)->get(route('laporan.dispensasi'))->assertOk();
});

it('exports report data as csv', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('laporan.jurnal.export'))->assertDownload('rekap-jurnal.csv');
    $this->actingAs($user)->get(route('laporan.absensi.export'))->assertDownload('rekap-absensi.csv');
    $this->actingAs($user)->get(route('laporan.dispensasi.export'))->assertDownload('rekap-dispensasi.csv');
});

it('classifies scheduled lessons by journal and teacher leave status', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacherUser->id, 'nip' => 'MONITOR-1', 'nama_guru' => 'Guru Monitor', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X MONITOR', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'MON', 'nama_mapel' => 'Monitoring']);
    $periods = collect([
        ['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00'],
        ['jam_ke' => 2, 'jam_mulai' => '07:40:00', 'jam_selesai' => '08:20:00'],
        ['jam_ke' => 3, 'jam_mulai' => '08:20:00', 'jam_selesai' => '09:00:00'],
        ['jam_ke' => 4, 'jam_mulai' => '09:00:00', 'jam_selesai' => '09:40:00'],
    ])->map(fn (array $period) => JamPelajaran::create([...$period, 'is_active' => true]));

    foreach ($periods as $period) {
        Jadwal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
            'jam_pelajaran_id' => $period->id, 'hari' => 'Senin', 'is_active' => true]);
    }

    foreach (['Hadir', 'Izin', 'Sakit'] as $index => $status) {
        Jurnal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
            'jam_mulai_id' => $periods[$index]->id, 'jam_selesai_id' => $periods[$index]->id,
            'tanggal' => '2026-09-14', 'status_guru' => $status, 'materi' => 'Materi']);
    }

    $response = $this->actingAs($admin)->get(route('laporan.jurnal', ['monitoring_date' => '2026-09-14']));

    $response->assertOk()->assertViewHas('monitoring', function ($monitoring): bool {
        return $monitoring->pluck('status')->all() === [
            'Jurnal sudah dibuat',
            'Guru izin',
            'Guru sakit',
            'Belum mengisi jurnal',
        ];
    });
});
