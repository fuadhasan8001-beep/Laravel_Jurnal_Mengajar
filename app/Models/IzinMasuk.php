<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinMasuk extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'waktu_masuk',
        'jam_masuk_ke',
        'alasan',
        'piket_id',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function piket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'piket_id');
    }
}