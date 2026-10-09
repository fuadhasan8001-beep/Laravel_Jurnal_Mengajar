<?php

namespace App\Console\Commands;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\NationalHoliday;
use App\Models\SchoolEvent;
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
        $isNationalHoliday = NationalHoliday::whereDate('holiday_date', $date)->exists();
        $overrides = SchoolEvent::whereDate('event_date', $date)
            ->whereIn('attendance_mode', ['morning_evening', 'once', 'none'])->get();
        $overrideUserIds = $overrides->flatMap(fn (SchoolEvent $event) => $event->targetUsers()->pluck('users.id'))->unique()->all();
        $dismissalByUser = SchoolEvent::whereDate('event_date', $date)->whereNotNull('early_dismissal_at')
            ->where('attendance_mode', 'normal')->get()
            ->flatMap(fn (SchoolEvent $event) => $event->targetUsers()->pluck('users.id')->mapWithKeys(fn (int $userId): array => [$userId => $event->early_dismissal_at]))
            ->all();

        Jadwal::with(['guru.user', 'jamPelajaran'])
            ->where('hari', $hari)
            ->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->get()
            ->filter(function (Jadwal $jadwal) use ($time, $date, $isNationalHoliday, $overrideUserIds, $dismissalByUser): bool {
                if ($isNationalHoliday || in_array($jadwal->guru->user?->id, $overrideUserIds, true)) {
                    return false;
                }
                [$start, $end] = $jadwal->jamPelajaran->timesForDay($jadwal->hari);

                if (isset($dismissalByUser[$jadwal->guru->user?->id]) && $start >= $dismissalByUser[$jadwal->guru->user->id]) {
                    return false;
                }

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
