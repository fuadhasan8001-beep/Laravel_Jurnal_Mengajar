@extends('layouts.app')

@section('content')
    <h2>Rekap Jurnal Mingguan</h2>

    {{-- Filter tanggal --}}
    <form method="GET" action="{{ route('rekap.mingguan') }}">
        <input type="date" name="tanggal" value="{{ $tanggal }}">
        <button type="submit">Tampilkan</button>
    </form>

    <p>
        Periode: <strong>{{ $senin->format('d M Y') }} - {{ $minggu->format('d M Y') }}</strong>
    </p>

    {{-- Loop 7 hari, dari Senin sampai Minggu --}}
    @for($i = 0; $i < 7; $i++)
        @php
            $hari = $senin->copy()->addDays($i);
            $tgl = $hari->format('Y-m-d');
            $dataHari = $jurnals->get($tgl, collect()); // ambil jurnal hari itu (kosongkan kalau tidak ada)
        @endphp

        <h3>{{ $hari->translatedFormat('l, d M Y') }}</h3>

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
                @forelse($dataHari as $jurnal)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $jurnal->guru->nama }}</td>
                        <td>{{ $jurnal->kelas->nama_kelas }}</td>
                        <td>{{ $jurnal->mapel->nama_mapel }}</td>
                        <td>{{ $jurnal->materi }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Tidak ada jurnal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endfor
@endsection