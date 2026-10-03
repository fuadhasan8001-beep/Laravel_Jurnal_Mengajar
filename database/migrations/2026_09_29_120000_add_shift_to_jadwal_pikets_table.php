<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->string('shift', 20)->default('pagi')->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->dropColumn('shift');
        });
    }
};