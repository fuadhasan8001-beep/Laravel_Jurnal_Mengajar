<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEventAttendance extends Model
{
    protected $fillable = ['school_event_id', 'guru_id', 'morning_at', 'morning_latitude', 'morning_longitude', 'morning_accuracy', 'morning_distance', 'evening_at', 'evening_latitude', 'evening_longitude', 'evening_accuracy', 'evening_distance'];

    protected function casts(): array
    {
        return ['morning_at' => 'datetime', 'evening_at' => 'datetime'];
    }

    public function schoolEvent(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
