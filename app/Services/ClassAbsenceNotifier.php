<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\ClassAbsenceRecorded;
use Illuminate\Database\Eloquent\Collection;

class ClassAbsenceNotifier
{
    /**
     * Notify class secretaries and teachers scheduled with the affected class today.
     *
     * @param  Collection<int, Siswa>  $students
     */
    public function notify(Collection $students, string $statusLabel): void
    {
        $weekday = today()->locale('id')->translatedFormat('l');

        foreach ($students->groupBy('kelas_id') as $classId => $classStudents) {
            $kelas = Kelas::with('sekretarisUsers')->find($classId);
            if (! $kelas) {
                continue;
            }

            $teacherUserIds = Guru::query()
                ->whereIn('id', Jadwal::query()
                    ->where('kelas_id', $classId)
                    ->where('hari', $weekday)
                    ->where('is_active', true)
                    ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
                    ->select('guru_id'))
                ->whereNotNull('user_id')
                ->pluck('user_id');

            $recipients = $kelas->sekretarisUsers
                ->where('is_active', true)
                ->merge(User::query()->whereIn('id', $teacherUserIds)->where('is_active', true)->get())
                ->unique('id');

            if ($recipients->isEmpty()) {
                continue;
            }

            $studentNames = $classStudents->pluck('nama_siswa')->join(', ');
            $message = 'Guru piket mencatat '.$studentNames.' sebagai '.$statusLabel.' di kelas '.$kelas->nama_kelas.' untuk hari ini.';
            $url = route('laporan.absensi', ['tanggal_mulai' => today()->toDateString(), 'tanggal_selesai' => today()->toDateString(), 'kelas_id' => $kelas->id]);

            $recipients->each(fn (User $recipient) => $recipient->notify(new ClassAbsenceRecorded($message, $url)));
        }
    }
}
