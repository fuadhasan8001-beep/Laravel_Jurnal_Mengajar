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
    Schema::create('verifikasi_jurnals', function (Blueprint $table) {
        $table->id();

        $table->foreignId('jurnal_id')
            ->constrained('jurnals')
            ->cascadeOnDelete();

        // Sekretaris adalah user dengan role sekretaris
        $table->foreignId('verifikator_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->enum('status', [
            'Disetujui',
            'Ditolak'
        ]);

        $table->text('catatan')->nullable();
        $table->timestamp('verified_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('verifikasi_jurnals');
}
};
