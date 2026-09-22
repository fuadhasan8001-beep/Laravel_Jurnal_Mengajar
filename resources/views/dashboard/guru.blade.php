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

    @if ($jurnalTerbaru)
        <section class="panel panel-top-spaced">
            <div class="panel-head">
                <h2>Detail pembelajaran</h2>
                <span class="eyebrow">{{ $jurnalTerbaru->kelas->nama_kelas }} · {{ $jurnalTerbaru->mapel->nama_mapel }}</span>
            </div>
            <div class="panel-body">
                <dl class="detail-grid">
                    <div class="detail-item"><dt>Tanggal</dt><dd>{{ $jurnalTerbaru->tanggal->format('d M Y') }}</dd></div>
                    <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnalTerbaru->guru->nama_guru }}</dd></div>
                    <div class="detail-item"><dt>Kelas</dt><dd>{{ $jurnalTerbaru->kelas->nama_kelas }}</dd></div>
                    <div class="detail-item"><dt>Mata pelajaran</dt><dd>{{ $jurnalTerbaru->mapel->nama_mapel }}</dd></div>
                    <div class="detail-item"><dt>Jam pelajaran</dt><dd>{{ $jurnalTerbaru->jamMulai->jam_mulai }} - {{ $jurnalTerbaru->jamSelesai->jam_selesai }}</dd></div>
                    <div class="detail-item"><dt>Materi</dt><dd>{{ $jurnalTerbaru->materi ?: '-' }}</dd></div>
                    <div class="detail-item"><dt>Status guru</dt><dd>{{ $jurnalTerbaru->status_guru ?: '-' }}</dd></div>
                    <div class="detail-item detail-item-full"><dt>Tujuan pembelajaran</dt><dd>{{ $jurnalTerbaru->tujuan_pembelajaran ?: '-' }}</dd></div>
                    <div class="detail-item detail-item-full"><dt>Kegiatan</dt><dd>{{ $jurnalTerbaru->kegiatan ?: '-' }}</dd></div>
                    <div class="detail-item detail-item-full"><dt>Tugas</dt><dd>{{ $jurnalTerbaru->tugas ?: '-' }}</dd></div>
                    <div class="detail-item detail-item-full"><dt>Catatan</dt><dd>{{ $jurnalTerbaru->catatan ?: '-' }}</dd></div>
                </dl>
                @if ($jurnalTerbaru->tanda_tangan)
                    <p><a href="{{ route('jurnal.signature', $jurnalTerbaru) }}">Lihat tanda tangan guru</a></p>
                @endif
                <div class="attendance-section attendance-card">
                    <div class="journal-card-header">
                        <div>
                            <h3>Absensi siswa</h3>
                            <p>{{ $jurnalTerbaru->absensis->count() }} siswa tercatat</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jurnalTerbaru->absensis as $absensi)
                                    <tr>
                                        <td>{{ $absensi->siswa->nama_siswa }}</td>
                                        <td><span class="status {{ $absensi->status === 'D' ? 'approved' : '' }}">{{ ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispen'][$absensi->status] ?? $absensi->status }}</span></td>
                                        <td>{{ $absensi->catatan ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">Belum ada absensi siswa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    @endif

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
                            <th>Jam ke</th>
                            <th>Kelas</th>
                            <th>Mata pelajaran</th>
                            <th>Status jurnal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwalHariIni as $jadwal)
                            @php
                                [$jamMulai, $jamSelesai] = $jadwal->jamPelajaran->timesForDay($jadwal->hari);
                                $jurnal = $jurnalHariIni->first(fn ($item) => (int) $item->kelas_id === (int) $jadwal->kelas_id
                                    && (int) $item->mapel_id === (int) $jadwal->mapel_id
                                    && $item->jamMulai->jam_ke <= $jadwal->jamPelajaran->jam_ke
                                    && $item->jamSelesai->jam_ke >= $jadwal->jamPelajaran->jam_ke);
                            @endphp
                            <tr>
                                <td>Jam {{ $jadwal->jamPelajaran->jam_ke }}<br><span class="eyebrow">{{ substr($jamMulai, 0, 5) }} - {{ substr($jamSelesai, 0, 5) }}</span></td>
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
                                    @elseif ($activeJadwalIds->contains($jadwal->id))
                                        <a href="{{ route('jurnal.create', ['jadwal_id' => $jadwal->id]) }}">Buat jurnal</a>
                                    @else
                                        <span class="eyebrow">Belum aktif</span>
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
