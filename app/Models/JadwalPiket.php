<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPiket extends Model
{
    public const SHIFT_PAGI = 'pagi';

    public const SHIFT_SIANG = 'siang';

    protected $fillable = ['guru_id', 'tanggal', 'shift', 'dibuat_oleh'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function shiftLabel(): string
    {
        return match ($this->shift) {
            self::SHIFT_SIANG => 'Siang (11.00–15.00)',
            default => 'Pagi (07.00–11.00)',
        };
    }

    public function isActiveNow(): bool
    {
        $hour = now()->hour + (now()->minute / 60);

        return match ($this->shift) {
            self::SHIFT_SIANG => $hour >= 11 && $hour < 15,
            default => $hour >= 7 && $hour < 11,
        };
    }
}
