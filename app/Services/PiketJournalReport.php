<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PiketJournalReport
{
    /** @return Collection<int, array<string, mixed>> */
    public function rows(Kelas $kelas, Carbon $start, Carbon $end): Collection
    {
        $schedules = Jadwal::with(['guru', 'mapel', 'jamPelajaran'])
            ->where('kelas_id', $kelas->id)->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->get()->sortBy('jamPelajaran.jam_ke')->groupBy('hari');
        $journals = Jurnal::with(['guru', 'mapel', 'jamMulai', 'jamSelesai'])
            ->where('kelas_id', $kelas->id)->whereDate('tanggal', '>=', $start->toDateString())->whereDate('tanggal', '<=', $end->toDateString())
            ->orderBy('id')->get()->groupBy(fn (Jurnal $journal): string => $journal->tanggal->toDateString());
        $rows = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $day = $date->copy()->locale('id')->translatedFormat('l');
            $dailyJournals = $journals->get($date->toDateString(), collect());
            $matched = [];
            foreach ($schedules->get($day, collect()) as $schedule) {
                [$from, $until] = $schedule->jamPelajaran->timesForDay($day);
                $matches = $dailyJournals->filter(fn (Jurnal $journal): bool => $journal->guru_id === $schedule->guru_id
                    && $journal->mapel_id === $schedule->mapel_id
                    && $journal->jamMulai->jam_ke <= $schedule->jamPelajaran->jam_ke
                    && $journal->jamSelesai->jam_ke >= $schedule->jamPelajaran->jam_ke);
                foreach ($matches->isEmpty() ? [null] : $matches as $journal) {
                    if ($journal) {
                        $matched[$journal->id] = true;
                    }
                    $rows->push([
                        'tanggal' => $date->copy(), 'kelas' => $kelas->nama_kelas,
                        'jam_ke' => (string) $schedule->jamPelajaran->jam_ke,
                        'mulai' => substr($from, 0, 5), 'selesai' => substr($until, 0, 5),
                        'mapel' => $schedule->mapel->nama_mapel, 'guru' => $schedule->guru->nama_guru,
                        'status' => $journal ? 'Sudah Diisi' : $this->missingStatus($date, $from),
                        'jurnal' => $journal,
                    ]);
                }
            }

            // Preserve recorded journals even when their original timetable has since changed.
            foreach ($dailyJournals->reject(fn (Jurnal $journal): bool => isset($matched[$journal->id])) as $journal) {
                [$from] = $journal->jamMulai->timesForDay($day);
                [, $until] = $journal->jamSelesai->timesForDay($day);
                $rows->push([
                    'tanggal' => $date->copy(), 'kelas' => $kelas->nama_kelas,
                    'jam_ke' => $journal->jamMulai->jam_ke.' - '.$journal->jamSelesai->jam_ke,
                    'mulai' => substr($from, 0, 5), 'selesai' => substr($until, 0, 5),
                    'mapel' => $journal->mapel->nama_mapel, 'guru' => $journal->guru->nama_guru,
                    'status' => 'Sudah Diisi', 'jurnal' => $journal,
                ]);
            }
        }

        return $rows->sortBy(fn (array $row): string => $row['tanggal']->toDateString().' '.$row['mulai'])->values();
    }

    private function missingStatus(Carbon $date, string $start): string
    {
        if ($date->isToday()) {
            return now()->format('H:i:s') >= $start ? 'Belum Diisi' : 'Belum waktunya';
        }

        // Weekly schedules have no historical validity dates or holiday calendar.
        return $date->isFuture() ? 'Terjadwal' : 'Tidak ada jurnal tercatat';
    }
}
