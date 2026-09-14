<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JamPelajaran extends Model
{
    protected $fillable = [
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'jam_mulai_jumat',
        'jam_selesai_jumat',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return array{0: string, 1: string} */
    public function timesForDay(string $hari): array
    {
        return $hari === 'Jumat'
            ? [$this->jam_mulai_jumat ?? $this->jam_mulai, $this->jam_selesai_jumat ?? $this->jam_selesai]
            : [$this->jam_mulai, $this->jam_selesai];
    }

    public function jurnalsMulai(): HasMany
    {
        return $this->hasMany(Jurnal::class, 'jam_mulai_id');
    }

    public function jurnalsSelesai(): HasMany
    {
        return $this->hasMany(Jurnal::class, 'jam_selesai_id');
    }
}
