@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <div class="page-head">
        <div>
            <h1>Ruang mengajar</h1>
            <p>Kelola jurnal dan pantau kehadiran kelas Anda.</p>
        </div>

        <a
            class="btn"
            href="{{ route('absensi.index') }}"
        >
            Lihat absensi
        </a>
    </div>

    <div class="stats">
        <div class="stat-card">
            <span class="stat-icon">
                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 5h16v14H4z" />
                    <path d="M8 9h8M8 13h5" />
                </svg>
            </span>

            <span>
                <small>Jurnal minggu ini</small>
                <strong>{{ $jurnalMingguIni }}</strong>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-icon green">
                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="m5 12 4 4L19 6" />
                </svg>
            </span>

            <span>
                <small>Rata-rata hadir</small>
                <strong>-</strong>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-icon amber">
                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 19V5M4 19h16M8 16V9M12 16V6M16 16v-4" />
                </svg>
            </span>

            <span>
                <small>Kelas aktif</small>
                <strong>{{ $kelasAktif }}</strong>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-icon">
                <svg
                    width="21"
                    height="21"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle
                        cx="12"
                        cy="8"
                        r="3"
                    />
                    <path d="M5 20a7 7 0 0 1 14 0" />
                </svg>
            </span>

            <span>
                <small>Siswa terpantau</small>
                <strong>{{ $siswaTerpantau }}</strong>
            </span>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <h2>Jadwal mengajar hari ini</h2>
            <span class="eyebrow">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>

        @if ($jadwalHariIni->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Kelas</th>
                            <th>Mata pelajaran</th>
                            <th>Status jurnal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwalHariIni as $jadwal)
                            @php
                                $jurnal = $jurnalHariIni->first(fn ($item) => (int) $item->kelas_id === (int) $jadwal->kelas_id && (int) $item->mapel_id === (int) $jadwal->mapel_id && (int) $item->jam_mulai_id === (int) $jadwal->jam_pelajaran_id);
                            @endphp
                            <tr>
                                <td>{{ $jadwal->jamPelajaran->jam_mulai }} - {{ $jadwal->jamPelajaran->jam_selesai }}</td>
                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                <td>{{ $jadwal->mapel->nama_mapel }}</td>
                                <td>
                                    <span class="status {{ $jurnal ? 'approved' : 'pending' }}">
                                        {{ $jurnal ? $jurnal->status_verifikasi : 'Belum dibuat' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($jurnal)
                                        <a href="{{ route('jurnal.show', $jurnal) }}">Lihat jurnal</a>
                                    @else
                                        <a href="{{ route('jurnal.create') }}">Buat jurnal</a>
                                    @endif
                                    <a href="{{ route('absensi.index') }}">Absensi</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">Belum ada jadwal mengajar hari ini.</div>
        @endif
    </section>
@endsection
