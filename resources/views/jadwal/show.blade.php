@extends('layouts.app')

@section('title', 'Detail Jadwal')

@section('content')
    <div class="page-head">
        <div><h1>Detail jadwal</h1><p>{{ $jadwal->hari }} · {{ $jadwal->kelas->nama_kelas }} · {{ $jadwal->mapel->nama_mapel }}</p></div>
        @if (auth()->user()->role === 'admin')
            <div class="form-actions">
                <a class="btn btn-muted" href="{{ route('jadwal.edit', $jadwal) }}">Edit</a>
                <form action="{{ route('jadwal.destroy', $jadwal) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Hapus</button>
                </form>
            </div>
        @endif
    </div>
    <section class="panel">
        <div class="panel-head"><h2>{{ $jadwal->mapel->nama_mapel }}</h2><span class="status {{ $jadwal->is_active ? 'approved' : 'rejected' }}">{{ $jadwal->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div class="detail-item"><dt>Hari</dt><dd>{{ $jadwal->hari }}</dd></div>
                <div class="detail-item"><dt>Jam</dt><dd>{{ $jadwal->jamPelajaran->jam_mulai }} - {{ $jadwal->jamPelajaran->jam_selesai }}</dd></div>
                <div class="detail-item"><dt>Guru</dt><dd>{{ $jadwal->guru->nama_guru }}</dd></div>
                <div class="detail-item"><dt>Kelas</dt><dd>{{ $jadwal->kelas->nama_kelas }}</dd></div>
            </dl>
        </div>
    </section>
@endsection
