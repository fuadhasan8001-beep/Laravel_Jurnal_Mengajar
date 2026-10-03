<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEventAttendanceRecord extends Model
{
    protected $fillable = ['school_event_id', 'user_id', 'participant_type', 'status_once', 'status_morning', 'status_evening', 'once_at', 'once_latitude', 'once_longitude', 'once_accuracy', 'once_distance', 'morning_at', 'morning_latitude', 'morning_longitude', 'morning_accuracy', 'morning_distance', 'evening_at', 'evening_latitude', 'evening_longitude', 'evening_accuracy', 'evening_distance', 'activity_note'];

    protected function casts(): array
    {
        return ['once_at' => 'datetime', 'morning_at' => 'datetime', 'evening_at' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'school_event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
