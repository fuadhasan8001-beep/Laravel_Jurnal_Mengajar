@extends('layouts.app')

@section('content')
    <h2>Rekap Jurnal Tahunan</h2>

    {{-- Filter tahun --}}
    <form method="GET" action="{{ route('rekap.tahunan') }}">
        <input type="number" name="tahun" value="{{ $tahun }}" min="2020" max="2100">
        <button type="submit">Tampilkan</button>
    </form>

    <p>
        Tahun: <strong>{{ $tahun }}</strong>
        | Total Jurnal: <strong>{{ $totalJurnal }}</strong>
    </p>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Bulan</th>
                <th>Jumlah Jurnal</th>
            </tr>
        </thead>
        <tbody>
            @foreach(range(1, 12) as $b)
                <tr>
                    <td>{{ $b }}</td>
                    <td>{{ \Carbon\Carbon::createFromDate($tahun, $b, 1)->translatedFormat('F') }}</td>
                    <td>{{ $perBulan->get($b, collect())->count() }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2"><strong>TOTAL</strong></td>
                <td><strong>{{ $totalJurnal }}</strong></td>
            </tr>
        </tbody>
    </table>
@endsection