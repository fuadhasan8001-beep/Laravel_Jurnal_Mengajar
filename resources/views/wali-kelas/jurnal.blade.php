@extends('layouts.app')
@section('title', 'Rekap Jurnal Wali Kelas')
@section('content')
    <div class="page-head"><div><h1>Rekap jurnal kelas</h1><p>{{ $kelasWali->pluck('nama_kelas')->join(', ') }} · Terverifikasi sekretaris</p></div></div>
    <form method="GET" action="{{ route('wali-kelas.jurnal.index') }}" class="form-grid wali-jurnal-filter">
        <div class="field"><label for="rekap-tanggal">Tanggal (opsional)</label><input type="date" id="rekap-tanggal" name="tanggal" value="{{ $tanggal }}">@error('tanggal')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="rekap-guru">Cari nama guru</label><input type="search" id="rekap-guru" name="guru" value="{{ $guru }}" maxlength="100" placeholder="Nama guru">@error('guru')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="form-actions"><button class="btn" type="submit">Cari jurnal</button><a class="btn btn-muted" href="{{ route('wali-kelas.jurnal.index') }}">Reset</a></div>
    </form>
    <section class="panel panel-top-spaced">
        <div class="panel-head"><h2>Jurnal terverifikasi</h2><span class="eyebrow">{{ $jurnals->total() }} jurnal</span></div>
        <div class="table-wrap responsive-card-table-wrap"><table class="responsive-card-table wali-jurnal-table">
            <thead><tr><th>Guru</th><th>Kelas / Mapel</th><th>Jam</th><th>Kehadiran</th><th></th></tr></thead>
            <tbody>
            @forelse ($jurnals as $jurnal)
                @php($hari = $jurnal->tanggal->copy()->locale('id')->translatedFormat('l'))
                <tr>
                    <td data-label="Guru">{{ $jurnal->guru->nama_guru }}</td>
                    <td data-label="Kelas / Mapel">{{ $jurnal->kelas->nama_kelas }}<br>{{ $jurnal->mapel->nama_mapel }}</td>
                    <td data-label="Jam">{{ substr($jurnal->jamMulai->timesForDay($hari)[0], 0, 5) }} - {{ substr($jurnal->jamSelesai->timesForDay($hari)[1], 0, 5) }}</td>
                    <td data-label="Kehadiran">{{ $jurnal->status_guru }}</td>
                    <td class="wali-jurnal-action"><button class="btn btn-muted" type="button" data-open-dialog="wali-jurnal-detail-{{ $jurnal->id }}">Tampilkan detail</button></td>
                </tr>
                <dialog class="journal-detail-dialog" id="wali-jurnal-detail-{{ $jurnal->id }}" aria-labelledby="wali-jurnal-title-{{ $jurnal->id }}">
                    <div class="journal-detail-head"><h2 id="wali-jurnal-title-{{ $jurnal->id }}">Detail jurnal</h2><form method="dialog"><button class="btn btn-muted" aria-label="Tutup detail">Tutup</button></form></div>
                    <dl class="detail-grid">
                        <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnal->guru->nama_guru }}</dd></div>
                        <div class="detail-item"><dt>Kelas</dt><dd>{{ $jurnal->kelas->nama_kelas }}</dd></div>
                        <div class="detail-item"><dt>Mata pelajaran</dt><dd>{{ $jurnal->mapel->nama_mapel }}</dd></div>
                        <div class="detail-item"><dt>Jam</dt><dd>{{ substr($jurnal->jamMulai->timesForDay($hari)[0], 0, 5) }} - {{ substr($jurnal->jamSelesai->timesForDay($hari)[1], 0, 5) }}</dd></div>
                        <div class="detail-item"><dt>Kehadiran</dt><dd>{{ $jurnal->status_guru }}</dd></div>
                        <div class="detail-item"><dt>Materi</dt><dd>{{ $jurnal->materi ?: '-' }}</dd></div>
                        <div class="detail-item detail-item-full"><dt>Tugas</dt><dd>{{ $jurnal->tugas ?: '-' }}</dd></div>
                    </dl>
                    <a class="btn btn-muted" href="{{ route('wali-kelas.jurnal.show', $jurnal) }}">Buka jurnal lengkap</a>
                </dialog>
            @empty
                <tr><td colspan="5">Belum ada jurnal terverifikasi untuk tanggal dan pencarian ini.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $jurnals->links() }}
    </section>
@endsection

@push('styles')
<style>
    .wali-jurnal-filter {
        grid-template-columns: minmax(180px, 240px) minmax(0, 1fr) auto;
        align-items: end;
    }
    .wali-jurnal-filter .field { min-width: 0; }
    .wali-jurnal-filter .form-actions {
        width: auto;
        margin: 0;
        align-self: end;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    @media (max-width: 900px) {
        .wali-jurnal-filter { grid-template-columns: minmax(0, 1fr); }
        .wali-jurnal-filter .form-actions { flex-direction: row; }
        .wali-jurnal-filter .form-actions .btn { width: auto; }
    }
        @media (max-width: 720px) {
            .wali-jurnal-table .wali-jurnal-action { grid-column: 1 / -1; }
            .wali-jurnal-table .wali-jurnal-action::before { content: none; }
            .wali-jurnal-table .wali-jurnal-action .btn { width: 100%; }
        }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal());
    });
</script>
@endpush
