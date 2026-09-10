@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
    <div class="page-head">
        <div><h1>Rekap absensi</h1><p>Ringkasan kehadiran siswa berdasarkan jurnal.</p></div>
        <a class="btn" href="{{ route('laporan.absensi.export', request()->query()) }}">Export CSV</a>
    </div>
    <div class="stats">
        @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $status => $label)
            <div class="stat-card"><span><small>{{ $label }}</small><strong>{{ $summary[$status] ?? 0 }}</strong></span></div>
        @endforeach
    </div>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('laporan.absensi') }}">
                <div class="form-grid">
                    <div class="field"><label for="tanggal_mulai">Dari tanggal</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"></div>
                    <div class="field"><label for="tanggal_selesai">Sampai tanggal</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"></div>
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id"><option value="">Semua guru</option>@foreach ($gurus as $guru)<option value="{{ $guru->id }}" @selected((string) request('guru_id') === (string) $guru->id)>{{ $guru->nama_guru }}</option>@endforeach</select></div>
                    <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
                    <div class="field"><label for="mapel_id">Mapel</label><select id="mapel_id" name="mapel_id"><option value="">Semua mapel</option>@foreach ($mapels as $mapel)<option value="{{ $mapel->id }}" @selected((string) request('mapel_id') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div class="field"><label for="siswa_id">Siswa</label><select id="siswa_id" name="siswa_id"><option value="">Semua siswa</option>@foreach ($siswas as $siswa)<option value="{{ $siswa->id }}" @selected((string) request('siswa_id') === (string) $siswa->id)>{{ $siswa->nama_siswa }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('laporan.absensi') }}">Reset</a><button class="btn" type="submit">Terapkan filter</button></div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data absensi</h2><span class="eyebrow">{{ $absensis->total() }} data</span></div>
        @if ($absensis->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Guru</th><th>Mapel</th><th>Status</th><th>Catatan</th></tr></thead><tbody>@foreach ($absensis as $absensi)<tr><td>{{ $absensi->jurnal->tanggal->format('d M Y') }}</td><td>{{ $absensi->siswa->nama_siswa }}</td><td>{{ $absensi->jurnal->kelas->nama_kelas }}</td><td>{{ $absensi->jurnal->guru->nama_guru }}</td><td>{{ $absensi->jurnal->mapel->nama_mapel }}</td><td>{{ $absensi->status }}</td><td>{{ $absensi->catatan ?: '-' }}</td></tr>@endforeach</tbody></table></div>
            <div class="panel-body">{{ $absensis->links() }}</div>
        @else
            <div class="empty">Belum ada data absensi pada filter ini.</div>
        @endif
    </section>
@endsection
