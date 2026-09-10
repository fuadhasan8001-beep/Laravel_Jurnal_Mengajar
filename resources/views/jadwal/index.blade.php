@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('content')
    <div class="page-head">
        <div>
            <h1>Jadwal mengajar</h1>
            <p>Atur guru, kelas, mata pelajaran, dan jam pelajaran.</p>
        </div>
        @if (auth()->user()->role === 'admin')
            <a class="btn" href="{{ route('jadwal.create') }}">Tambah jadwal</a>
        @endif
    </div>

    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('jadwal.index') }}">
                <div class="form-grid">
                    <div class="field">
                        <label for="hari">Hari</label>
                        <select id="hari" name="hari">
                            <option value="">Semua hari</option>
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                                <option value="{{ $hari }}" @selected(request('hari') === $hari)>{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="guru_id">Guru</label>
                        <select id="guru_id" name="guru_id">
                            <option value="">Semua guru</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}" @selected((string) request('guru_id') === (string) $guru->id)>{{ $guru->nama_guru }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="kelas_id">Kelas</label>
                        <select id="kelas_id" name="kelas_id">
                            <option value="">Semua kelas</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('jadwal.index') }}">Reset</a>
                    <button class="btn" type="submit">Terapkan filter</button>
                </div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Daftar jadwal</h2><span class="eyebrow">{{ $jadwals->total() }} jadwal</span></div>
        @if ($jadwals->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Hari</th><th>Jam</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                            <tr>
                                <td>{{ $jadwal->hari }}</td>
                                <td>{{ $jadwal->jamPelajaran->jam_mulai }} - {{ $jadwal->jamPelajaran->jam_selesai }}</td>
                                <td>{{ $jadwal->guru->nama_guru }}</td>
                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                <td>{{ $jadwal->mapel->nama_mapel }}</td>
                                <td><span class="status {{ $jadwal->is_active ? 'approved' : 'rejected' }}">{{ $jadwal->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td><a href="{{ route('jadwal.show', $jadwal) }}">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">{{ $jadwals->links() }}</div>
        @else
            <div class="empty">Belum ada jadwal.</div>
        @endif
    </section>
@endsection
