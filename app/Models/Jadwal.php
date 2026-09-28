<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Jadwal extends Model
{
    protected $fillable = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'jam_pelajaran_id',
        'hari',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function sessionsForGuru(Guru $guru, CarbonInterface $time): Collection
    {
        $hari = $time->copy()->locale('id')->translatedFormat('l');
        $rows = static::with(['kelas', 'mapel', 'jamPelajaran'])
            ->where('guru_id', $guru->id)->where('hari', $hari)->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->get()->sortBy('jamPelajaran.jam_ke');
        $sessions = [];
        foreach ($rows->groupBy(fn (self $row): string => $row->kelas_id.'-'.$row->mapel_id) as $group) {
            $session = null;
            foreach ($group as $row) {
                [$start, $end] = $row->jamPelajaran->timesForDay($hari);
                $active = $start <= $time->format('H:i:s') && $end > $time->format('H:i:s');
                if ($session === null || $row->jamPelajaran->jam_ke !== $session['jam_selesai_ke'] + 1) {
                    if ($session !== null) {
                        $sessions[] = $session;
                    }
                    $session = [
                        'id' => $row->id, 'kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id,
                        'kelas' => $row->kelas->nama_kelas, 'mapel' => $row->mapel->nama_mapel,
                        'jadwal_ids' => [],
                        'jam_mulai_id' => $row->jam_pelajaran_id, 'jam_mulai_ke' => $row->jamPelajaran->jam_ke,
                        'jam_mulai' => $start, 'active' => false,
                    ];
                }
                $session['jadwal_ids'][] = $row->id;
                $session['jam_selesai_id'] = $row->jam_pelajaran_id;
                $session['jam_selesai_ke'] = $row->jamPelajaran->jam_ke;
                $session['jam_selesai'] = $end;
                $session['active'] = $session['active'] || $active;
            }
            if ($session !== null) {
                $sessions[] = $session;
            }
        }

        return collect($sessions)->sortBy('jam_mulai')->values();
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

    public function jamPelajaran(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class);
    }
}
