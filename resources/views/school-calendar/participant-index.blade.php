@extends('layouts.app')

@section('title', 'Kalender Sekolah')

@section('content')
<div class="page-head"><div><h1>Kalender sekolah</h1><p>Jadwal kegiatan yang ditujukan kepada Anda dan kalender hari libur nasional.</p></div><form method="GET" action="{{ route('calendar.index') }}" class="page-actions"><input type="month" name="month" value="{{ $month }}"><button class="btn" type="submit">Tampilkan</button></form></div>
<section class="panel"><div class="panel-head"><h2>{{ $monthStart->locale('id')->translatedFormat('F Y') }}</h2></div><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Status kalender</th><th>Kegiatan</th></tr></thead><tbody>
    @foreach ($days as $day)
        @php($holiday = $holidays->get($day->toDateString()))
        @php($dayEvents = $events->get($day->toDateString(), collect()))
        <tr><td>{{ $day->locale('id')->translatedFormat('d F Y') }}</td><td>{{ $holiday?->name ?? 'Hari normal' }}{{ $holiday ? ' · Libur Nasional' : '' }}</td><td>
            @forelse ($dayEvents as $event)
                <details class="participant-event"><summary>{{ $event->title }} · {{ $event->event_type }} · {{ ['normal' => 'Absensi normal', 'morning_evening' => 'Pagi & Sore', 'once' => 'Sekali saja', 'none' => 'Tanpa absensi'][$event->attendance_mode] }}</summary><div class="panel-body">@include('school-calendar._event-card', ['event' => $event])</div></details>
            @empty
                —
            @endforelse
        </td></tr>
    @endforeach
</tbody></table></div></section>
@endsection
@push('styles')
<style>.participant-event{padding:10px;border-bottom:1px solid var(--line)}.participant-event>summary{cursor:pointer}.participant-event .panel-body{display:grid;gap:12px}.school-event-card{box-shadow:none}</style>
@endpush
