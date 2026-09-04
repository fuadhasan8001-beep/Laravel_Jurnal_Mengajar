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
    Schema::create('absensis', function (Blueprint $table) {
        $table->id();

        $table->foreignId('jurnal_id')
            ->constrained('jurnals')
            ->cascadeOnDelete();

        $table->foreignId('siswa_id')
            ->constrained('siswas')
            ->cascadeOnDelete();

        $table->enum('status', [
            'H',
            'S',
            'I',
            'A',
            'D'
        ])->default('H');

        $table->text('catatan')->nullable();

        $table->timestamps();

        // Satu siswa hanya boleh punya satu absensi
        // dalam satu jurnal
        $table->unique(['jurnal_id', 'siswa_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('absensis');
}
};
