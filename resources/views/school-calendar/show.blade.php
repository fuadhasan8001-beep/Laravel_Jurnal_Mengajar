@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="page-head"><div><h1>{{ $event->title }}</h1><p>{{ $event->event_type }} · {{ $event->event_date->locale('id')->translatedFormat('l, d F Y') }}</p></div><a class="btn btn-muted" href="{{ auth()->user()->role === 'admin' || auth()->user()->role === 'waka' ? route('admin.calendar.index', ['month' => $event->event_date->format('Y-m')]) : url('/'.auth()->user()->role) }}">Kembali</a></div>
<section class="panel panel-spaced"><div class="panel-body">
    @if ($holiday)<span class="status approved">{{ $holiday->name }} · Libur Nasional</span>@endif
    <p>{{ $event->description ?: 'Tidak ada keterangan tambahan.' }}</p>
    <p><strong>Waktu:</strong> {{ substr($event->activity_start, 0, 5) }}–{{ substr($event->activity_end, 0, 5) }}</p>
    <p><strong>Peserta:</strong> {{ ['semua_guru' => 'Semua guru', 'guru_tertentu' => 'Guru tertentu', 'semua_siswa' => 'Semua siswa', 'kelas_tertentu' => 'Kelas tertentu'][$event->participant_scope] }}</p>
    <p><strong>Lokasi:</strong> {{ $event->location_mode === 'school' ? 'Sekolah' : 'Lokasi khusus · '.$event->location_latitude.', '.$event->location_longitude }} · Radius {{ $event->location_mode === 'custom' ? $event->location_radius_meters : config('school.radius_meters') }} meter</p>
    <p><strong>Mode absensi:</strong> {{ ['normal' => 'Normal sesuai jadwal', 'morning_evening' => 'Pagi & Sore', 'once' => 'Sekali saja', 'none' => 'Tanpa absensi'][$event->attendance_mode] }}</p>
    @if ($event->attendance_mode === 'morning_evening')<p><strong>Absensi pagi:</strong> {{ substr($event->morning_start, 0, 5) }}–{{ substr($event->morning_deadline, 0, 5) }} · <strong>sore:</strong> {{ substr($event->evening_start, 0, 5) }}–{{ substr($event->evening_deadline, 0, 5) }}</p>@elseif ($event->attendance_mode === 'once')<p><strong>Absensi:</strong> {{ substr($event->once_start, 0, 5) }}–{{ substr($event->once_deadline, 0, 5) }}</p>@endif
    @if ($conflicts->isNotEmpty())
        <div class="status pending">Kegiatan ini bertabrakan dengan jadwal mengajar Anda:</div>
        <ul>@foreach ($conflicts as $conflict)<li>{{ $conflict['class'] }} · {{ $conflict['subject'] }} · {{ $conflict['start'] }}–{{ $conflict['end'] }}</li>@endforeach</ul>
    @endif
</div></section>
@if (auth()->user()->role === 'guru' || auth()->user()->role === 'siswa')
    <section class="panel"><div class="panel-head"><div><h2>Absensi kegiatan</h2><span class="eyebrow">Status otomatis mengikuti waktu sesi dan izin yang tercatat</span></div></div><div class="panel-body">
        @if ($attendance)
            @if ($attendance->activity_note)<p><strong>Catatan Anda:</strong> {{ $attendance->activity_note }}</p>@endif
            <div class="stats">
                @foreach (['once' => 'Sekali', 'morning' => 'Pagi', 'evening' => 'Sore/pulang'] as $session => $label)
                    @if ($event->attendance_mode === 'once' && $session === 'once' || $event->attendance_mode === 'morning_evening' && $session !== 'once')
                        <div class="stat-card"><span><small>{{ $label }}</small><strong>{{ $attendance->{'status_'.$session} ?? 'Belum hadir' }}</strong>@if ($attendance->{$session.'_at'})<small>{{ $attendance->{$session.'_at'}->format('H:i') }}</small>@endif</span></div>
                    @endif
                @endforeach
            </div>
        @endif
        @if ($event->attendance_mode === 'none')
            <p>Kegiatan ini tidak memerlukan absensi.</p>
        @elseif ($event->attendance_mode === 'normal' && $holiday)
            <p>Kegiatan ini tidak mengubah status Libur Nasional dan tidak mengaktifkan absensi khusus. Guru tidak perlu membuat jurnal mengajar pada tanggal ini.</p>
        @elseif ($event->attendance_mode === 'normal')
            <p>Gunakan jurnal dan absensi sesuai jadwal mengajar yang berlaku.</p>
        @else
            <p>Absensi lokasi menggunakan geofence {{ $event->location_mode === 'school' ? 'sekolah' : 'lokasi kegiatan khusus' }}. Jika berada di luar area, sistem menolak absensi.</p>
            <div class="page-actions">
                @foreach (($event->attendance_mode === 'once' ? ['once' => 'Absensi kegiatan'] : ['morning' => 'Absensi masuk pagi', 'evening' => 'Absensi sore/pulang']) as $session => $label)
                    @if (! $attendance?->{$session.'_at'} && ! $attendance?->{'status_'.$session})
                        <form method="POST" action="{{ route('calendar.attend', $event) }}" data-event-attendance-form>
                            @csrf<input type="hidden" name="session" value="{{ $session }}"><input type="hidden" name="latitude"><input type="hidden" name="longitude"><input type="hidden" name="accuracy">
                            <button class="btn" type="submit">{{ $label }}</button>
                        </form>
                    @endif
                @endforeach
            </div>
        @endif
        @if ($event->event_date->isToday())
            <form method="POST" action="{{ route('calendar.note', $event) }}" class="form-stack">@csrf<label for="activity_note">Catatan kegiatan (opsional)</label><textarea id="activity_note" name="activity_note" maxlength="2000" rows="3">{{ old('activity_note', $attendance?->activity_note) }}</textarea><button class="btn btn-muted" type="submit">Simpan catatan</button></form>
        @endif
    </div></section>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-event-attendance-form]').forEach((form) => form.addEventListener('submit', (event) => {
    event.preventDefault();
    if (!navigator.geolocation) { alert('Perangkat tidak mendukung GPS.'); return; }
    const button = form.querySelector('button'); button.disabled = true; button.textContent = 'Memeriksa lokasi…';
    navigator.geolocation.getCurrentPosition((position) => {
        form.elements.latitude.value = position.coords.latitude;
        form.elements.longitude.value = position.coords.longitude;
        form.elements.accuracy.value = position.coords.accuracy;
        form.submit();
    }, () => { button.disabled = false; alert('Lokasi gagal dibaca. Aktifkan GPS dan izinkan akses lokasi.'); }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
}));
</script>
@endpush
