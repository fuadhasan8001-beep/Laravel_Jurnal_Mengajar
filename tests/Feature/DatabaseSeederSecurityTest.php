<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('falls back to a known local development password when no custom seed password is configured', function () {
    config(['seeding.default_password' => null]);

    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@test.com')->firstOrFail();

    expect(Hash::check('password', $admin->password))->toBeTrue();
});

it('uses the configured development seed password and preserves existing credentials', function () {
    config(['seeding.default_password' => 'configured-development-password']);

    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@test.com')->firstOrFail();
    expect(Hash::check('configured-development-password', $admin->password))->toBeTrue();
    $admin->update(['password' => Hash::make('manually-changed-password')]);
    $changedPasswordHash = $admin->fresh()->password;

    $this->seed(DatabaseSeeder::class);

    expect($admin->fresh()->password)->toBe($changedPasswordHash);
});
