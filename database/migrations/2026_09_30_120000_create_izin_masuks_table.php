<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_masuks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu_masuk');
            $table->unsignedSmallInteger('jam_masuk_ke');
            $table->string('alasan', 1000)->nullable();
            $table->foreignId('piket_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_masuks');
    }
};