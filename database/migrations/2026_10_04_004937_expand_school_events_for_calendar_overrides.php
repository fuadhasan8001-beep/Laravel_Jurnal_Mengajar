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
        Schema::table('school_events', function (Blueprint $table) {
            $table->dropUnique(['event_date']);
            $table->string('event_type', 100)->default('Kegiatan sekolah');
            $table->string('participant_scope', 30)->default('semua_guru');
            $table->json('participant_ids')->nullable();
            $table->time('activity_start')->nullable();
            $table->time('activity_end')->nullable();
            $table->time('once_start')->nullable();
            $table->time('once_deadline')->nullable();
            $table->string('location_mode', 20)->default('school');
            $table->decimal('location_latitude', 10, 7)->nullable();
            $table->decimal('location_longitude', 10, 7)->nullable();
            $table->unsignedInteger('location_radius_meters')->nullable();
            $table->string('attendance_mode', 30)->default('normal')->change();
        });
        DB::table('school_events')->where('attendance_enabled', true)->update(['attendance_mode' => 'morning_evening']);
        DB::table('school_events')->where('attendance_enabled', false)->where('attendance_mode', 'morning_evening')->update(['attendance_mode' => 'normal']);
        DB::table('school_events')->whereNull('activity_start')->update(['activity_start' => '00:00:00', 'activity_end' => '23:59:00']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasDuplicateDates = DB::table('school_events')->select('event_date')->groupBy('event_date')->havingRaw('count(*) > 1')->exists();
        if ($hasDuplicateDates) {
            throw new RuntimeException('School events on the same date must be consolidated before rolling back this migration.');
        }
        DB::table('school_events')->whereIn('attendance_mode', ['morning_evening', 'once'])->update(['attendance_enabled' => true]);
        DB::table('school_events')->whereIn('attendance_mode', ['normal', 'none'])->update(['attendance_enabled' => false]);
        Schema::table('school_events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'participant_scope', 'participant_ids', 'activity_start', 'activity_end', 'once_start', 'once_deadline', 'location_mode', 'location_latitude', 'location_longitude', 'location_radius_meters']);
            $table->string('attendance_mode')->default('morning_evening')->change();
            $table->unique('event_date');
        });
    }
};
