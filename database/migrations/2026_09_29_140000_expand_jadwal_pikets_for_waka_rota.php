<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->index('guru_id', 'jadwal_pikets_guru_id_support_index');
        });

        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->dropUnique('jadwal_pikets_guru_id_tanggal_unique');
            $table->foreignId('guru_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->after('guru_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['guru_id', 'tanggal', 'shift'], 'jadwal_pikets_guru_date_shift_unique');
            $table->unique(['user_id', 'tanggal', 'shift'], 'jadwal_pikets_user_date_shift_unique');
        });

        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->dropIndex('jadwal_pikets_guru_id_support_index');
        });
    }

    public function down(): void
    {
        if (DB::table('jadwal_pikets')->whereNotNull('user_id')->exists()) {
            throw new RuntimeException('Hapus atau pindahkan jadwal Piket Waka sebelum rollback.');
        }

        Schema::table('jadwal_pikets', function (Blueprint $table): void {
            $table->dropUnique('jadwal_pikets_guru_date_shift_unique');
            $table->dropUnique('jadwal_pikets_user_date_shift_unique');
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('guru_id')->nullable(false)->change();
            $table->unique(['guru_id', 'tanggal']);
        });
    }
};
