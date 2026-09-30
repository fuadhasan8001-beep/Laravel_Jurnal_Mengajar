@extends('layouts.app')

@section('title', 'Rekap Jurnal per Kelas')

@section('content')
    <div class="page-head">
        <div><h1>Rekap jurnal per kelas</h1><p>{{ $selectedClass?->nama_kelas ?? 'Piket KBM' }}</p></div>
        @if ($selectedClass)
            <a class="btn btn-muted" href="{{ route('piket.rekap-jurnal.export', ['kelas_id' => $selectedClass->id, 'tanggal_mulai' => $start->toDateString(), 'tanggal_selesai' => $end->toDateString()]) }}">Export CSV</a>
        @endif
    </div>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('piket.rekap-jurnal') }}">
                <div class="form-grid">
                    <div class="field">
                        <label for="kelas_id">Kelas</label>
                        <select id="kelas_id" name="kelas_id" required>
                            <option value="">Pilih kelas</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item->id }}" @selected((string) old('kelas_id', $selectedClass?->id) === (string) $item->id)>{{ $item->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="tanggal_mulai">Dari tanggal</label>
                        <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $start->toDateString()) }}" required>
                        @error('tanggal_mulai')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="tanggal_selesai">Sampai tanggal</label>
                        <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', $end->toDateString()) }}" required>
                        @error('tanggal_selesai')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('piket.rekap-jurnal') }}">Reset</a>
                    <button class="btn" type="submit">Terapkan filter</button>
                </div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>{{ $selectedClass?->nama_kelas ?? 'Jurnal kelas' }}</h2><span class="eyebrow">{{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}</span></div>
        @if (! $selectedClass)
            <div class="empty">Belum ada kelas dipilih.</div>
        @elseif ($rows->isEmpty())
            <div class="empty">Tidak ada jadwal aktif atau jurnal tercatat pada periode ini.</div>
        @else
            <div class="table-wrap responsive-card-table-wrap">
                <table class="responsive-card-table">
                    <thead><tr><th>Tanggal / hari</th><th>Jam pelajaran</th><th>Mata pelajaran</th><th>Guru pengajar</th><th>Status jurnal dan verifikasi</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $availabilityClass = match ($row['status']) {
                                    'Sudah Diisi' => 'is-filled',
                                    'Belum Diisi' => 'is-missing',
                                    'Belum waktunya', 'Terjadwal' => 'is-upcoming',
                                    default => 'is-neutral',
                                };
                                $verificationStatus = $row['jurnal']?->status_verifikasi;
                                $verificationClass = match ($verificationStatus) {
                                    'Disetujui' => 'is-approved',
                                    'Ditolak' => 'is-rejected',
                                    'Menunggu' => 'is-pending',
                                    default => 'is-neutral',
                                };
                            @endphp
                            <tr>
                                <td data-label="Tanggal / hari">{{ $row['tanggal']->locale('id')->translatedFormat('l, d M Y') }}</td>
                                <td data-label="Jam pelajaran">Ke-{{ $row['jam_ke'] }}<br>{{ $row['mulai'] }} - {{ $row['selesai'] }}</td>
                                <td data-label="Mata pelajaran">{{ $row['mapel'] }}</td>
                                <td data-label="Guru pengajar">{{ $row['guru'] }}</td>
                                <td data-label="Status jurnal dan verifikasi" class="piket-report-status">
                                    <span class="piket-status-pill {{ $availabilityClass }}">{{ $row['status'] }}</span>
                                    <span class="piket-status-pill {{ $verificationClass }}">Verifikasi: {{ $verificationStatus ?? 'Belum ada jurnal' }}</span>
                                </td>
                                <td class="piket-report-action">
                                    @if ($row['jurnal'])
                                        <button class="btn btn-muted" type="button" data-piket-detail="piket-jurnal-{{ $row['jurnal']->id }}">Tampilkan detail</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">{{ $rows->links() }}</div>
        @endif
    </section>
    @foreach (collect($rows->items())->pluck('jurnal')->filter()->unique('id') as $jurnal)
        <dialog class="journal-detail-dialog" id="piket-jurnal-{{ $jurnal->id }}" aria-labelledby="piket-title-{{ $jurnal->id }}">
            <div class="journal-detail-head"><h2 id="piket-title-{{ $jurnal->id }}">Detail jurnal {{ $selectedClass->nama_kelas }}</h2><form method="dialog"><button class="btn btn-muted" aria-label="Tutup detail">Tutup</button></form></div>
            <dl class="detail-grid">
                <div class="detail-item"><dt>Tanggal</dt><dd>{{ $jurnal->tanggal->locale('id')->translatedFormat('l, d M Y') }}</dd></div>
                <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnal->guru->nama_guru }}</dd></div>
                <div class="detail-item"><dt>Mata pelajaran</dt><dd>{{ $jurnal->mapel->nama_mapel }}</dd></div>
                <div class="detail-item"><dt>Jam pelajaran</dt><dd>{{ $jurnal->jamMulai->jam_ke }} - {{ $jurnal->jamSelesai->jam_ke }}</dd></div>
                <div class="detail-item"><dt>Kehadiran guru</dt><dd>{{ $jurnal->status_guru }}</dd></div>
                <div class="detail-item"><dt>Status verifikasi</dt><dd>{{ $jurnal->status_verifikasi }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Materi</dt><dd>{{ $jurnal->materi ?: '-' }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Tujuan pembelajaran</dt><dd>{{ $jurnal->tujuan_pembelajaran ?: '-' }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Kegiatan</dt><dd>{{ $jurnal->kegiatan ?: '-' }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Tugas</dt><dd>{{ $jurnal->tugas ?: '-' }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Catatan</dt><dd>{{ $jurnal->catatan ?: '-' }}</dd></div>
            </dl>
        </dialog>
    @endforeach
@endsection

@push('styles')
<style>
    .piket-report-status {
        min-width: 190px;
    }

    .piket-report-status .piket-status-pill {
        display: inline-flex;
        width: fit-content;
        margin: 2px 4px 2px 0;
        padding: 6px 10px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.25;
    }

    .piket-status-pill.is-filled,
    .piket-status-pill.is-approved {
        border-color: #a7dfbd;
        background: #e8f8f0;
        color: #176a3a;
    }

    .piket-status-pill.is-missing,
    .piket-status-pill.is-rejected {
        border-color: #f0b9b9;
        background: #fff0f1;
        color: #9e2828;
    }

    .piket-status-pill.is-upcoming,
    .piket-status-pill.is-neutral {
        border-color: #cbd8e3;
        background: #f0f5f8;
        color: #43596b;
    }

    .piket-status-pill.is-pending {
        border-color: #efd879;
        background: #fff9c4;
        color: #705d12;
    }

    .journal-detail-dialog dd { white-space: pre-wrap; overflow-wrap: anywhere; }
    @media (max-width: 720px) {
        .piket-report-status {
            grid-column: 1 / -1;
            padding: 10px !important;
            border: 1px solid #dfd5bf !important;
            border-radius: 8px;
            background: #fbf8f1;
        }

        .piket-report-action { grid-column: 1 / -1; }
        .piket-report-action::before { content: none !important; }
        .piket-report-action .btn { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-piket-detail]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(button.dataset.piketDetail)?.showModal());
    });
</script>
@endpush
