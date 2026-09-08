<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurnal extends Model
{
    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'jam_mulai_id',
        'jam_selesai_id',
        'tanggal',
        'status_guru',
        'materi',
        'tujuan_pembelajaran',
        'kegiatan',
        'tugas',
        'catatan',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function jamMulai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_mulai_id');
    }

    public function jamSelesai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_selesai_id');
    }

    public function verifikasiJurnals(): HasMany
    {
        return $this->hasMany(VerifikasiJurnal::class);
    }
}
