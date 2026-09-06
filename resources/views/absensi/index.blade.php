@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
    <h2>Absensi Siswa</h2>

    @forelse ($jurnals as $jurnal)
        <div>
            <h3>
                {{ $jurnal->tanggal }}
            </h3>

            <p>
                Guru ID: {{ $jurnal->guru_id }} |
                Kelas ID: {{ $jurnal->kelas_id }} |
                Mapel ID: {{ $jurnal->mapel_id }}
            </p>

            @if ($jurnal->absensis->count())
                <table border="1" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($jurnal->absensis as $absensi)
                            <tr>
                                <td>{{ $absensi->siswa->nama ?? 'Nama siswa belum tersedia' }}</td>
                                <td>{{ $absensi->status }}</td>
                                <td>{{ $absensi->catatan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Belum ada data absensi.</p>
            @endif

        </div>

        <hr>
    @empty
        <p>Belum ada jurnal yang tersedia.</p>
    @endforelse
@endsection