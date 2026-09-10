@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="page-head">
        <div>
            <h1>Selamat datang, {{ auth()->user()->name }}</h1>
            <p>Pantau aktivitas akademik dan proses verifikasi dari satu tempat.</p>
        </div><a
            class="btn"
            href="{{ route('dispensasi.index') }}"
        >Buka verifikasi</a>
    </div>
    <div class="stats">
        <div class="stat-card"><span class="stat-icon"><small>Guru</small></span><span><small>Total guru</small><strong>{{ $totalGuru }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon"><small>Siswa</small></span><span><small>Total siswa</small><strong>{{ $totalSiswa }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon"><small>Kelas</small></span><span><small>Total kelas</small><strong>{{ $totalKelas }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon"><small>Mapel</small></span><span><small>Total mapel</small><strong>{{ $totalMapel }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 19V5M4 19h16M8 16V9M12 16V6M16 16v-4" />
                </svg></span><span><small>Total
                    jurnal</small><strong>{{ $totalJurnal }}</strong></span></div>
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
                </svg></span><span><small>Menunggu
                    verifikasi</small><strong>{{ $menungguVerifikasi }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon green"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="m5 12 4 4L19 6" />
                </svg></span><span><small>Disetujui bulan
                    ini</small><strong>{{ $disetujuiBulanIni }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon red"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M12 8v5M12 17h.01" />
                    <path
                        d="M10.3 3.8 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"
                    />
                </svg></span><span><small>Perlu
                    perhatian</small><strong>{{ $perluPerhatian }}</strong></span></div>
    </div>
    <section class="panel">
        <div class="panel-head">
            <h2>Akses cepat</h2><span class="eyebrow">Operasional hari ini</span>
        </div>
        <div class="panel-body">
            <div class="quick-grid"><a
                    class="quick-card"
                    href="{{ route('dispensasi.index') }}"
                ><strong>Verifikasi dispensasi</strong><span>Tinjau pengajuan siswa yang
                        masuk</span></a><a
                    class="quick-card"
                    href="{{ route('absensi.index') }}"
                ><strong>Kelola absensi</strong><span>Periksa status kehadiran kelas</span></a></div>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Monitoring jurnal</h2><span class="eyebrow">Aktivitas akademik</span></div>
        <div class="panel-body">
            <div class="quick-grid">
                <a class="quick-card" href="{{ route('jurnal.index') }}"><strong>Jurnal hari ini</strong><span>{{ $jurnalHariIni }} jurnal tercatat</span></a>
                <a class="quick-card" href="{{ route('jurnal.index', ['status_verifikasi' => 'Menunggu']) }}"><strong>Jurnal menunggu</strong><span>{{ $jurnalBelumLengkap }} jurnal belum lengkap</span></a>
                <a class="quick-card" href="{{ route('jadwal.index') }}"><strong>Kelola jadwal</strong><span>{{ $jurnalBulanIni }} jurnal bulan ini</span></a>
            </div>
        </div>
    </section>
@endsection
