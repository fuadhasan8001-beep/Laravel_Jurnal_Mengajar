@extends('layouts.app')

@section('title', 'Rekap Jurnal')

@section('content')
    <div class="page-head">
        <div><h1>Rekap jurnal mengajar</h1><p>Gunakan filter untuk meninjau dan mengunduh jurnal.</p></div>
        <a class="btn" href="{{ route('laporan.jurnal.export', request()->query()) }}">Export CSV</a>
    </div>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('laporan.jurnal') }}">
                <div class="form-grid">
                    <div class="field"><label for="tanggal_mulai">Dari tanggal</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"></div>
                    <div class="field"><label for="tanggal_selesai">Sampai tanggal</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"></div>
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id"><option value="">Semua guru</option>@foreach ($gurus as $guru)<option value="{{ $guru->id }}" @selected((string) request('guru_id') === (string) $guru->id)>{{ $guru->nama_guru }}</option>@endforeach</select></div>
                    <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
                    <div class="field"><label for="mapel_id">Mapel</label><select id="mapel_id" name="mapel_id"><option value="">Semua mapel</option>@foreach ($mapels as $mapel)<option value="{{ $mapel->id }}" @selected((string) request('mapel_id') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div class="field"><label for="status_verifikasi">Status</label><select id="status_verifikasi" name="status_verifikasi"><option value="">Semua status</option>@foreach (['Menunggu', 'Disetujui', 'Ditolak'] as $status)<option value="{{ $status }}" @selected(request('status_verifikasi') === $status)>{{ $status }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('laporan.jurnal') }}">Reset</a><button class="btn" type="submit">Terapkan filter</button></div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data jurnal</h2><span class="eyebrow">{{ $jurnals->total() }} data</span></div>
        @if ($jurnals->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Jam</th><th>Materi</th><th>Status</th></tr></thead><tbody>@foreach ($jurnals as $jurnal)<tr><td>{{ $jurnal->tanggal->format('d M Y') }}</td><td>{{ $jurnal->guru->nama_guru }}</td><td>{{ $jurnal->kelas->nama_kelas }}</td><td>{{ $jurnal->mapel->nama_mapel }}</td><td>{{ $jurnal->jamMulai->jam_ke }} - {{ $jurnal->jamSelesai->jam_ke }}</td><td>{{ $jurnal->materi }}</td><td>{{ $jurnal->status_verifikasi }}</td></tr>@endforeach</tbody></table></div>
            <div class="panel-body">{{ $jurnals->links() }}</div>
        @else
            <div class="empty">Belum ada jurnal pada periode ini.</div>
        @endif
    </section>
@endsection
