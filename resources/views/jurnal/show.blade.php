@extends('layouts.app')

@section('title', 'Detail Jurnal')

@section('content')
    <div class="page-head">
        <div>
            <h1>Detail jurnal</h1>
            <p>{{ $jurnal->tanggal->format('d M Y') }} · {{ $jurnal->kelas->nama_kelas }} · {{ $jurnal->mapel->nama_mapel }}</p>
        </div>
        <div class="form-actions">
            @if (auth()->user()->role === 'guru' && $jurnal->status_verifikasi === 'Menunggu')
                <a class="btn btn-muted" href="{{ route('jurnal.edit', $jurnal) }}">Edit</a>
                <form action="{{ route('jurnal.destroy', $jurnal) }}" method="POST" onsubmit="return confirm('Hapus jurnal ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Hapus</button>
                </form>
            @endif
            <a class="btn btn-muted" href="{{ route('jurnal.index') }}">Kembali</a>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <h2>{{ $jurnal->materi }}</h2>
            <span class="status {{ $jurnal->status_verifikasi === 'Disetujui' ? 'approved' : ($jurnal->status_verifikasi === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $jurnal->status_verifikasi }}</span>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnal->guru->nama_guru }}</dd></div>
                <div class="detail-item"><dt>Jam</dt><dd>{{ $jurnal->jamMulai->jam_mulai }} - {{ $jurnal->jamSelesai->jam_selesai }}</dd></div>
                <div class="detail-item"><dt>Status guru</dt><dd>{{ $jurnal->status_guru }}</dd></div>
                <div class="detail-item"><dt>Kelas</dt><dd>{{ $jurnal->kelas->nama_kelas }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Tujuan pembelajaran</dt><dd>{{ $jurnal->tujuan_pembelajaran ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Kegiatan</dt><dd>{{ $jurnal->kegiatan ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Tugas</dt><dd>{{ $jurnal->tugas ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Catatan</dt><dd>{{ $jurnal->catatan ?: '-' }}</dd></div>
            </dl>
        </div>
    </section>
@endsection
