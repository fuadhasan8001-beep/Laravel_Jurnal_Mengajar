@extends('layouts.app')

@section('content')
    <h2>Rekap Jurnal Bulanan</h2>

    {{-- Filter bulan --}}
    <form method="GET" action="{{ route('rekap.bulanan') }}">
        <input type="month" name="bulan" value="{{ $bulan }}">
        <button type="submit">Tampilkan</button>
    </form>

    <p>
        Bulan:
        <strong>{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}</strong>
        | Total Jurnal: <strong>{{ $totalJurnal }}</strong>
    </p>

    @forelse($jurnals as $tgl => $dataHari)
        <h3>{{ \Carbon\Carbon::parse($tgl)->translatedFormat('l, d M Y') }}</h3>

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
                @foreach($dataHari as $jurnal)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $jurnal->guru->nama }}</td>
                        <td>{{ $jurnal->kelas->nama_kelas }}</td>
                        <td>{{ $jurnal->mapel->nama_mapel }}</td>
                        <td>{{ $jurnal->materi }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Tidak ada jurnal pada bulan ini.</p>
    @endforelse
@endsection