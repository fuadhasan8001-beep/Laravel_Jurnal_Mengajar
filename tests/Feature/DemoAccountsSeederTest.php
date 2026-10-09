<?php

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\SchoolEvent;
use App\Models\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('creates connected demo accounts and workflow records idempotently', function () {
    $this->seed(DemoAccountsSeeder::class);

    $teacherUser = User::where('username', 'demo.guru')->firstOrFail();
    $studentUser = User::where('username', 'demo.siswa')->firstOrFail();
    $secretary = User::where('username', 'demo.sekretaris')->firstOrFail();
    $class = Kelas::where('nama_kelas', 'X RPL 99')->firstOrFail();
    $journal = Jurnal::where('guru_id', $teacherUser->guru->id)
        ->where('materi', 'Demo: jurnal untuk diverifikasi')->firstOrFail();
    $dispensation = Dispensasi::where('siswa_id', $studentUser->siswa->id)
        ->where('alasan', 'Demo: pengajuan dispensasi siswa')->firstOrFail();

    expect(User::whereIn('username', [
        'demo.admin', 'demo.waka', 'demo.guru', 'demo.siswa', 'demo.siswa2', 'demo.sekretaris', 'demo.piket',
    ])->count())->toBe(7)
        ->and($teacherUser->kelasWali()->whereKey($class->id)->exists())->toBeTrue()
        ->and($secretary->kelasSekretaris()->whereKey($class->id)->exists())->toBeTrue()
        ->and($studentUser->siswa->kelas_id)->toBe($class->id)
        ->and(Jadwal::where('guru_id', $teacherUser->guru->id)->where('kelas_id', $class->id)->count())->toBe(12)
        ->and($journal->status_verifikasi)->toBe('Menunggu')
        ->and($journal->school_event_id)->not->toBeNull()
        ->and(Absensi::where('jurnal_id', $journal->id)->count())->toBe(2)
        ->and($dispensation->status_piket)->toBe('Menunggu')
        ->and($dispensation->status_akhir)->toBe('Menunggu')
        ->and(SchoolEvent::whereIn('title', ['Demo: kegiatan kelas RPL', 'Demo: lokakarya guru'])->count())->toBe(2);

    $this->seed(DemoAccountsSeeder::class);

    expect(User::whereIn('username', [
        'demo.admin', 'demo.waka', 'demo.guru', 'demo.siswa', 'demo.siswa2', 'demo.sekretaris', 'demo.piket',
    ])->count())->toBe(7)
        ->and(Jurnal::where('guru_id', $teacherUser->guru->id)->where('materi', 'Demo: jurnal untuk diverifikasi')->count())->toBe(1)
        ->and(Dispensasi::where('siswa_id', $studentUser->siswa->id)->where('alasan', 'Demo: pengajuan dispensasi siswa')->count())->toBe(1);
});

it('lets every demo role log in and reach its role dashboard', function (string $username, string $dashboard) {
    $this->seed(DemoAccountsSeeder::class);

    $this->post(route('login'), [
        'login' => $username,
        'password' => 'password123',
    ])->assertRedirect($dashboard);

    $this->get($dashboard)->assertOk();
})->with([
    'admin' => ['demo.admin', '/admin'],
    'waka' => ['demo.waka', '/admin'],
    'guru wali kelas' => ['demo.guru', '/guru'],
    'siswa' => ['demo.siswa', '/siswa'],
    'sekretaris' => ['demo.sekretaris', '/sekretaris'],
    'piket' => ['demo.piket', '/piket'],
]);

it('connects the seeded student, piket, waka, teacher, and secretary workflows', function () {
    $this->seed(DemoAccountsSeeder::class);
    Notification::fake();

    $student = User::where('username', 'demo.siswa')->firstOrFail();
    $piket = User::where('username', 'demo.piket')->firstOrFail();
    $waka = User::where('username', 'demo.waka')->firstOrFail();
    $secretary = User::where('username', 'demo.sekretaris')->firstOrFail();
    $dispensation = Dispensasi::where('siswa_id', $student->siswa->id)
        ->where('alasan', 'Demo: pengajuan dispensasi siswa')->firstOrFail();
    $journal = Jurnal::where('materi', 'Demo: jurnal untuk diverifikasi')->firstOrFail();

    $this->actingAs($student)->get(route('dispensasi.index'))
        ->assertOk()->assertSee('Siswa Demo')->assertSee('Menunggu');
    $this->actingAs($piket)->post(route('dispensasi.verify', $dispensation), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensation));
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensation->id, 'status_piket' => 'Disetujui', 'status_akhir' => 'Menunggu']);

    $this->actingAs($waka)->post(route('dispensasi.verify', $dispensation), ['status' => 'Disetujui'])
        ->assertRedirect(route('dispensasi.show', $dispensation));
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensation->id, 'status_akhir' => 'Disetujui']);
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->siswa->id, 'status' => 'D']);

    $this->actingAs($secretary)->get(route('jurnal.index'))
        ->assertOk()->assertSee('Demo: jurnal untuk diverifikasi');
    $this->post(route('jurnal.verify', $journal), ['status' => 'Disetujui'])
        ->assertRedirect(route('jurnal.show', $journal));
    $this->assertDatabaseHas('jurnals', ['id' => $journal->id, 'status_verifikasi' => 'Disetujui']);
});
