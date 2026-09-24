<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('does not allow a user to mark another users notification as read', function () {
    $owner = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $notificationId = (string) Str::uuid();
    DB::table('notifications')->insert([
        'id' => $notificationId,
        'type' => 'test',
        'notifiable_type' => User::class,
        'notifiable_id' => $owner->id,
        'data' => json_encode(['message' => 'Private notification'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($otherUser)->post(route('notifications.read', $notificationId))
        ->assertRedirect();

    expect(DB::table('notifications')->where('id', $notificationId)->value('read_at'))->toBeNull();
});
