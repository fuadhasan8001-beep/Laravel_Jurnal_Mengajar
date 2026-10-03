@extends('layouts.app')
@include('absensi._styles')

@pushOnce('styles', 'teacher-schedule-compact')
    <style>
        .teacher-schedule .status.pending {
            border: 1px solid #e4bb54;
            background: #fff3ce;
            color: #795600;
        }

        .teacher-schedule .status.approved {
            border: 1px solid #75c99e;
            background: #e4f7ed;
            color: #17663f;
        }

        .teacher-schedule .status.rejected {
            border: 1px solid #e7a4aa;
            background: #fff0f1;
            color: #9d2633;
        }

        .teacher-schedule .status.not-created {
            border: 1px solid #c8c4b8;
            background: #f2f0ea;
            color: #5e5a50;
        }

        .teacher-schedule .schedule-action {
            display: inline-flex;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            padding: 4px 0;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color .15s ease;
        }

        .teacher-schedule .schedule-action-journal {
            color: #684b0e;
            min-height: 36px;
            font-size: 14px;
        }

        .teacher-schedule .schedule-action:hover {
            text-decoration-thickness: 2px;
        }

        .teacher-schedule .schedule-action-journal:hover {
            color: #9a6200;
        }

        .teacher-schedule .schedule-action:focus-visible {
            outline: 3px solid #283e71;
            outline-offset: 2px;
        }

        .teacher-schedule .table-wrap {
            overflow-x: visible;
        }

        .teacher-schedule table {
            min-width: 0;
            table-layout: fixed;
        }

        @media (max-width: 680px) {
            .teacher-schedule thead {
                display: none;
            }

            .teacher-schedule tbody,
            .teacher-schedule tbody tr {
                display: block;
            }

            .teacher-schedule tbody tr {
                display: grid;
                grid-template-columns: minmax(0, 1fr) max-content;
                align-items: center;
                gap: 2px 12px;
                padding: 8px 12px;
                border-bottom: 1px solid var(--line);
            }

            .teacher-schedule td {
                min-width: 0;
                padding: 3px 0;
                border: 0;
                font-size: 12px;
                overflow-wrap: anywhere;
            }

            .teacher-schedule td:nth-child(1) {
                grid-column: 1;
                grid-row: 1;
            }

            .teacher-schedule td:nth-child(2) {
                grid-column: 1 / -1;
                grid-row: 2;
                padding-top: 5px;
                font-weight: 700;
            }

            .teacher-schedule td:nth-child(3) {
                grid-column: 1 / -1;
                grid-row: 3;
                color: var(--muted);
                line-height: 1.4;
                overflow-wrap: anywhere;
                white-space: normal;
            }

            .teacher-schedule td:nth-child(4) {
                grid-column: 2;
                grid-row: 1;
                justify-self: end;
            }

            .teacher-schedule .status {
                white-space: nowrap;
            }

            .teacher-schedule .schedule-action {
                margin-right: 0;
            }

            .teacher-schedule td:nth-child(5) {
                display: flex;
                grid-column: 1 / -1;
                grid-row: 4;
                justify-content: flex-start;
                gap: 16px;
                padding-top: 6px;
            }
        }

        @media (max-width: 380px) {
            .teacher-schedule tbody tr {
                grid-template-columns: minmax(0, 1fr) max-content;
                gap: 2px 8px;
                padding-right: 10px;
                padding-left: 10px;
            }

            .teacher-schedule .status {
                padding: 5px 8px;
                font-size: 11px;
            }
        }
    </style>
@endPushOnce

@section('title', 'Dashboard Guru')

@section('content')
    <div class="page-head">
        <div>
            <h1>Ruang mengajar</h1>
            <p>Kelola jurnal dan pantau kehadiran kelas Anda.</p>
        </div>

        <div class="page-actions">
            @if (! $specialDay)<a class="btn" href="{{ route('jurnal.create') }}">Isi jurnal</a>@endif
            <a class="btn btn-muted" href="{{ route('calendar.index') }}">Kalender sekolah</a>
        </div>
    </div>

    @if ($todayEvents->isNotEmpty() || $nationalHolidayName)
        <section class="panel panel-spaced">
            <div class="panel-head"><div><h2>Kalender hari ini</h2><span class="eyebrow">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span></div>@if ($nationalHolidayName)<span class="status approved">{{ $nationalHolidayName }} · Libur Nasional</span>@endif</div>
            <div class="panel-body">
                @forelse ($todayEvents as $event)
                    @include('school-calendar._event-card', ['event' => $event])
                @empty
                    @if ($nationalHolidayName)<p>Hari ini libur nasional dan tidak ada kegiatan yang ditujukan kepada Anda.</p>@endif
                @endforelse
            </div>
        </section>
    @endif

    @if ($schoolEvents->contains(fn ($event) => ! $event->event_date->isToday()))
        <section class="panel panel-spaced"><div class="panel-head"><h2>Kegiatan mendatang</h2><a href="{{ route('calendar.show', $schoolEvents->first(fn ($event) => ! $event->event_date->isToday())) }}">Lihat kalender</a></div><div class="panel-body event-card-list">@foreach ($schoolEvents->filter(fn ($event) => ! $event->event_date->isToday()) as $event)@include('school-calendar._event-card', ['event' => $event])@endforeach</div></section>
    @endif

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
                    <div class="detail-item"><dt>Jam pelajaran</dt><dd>{{ $jurnalTerbaru->jamMulai->timesForDay($jurnalTerbaru->tanggal->locale('id')->translatedFormat('l'))[0] }} - {{ $jurnalTerbaru->jamSelesai->timesForDay($jurnalTerbaru->tanggal->locale('id')->translatedFormat('l'))[1] }}</dd></div>
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
                        </div>
                    </div>
                    @if ($jurnalTerbaru->absensis->isEmpty())
                        <p>Nihil</p>
                    @else
                        <details class="attendance-dropdown">
                            <summary>{{ $jurnalTerbaru->absensis->count() }} siswa tercatat · Lihat absensi</summary>
                            <div class="table-wrap">
                                <table>
                                    <thead><tr><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead>
                                    <tbody>
                                        @foreach ($jurnalTerbaru->absensis as $absensi)
                                            <tr>
                                                <td>{{ $absensi->siswa->nama_siswa }}</td>
                                                <td><span class="status {{ $absensi->status === 'D' ? 'approved' : '' }}">{{ ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispen'][$absensi->status] ?? $absensi->status }}</span></td>
                                                <td>{{ $absensi->catatan ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if (! $specialDay)
    <section class="panel">
        <div class="panel-head">
            <h2>Jadwal mengajar hari ini</h2>
            <span class="eyebrow">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
        </div>

        @if ($jadwalHariIni->isNotEmpty())
            <div class="table-wrap teacher-schedule">
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
                                $statusClass = match ($jurnal?->status_verifikasi) {
                                    'Disetujui' => 'approved',
                                    'Ditolak' => 'rejected',
                                    'Menunggu' => 'pending',
                                    default => 'not-created',
                                };
                            @endphp
                            <tr>
                                <td>Jam {{ $jadwal->jamPelajaran->jam_ke }}<br><span class="eyebrow">{{ substr($jamMulai, 0, 5) }} - {{ substr($jamSelesai, 0, 5) }}</span></td>
                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                <td>{{ $jadwal->mapel->nama_mapel }}</td>
                                <td>
                                    <span class="status {{ $statusClass }}">
                                        {{ $jurnal ? $jurnal->status_verifikasi : 'Belum dibuat' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($jurnal)
                                        <a class="schedule-action schedule-action-journal" href="{{ route('jurnal.show', $jurnal) }}">Lihat jurnal</a>
                                    @elseif ($activeJadwalIds->contains($jadwal->id))
                                        <a class="schedule-action schedule-action-journal" href="{{ route('jurnal.create', ['jadwal_id' => $jadwal->id]) }}">Buat jurnal</a>
                                    @else
                                        <span class="eyebrow">Belum aktif</span>
                                    @endif
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
    @endif
@endsection

@push('styles')
<style>.event-card-list,.event-attendance-status{display:grid;gap:12px}.school-event-card{box-shadow:none}.school-event-card .panel-body{display:grid;gap:12px}</style>
@endpush
