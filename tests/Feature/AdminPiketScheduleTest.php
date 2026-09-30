<?php

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-09-30 08:00:00'));
});

it('can schedule both kbm shifts for the same teacher on one day', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => 'SHIFT-1', 'nama_guru' => 'Guru Shift', 'status_kepegawaian' => 'Honorer']);

    $this->actingAs($admin)->post(route('admin.piket.store'), [
        'jenis_tugas' => 'kbm', 'guru_id' => $guru->id, 'tanggal' => '2026-09-30', 'shift' => 'pagi',
    ])->assertRedirect();
    $this->post(route('admin.piket.store'), [
        'jenis_tugas' => 'kbm', 'guru_id' => $guru->id, 'tanggal' => '2026-09-30', 'shift' => 'siang',
    ])->assertRedirect();

    expect(JadwalPiket::where('guru_id', $guru->id)->whereDate('tanggal', '2026-09-30')->pluck('shift')->all())
        ->toBe(['pagi', 'siang']);
});

it('stores a Waka duty separately from teacher KBM shifts', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $waka = User::factory()->create(['role' => 'waka', 'name' => 'Waka Piket']);

    $this->actingAs($admin)->post(route('admin.piket.store'), [
        'jenis_tugas' => 'waka', 'user_id' => $waka->id, 'tanggal' => '2026-09-30',
    ])->assertRedirect();

    $schedule = JadwalPiket::where('user_id', $waka->id)->whereDate('tanggal', '2026-09-30')->firstOrFail();
    expect($schedule->guru_id)->toBeNull()
        ->and($schedule->shift)->toBe(JadwalPiket::SHIFT_WAKA)
        ->and($schedule->shiftLabel())->toBe('Piket Waka');
});

it('shows past and future Waka and KBM assignments within the selected month', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => 'MONTH-1', 'nama_guru' => 'Koordinator Pagi', 'status_kepegawaian' => 'Honorer']);
    $waka = User::factory()->create(['role' => 'waka', 'name' => 'Piket Waka September']);
    JadwalPiket::create(['guru_id' => $guru->id, 'tanggal' => '2026-09-01', 'shift' => 'pagi']);
    JadwalPiket::create(['user_id' => $waka->id, 'tanggal' => '2026-09-30', 'shift' => 'waka']);

    $this->actingAs($admin)->get(route('admin.piket.index', ['bulan' => '2026-09']))
        ->assertOk()
        ->assertViewHas('jadwals', fn ($jadwals) => $jadwals->count() === 2)
        ->assertSee('Koordinator Pagi')
        ->assertSee('Pagi (07.00–11.00)')
        ->assertSee('Piket Waka September')
        ->assertSee('Piket Waka');

    expect($waka->fresh()->isPiketHariIni())->toBeFalse();
});

it('preserves the coordinator assignment through the admin form and enables its shift access', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => 'COORD-1', 'nama_guru' => 'Koordinator Test', 'status_kepegawaian' => 'Honorer']);
    $payload = ['jenis_tugas' => 'kbm', 'guru_id' => $guru->id, 'tanggal' => '2026-09-30', 'shift' => 'pagi', 'is_koordinator' => '1'];
    $this->actingAs($admin)->post(route('admin.piket.store'), $payload)->assertRedirect();
    $this->post(route('admin.piket.store'), $payload)->assertRedirect();
    expect(JadwalPiket::where('guru_id', $guru->id)->count())->toBe(1);
    $schedule = JadwalPiket::where('guru_id', $guru->id)->firstOrFail();
    expect($schedule->is_koordinator)->toBeTrue()->and($teacher->isPiketHariIni())->toBeTrue();
    $this->get(route('admin.piket.index', ['bulan' => '2026-09']))->assertOk()->assertSee('Koordinator pagi');
    $this->put(route('admin.piket.update', $schedule), [...$payload, 'shift' => 'siang'])->assertRedirect();
    expect($schedule->fresh()->is_koordinator)->toBeTrue()->and($teacher->fresh()->isPiketHariIni())->toBeFalse();
    $this->put(route('admin.piket.update', $schedule), [...$payload, 'is_koordinator' => '0'])->assertRedirect();
    expect($schedule->fresh()->is_koordinator)->toBeFalse();
});

it('keeps the original piket date fixed when editing a scheduled assignment', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => 'FIXED-DATE', 'nama_guru' => 'Guru Tanggal Tetap', 'status_kepegawaian' => 'Honorer']);

    $this->actingAs($admin)->post(route('admin.piket.store'), [
        'jenis_tugas' => 'kbm',
        'guru_id' => $guru->id,
        'tanggal' => '2026-09-30',
        'shift' => 'pagi',
    ])->assertRedirect();

    $schedule = JadwalPiket::where('guru_id', $guru->id)->firstOrFail();

    $this->put(route('admin.piket.update', $schedule), [
        'jenis_tugas' => 'kbm',
        'guru_id' => $guru->id,
        'tanggal' => '2026-10-02',
        'shift' => 'siang',
    ])->assertRedirect();

    expect($schedule->fresh()->tanggal->toDateString())->toBe('2026-09-30')
        ->and($schedule->fresh()->shift)->toBe('siang');
});
