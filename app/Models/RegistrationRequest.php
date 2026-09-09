<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class RegistrationRequest extends Model
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
