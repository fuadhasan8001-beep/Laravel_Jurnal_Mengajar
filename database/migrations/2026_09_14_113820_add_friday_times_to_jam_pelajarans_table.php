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
        Schema::table('jam_pelajarans', function (Blueprint $table) {
            $table->time('jam_mulai_jumat')->nullable();
            $table->time('jam_selesai_jumat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jam_pelajarans', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai_jumat', 'jam_selesai_jumat']);
        });
    }
};
