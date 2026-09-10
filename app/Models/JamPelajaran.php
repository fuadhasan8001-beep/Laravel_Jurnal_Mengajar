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
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
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
