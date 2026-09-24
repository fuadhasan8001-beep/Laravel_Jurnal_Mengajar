<?php

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
