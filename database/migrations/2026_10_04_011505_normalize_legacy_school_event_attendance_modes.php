<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('school_events')
            ->where('attendance_enabled', false)
            ->where('attendance_mode', 'morning_evening')
            ->update(['attendance_mode' => 'normal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('school_events')
            ->where('attendance_enabled', false)
            ->where('attendance_mode', 'normal')
            ->update(['attendance_mode' => 'morning_evening']);
    }
};
