<?php

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Notifications\JurnalReminderNotification;
use Illuminate\Console\Command;

class RemindMissingJournals extends Command
{
    protected $signature = 'jurnal:remind-missing {--date= : Date in Y-m-d format}';

    protected $description = 'Notify teachers whose active lessons have no journal yet';

    public function handle(): int
    {
        $date = $this->option('date') ?: today()->toDateString();
        $time = now();
        $hari = $time->copy()->locale('id')->translatedFormat('l');
        $sent = 0;

        Jadwal::with(['guru.user', 'jamPelajaran'])
            ->where('hari', $hari)
            ->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->get()
            ->filter(function (Jadwal $jadwal) use ($time, $date): bool {
                [$start, $end] = $jadwal->jamPelajaran->timesForDay($jadwal->hari);
                return $date === today()->toDateString()
                    && $start <= $time->format('H:i:s')
                    && $end <= $time->format('H:i:s')
                    && ! Jurnal::where('guru_id', $jadwal->guru_id)->whereDate('tanggal', $date)
                        ->where('kelas_id', $jadwal->kelas_id)->where('mapel_id', $jadwal->mapel_id)
                        ->where('jam_mulai_id', $jadwal->jam_pelajaran_id)->exists();
            })
            ->groupBy('guru_id')
            ->each(function ($schedules) use (&$sent): void {
                $guru = $schedules->first()->guru;
                $guru->user?->notify(new JurnalReminderNotification($schedules->count()));
                $sent++;
            });

        $this->info("Sent {$sent} journal reminder(s).");

        return self::SUCCESS;
    }
}
