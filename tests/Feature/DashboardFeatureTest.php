<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders each role dashboard with its required view data', function (string $role, string $path) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->actingAs($user)->get($path)->assertOk();
})->with([
    'admin' => ['admin', '/admin'],
    'guru' => ['guru', '/guru'],
    'siswa' => ['siswa', '/siswa'],
    'piket' => ['piket', '/piket'],
    'sekretaris' => ['sekretaris', '/sekretaris'],
]);

it('denies a role access to another role dashboard', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('redirects guests from dashboards to login', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/admin', '/guru', '/siswa', '/piket', '/sekretaris']);

it('returns a not found response for an unknown page', function () {
    $this->get('/page-that-does-not-exist')->assertNotFound();
});
