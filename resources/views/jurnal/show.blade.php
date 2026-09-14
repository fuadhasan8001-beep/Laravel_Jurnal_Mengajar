@extends('layouts.app')

@section('title', 'Detail Jurnal')

@section('content')
    <div class="page-head">
        <div>
            <h1>Detail jurnal</h1>
            <p>{{ $jurnal->tanggal->format('d M Y') }} · {{ $jurnal->kelas->nama_kelas }} · {{ $jurnal->mapel->nama_mapel }}</p>
        </div>
        <div class="form-actions">
            @if (auth()->user()->role === 'guru' && $jurnal->status_verifikasi === 'Menunggu')
                <a class="btn btn-muted" href="{{ route('jurnal.edit', $jurnal) }}">Edit</a>
                <form action="{{ route('jurnal.destroy', $jurnal) }}" method="POST" onsubmit="return confirm('Hapus jurnal ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn" type="submit">Hapus</button>
                </form>
            @endif
            <a class="btn btn-muted" href="{{ route('jurnal.index') }}">Kembali</a>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <h2>{{ $jurnal->materi }}</h2>
            <span class="status {{ $jurnal->status_verifikasi === 'Disetujui' ? 'approved' : ($jurnal->status_verifikasi === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $jurnal->status_verifikasi }}</span>
        </div>
        <div class="panel-body">
            <dl class="detail-grid">
                <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnal->guru->nama_guru }}</dd></div>
                <div class="detail-item"><dt>Jam</dt><dd>{{ substr($jurnal->jamMulai->timesForDay($jurnal->tanggal->copy()->locale('id')->translatedFormat('l'))[0], 0, 5) }} - {{ substr($jurnal->jamSelesai->timesForDay($jurnal->tanggal->copy()->locale('id')->translatedFormat('l'))[1], 0, 5) }}</dd></div>
                <div class="detail-item"><dt>Status guru</dt><dd>{{ $jurnal->status_guru }}</dd></div>
                <div class="detail-item"><dt>Kelas</dt><dd>{{ $jurnal->kelas->nama_kelas }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Tujuan pembelajaran</dt><dd>{{ $jurnal->tujuan_pembelajaran ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Kegiatan</dt><dd>{{ $jurnal->kegiatan ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Tugas</dt><dd>{{ $jurnal->tugas ?: '-' }}</dd></div>
                <div class="detail-item" style="grid-column:1/-1"><dt>Catatan</dt><dd>{{ $jurnal->catatan ?: '-' }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="panel" style="margin-top:24px">
        <div class="panel-head"><h2>Absensi siswa</h2><span class="eyebrow">{{ $jurnal->absensis->count() }} siswa</span></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead>
            <tbody>
                @forelse ($jurnal->absensis as $absensi)
                    <tr>
                        <td>{{ $absensi->siswa->nama_siswa }}</td>
                        <td><span class="status {{ $absensi->status === 'D' ? 'approved' : '' }}">{{ ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispen'][$absensi->status] ?? $absensi->status }}</span></td>
                        <td>{{ $absensi->catatan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Belum ada absensi siswa.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </section>

    @if (auth()->user()->role === 'sekretaris' && $jurnal->status_verifikasi === 'Menunggu')
        <section class="panel form-panel">
            <div class="panel-head"><h2>Verifikasi jurnal</h2><span class="eyebrow">Periksa kelengkapan jurnal</span></div>
            <div class="panel-body">
                <form action="{{ route('jurnal.verify', $jurnal) }}" method="POST">
                    @csrf
                    <div class="form-grid">
                        <div class="field"><label for="status">Keputusan</label><select id="status" name="status" required><option value="Disetujui">Disetujui</option><option value="Ditolak">Ditolak</option></select></div>
                        <div class="field full"><label for="catatan">Catatan</label><textarea id="catatan" name="catatan" rows="3"></textarea></div>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit">Simpan verifikasi</button></div>
                </form>
            </div>
        </section>
    @endif
@endsection
