@extends('layouts.app')

@section('title', 'Jurnal Mengajar')

@section('content')
    <div class="page-head">
        <div>
            <h1>Jurnal mengajar</h1>
            <p>Catat dan pantau kegiatan pembelajaran.</p>
        </div>
        @if (auth()->user()->role === 'guru')
            <a class="btn" href="{{ route('jurnal.create') }}">Isi jurnal</a>
        @endif
    </div>

    @unless (auth()->user()->role === 'sekretaris')
    <section class="panel">
        <div class="panel-head"><h2>Filter riwayat jurnal</h2></div>
        <div class="panel-body">
            <form method="GET" action="{{ route('jurnal.index') }}">
                <div class="form-grid">
                    <div class="field">
                        <label for="tanggal_mulai">Dari tanggal</label>
                        <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="field">
                        <label for="tanggal_selesai">Sampai tanggal</label>
                        <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">
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
                    <div class="field">
                        <label for="mapel_id">Mata pelajaran</label>
                        <select id="mapel_id" name="mapel_id">
                            <option value="">Semua mata pelajaran</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}" @selected((string) request('mapel_id') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="status_verifikasi">Status</label>
                        <select id="status_verifikasi" name="status_verifikasi">
                            <option value="">Semua status</option>
                            @foreach (['Menunggu', 'Disetujui', 'Ditolak'] as $status)
                                <option value="{{ $status }}" @selected(request('status_verifikasi') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('jurnal.index') }}">Reset</a>
                    <button class="btn" type="submit">Terapkan filter</button>
                </div>
            </form>
        </div>
    </section>
    @endunless

    <section class="panel">
        <div class="panel-head">
            <h2>Daftar jurnal</h2>
            <span class="eyebrow">{{ $jurnals->total() }} jurnal</span>
        </div>
        @if ($jurnals->isNotEmpty())
            <div class="table-wrap responsive-card-table-wrap">
                <table class="responsive-card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Jam</th>
                            <th>Materi</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jurnals as $jurnal)
                            <tr>
                                <td data-label="Tanggal">{{ $jurnal->tanggal->format('d M Y') }}</td>
                                <td data-label="Guru">{{ $jurnal->guru->nama_guru }}</td>
                                <td data-label="Kelas">{{ $jurnal->kelas->nama_kelas }}</td>
                                <td data-label="Mapel">{{ $jurnal->mapel->nama_mapel }}</td>
                                <td data-label="Jam">{{ $jurnal->jamMulai->jam_ke }} - {{ $jurnal->jamSelesai->jam_ke }}</td>
                                <td data-label="Materi">{{ $jurnal->materi }}</td>
                                <td data-label="Status"><span class="status {{ $jurnal->status_verifikasi === 'Disetujui' ? 'approved' : ($jurnal->status_verifikasi === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $jurnal->status_verifikasi }}</span></td>
                                <td class="journal-table-action" data-label="Detail"><a href="{{ route('jurnal.show', $jurnal) }}">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">{{ $jurnals->links() }}</div>
        @else
            <div class="empty">Belum ada jurnal yang sesuai filter.</div>
        @endif
    </section>
@endsection
