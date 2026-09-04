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
    Schema::create('jurnals', function (Blueprint $table) {
        $table->id();

        $table->foreignId('guru_id')
            ->constrained('gurus')
            ->cascadeOnDelete();

        $table->foreignId('kelas_id')
            ->constrained('kelas')
            ->restrictOnDelete();

        $table->foreignId('mapel_id')
            ->constrained('mapels')
            ->restrictOnDelete();

        $table->foreignId('jam_mulai_id')
    ->constrained('jam_pelajarans')
    ->restrictOnDelete();

$table->foreignId('jam_selesai_id')
    ->constrained('jam_pelajarans')
    ->restrictOnDelete();

        $table->date('tanggal');

        $table->enum('status_guru', [
            'Hadir',
            'Izin',
            'Sakit',
            'Dinas',
            'Tanpa Keterangan'
        ])->default('Hadir');

        $table->string('materi', 200);
        $table->text('kegiatan')->nullable();
        $table->text('tugas')->nullable();

        $table->enum('status_verifikasi', [
            'Menunggu',
            'Disetujui',
            'Ditolak'
        ])->default('Menunggu');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('jurnals');
}
};
