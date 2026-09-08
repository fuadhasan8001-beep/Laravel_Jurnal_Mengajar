<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensasi extends Model
{
    protected $fillable = [
        'siswa_id',
        'tanggal',
        'jam_mulai_id',
        'jam_selesai_id',
        'alasan',
        'bukti',
        'status_piket',
        'piket_id',
        'verified_piket_at',
        'status_admin',
        'admin_id',
        'verified_admin_at',
        'status_akhir',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'verified_piket_at' => 'datetime',
            'verified_admin_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jamMulai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_mulai_id');
    }

    public function jamSelesai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_selesai_id');
    }

    public function piket(): BelongsTo
    {
        return $this->belongsTo(User::class, 'piket_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
