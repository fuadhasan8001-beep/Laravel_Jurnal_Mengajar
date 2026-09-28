<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('izin_sekolahs', function (Blueprint $table): void {
            $table->enum('status', ['I', 'S'])->default('I')->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('izin_sekolahs', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};