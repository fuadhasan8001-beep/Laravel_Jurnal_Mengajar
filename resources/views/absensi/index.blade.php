@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
    <div class="page-head"><div><h1>Kelola absensi</h1><p>Rekap kehadiran siswa berdasarkan jurnal mengajar.</p></div><span class="status approved">Status D = dispensasi</span></div>
    @forelse ($jurnals as $jurnal)
        <section class="panel" style="margin-bottom:18px"><div class="panel-head"><div><h2>{{ $jurnal->tanggal }}</h2><span class="eyebrow">Guru ID {{ $jurnal->guru_id }} · Kelas {{ $jurnal->kelas_id }} · Mapel {{ $jurnal->mapel_id }}</span></div><span class="eyebrow">{{ $jurnal->absensis->count() }} siswa tercatat</span></div>@if ($jurnal->absensis->count())<div class="table-wrap"><table><thead><tr><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead><tbody>@foreach ($jurnal->absensis as $absensi)<tr><td><strong>{{ $absensi->siswa->nama_siswa ?? 'Nama siswa belum tersedia' }}</strong></td><td><span class="status {{ $absensi->status === 'D' ? 'approved' : 'pending' }}">{{ $absensi->status }}</span></td><td>{{ $absensi->catatan ?? '-' }}</td></tr>@endforeach</tbody></table></div>@else<div class="empty">Belum ada data absensi untuk jurnal ini.</div>@endif</section>
    @empty
        <section class="panel"><div class="empty">Belum ada jurnal yang tersedia.</div></section>
    @endforelse
@endsection