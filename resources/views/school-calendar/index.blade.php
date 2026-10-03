@extends('layouts.app')

@section('title', 'Kalender & Kegiatan Sekolah')

@section('content')
<div class="page-head">
    <div><h1>Kalender &amp; kegiatan sekolah</h1><p>Status libur nasional tersimpan terpisah. Kegiatan dapat mengatur override absensi untuk tanggal tersebut.</p></div>
    <div class="page-actions"><a class="btn btn-muted" href="{{ route('admin.calendar.audit') }}">Riwayat audit</a>
    <form method="GET" action="{{ route('admin.calendar.index') }}" class="page-actions"><input type="month" name="month" value="{{ $month }}"><button class="btn" type="submit">Tampilkan</button></form>
    </div>
</div>
<section class="panel panel-spaced">
    <div class="panel-head"><h2>{{ $monthStart->locale('id')->translatedFormat('F Y') }}</h2><span class="eyebrow">Data hari libur nasional tersinkronisasi</span></div>
    <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Status hari</th><th>Kegiatan &amp; override</th></tr></thead><tbody>
        @foreach ($days as $day)
            @php($holiday = $holidays->get($day->toDateString()))
            @php($dayEvents = $events->get($day->toDateString(), collect()))
            <tr><td>{{ $day->locale('id')->translatedFormat('d F Y') }}</td><td>{{ $holiday?->name ?? 'Hari normal' }}{{ $holiday ? ' · Libur Nasional' : '' }}</td><td>
                @forelse ($dayEvents as $event)
                    <details class="calendar-event-card">
                        <summary><strong>{{ $event->title }}</strong> · {{ $event->event_type }} · {{ ['normal' => 'Normal sesuai jadwal', 'morning_evening' => 'Pagi & Sore', 'once' => 'Sekali saja', 'none' => 'Tanpa absensi'][$event->attendance_mode] }}</summary>
                        <div class="panel-body">
                            <p>{{ $event->activity_start }}–{{ $event->activity_end }} · {{ $event->participant_scope }} · {{ $event->location_mode === 'school' ? 'Lokasi sekolah' : 'Lokasi khusus' }}</p>
                            @if ($event->schedule_conflicts->isNotEmpty())
                                <div class="status pending">Konflik dengan jadwal mengajar ({{ $event->schedule_conflicts->count() }})</div>
                                <ul>@foreach ($event->schedule_conflicts as $conflict)<li>{{ $conflict['teacher'] }} · {{ $conflict['class'] }} · {{ $conflict['subject'] }} ({{ $conflict['start'] }}–{{ $conflict['end'] }})</li>@endforeach</ul>
                            @endif
                            <p><strong>Peserta kegiatan ({{ $event->attendances->count() }}):</strong></p>
                            <div class="table-wrap"><table><thead><tr><th>Peserta</th>@if (in_array($event->attendance_mode, ['morning_evening', 'once'], true))<th>Pagi / sekali</th><th>Sore</th><th>Catatan</th><th>Ubah status</th>@endif</tr></thead><tbody>
                                @foreach ($event->attendances as $record)
                                    <tr><td>{{ $record->user->name }}</td>@if (in_array($event->attendance_mode, ['morning_evening', 'once'], true))<td>{{ $record->status_once ?? $record->status_morning ?? 'belum hadir' }}</td><td>{{ $record->status_evening ?? '—' }}</td><td>{{ $record->activity_note ?: '—' }}</td><td>
                                        @if ($event->attendance_mode === 'once' || $event->attendance_mode === 'morning_evening')
                                            @foreach (($event->attendance_mode === 'once' ? ['once' => 'Sekali'] : ['morning' => 'Pagi', 'evening' => 'Sore']) as $session => $label)
                                                <form method="POST" action="{{ route('admin.calendar.attendance-status', [$event, $record]) }}" class="attendance-status-form">@csrf @method('PUT')<input type="hidden" name="session" value="{{ $session }}"><select name="status" required><option value="" selected disabled>Pilih status</option><option value="hadir" @selected($record->{'status_'.$session} === 'hadir')>Hadir</option><option value="terlambat" @selected($record->{'status_'.$session} === 'terlambat')>Terlambat</option><option value="izin" @selected($record->{'status_'.$session} === 'izin')>Izin</option><option value="sakit" @selected($record->{'status_'.$session} === 'sakit')>Sakit</option><option value="tidak_hadir" @selected($record->{'status_'.$session} === 'tidak_hadir')>Tidak hadir</option></select><button class="btn btn-muted" type="submit">{{ $label }}</button></form>
                                            @endforeach
                                        @else
                                            —
                                        @endif
                                    </td>@endif</tr>
                                @endforeach
                            </tbody></table></div>
                            <details><summary>Ubah kegiatan</summary><div class="panel-body">@include('school-calendar._form', ['event' => $event])</div></details>
                            <details><summary>Riwayat perubahan</summary><ul>@foreach ($event->audits as $audit)<li>{{ $audit->created_at->format('d/m/Y H:i') }} · {{ $audit->actor?->name ?? 'Pengguna dihapus' }} · {{ $audit->action }}</li>@endforeach</ul></details>
                            <form method="POST" action="{{ route('admin.calendar.destroy', $event) }}" onsubmit="return confirm('Hapus kegiatan ini?')">@csrf @method('DELETE')<button class="btn btn-muted" type="submit">Hapus kegiatan</button></form>
                        </div>
                    </details>
                @empty
                    <span>—</span>
                @endforelse
            </td></tr>
        @endforeach
    </tbody></table></div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>Buat kegiatan</h2><span class="eyebrow">Dapat dijadwalkan saat libur nasional</span></div></div>
    <div class="panel-body">
        @if ($errors->any())<div class="status rejected"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @include('school-calendar._form', ['event' => null])
    </div>
</section>
@endsection
@push('styles')
<style>
.calendar-event-card { padding: 12px; border-bottom: 1px solid var(--line); }
.calendar-event-card > summary,.calendar-event-card details > summary { cursor: pointer; }
.calendar-event-card .panel-body,.attendance-status-form { display: grid; gap: 12px; }
.attendance-status-form { grid-template-columns: 1fr auto; margin-bottom: 6px; }
@media (max-width: 680px) { .calendar-event-card .table-wrap { overflow-x: auto; } }
</style>
@endpush
