@extends('layouts.app')
@section('title', 'Rekap Jurnal Wali Kelas')
@section('content')
    <div class="page-head"><div><h1>Rekap jurnal kelas</h1><p>{{ $kelasWali->pluck('nama_kelas')->join(', ') }} · Terverifikasi sekretaris</p></div></div>
    <form method="GET" action="{{ route('wali-kelas.jurnal.index') }}" class="form-grid">
        <div class="field"><label for="rekap-tanggal">Tanggal</label><input type="date" id="rekap-tanggal" name="tanggal" value="{{ $tanggal }}">@error('tanggal')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="field"><label for="rekap-guru">Cari nama guru</label><input type="search" id="rekap-guru" name="guru" value="{{ $guru }}" maxlength="100" placeholder="Nama guru">@error('guru')<small class="error">{{ $message }}</small>@enderror</div>
        <div class="form-actions"><button class="btn" type="submit">Cari jurnal</button><a class="btn btn-muted" href="{{ route('wali-kelas.jurnal.index') }}">Reset</a></div>
    </form>
    <section class="panel panel-top-spaced">
        <div class="panel-head"><h2>Jurnal terverifikasi</h2><span class="eyebrow">{{ $jurnals->total() }} jurnal</span></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Guru</th><th>Kelas / Mapel</th><th>Jam</th><th>Kehadiran</th><th>Materi</th><th></th></tr></thead>
            <tbody>
            @forelse ($jurnals as $jurnal)
                @php($hari = $jurnal->tanggal->copy()->locale('id')->translatedFormat('l'))
                <tr>
                    <td>{{ $jurnal->guru->nama_guru }}</td>
                    <td>{{ $jurnal->kelas->nama_kelas }}<br>{{ $jurnal->mapel->nama_mapel }}</td>
                    <td>{{ substr($jurnal->jamMulai->timesForDay($hari)[0], 0, 5) }} - {{ substr($jurnal->jamSelesai->timesForDay($hari)[1], 0, 5) }}</td>
                    <td>{{ $jurnal->status_guru }}</td><td>{{ $jurnal->materi ?: '-' }}</td>
                    <td><a class="btn btn-muted" href="{{ route('wali-kelas.jurnal.show', $jurnal) }}">Lihat detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada jurnal terverifikasi untuk tanggal dan pencarian ini.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $jurnals->links() }}
    </section>
@endsection
