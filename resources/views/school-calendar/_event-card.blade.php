<article class="panel school-event-card">
    <div class="panel-head">
        <div><h3>{{ $event->title }}</h3><span class="eyebrow">{{ $event->event_type }} · {{ $event->event_date->locale('id')->translatedFormat('d F Y') }} · {{ substr($event->activity_start, 0, 5) }}–{{ substr($event->activity_end, 0, 5) }}</span></div>
        @if ($event->holiday_name)<span class="status approved">{{ $event->holiday_name }} · Libur Nasional</span>@endif
    </div>
    <div class="panel-body">
        @if ($event->description)<p>{{ $event->description }}</p>@endif
        @if ($event->early_dismissal_at)<p><span class="status pending">Pulang lebih awal {{ substr($event->early_dismissal_at, 0, 5) }} · sesi setelah waktu ini tidak dijadwalkan</span></p>@endif
        @if ($event->attendance_mode === 'morning_evening')<p>Absensi: pagi {{ substr($event->morning_start, 0, 5) }}–{{ substr($event->morning_deadline, 0, 5) }} · sore {{ substr($event->evening_start, 0, 5) }}–{{ substr($event->evening_deadline, 0, 5) }}</p>@elseif ($event->attendance_mode === 'once')<p>Absensi: {{ substr($event->once_start, 0, 5) }}–{{ substr($event->once_deadline, 0, 5) }}</p>@endif
        @if ($event->schedule_conflicts?->isNotEmpty())
            <div class="status pending">Ada konflik dengan {{ $event->schedule_conflicts->count() }} sesi jadwal mengajar</div>
            <ul>@foreach ($event->schedule_conflicts as $conflict)<li>{{ $conflict['class'] }} · {{ $conflict['subject'] }} · {{ $conflict['start'] }}–{{ $conflict['end'] }}</li>@endforeach</ul>
        @endif
        @if (in_array($event->attendance_mode, ['morning_evening', 'once'], true))
            <div class="event-attendance-status">
                @foreach (($event->attendance_mode === 'once' ? ['once' => 'Absensi kegiatan'] : ['morning' => 'Absensi masuk pagi', 'evening' => 'Absensi sore/pulang']) as $session => $label)
                    @php($status = $event->attendance_record?->{'status_'.$session})
                    <span class="status {{ $status === 'hadir' || $status === 'terlambat' ? 'approved' : ($status ? 'rejected' : 'pending') }}">{{ $label }}: {{ $status ? ucfirst(str_replace('_', ' ', $status)) : 'Belum tercatat' }}</span>
                @endforeach
            </div>
        @else
            <span class="status pending">{{ $event->holiday_name && $event->attendance_mode === 'normal' ? 'Libur Nasional · tanpa override absensi' : (['normal' => 'Absensi normal sesuai jadwal', 'none' => 'Tanpa absensi'][$event->attendance_mode] ?? '') }}</span>
        @endif
        <a class="btn" href="{{ route('calendar.show', $event) }}">Detail kegiatan{{ in_array($event->attendance_mode, ['morning_evening', 'once'], true) ? ' & absensi' : '' }}</a>
    </div>
</article>
