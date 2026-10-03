<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolEventAudit extends Model
{
    protected $fillable = ['school_event_id', 'actor_id', 'action', 'changes', 'event_snapshot'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'event_snapshot' => 'array'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(SchoolEvent::class, 'school_event_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
