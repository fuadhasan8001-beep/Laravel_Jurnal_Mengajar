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
        Schema::create('alokasi_jam_pelajarans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('jam_pelajaran_id')->constrained('jam_pelajarans')->cascadeOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->unique(['kelas_id', 'jam_pelajaran_id', 'hari']);
            $table->index(['kelas_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_jam_pelajarans');
    }
};
