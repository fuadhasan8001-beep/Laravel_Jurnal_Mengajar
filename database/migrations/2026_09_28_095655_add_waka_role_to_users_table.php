<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['guru', 'siswa', 'sekretaris', 'piket', 'admin', 'waka'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('users')->where('role', 'waka')->exists()) {
            throw new RuntimeException('Pindahkan akun Waka sebelum rollback role.');
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['guru', 'siswa', 'sekretaris', 'piket', 'admin'])->change();
        });
    }
};
