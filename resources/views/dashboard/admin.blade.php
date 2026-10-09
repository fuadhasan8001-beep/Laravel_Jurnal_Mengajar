@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="page-head"><div><h1>Selamat datang, {{ auth()->user()->name }}</h1><p>Pantau aktivitas akademik dan proses verifikasi dari satu tempat.</p></div><a class="btn" href="{{ route('dispensasi.index') }}">Verifikasi dispensasi</a></div>
    @if (auth()->user()->isMaster())
        <section class="panel"><div class="panel-head"><h2>Bypass master</h2><span class="eyebrow">{{ session('master_bypass_enabled') ? 'Aktif' : 'Nonaktif' }}</span></div><div class="panel-body"><form method="POST" action="{{ route('admin.master-bypass') }}" class="form-stack">@csrf<input type="hidden" name="enabled" value="1"><label for="master-guru">Guru yang dibantu</label><select id="master-guru" name="guru_id" required><option value="">Pilih guru</option>@foreach ($masterGurus as $guru)<option value="{{ $guru->id }}" @selected(session('master_bypass_guru_id') == $guru->id)>{{ $guru->nama_guru }}</option>@endforeach</select><button class="btn" type="submit">Aktifkan bypass master</button></form><form method="POST" action="{{ route('admin.master-bypass') }}">@csrf<input type="hidden" name="enabled" value="0"><button class="btn" type="submit">Nonaktifkan bypass</button></form>@if (session('master_bypass_enabled'))<a class="btn" href="{{ route('jurnal.create') }}">Isi jurnal untuk guru terpilih</a>@endif<p>Aktifkan izin piket dan pengisian jurnal untuk guru terpilih. Pengisian jurnal akan diberi catatan “bypass master”.</p></div></section>
    @endif
    <div class="stats">
        <div class="stat-card"><span class="stat-icon"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5M4 19h16M8 16V9M12 16V6M16 16v-4"/></svg></span><span><small>Total jurnal</small><strong>{{ $totalJurnal }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon amber"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span><small>Menunggu verifikasi</small><strong>{{ $menungguVerifikasi }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon green"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg></span><span><small>Disetujui bulan ini</small><strong>{{ $disetujuiBulanIni }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon red"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5M12 17h.01"/><path d="M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/></svg></span><span><small>Perlu perhatian</small><strong>{{ $perluPerhatian }}</strong></span></div>
    </div>
    <section class="panel"><div class="panel-head"><h2>Akses cepat</h2><span class="eyebrow">Operasional hari ini</span></div><div class="panel-body"><div class="quick-grid"><a class="quick-card" href="{{ route('absensi.index') }}"><strong>Kelola absensi</strong><span>Periksa status kehadiran kelas</span></a><a class="quick-card" href="{{ route('admin.calendar.index') }}"><strong>Kalender &amp; kegiatan</strong><span>Atur kegiatan dan absensi khusus</span></a><a class="quick-card" href="{{ route('admin.gurus.index') }}"><strong>Data master</strong><span>Kelola siswa, guru, kelas, dan referensi</span></a></div></div></section>
    @if ($nationalHolidayToday || $schoolEventsToday->isNotEmpty())
        <section class="panel panel-spaced"><div class="panel-head"><div><h2>Kalender hari ini</h2><span class="eyebrow">{{ today()->locale('id')->translatedFormat('l, d F Y') }}</span></div>@if ($nationalHolidayToday)<span class="status approved">{{ $nationalHolidayToday }} · Libur Nasional</span>@endif</div><div class="panel-body"><div class="quick-grid">@forelse ($schoolEventsToday as $event)<a class="quick-card" href="{{ route('admin.calendar.index', ['month' => $event->event_date->format('Y-m')]) }}"><strong>{{ $event->title }}</strong><span>{{ $event->event_type }} · {{ ['normal' => 'Normal sesuai jadwal', 'morning_evening' => 'Pagi & Sore', 'once' => 'Sekali saja', 'none' => 'Tanpa absensi'][$event->attendance_mode] }} · {{ $event->attendances->count() }} peserta</span></a>@empty<span>Tidak ada kegiatan sekolah hari ini.</span>@endforelse</div></div></section>
    @endif

    <h1 class="page-title">Dashboard Admin</h1>
    <p class="page-subtitle">
        Kelola data utama dan pantau aktivitas sistem jurnal mengajar.
    </p>

    <div class="stats-grid">
    </div>
@endsection
