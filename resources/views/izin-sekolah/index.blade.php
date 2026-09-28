@extends('layouts.app')

@section('title', 'Izin Sekolah')

@section('content')
    <div class="page-head">
        <div>
            <h1>Izin sekolah seharian</h1>
            <p>Surat orang tua yang sudah dicatat Piket.</p>
        </div>
        <a class="btn" href="{{ route('piket.izin-sekolah.create') }}">Catat izin siswa</a>
    </div>

    <section class="panel">
        <div class="panel-head"><h2>Riwayat izin</h2><span class="eyebrow">{{ $izinSekolahs->total() }} catatan</span></div>
        @if ($izinSekolahs->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Catatan</th><th>Dicatat oleh</th><th>Surat</th></tr></thead>
                    <tbody>
                        @foreach ($izinSekolahs as $izin)
                            <tr>
                                <td>{{ $izin->tanggal->format('d M Y') }}</td>
                                <td>{{ $izin->siswa->nama_siswa }}</td>
                                <td>{{ $izin->siswa->kelas->nama_kelas }}</td>
                                <td>{{ $izin->alasan ?: '-' }}</td>
                                <td>{{ $izin->piket?->name ?: '-' }}</td>
                                <td><a href="{{ route('piket.izin-sekolah.surat', $izin) }}">Lihat surat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">{{ $izinSekolahs->links() }}</div>
        @else
            <div class="empty">Belum ada catatan izin sekolah.</div>
        @endif
    </section>
@endsection