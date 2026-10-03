<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\SchoolEvent;
use Illuminate\Support\Collection;

class SchoolEventScheduleConflictDetector
{
    /** @return Collection<int, array{teacher_id: int, teacher: string, class: string, subject: string, start: string, end: string}> */
    public function conflicts(SchoolEvent $event): Collection
    {
        if (! $event->activity_start || ! $event->activity_end || ! in_array($event->participant_scope, ['semua_guru', 'all_guru', 'guru_tertentu'], true)) {
            return collect();
        }

        $teacherUsers = $event->targetUsers()->pluck('users.id');
        $teacherIds = Guru::whereIn('user_id', $teacherUsers)->pluck('id');
        $weekday = $event->event_date->locale('id')->translatedFormat('l');

        return Jadwal::with(['guru', 'kelas', 'mapel', 'jamPelajaran'])
            ->whereIn('guru_id', $teacherIds)
            ->where('hari', $weekday)
            ->where('is_active', true)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->get()
            ->map(function (Jadwal $schedule) use ($event, $weekday): ?array {
                [$start, $end] = $schedule->jamPelajaran->timesForDay($weekday);
                $start = substr($start, 0, 5);
                $end = substr($end, 0, 5);
                $activityStart = substr($event->activity_start, 0, 5);
                $activityEnd = substr($event->activity_end, 0, 5);
                if ($start >= $activityEnd || $end <= $activityStart) {
                    return null;
                }

                return [
                    'teacher_id' => $schedule->guru_id,
                    'teacher' => $schedule->guru->nama_guru,
                    'class' => $schedule->kelas->nama_kelas,
                    'subject' => $schedule->mapel->nama_mapel,
                    'start' => substr($start, 0, 5),
                    'end' => substr($end, 0, 5),
                ];
            })
            ->filter()
            ->values();
    }
}
