@extends('layouts.app')

@section('content')
    <h2>Rekap Jurnal Harian</h2>

    {{-- Form filter tanggal --}}
    <form method="GET" action="{{ route('rekap.harian') }}">
        <input type="date" name="tanggal" value="{{ $tanggal }}">
        <button type="submit">Tampilkan</button>
    </form>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Guru</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Materi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $jurnal)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $jurnal->guru->nama }}</td>
                    <td>{{ $jurnal->kelas->nama_kelas }}</td>
                    <td>{{ $jurnal->mapel->nama_mapel }}</td>
                    <td>{{ $jurnal->materi }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada jurnal pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection