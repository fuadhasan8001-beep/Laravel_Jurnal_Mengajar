<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alokasi_jam_pelajarans')) {
            return;
        }

        if (DB::table('alokasi_jam_pelajarans')->exists()) {
            throw new RuntimeException('Tabel alokasi_jam_pelajarans masih berisi data. Migrasi dibatalkan agar data tidak terhapus.');
        }

        Schema::drop('alokasi_jam_pelajarans');
    }

    public function down(): void
    {
        if (Schema::hasTable('alokasi_jam_pelajarans')) {
            return;
        }

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
};
