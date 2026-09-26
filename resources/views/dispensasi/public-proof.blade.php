@extends('layouts.app')

@section('title', 'Bukti Dispensasi')

@section('content')
    <section class="panel">
        <div class="panel-head"><h1>Bukti dispensasi</h1><span class="status approved">Disetujui</span></div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div class="detail-item"><dt>Nama siswa</dt><dd>{{ $dispensasi->siswa->nama_siswa }}</dd></div>
                <div class="detail-item"><dt>NIS</dt><dd>{{ $dispensasi->siswa->nis }}</dd></div>
                <div class="detail-item"><dt>Kelas</dt><dd>{{ $dispensasi->siswa->kelas->nama_kelas }}</dd></div>
                <div class="detail-item"><dt>Tanggal</dt><dd>{{ $dispensasi->tanggal->format('d M Y') }}</dd></div>
                <div class="detail-item"><dt>Waktu</dt><dd>{{ $dispensasi->jamMulai->jam_mulai }} – {{ $dispensasi->jamSelesai->jam_selesai }}</dd></div>
                <div class="detail-item"><dt>Guru piket</dt><dd>{{ $dispensasi->piket?->name ?? '-' }}</dd></div>
                <div class="detail-item"><dt>Disetujui waka</dt><dd>{{ $dispensasi->waka?->name ?? '-' }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Keperluan</dt><dd>{{ $dispensasi->alasan }}</dd></div>
            </dl>
        </div>
    </section>
@endsection
