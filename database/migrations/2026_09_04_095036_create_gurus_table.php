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
    Schema::create('gurus', function (Blueprint $table) {
        $table->id();

        // Akun login guru
        $table->foreignId('user_id')
            ->unique()
            ->constrained('users')
            ->cascadeOnDelete();

        $table->string('nip', 30)->unique();
        $table->string('nama_guru', 100);
        $table->string('no_hp', 20)->nullable();

        $table->enum('status_kepegawaian', [
            'PNS',
            'PPPK',
            'Honorer'
        ])->default('Honorer');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('gurus');
}
};
