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
        Schema::table('dispensasis', function (Blueprint $table) {
            $table->string('surat_izin_path')->nullable()->after('bukti');
            $table->enum('attendance_status', ['H', 'S', 'I', 'A', 'D'])->nullable()->after('surat_izin_path');
            $table->foreignId('waka_id')->nullable()->after('admin_id')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_waka_at')->nullable()->after('verified_admin_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensasis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('waka_id');
            $table->dropColumn(['surat_izin_path', 'attendance_status', 'verified_waka_at']);
        });
    }
};
