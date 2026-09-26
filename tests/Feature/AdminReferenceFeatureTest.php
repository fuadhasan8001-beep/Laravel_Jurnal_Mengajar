<?php

use App\Models\JamPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects lesson end times that are equal to or earlier than the start time', function (string $start, string $end) {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->post(route('admin.jam.store'), [
        'jam_ke' => 1,
        'jam_mulai' => $start,
        'jam_selesai' => $end,
        'is_active' => '1',
    ])->assertSessionHasErrors('jam_selesai');

    $this->assertDatabaseCount('jam_pelajarans', 0);
})->with([
    'equal times' => ['14:30', '14:30'],
    'end before start' => ['14:30', '14:00'],
]);

it('accepts consecutive lessons that touch at the boundary', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->post(route('admin.jam.store'), [
        'jam_ke' => 1,
        'jam_mulai' => '14:00',
        'jam_selesai' => '14:30',
        'is_active' => '1',
    ])->assertRedirect(route('admin.jam.index'));
    $this->post(route('admin.jam.store'), [
        'jam_ke' => 2,
        'jam_mulai' => '14:30',
        'jam_selesai' => '15:00',
        'is_active' => '1',
    ])->assertRedirect(route('admin.jam.index'));

    $this->assertDatabaseCount('jam_pelajarans', 2);
});

it('creates and updates optional Friday lesson times', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->post(route('admin.jam.store'), [
        'jam_ke' => 1,
        'jam_mulai' => '07:00',
        'jam_selesai' => '07:45',
        'jam_mulai_jumat' => '10:00',
        'jam_selesai_jumat' => '10:30',
        'is_active' => 1,
    ])->assertRedirect(route('admin.jam.index'));

    $period = JamPelajaran::sole();
    expect($period->timesForDay('Jumat'))->toBe(['10:00', '10:30']);
    $this->get(route('admin.jam.edit', $period))->assertOk()->assertSee('jam_mulai_jumat');
    $this->get(route('admin.jam.index'))->assertOk()->assertSee('10:00')->assertSee('10:30');

    $this->put(route('admin.jam.update', $period), [
        'jam_ke' => 1,
        'jam_mulai' => '07:00',
        'jam_selesai' => '07:45',
        'jam_mulai_jumat' => '10:05',
        'jam_selesai_jumat' => '10:35',
        'is_active' => 1,
    ])->assertRedirect(route('admin.jam.index'));

    expect($period->fresh()->timesForDay('Jumat'))->toBe(['10:05', '10:35']);
});

it('rejects invalid Friday periods and overlapping Friday periods', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    JamPelajaran::create([
        'jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45',
        'jam_mulai_jumat' => '10:00', 'jam_selesai_jumat' => '10:30', 'is_active' => true,
    ]);

    $base = ['jam_ke' => 2, 'jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'is_active' => 1];
    $this->actingAs($admin)->post(route('admin.jam.store'), [...$base, 'jam_mulai_jumat' => '10:30', 'jam_selesai_jumat' => '10:30'])
        ->assertSessionHasErrors('jam_selesai_jumat');
    $this->post(route('admin.jam.store'), [...$base, 'jam_mulai_jumat' => '10:20', 'jam_selesai_jumat' => '10:40'])
        ->assertSessionHasErrors('jam_mulai');

    $this->assertDatabaseCount('jam_pelajarans', 1);
});
