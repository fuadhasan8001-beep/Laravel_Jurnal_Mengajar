<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPiket extends Model
{
    public const SHIFT_PAGI = 'pagi';

    public const SHIFT_SIANG = 'siang';

    public const SHIFT_WAKA = 'waka';

    protected $fillable = ['guru_id', 'user_id', 'tanggal', 'shift', 'dibuat_oleh', 'is_koordinator'];

    protected function casts(): array
    {
        return ['tanggal' => 'date', 'is_koordinator' => 'boolean'];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shiftLabel(): string
    {
        return match ($this->shift) {
            self::SHIFT_WAKA => 'Piket Waka',
            self::SHIFT_SIANG => 'Siang (11.00–15.00)',
            default => 'Pagi (07.00–11.00)',
        };
    }

    public function isActiveNow(): bool
    {
        if (! $this->tanggal?->isToday()) {
            return false;
        }

        $hour = now()->hour + (now()->minute / 60);

        return match ($this->shift) {
            self::SHIFT_WAKA => false,
            self::SHIFT_PAGI => $hour >= 7 && $hour < 11,
            self::SHIFT_SIANG => $hour >= 11 && $hour < 15,
            default => false,
        };
    }
}
