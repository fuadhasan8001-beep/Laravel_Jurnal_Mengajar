@extends('layouts.app')

@section('title', 'Dispensasi Siswa')

@section('content')
    <div class="page-head"><div><h1>Dispensasi siswa</h1><p>{{ auth()->user()->role === 'siswa' ? 'Pantau seluruh pengajuan dan keputusan dispensasi Anda.' : 'Tinjau pengajuan dispensasi yang membutuhkan tindakan.' }}</p></div>@if (auth()->user()->role === 'siswa')<a class="btn" href="{{ route('dispensasi.create') }}">+ Ajukan dispensasi</a>@endif</div>
    <section class="panel"><div class="panel-head"><h2>Riwayat pengajuan</h2><span class="eyebrow">{{ $dispensasis->count() }} pengajuan</span></div>@if ($dispensasis->isNotEmpty())<div class="table-wrap"><table><thead><tr><th>Siswa</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th></th></tr></thead><tbody>@foreach ($dispensasis as $item)<tr><td><strong>{{ $item->siswa->nama_siswa }}</strong></td><td>{{ $item->tanggal->format('d M Y') }}</td><td>Jam {{ $item->jamMulai->jam_ke }} - {{ $item->jamSelesai->jam_ke }}</td><td><span class="status {{ $item->status_akhir === 'Disetujui' ? 'approved' : ($item->status_akhir === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $item->status_akhir }}</span></td><td><a href="{{ route('dispensasi.show', $item) }}">Lihat detail →</a></td></tr>@endforeach</tbody></table></div>@else<div class="empty">Belum ada pengajuan dispensasi.</div>@endif</section>
@endsection