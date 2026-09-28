<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPiket extends Model
{
    protected $fillable = ['guru_id', 'tanggal', 'dibuat_oleh'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
