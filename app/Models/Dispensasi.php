<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispensasi extends Model
{
    protected $fillable = [
        'group_key',
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

    public static function approvedForJournal(Jurnal $jurnal): Collection
    {
        $jurnal->loadMissing(['jamMulai', 'jamSelesai']);

        return static::with(['jamMulai', 'jamSelesai'])
            ->where('status_akhir', 'Disetujui')
            ->whereDate('tanggal', $jurnal->tanggal)
            ->whereHas('siswa', fn ($query) => $query->where('kelas_id', $jurnal->kelas_id))
            ->get()
            ->filter(fn (self $dispensasi): bool => $dispensasi->jamMulai->jam_ke <= $jurnal->jamSelesai->jam_ke
                && $dispensasi->jamSelesai->jam_ke >= $jurnal->jamMulai->jam_ke
            );
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

    public function groupStudents(): HasMany
    {
        return $this->hasMany(self::class, 'group_key', 'group_key');
    }
}
