<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('rejects inactive users during login', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => Hash::make('password'),
        'role' => 'guru',
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('blocks a role from another role dashboard', function () {
    $user = User::factory()->create([
        'role' => 'guru',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
