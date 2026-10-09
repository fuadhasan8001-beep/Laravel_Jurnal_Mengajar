<?php

use App\Models\SchoolEvent;
use App\Models\SchoolEventAttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('synchronizes current national holidays and removes stale dates for the synchronized year', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api-harilibur.pages.dev/api*' => Http::response([
            ['holiday_date' => '2026-12-25', 'holiday_name' => 'Hari Natal', 'is_national_holiday' => true],
            ['holiday_date' => '2026-12-26', 'holiday_name' => 'Cuti Bersama', 'is_national_holiday' => false],
        ]),
    ]);
    DB::table('national_holidays')->insert([
        'holiday_date' => '2026-01-01',
        'name' => 'Tanggal lama',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $exitCode = Artisan::call('calendar:sync-holidays', ['--year' => 2026]);
    expect($exitCode)->toBe(0)->and(Artisan::output())->toContain('Synchronized national holidays for 2026.');

    $this->assertDatabaseHas('national_holidays', ['holiday_date' => '2026-12-25', 'name' => 'Hari Natal']);
    $this->assertDatabaseMissing('national_holidays', ['holiday_date' => '2026-12-26']);
    $this->assertDatabaseMissing('national_holidays', ['holiday_date' => '2026-01-01']);
});

it('records an audit entry atomically when an admin changes event attendance status', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $event = SchoolEvent::create([
        'event_date' => '2026-10-09',
        'title' => 'Pelatihan guru',
        'event_type' => 'Pelatihan',
        'participant_scope' => 'semua_guru',
        'participant_ids' => [],
        'activity_start' => '09:00',
        'activity_end' => '12:00',
        'attendance_mode' => 'once',
        'once_start' => '08:00',
        'once_deadline' => '08:30',
        'location_mode' => 'school',
    ]);
    $participant = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $record = SchoolEventAttendanceRecord::create([
        'school_event_id' => $event->id,
        'user_id' => $participant->id,
        'participant_type' => 'guru',
    ]);

    $this->actingAs($admin)->put(route('admin.calendar.attendance-status', [$event, $record]), [
        'session' => 'once',
        'status' => 'terlambat',
    ])->assertRedirect();

    $this->assertDatabaseHas('school_event_attendance_records', [
        'id' => $record->id,
        'status_once' => 'terlambat',
    ]);
    $this->assertDatabaseHas('school_event_audits', [
        'school_event_id' => $event->id,
        'actor_id' => $admin->id,
        'action' => 'attendance_status_updated',
    ]);
});
