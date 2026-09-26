<?php

use App\Models\JamPelajaran;
use App\Models\Kelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function unusedAlokasiRemovalMigration(): object
{
    return require database_path('migrations/2026_09_24_120000_drop_unused_empty_alokasi_jam_pelajarans_table.php');
}

it('drops the unused alokasi table only when it is empty', function () {
    $migration = unusedAlokasiRemovalMigration();
    $migration->down();

    expect(Schema::hasTable('alokasi_jam_pelajarans'))->toBeTrue();
    $migration->up();
    expect(Schema::hasTable('alokasi_jam_pelajarans'))->toBeFalse();
});

it('refuses to drop the alokasi table when deployment data exists', function () {
    $migration = unusedAlokasiRemovalMigration();
    $migration->down();
    $kelas = Kelas::create(['nama_kelas' => 'X ALOKASI AUDIT', 'tingkat' => 'X']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45']);
    DB::table('alokasi_jam_pelajarans')->insert([
        'kelas_id' => $kelas->id,
        'jam_pelajaran_id' => $period->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '07:45',
    ]);

    expect(fn () => $migration->up())->toThrow(RuntimeException::class);
    expect(Schema::hasTable('alokasi_jam_pelajarans'))->toBeTrue();
    expect(DB::table('alokasi_jam_pelajarans')->count())->toBe(1);
});
