@extends('layouts.app')

@section('title', 'Dashboard Piket')

@section('content')
    <div class="page-head">
        <div>
            <h1>Meja piket</h1>
            <p>Dispensasi dan izin siswa.</p>
        </div>
    </div>
    <section class="panel panel-spaced">
        <div class="panel-head"><h2>Menu piket hari ini</h2><span class="eyebrow">Pilih tugas</span></div>
        <div class="panel-body quick-grid">
            <a class="quick-card quick-card-featured" href="{{ route('piket.rekap-jurnal') }}">
                <strong>Rekap jurnal per kelas</strong>
                <span>Pantau jurnal guru dan status verifikasi untuk setiap kelas.</span>
            </a>
            <a class="quick-card" href="{{ route('dispensasi.create') }}">
                <strong>Ajukan dispensasi</strong>
                <span>Pilih siswa, isi alasan, dan kirim pengajuan ke admin.</span>
            </a>
            <a class="quick-card" href="{{ route('piket.izin-sekolah.create') }}">
                <strong>Catat izin atau sakit seharian</strong>
                <span>Unggah surat orang tua agar status siswa tercatat untuk seluruh hari sekolah.</span>
            </a>
            <a class="quick-card" href="{{ route('piket.izin-masuk.create') }}">
                <strong>Buat surat izin masuk</strong>
                <span>Catat siswa terlambat, jam kedatangan, dan perbarui absensi mulai jam tersebut.</span>
            </a>
            <a class="quick-card" href="{{ route('piket.izin-sekolah.index') }}">
                <strong>Riwayat izin dan sakit</strong>
                <span>Lihat siswa yang tidak masuk seharian dan surat yang sudah dicatat.</span>
            </a>
            <a class="quick-card" href="{{ route('dispensasi.index') }}">
                <strong>Riwayat dispensasi</strong>
                <span>Lihat pengajuan piket dan status keputusan admin.</span>
            </a>
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
                </svg></span><span><small>Antrian
                    baru</small><strong>{{ $antrianBaru }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon green"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="m5 12 4 4L19 6" />
                </svg></span><span><small>Diverifikasi hari
                    ini</small><strong>{{ $diverifikasiHariIni }}</strong></span></div>
        <div class="stat-card"><span class="stat-icon"><svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M6 3h12a2 2 0 0 1 2 2v16l-8-4-8 4V5a2 2 0 0 1 2-2Z" />
                </svg></span><span><small>Total bulan
                    ini</small><strong>{{ $totalBulanIni }}</strong></span></div>
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
            <h2>Pengajuan dispensasi</h2><span class="eyebrow">Persetujuan admin</span>
        </div>
        <div class="panel-body">
            <p class="dashboard-note">Siswa datang ke meja piket. Piket mencatat siswa, alasan, dan bukti, lalu mengirim pengajuan kepada admin.</p>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Menunggu keputusan admin</h2>
            <a href="{{ route('dispensasi.index') }}">Lihat semua</a>
        </div>

        @if ($pengajuanMenunggu->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Bukti</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengajuanMenunggu as $pengajuan)
                            <tr>
                                <td>
                                    <strong>{{ $pengajuan->siswa->nama_siswa }}</strong>
                                </td>
                                <td>{{ $pengajuan->tanggal->format('d M Y') }}</td>
                                <td>
                                    {{ $pengajuan->jamMulai->jam_ke }} -
                                    {{ $pengajuan->jamSelesai->jam_ke }}
                                </td>
                                <td>
                                    <span class="status {{ $pengajuan->bukti ? 'approved' : 'pending' }}">
                                        {{ $pengajuan->bukti ? 'Tersedia' : 'Tidak ada' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('dispensasi.show', $pengajuan) }}">
                                        Lihat detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">
                Tidak ada pengajuan yang menunggu verifikasi.
            </div>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .quick-card-featured {
            border-color: var(--gold-dark);
            background: linear-gradient(135deg, rgba(168, 137, 74, .16), rgba(255, 255, 255, .96));
            box-shadow: 0 8px 22px rgba(105, 77, 28, .12);
        }

        .quick-card-featured strong {
            color: var(--gold-dark);
            font-size: 16px;
        }
    </style>
@endpush
