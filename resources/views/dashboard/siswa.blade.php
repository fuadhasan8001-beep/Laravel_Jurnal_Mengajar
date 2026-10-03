@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
    <div class="page-head">
        <div>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p>Kelola pengajuan dispensasi dan lihat riwayat status Anda.</p>
        </div>
        <div class="page-actions">
            <a class="btn" href="{{ route('dispensasi.create') }}">Ajukan dispensasi</a>
            <a class="btn btn-muted" href="{{ route('calendar.index') }}">Kalender sekolah</a>
        </div>
    </div>

    @if ($nationalHolidayName)
        <section class="panel panel-spaced"><div class="panel-head"><h2>{{ $nationalHolidayName }}</h2><span class="status approved">Libur Nasional</span></div><div class="panel-body"><p>Status libur nasional tetap berlaku; kegiatan yang ditujukan kepada Anda ditampilkan terpisah.</p></div></section>
    @endif
    @if ($schoolEvents->isNotEmpty())
        <section class="panel panel-spaced"><div class="panel-head"><h2>Kegiatan sekolah</h2><span class="eyebrow">30 hari ke depan</span></div><div class="panel-body event-card-list">@foreach ($schoolEvents as $event)@include('school-calendar._event-card', ['event' => $event])@endforeach</div></section>
    @endif

    <section class="panel panel-spaced">
        <div class="panel-head"><h2>Aksi cepat</h2><span class="eyebrow">Status pengajuan</span></div>
        <div class="panel-body">
            <div class="quick-grid">
                <a class="quick-card" href="{{ route('dispensasi.index') }}">
                    <strong>Riwayat dispensasi</strong>
                    <span>Cek status terbaru dan detail keputusan yang sudah diberikan.</span>
                </a>
                <a class="quick-card" href="{{ route('dispensasi.index') }}">
                    <strong>Status saya</strong>
                    <span>Lihat pengajuan yang menunggu, disetujui, atau ditolak.</span>
                </a>
            </div>
        </div>
    </section>

    <div class="stats">
        <div class="stat-card"><span class="stat-icon amber"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    />
                    <path d="M12 7v5l3 2" />
                </svg></span><span><small>Menunggu</small><strong>{{ $menunggu }}</strong></span>
        </div>
        <div class="stat-card"><span class="stat-icon green"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="m5 12 4 4L19 6" />
                </svg></span><span><small>Disetujui</small><strong>{{ $disetujui }}</strong></span>
        </div>
        <div class="stat-card"><span class="stat-icon red"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="m7 7 10 10M17 7 7 17" />
                </svg></span><span><small>Ditolak</small><strong>{{ $ditolak }}</strong></span>
        </div>
        <div class="stat-card"><span class="stat-icon"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M6 3h12a2 2 0 0 1 2 2v16l-8-4-8 4V5a2 2 0 0 1 2-2Z" />
                </svg></span><span><small>Total
                    pengajuan</small><strong>{{ $totalPengajuan }}</strong></span></div>
    </div>
    <section class="panel">
        <div class="panel-head">
            <h2>Aktivitas dispensasi</h2><a href="{{ route('dispensasi.index') }}">Lihat semua</a>
        </div>
        <div class="panel-body">
            <p class="dashboard-note">Ajukan dispensasi lebih awal agar
                proses verifikasi berjalan lancar.</p>
        </div>
    </section>
@endsection

@push('styles')
<style>.event-card-list{display:grid;gap:12px}.school-event-card{box-shadow:none}.school-event-card .panel-body,.event-attendance-status{display:grid;gap:12px}</style>
@endpush
