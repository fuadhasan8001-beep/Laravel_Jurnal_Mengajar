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
        DB::table('school_events')->where('participant_scope', 'all_guru')->update(['participant_scope' => 'semua_guru']);
        DB::table('school_events')->where('attendance_enabled', false)->where('attendance_mode', 'morning_evening')->update(['attendance_mode' => 'normal']);
        foreach (DB::table('school_events')->get(['id']) as $event) {
            $teachers = DB::table('gurus')->join('users', 'users.id', '=', 'gurus.user_id')
                ->where('users.role', 'guru')->where('users.is_active', true)->get(['gurus.id as guru_id', 'gurus.user_id']);
            foreach ($teachers as $teacher) {
                $legacy = DB::table('school_event_attendances')->where('school_event_id', $event->id)->where('guru_id', $teacher->guru_id)->first();
                $timestamp = now();
                DB::table('school_event_attendance_records')->insertOrIgnore([
                    'school_event_id' => $event->id, 'user_id' => $teacher->user_id, 'participant_type' => 'guru',
                    'status_morning' => $legacy?->morning_at ? 'hadir' : null,
                    'status_evening' => $legacy?->evening_at ? 'hadir' : null,
                    'morning_at' => $legacy?->morning_at, 'morning_latitude' => $legacy?->morning_latitude,
                    'morning_longitude' => $legacy?->morning_longitude, 'morning_accuracy' => $legacy?->morning_accuracy,
                    'morning_distance' => $legacy?->morning_distance, 'evening_at' => $legacy?->evening_at,
                    'evening_latitude' => $legacy?->evening_latitude, 'evening_longitude' => $legacy?->evening_longitude,
                    'evening_accuracy' => $legacy?->evening_accuracy, 'evening_distance' => $legacy?->evening_distance,
                    'created_at' => $timestamp, 'updated_at' => $timestamp,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('school_events')->where('participant_scope', 'semua_guru')->update(['participant_scope' => 'all_guru']);
    }
};
