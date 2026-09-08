@extends('layouts.app')

@section('title', 'Rekap Dispensasi')

@section('content')
    <div class="page-head">
        <div><h1>Rekap dispensasi</h1><p>Ringkasan pengajuan berdasarkan periode dan status.</p></div>
        <a class="btn" href="{{ route('laporan.dispensasi.export', request()->query()) }}">Export CSV</a>
    </div>
    <div class="stats">
        @foreach (['Menunggu', 'Disetujui', 'Ditolak'] as $status)
            <div class="stat-card"><span><small>{{ $status }}</small><strong>{{ $summary[$status] ?? 0 }}</strong></span></div>
        @endforeach
    </div>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('laporan.dispensasi') }}">
                <div class="form-grid">
                    <div class="field"><label for="tanggal_mulai">Dari tanggal</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"></div>
                    <div class="field"><label for="tanggal_selesai">Sampai tanggal</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"></div>
                    <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
                    <div class="field"><label for="status_akhir">Status</label><select id="status_akhir" name="status_akhir"><option value="">Semua status</option>@foreach (['Menunggu', 'Disetujui', 'Ditolak'] as $status)<option value="{{ $status }}" @selected(request('status_akhir') === $status)>{{ $status }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('laporan.dispensasi') }}">Reset</a><button class="btn" type="submit">Terapkan filter</button></div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data dispensasi</h2><span class="eyebrow">{{ $dispensasis->total() }} data</span></div>
        @if ($dispensasis->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jam</th><th>Status</th><th>Alasan</th></tr></thead><tbody>@foreach ($dispensasis as $item)<tr><td>{{ $item->tanggal->format('d M Y') }}</td><td>{{ $item->siswa->nama_siswa }}</td><td>{{ $item->siswa->kelas->nama_kelas }}</td><td>{{ $item->jamMulai->jam_ke }} - {{ $item->jamSelesai->jam_ke }}</td><td>{{ $item->status_akhir }}</td><td>{{ $item->alasan }}</td></tr>@endforeach</tbody></table></div>
            <div class="panel-body">{{ $dispensasis->links() }}</div>
        @else
            <div class="empty">Belum ada pengajuan pada filter ini.</div>
        @endif
    </section>
@endsection
