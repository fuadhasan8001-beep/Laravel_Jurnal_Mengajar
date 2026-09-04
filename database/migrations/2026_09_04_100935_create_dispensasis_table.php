<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('dispensasis', function (Blueprint $table) {
        $table->id();

        $table->foreignId('siswa_id')
            ->constrained('siswas')
            ->cascadeOnDelete();

        $table->date('tanggal');

        $table->foreignId('jam_mulai_id')
            ->constrained('jam_pelajarans')
            ->restrictOnDelete();

        $table->foreignId('jam_selesai_id')
            ->constrained('jam_pelajarans')
            ->restrictOnDelete();

        $table->text('alasan');

        // Path file bukti/surat yang di-upload siswa
        $table->string('bukti')->nullable();

       // Verifikasi oleh Petugas Piket
$table->enum('status_piket', [
    'Menunggu',
    'Disetujui',
    'Ditolak'
])->default('Menunggu');

$table->foreignId('piket_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();

$table->timestamp('verified_piket_at')->nullable();

// Verifikasi oleh Admin / Kurikulum
$table->enum('status_admin', [
    'Menunggu',
    'Disetujui',
    'Ditolak'
])->default('Menunggu');

$table->foreignId('admin_id')
    ->nullable()
    ->constrained('users')
    ->nullOnDelete();

$table->timestamp('verified_admin_at')->nullable();

// Status akhir dispensasi
$table->enum('status_akhir', [
    'Menunggu',
    'Disetujui',
    'Ditolak'
])->default('Menunggu');

$table->text('catatan_verifikasi')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('dispensasis');
}
};
