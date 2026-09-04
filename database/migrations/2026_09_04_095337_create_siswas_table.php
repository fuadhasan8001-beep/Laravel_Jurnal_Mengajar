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
    Schema::create('siswas', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->unique()
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('kelas_id')
            ->constrained('kelas')
            ->restrictOnDelete();

        $table->string('nis', 30)->unique();
        $table->string('nama_siswa', 100);

        $table->enum('jenis_kelamin', [
            'L',
            'P'
        ]);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('siswas');
}
};
