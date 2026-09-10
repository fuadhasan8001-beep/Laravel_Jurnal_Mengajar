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
    Schema::create('jam_pelajarans', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('jam_ke');
        $table->time('jam_mulai');
        $table->time('jam_selesai');
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->unique('jam_ke');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('jam_pelajarans');
}
};
