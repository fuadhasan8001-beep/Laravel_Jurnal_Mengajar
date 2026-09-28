<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('requires the current password before changing it', function () {
    $user = User::factory()->create(['password' => Hash::make('current-password'), 'role' => 'guru']);

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('current-password', $user->fresh()->password))->toBeTrue();
});

it('hashes a confirmed new password after validating the current password', function () {
    $user = User::factory()->create(['password' => Hash::make('current-password'), 'role' => 'guru']);

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'current-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
