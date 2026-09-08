<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows operational roles to view report pages', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('laporan.jurnal'))->assertOk();
    $this->actingAs($user)->get(route('laporan.absensi'))->assertOk();
    $this->actingAs($user)->get(route('laporan.dispensasi'))->assertOk();
});

it('exports report data as csv', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $this->actingAs($user)->get(route('laporan.jurnal.export'))->assertDownload('rekap-jurnal.csv');
    $this->actingAs($user)->get(route('laporan.absensi.export'))->assertDownload('rekap-absensi.csv');
    $this->actingAs($user)->get(route('laporan.dispensasi.export'))->assertDownload('rekap-dispensasi.csv');
});
