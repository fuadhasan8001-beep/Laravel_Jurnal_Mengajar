<?php

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows a visitor to open the registration page', function () {
    $this->get(route('register'))->assertOk()->assertSee('Daftar akun');
});

it('stores a registration request as pending without creating a user', function () {
    $this->post(route('register.store'), [
        'name' => 'Pendaftar Baru', 'email' => 'pendaftar@example.com', 'password' => 'password123',
        'password_confirmation' => 'password123', 'role' => 'guru',
    ])->assertRedirect(route('register.success'));

    $registration = RegistrationRequest::first();
    expect($registration->status)->toBe('pending')
        ->and(User::where('email', 'pendaftar@example.com')->exists())->toBeFalse()
        ->and(Hash::check('password123', $registration->password))->toBeTrue();
});

it('rejects duplicate pending emails', function () {
    RegistrationRequest::create([
        'name' => 'Pendaftar Lama', 'email' => 'pending@example.com', 'password' => Hash::make('password123'),
        'role' => 'guru', 'status' => 'pending',
    ]);

    $this->post(route('register.store'), [
        'name' => 'Pendaftar Baru', 'email' => 'pending@example.com', 'password' => 'password123',
        'password_confirmation' => 'password123', 'role' => 'siswa',
    ])->assertSessionHasErrors('email');

    expect(RegistrationRequest::where('email', 'pending@example.com')->count())->toBe(1);
});

it('does not allow a pending request to log in', function () {
    $this->post(route('register.store'), [
        'name' => 'Pendaftar Pending', 'email' => 'pending-login@example.com', 'password' => 'password123',
        'password_confirmation' => 'password123', 'role' => 'guru',
    ]);

    $this->post('/login', ['login' => 'pending-login@example.com', 'password' => 'password123'])
        ->assertSessionHasErrors('login');
    expect(auth()->check())->toBeFalse();
});

it('blocks non-admin users from registration administration', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->actingAs($user)->get(route('admin.registrations.index'))->assertForbidden();
});

it('allows an admin to approve a registration and the new user can log in', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $registration = RegistrationRequest::create([
        'name' => 'Guru Disetujui', 'email' => 'approved@example.com', 'password' => Hash::make('password123'),
        'role' => 'guru', 'status' => 'pending',
    ]);

    $this->actingAs($admin)->post(route('admin.registrations.approve', $registration))
        ->assertRedirect(route('admin.registrations.index'));

    $user = User::where('email', 'approved@example.com')->firstOrFail();
    expect($registration->fresh()->status)->toBe('approved')
        ->and($user->role)->toBe('guru')->and($user->is_active)->toBeTrue();

    auth()->logout();
    $this->post('/login', ['login' => $user->username, 'password' => 'password123'])
        ->assertRedirect('/guru');
});

it('allows an admin to reject a registration without creating a user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $registration = RegistrationRequest::create([
        'name' => 'Guru Ditolak', 'email' => 'rejected@example.com', 'password' => Hash::make('password123'),
        'role' => 'guru', 'status' => 'pending',
    ]);

    $this->actingAs($admin)->post(route('admin.registrations.reject', $registration), [
        'rejection_reason' => 'Data belum lengkap.',
    ])->assertRedirect(route('admin.registrations.index'));

    expect($registration->fresh()->status)->toBe('rejected')
        ->and($registration->fresh()->rejection_reason)->toBe('Data belum lengkap.')
        ->and(User::where('email', 'rejected@example.com')->exists())->toBeFalse();
});
