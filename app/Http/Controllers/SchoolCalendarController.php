<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\IzinSekolah;
use App\Models\Kelas;
use App\Models\NationalHoliday;
use App\Models\SchoolEvent;
use App\Models\SchoolEventAttendanceRecord;
use App\Models\SchoolEventAudit;
use App\Models\User;
use App\Notifications\SchoolEventNotification;
use App\Services\SchoolEventScheduleConflictDetector;
use App\Services\SchoolLocationVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolCalendarController extends Controller
{
    public function index(Request $request, SchoolEventScheduleConflictDetector $conflictDetector): View
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $request->input('month', now()->format('Y-m'));
        $start = now()->parse($month.'-01')->startOfMonth();

        $events = SchoolEvent::with(['attendances.user', 'audits.actor'])->whereBetween('event_date', [$start, $start->copy()->endOfMonth()])->orderBy('event_date')->orderBy('activity_start')->get();
        $events->each(fn (SchoolEvent $event) => $event->setAttribute('schedule_conflicts', $conflictDetector->conflicts($event)));

        return view('school-calendar.index', [
            'month' => $month,
            'monthStart' => $start,
            'days' => collect(range(1, $start->daysInMonth))->map(fn (int $day) => $start->copy()->day($day)),
            'events' => $events->groupBy(fn (SchoolEvent $event): string => $event->event_date->toDateString()),
            'holidays' => NationalHoliday::whereBetween('holiday_date', [$start, $start->copy()->endOfMonth()])->orderBy('holiday_date')->get()->keyBy(fn (NationalHoliday $holiday): string => $holiday->holiday_date->toDateString()),
            'gurus' => Guru::with('user')->orderBy('nama_guru')->get(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
        ]);
    }

    public function show(Request $request, SchoolEvent $schoolEvent, SchoolEventScheduleConflictDetector $conflictDetector): View
    {
        abort_unless(in_array($request->user()->role, ['admin', 'waka'], true) || $schoolEvent->isParticipant($request->user()), 403);
        $schoolEvent->load('attendances.user');
        $attendance = $schoolEvent->attendances->firstWhere('user_id', $request->user()->id);

        return view('school-calendar.show', [
            'event' => $schoolEvent,
            'attendance' => $attendance,
            'holiday' => NationalHoliday::whereDate('holiday_date', $schoolEvent->event_date)->first(),
            'conflicts' => $conflictDetector->conflicts($schoolEvent)->where('teacher_id', $request->user()->guru?->id),
        ]);
    }

    public function audit(): View
    {
        return view('school-calendar.audit', [
            'audits' => SchoolEventAudit::with(['actor', 'event'])->latest()->paginate(30),
        ]);
    }

    public function participantCalendar(Request $request, SchoolEventScheduleConflictDetector $conflictDetector): View
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $request->input('month', now()->format('Y-m'));
        $start = now()->parse($month.'-01')->startOfMonth();
        $events = SchoolEvent::with('attendances')->whereBetween('event_date', [$start, $start->copy()->endOfMonth()])
            ->orderBy('event_date')->orderBy('activity_start')->get()
            ->filter(fn (SchoolEvent $event): bool => $event->isParticipant($request->user()))
            ->values();
        $holidayNames = NationalHoliday::whereBetween('holiday_date', [$start, $start->copy()->endOfMonth()])->pluck('name', 'holiday_date');
        $events->each(function (SchoolEvent $event) use ($request, $conflictDetector, $holidayNames): void {
            $event->setAttribute('attendance_record', $event->attendances->firstWhere('user_id', $request->user()->id));
            $event->setAttribute('holiday_name', $holidayNames[$event->event_date->toDateString()] ?? null);
            $event->setAttribute('schedule_conflicts', $conflictDetector->conflicts($event)->where('teacher_id', $request->user()->guru?->id)->values());
        });

        return view('school-calendar.participant-index', [
            'month' => $month,
            'monthStart' => $start,
            'days' => collect(range(1, $start->daysInMonth))->map(fn (int $day) => $start->copy()->day($day)),
            'events' => $events->groupBy(fn (SchoolEvent $event): string => $event->event_date->toDateString()),
            'holidays' => NationalHoliday::whereBetween('holiday_date', [$start, $start->copy()->endOfMonth()])->orderBy('holiday_date')->get()->keyBy(fn (NationalHoliday $holiday): string => $holiday->holiday_date->toDateString()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedEvent($request);
        $data['participant_ids'] = $data['participant_ids'] ?? [];
        if (! in_array($data['participant_scope'], ['guru_tertentu', 'kelas_tertentu'], true)) {
            $data['participant_ids'] = [];
        }
        if ($data['location_mode'] === 'school') {
            $data['location_latitude'] = null;
            $data['location_longitude'] = null;
            $data['location_radius_meters'] = null;
        }
        $data['attendance_enabled'] = $data['attendance_mode'] !== 'normal' && $data['attendance_mode'] !== 'none';

        $event = DB::transaction(function () use ($data, $request): SchoolEvent {
            $event = SchoolEvent::create($data);
            $this->syncParticipants($event);
            $event->audits()->create([
                'actor_id' => $request->user()->id,
                'action' => 'created',
                'event_snapshot' => $event->getAttributes(),
            ]);

            return $event;
        });
        $this->notifyParticipants($event, 'Kegiatan baru: '.$event->title.' pada '.$event->event_date->locale('id')->translatedFormat('d F Y').'. '.$this->scheduleLabel($event));

        return back()->with('success', 'Kegiatan sekolah berhasil dibuat dan peserta telah diberi notifikasi.');
    }

    public function update(Request $request, SchoolEvent $schoolEvent): RedirectResponse
    {
        $data = $this->validatedEvent($request);
        $data['participant_ids'] = $data['participant_ids'] ?? [];
        if (! in_array($data['participant_scope'], ['guru_tertentu', 'kelas_tertentu'], true)) {
            $data['participant_ids'] = [];
        }
        if ($data['location_mode'] === 'school') {
            $data['location_latitude'] = null;
            $data['location_longitude'] = null;
            $data['location_radius_meters'] = null;
        }
        $data['attendance_enabled'] = ! in_array($data['attendance_mode'], ['normal', 'none'], true);
        $before = $schoolEvent->getAttributes();

        DB::transaction(function () use ($data, $request, $schoolEvent, $before): void {
            $schoolEvent->update($data);
            $this->syncParticipants($schoolEvent);
            $schoolEvent->audits()->create([
                'actor_id' => $request->user()->id,
                'action' => 'updated',
                'changes' => ['before' => $before, 'after' => $schoolEvent->getAttributes()],
                'event_snapshot' => $schoolEvent->getAttributes(),
            ]);
        });
        $this->notifyParticipants($schoolEvent, 'Perubahan kegiatan: '.$schoolEvent->title.'. '.$this->scheduleLabel($schoolEvent));

        return back()->with('success', 'Kegiatan dan peserta berhasil diperbarui.');
    }

    public function destroy(Request $request, SchoolEvent $schoolEvent): RedirectResponse
    {
        $participants = $schoolEvent->targetUsers()->get();
        DB::transaction(function () use ($request, $schoolEvent): void {
            $schoolEvent->audits()->create([
                'actor_id' => $request->user()->id,
                'action' => 'deleted',
                'event_snapshot' => $schoolEvent->getAttributes(),
            ]);
            $schoolEvent->delete();
        });
        $participants->each(fn (User $user) => $user->notify(new SchoolEventNotification($schoolEvent, 'Kegiatan '.$schoolEvent->title.' pada '.$schoolEvent->event_date->locale('id')->translatedFormat('d F Y').' telah dibatalkan.', route('calendar.index'))));

        return back()->with('success', 'Kegiatan sekolah berhasil dihapus.');
    }

    public function attend(Request $request, SchoolEvent $schoolEvent, SchoolLocationVerifier $locationVerifier): RedirectResponse
    {
        abort_unless($schoolEvent->isParticipant($request->user()), 403);
        abort_unless($schoolEvent->event_date->isToday(), 404);
        $data = $request->validate([
            'session' => ['required', Rule::in($this->sessionsFor($schoolEvent))],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'activity_note' => ['nullable', 'string', 'max:2000'],
        ]);
        abort_if($schoolEvent->attendance_mode === 'none' || $schoolEvent->attendance_mode === 'normal', 422, 'Kegiatan ini tidak menggunakan absensi khusus.');

        $session = $data['session'];
        $record = SchoolEventAttendanceRecord::where('school_event_id', $schoolEvent->id)->where('user_id', $request->user()->id)->firstOrFail();
        if ($request->user()->role === 'siswa') {
            $studentId = $request->user()->siswa?->id;
            $leave = $studentId ? IzinSekolah::where('siswa_id', $studentId)->whereDate('tanggal', $schoolEvent->event_date)->first() : null;
            if ($leave) {
                DB::transaction(function () use ($record, $session, $leave): void {
                    $lockedRecord = SchoolEventAttendanceRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                    throw_if($lockedRecord->{$session.'_at'}, ValidationException::withMessages(['session' => 'Absensi sesi ini sudah tercatat.']));
                    $status = $leave->status === 'S' ? 'sakit' : 'izin';
                    throw_if($lockedRecord->{'status_'.$session} && $lockedRecord->{'status_'.$session} !== $status, ValidationException::withMessages(['session' => 'Status absensi sesi ini sudah ditetapkan.']));
                    $lockedRecord->update(['status_'.$session => $status]);
                });

                return back()->with('success', $leave->status === 'S' ? 'Status sakit dari izin sekolah diterapkan otomatis.' : 'Status izin sekolah diterapkan otomatis.');
            }
        }
        $now = now();
        $startsAt = $now->copy()->setTimeFromTimeString($schoolEvent->{$session.'_start'});
        $deadline = $now->copy()->setTimeFromTimeString($schoolEvent->{$session.'_deadline'});
        throw_if($now->lt($startsAt) || $now->gt($deadline), ValidationException::withMessages(['session' => 'Absensi belum dibuka atau batas waktu absensi telah lewat.']));

        $location = $schoolEvent->location_mode === 'custom'
            ? $locationVerifier->verify($data['latitude'], $data['longitude'], $data['accuracy'], $schoolEvent->location_latitude, $schoolEvent->location_longitude, $schoolEvent->location_radius_meters)
            : $locationVerifier->verify($data['latitude'], $data['longitude'], $data['accuracy']);
        DB::transaction(function () use ($record, $session, $now, $location, $startsAt, $data): void {
            $lockedRecord = SchoolEventAttendanceRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
            throw_if($lockedRecord->{$session.'_at'}, ValidationException::withMessages(['session' => 'Absensi sesi ini sudah tercatat.']));
            throw_if(in_array($lockedRecord->{'status_'.$session}, ['izin', 'sakit', 'tidak_hadir'], true), ValidationException::withMessages(['session' => 'Status absensi sesi ini sudah ditetapkan.']));
            $lockedRecord->fill([
                $session.'_at' => $now,
                $session.'_latitude' => $location['latitude'],
                $session.'_longitude' => $location['longitude'],
                $session.'_accuracy' => $location['accuracy'],
                $session.'_distance' => $location['distance'],
                'status_'.$session => $now->gt($startsAt) ? 'terlambat' : 'hadir',
                'activity_note' => $data['activity_note'] ?? $lockedRecord->activity_note,
            ])->save();
        });

        return back()->with('success', 'Absensi '.$this->sessionLabel($session).' berhasil dicatat.');
    }

    public function storeNote(Request $request, SchoolEvent $schoolEvent): RedirectResponse
    {
        abort_unless($schoolEvent->isParticipant($request->user()), 403);
        $data = $request->validate(['activity_note' => ['required', 'string', 'max:2000']]);
        SchoolEventAttendanceRecord::updateOrCreate(
            ['school_event_id' => $schoolEvent->id, 'user_id' => $request->user()->id],
            ['participant_type' => $request->user()->role === 'guru' ? 'guru' : 'siswa', 'activity_note' => $data['activity_note']],
        );

        return back()->with('success', 'Catatan kegiatan berhasil disimpan.');
    }

    public function updateAttendanceStatus(Request $request, SchoolEvent $schoolEvent, SchoolEventAttendanceRecord $attendanceRecord): RedirectResponse
    {
        abort_unless($attendanceRecord->school_event_id === $schoolEvent->id, 404);
        $data = $request->validate([
            'session' => ['required', Rule::in(['once', 'morning', 'evening'])],
            'status' => ['required', Rule::in(['hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir'])],
        ]);
        abort_if($data['session'] === 'once' && $schoolEvent->attendance_mode !== 'once', 422);
        abort_if($data['session'] !== 'once' && $schoolEvent->attendance_mode !== 'morning_evening', 422);
        $statusField = 'status_'.$data['session'];
        DB::transaction(function () use ($attendanceRecord, $data, $statusField, $request, $schoolEvent): void {
            $attendanceRecord = SchoolEventAttendanceRecord::whereKey($attendanceRecord->id)->lockForUpdate()->firstOrFail();
            $before = $attendanceRecord->{$statusField};
            $attendanceRecord->update([$statusField => $data['status']]);
            $schoolEvent->audits()->create([
                'actor_id' => $request->user()->id,
                'action' => 'attendance_status_updated',
                'changes' => ['user_id' => $attendanceRecord->user_id, 'field' => $statusField, 'before' => $before, 'after' => $data['status']],
                'event_snapshot' => $schoolEvent->getAttributes(),
            ]);
        });

        return back()->with('success', 'Status absensi peserta berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validatedEvent(Request $request): array
    {
        return $request->validate([
            'event_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:150'],
            'event_type' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'participant_scope' => ['required', Rule::in(['semua_guru', 'guru_tertentu', 'semua_siswa', 'kelas_tertentu'])],
            'participant_ids' => [Rule::when(in_array($request->input('participant_scope'), ['guru_tertentu', 'kelas_tertentu'], true), ['required', 'array', 'min:1'], ['nullable', 'array'])],
            'participant_ids.*' => ['integer', 'distinct', Rule::when($request->input('participant_scope') === 'guru_tertentu', ['exists:gurus,id']), Rule::when($request->input('participant_scope') === 'kelas_tertentu', ['exists:kelas,id'])],
            'activity_start' => ['required', 'date_format:H:i'],
            'activity_end' => ['required', 'date_format:H:i', 'after:activity_start'],
            'early_dismissal_at' => ['nullable', 'date_format:H:i', 'after:activity_start', 'before:activity_end'],
            'attendance_mode' => ['required', Rule::in(['normal', 'morning_evening', 'once', 'none'])],
            'once_start' => ['required_if:attendance_mode,once', 'nullable', 'date_format:H:i'],
            'once_deadline' => ['required_if:attendance_mode,once', 'nullable', 'date_format:H:i', 'after_or_equal:once_start'],
            'morning_start' => ['required_if:attendance_mode,morning_evening', 'nullable', 'date_format:H:i'],
            'morning_deadline' => ['required_if:attendance_mode,morning_evening', 'nullable', 'date_format:H:i', 'after_or_equal:morning_start'],
            'evening_start' => ['required_if:attendance_mode,morning_evening', 'nullable', 'date_format:H:i'],
            'evening_deadline' => ['required_if:attendance_mode,morning_evening', 'nullable', 'date_format:H:i', 'after_or_equal:evening_start'],
            'location_mode' => ['required', Rule::in(['school', 'custom'])],
            'location_latitude' => ['required_if:location_mode,custom', 'nullable', 'numeric', 'between:-90,90'],
            'location_longitude' => ['required_if:location_mode,custom', 'nullable', 'numeric', 'between:-180,180'],
            'location_radius_meters' => ['required_if:location_mode,custom', 'nullable', 'integer', 'min:1', 'max:50000'],
        ]);
    }

    private function syncParticipants(SchoolEvent $event): void
    {
        $users = $event->targetUsers()->get();
        $users->load('siswa');
        $event->attendances()->whereNotIn('user_id', $users->modelKeys())
            ->whereNull('once_at')->whereNull('morning_at')->whereNull('evening_at')
            ->whereNull('status_once')->whereNull('status_morning')->whereNull('status_evening')
            ->delete();
        $studentIds = $users->filter(fn (User $user): bool => $user->role === 'siswa')->map(fn (User $user): ?int => $user->siswa?->id)->filter()->values();
        $leaves = IzinSekolah::whereDate('tanggal', $event->event_date)->whereIn('siswa_id', $studentIds)->get()->keyBy('siswa_id');
        foreach ($users as $user) {
            $leave = $user->role === 'siswa' ? $leaves->get($user->siswa?->id) : null;
            $defaultStatuses = ['participant_type' => $user->role === 'guru' ? 'guru' : 'siswa'];
            if ($leave && $event->attendance_mode === 'once') {
                $defaultStatuses['status_once'] = $leave->status === 'S' ? 'sakit' : 'izin';
            } elseif ($leave && $event->attendance_mode === 'morning_evening') {
                $status = $leave->status === 'S' ? 'sakit' : 'izin';
                $defaultStatuses['status_morning'] = $status;
                $defaultStatuses['status_evening'] = $status;
            }
            $event->attendances()->firstOrCreate(
                ['user_id' => $user->id],
                $defaultStatuses,
            );
        }
    }

    private function notifyParticipants(SchoolEvent $event, string $message): void
    {
        $event->targetUsers()->get()->each(fn (User $user) => $user->notify(new SchoolEventNotification($event, $message)));
    }

    /** @return array<int, string> */
    private function sessionsFor(SchoolEvent $event): array
    {
        return match ($event->attendance_mode) {
            'morning_evening' => ['morning', 'evening'],
            'once' => ['once'],
            default => [],
        };
    }

    private function modeLabel(SchoolEvent $event): string
    {
        return ['normal' => 'normal sesuai jadwal', 'morning_evening' => 'pagi & sore', 'once' => 'sekali saja', 'none' => 'tanpa absensi'][$event->attendance_mode] ?? $event->attendance_mode;
    }

    private function scheduleLabel(SchoolEvent $event): string
    {
        $schedule = match ($event->attendance_mode) {
            'morning_evening' => 'Absensi pagi '.substr($event->morning_start, 0, 5).'-'.substr($event->morning_deadline, 0, 5).', sore '.substr($event->evening_start, 0, 5).'-'.substr($event->evening_deadline, 0, 5).'.',
            'once' => 'Absensi sekali '.substr($event->once_start, 0, 5).'-'.substr($event->once_deadline, 0, 5).'.',
            'none' => 'Tidak ada absensi.',
            default => 'Absensi normal mengikuti jadwal mengajar.',
        };

        return 'Mode '.$this->modeLabel($event).'. '.$schedule.' Lokasi: '.($event->location_mode === 'school' ? 'sekolah' : 'lokasi kegiatan khusus').'.';
    }

    private function sessionLabel(string $session): string
    {
        return ['morning' => 'pagi', 'evening' => 'sore/pulang', 'once' => 'kegiatan'][$session] ?? $session;
    }
}
