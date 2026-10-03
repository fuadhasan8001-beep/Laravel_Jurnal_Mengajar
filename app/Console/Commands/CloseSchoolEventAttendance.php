<?php

namespace App\Console\Commands;

use App\Models\IzinSekolah;
use App\Models\SchoolEvent;
use App\Models\SchoolEventAttendanceRecord;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('calendar:close-event-attendance')]
#[Description('Mark event attendance sessions as absent after their deadlines')]
class CloseSchoolEventAttendance extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $closed = 0;
        SchoolEvent::query()->whereDate('event_date', '<=', today())
            ->whereIn('attendance_mode', ['once', 'morning_evening'])
            ->with('attendances.user.siswa')
            ->get()
            ->each(function (SchoolEvent $event) use (&$closed): void {
                $users = $event->targetUsers()->pluck('users.id');
                $records = $event->attendances->whereIn('user_id', $users);
                foreach ($this->sessions($event) as $session) {
                    $deadline = $event->event_date->copy()->setTimeFromTimeString($event->{$session.'_deadline'});
                    if ($deadline->isFuture()) {
                        continue;
                    }
                    $studentIds = $records->where('participant_type', 'siswa')->pluck('user.siswa.id')->filter()->values();
                    $leaves = IzinSekolah::whereDate('tanggal', $event->event_date)
                        ->whereIn('siswa_id', $studentIds)->get()->keyBy('siswa_id');
                    foreach ($records as $record) {
                        if ($record->{$session.'_at'} || $record->{'status_'.$session} !== null) {
                            continue;
                        }
                        $leave = $record->participant_type === 'siswa'
                            ? $leaves->get($record->user?->siswa?->id)
                            : null;
                        $status = $leave ? ($leave->status === 'S' ? 'sakit' : 'izin') : 'tidak_hadir';
                        $wasClosed = DB::transaction(function () use ($record, $session, $status): bool {
                            $lockedRecord = SchoolEventAttendanceRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                            if ($lockedRecord->{$session.'_at'} || $lockedRecord->{'status_'.$session} !== null) {
                                return false;
                            }
                            $lockedRecord->update(['status_'.$session => $status]);

                            return true;
                        });
                        $closed += (int) $wasClosed;
                    }
                }
            });
        $this->info("Closed {$closed} event attendance session(s).");

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function sessions(SchoolEvent $event): array
    {
        return $event->attendance_mode === 'once' ? ['once'] : ['morning', 'evening'];
    }
}
