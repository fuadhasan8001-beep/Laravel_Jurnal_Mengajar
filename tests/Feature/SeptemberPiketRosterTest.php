<?php

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use App\Support\SeptemberPiketRoster;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSeptemberRosterPeople(): void
{
    $groups = collect(SeptemberPiketRoster::groups());
    $names = $groups->flatMap(fn ($group) => [...$group['pagi'], ...$group['siang']])->unique();
    foreach ($names as $index => $name) {
        $name = SeptemberPiketRoster::canonicalName($name);
        $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
        Guru::create(['user_id' => $user->id, 'nama_guru' => $name.', S.Pd', 'nip' => 'ROSTER-'.$index, 'status_kepegawaian' => 'Honorer']);
    }
    foreach ($groups->pluck('waka')->unique() as $name) {
        User::factory()->create(['name' => $name.', S.Pd', 'role' => 'waka', 'is_active' => true]);
    }
}

it('imports all dated shifts and coordinators idempotently without changing other months', function () {
    createSeptemberRosterPeople();
    $widodo = Guru::where('nama_guru', 'Widodo, S.Pd')->firstOrFail();
    JadwalPiket::create(['guru_id' => $widodo->id, 'tanggal' => '2026-09-01', 'shift' => 'pagi']);
    $october = JadwalPiket::create(['guru_id' => $widodo->id, 'tanggal' => '2026-10-01', 'shift' => 'pagi']);

    $this->artisan('app:import-september-piket-roster')->assertSuccessful();
    expect(JadwalPiket::count())->toBe(2);
    $this->artisan('app:import-september-piket-roster', ['--apply' => true])->assertSuccessful();
    $ids = JadwalPiket::orderBy('id')->pluck('id')->all();
    $this->artisan('app:import-september-piket-roster', ['--apply' => true])->assertSuccessful();
    expect(JadwalPiket::orderBy('id')->pluck('id')->all())->toBe($ids);
    $september = JadwalPiket::whereDate('tanggal', '>=', '2026-09-01')->whereDate('tanggal', '<=', '2026-09-30')->get();
    expect($september)->toHaveCount(198)
        ->and($september->where('shift', 'pagi'))->toHaveCount(88)
        ->and($september->where('shift', 'siang'))->toHaveCount(88)
        ->and($september->where('shift', 'waka'))->toHaveCount(22)
        ->and($september->where('is_koordinator', true))->toHaveCount(44);
    expect($october->fresh()->tanggal->toDateString())->toBe('2026-10-01');
    $widodoRows = $september->where('guru_id', $widodo->id);
    expect($widodoRows->pluck('shift')->unique()->all())->toBe(['siang'])
        ->and($widodoRows->every(fn ($row) => $row->is_koordinator))->toBeTrue()
        ->and($widodoRows->map(fn ($row) => $row->tanggal->toDateString())->sort()->values()->all())->toBe(['2026-09-01', '2026-09-15', '2026-09-29'])
        ->and($september->contains(fn ($row) => $row->tanggal->toDateString() === '2026-09-05'))->toBeFalse();
    expect($september->where('shift', 'waka')->every(fn ($row) => $row->guru_id === null && $row->user_id !== null && ! $row->is_koordinator))->toBeTrue();
});

it('refuses all writes when a roster name has no unique active match', function (string $problem) {
    createSeptemberRosterPeople();
    $guru = Guru::where('nama_guru', 'Widodo, S.Pd')->firstOrFail();
    $existing = JadwalPiket::create(['guru_id' => $guru->id, 'tanggal' => '2026-09-01', 'shift' => 'pagi']);
    if ($problem === 'duplicate') {
        Guru::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'nama_guru' => 'Widodo, M.Pd', 'nip' => 'DUPLICATE', 'status_kepegawaian' => 'Honorer']);
    } elseif ($problem === 'missing') {
        $guru->update(['nama_guru' => 'Nama berbeda']);
    } else {
        $guru->user->update(['is_active' => false]);
    }

    $this->artisan('app:import-september-piket-roster', ['--apply' => true])->assertFailed();
    expect(JadwalPiket::count())->toBe(1);
    $this->assertDatabaseHas('jadwal_pikets', ['id' => $existing->id, 'shift' => 'pagi']);
})->with(['duplicate', 'missing', 'inactive']);

it('uses the exact date and shift for ordinary staff and coordinators', function (string $shift, bool $coordinator) {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $user->id, 'nama_guru' => 'Petugas', 'nip' => 'ACCESS', 'status_kepegawaian' => 'Honorer']);
    $assignment = JadwalPiket::create(['guru_id' => $guru->id, 'tanggal' => '2026-09-30', 'shift' => $shift, 'is_koordinator' => $coordinator]);
    $this->actingAs($user);
    foreach (['06:59:59', '07:00:00', '10:59:59', '11:00:00', '14:59:59', '15:00:00'] as $time) {
        $this->travelTo(Carbon::parse('2026-09-30 '.$time));
        $active = $shift === 'pagi' ? in_array($time, ['07:00:00', '10:59:59']) : in_array($time, ['11:00:00', '14:59:59']);
        expect($user->fresh()->isPiketHariIni())->toBe($active);
        $this->get(route('piket.rekap-jurnal'))->assertStatus($active ? 200 : 403);
        $this->get(route('piket.rekap-jurnal.export'))->assertStatus($active ? 200 : 403);
        $this->get('/piket')->assertStatus($active ? 200 : 403);
        $this->get(route('dispensasi.create'))->assertStatus($active ? 200 : 403);
        $dashboard = $this->get('/guru')->assertOk();
        if ($active) {
            $dashboard->assertSee('Rekap jurnal per kelas');
        } else {
            $dashboard->assertDontSee('Rekap jurnal per kelas');
        }
    }
    $this->travelTo(Carbon::parse('2026-10-07 08:00:00'));
    expect($assignment->isActiveNow())->toBeFalse()->and($user->fresh()->isPiketHariIni())->toBeFalse();
    $this->get(route('piket.rekap-jurnal'))->assertForbidden();
})->with([
    'staff morning' => ['pagi', false], 'coordinator morning' => ['pagi', true],
    'staff afternoon' => ['siang', false], 'coordinator afternoon' => ['siang', true],
]);

it('keeps Waka duty separate from KBM authorization', function () {
    $this->travelTo(Carbon::parse('2026-09-30 08:00:00'));
    $waka = User::factory()->create(['role' => 'waka', 'is_active' => true]);
    JadwalPiket::create(['user_id' => $waka->id, 'tanggal' => today(), 'shift' => 'waka']);
    expect($waka->isPiketHariIni())->toBeFalse();
    $this->actingAs($waka)->get(route('piket.rekap-jurnal'))->assertForbidden();
    $this->get(route('dispensasi.index'))->assertOk();
});
