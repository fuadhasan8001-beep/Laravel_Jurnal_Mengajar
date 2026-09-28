<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table): void {
            $table->string('bukti_kehadiran')->nullable()->after('tanda_tangan');
            $table->decimal('lokasi_latitude', 10, 7)->nullable()->after('bukti_kehadiran');
            $table->decimal('lokasi_longitude', 10, 7)->nullable()->after('lokasi_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table): void {
            $table->dropColumn(['bukti_kehadiran', 'lokasi_latitude', 'lokasi_longitude']);
        });
    }
};
