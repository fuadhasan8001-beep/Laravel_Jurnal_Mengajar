@extends('layouts.app')

@section('title', 'Detail Dispensasi')

@section('content')
    <div class="page-head"><div><h1>Detail dispensasi</h1><p>Periksa informasi pengajuan dan jejak verifikasinya.</p></div><a class="btn btn-muted" href="{{ route('dispensasi.index') }}">Kembali ke riwayat</a></div>
    <section class="panel" style="margin-bottom:18px"><div class="panel-head"><h2>Informasi pengajuan</h2><span class="status {{ $dispensasi->status_akhir === 'Disetujui' ? 'approved' : ($dispensasi->status_akhir === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $dispensasi->status_akhir }}</span></div><div class="panel-body"><dl class="detail-grid"><div class="detail-item"><dt>Siswa</dt><dd>{{ $dispensasi->siswa->nama_siswa }} ({{ $dispensasi->siswa->nis }})</dd></div><div class="detail-item"><dt>Tanggal</dt><dd>{{ $dispensasi->tanggal->format('d M Y') }}</dd></div><div class="detail-item"><dt>Waktu</dt><dd>{{ $dispensasi->jamMulai->jam_mulai }} - {{ $dispensasi->jamSelesai->jam_selesai }}</dd></div><div class="detail-item"><dt>Bukti</dt><dd>
            @if ($dispensasi->bukti)
                <a href="{{ route('dispensasi.evidence', $dispensasi) }}">Unduh bukti ↗</a>
            @else
                Tidak ada bukti
            @endif
        </dd></div><div class="detail-item" style="grid-column:1/-1"><dt>Alasan/kegiatan</dt><dd>{{ $dispensasi->alasan }}</dd></div><div class="detail-item"><dt>Verifikasi piket</dt><dd>{{ $dispensasi->status_piket }}{{ $dispensasi->piket ? ' · ' . $dispensasi->piket->name : '' }}</dd></div><div class="detail-item"><dt>Verifikasi admin</dt><dd>{{ $dispensasi->status_admin }}{{ $dispensasi->admin ? ' · ' . $dispensasi->admin->name : '' }}</dd></div>
        @if ($dispensasi->catatan_verifikasi)
            <div class="detail-item" style="grid-column:1/-1"><dt>Catatan verifikasi</dt><dd>{{ $dispensasi->catatan_verifikasi }}</dd></div>
        @endif
    </dl></div></section>

    @if (in_array(auth()->user()->role, ['piket', 'admin'], true) && $dispensasi->status_akhir === 'Menunggu')
        <section class="panel form-panel"><div class="panel-head"><h2>Ambil keputusan</h2><span class="eyebrow">Tahap {{ $dispensasi->status_piket === 'Disetujui' ? 'kedua' : 'pertama' }}</span></div><div class="panel-body"><form action="{{ route('dispensasi.verify', $dispensasi) }}" method="POST">
            @csrf
            <div class="form-grid"><div class="field"><label for="status">Keputusan</label><select id="status" name="status" required>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select></div><div class="field full"><label for="catatan_verifikasi">Catatan</label><textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3"></textarea></div></div><div class="form-actions"><button class="btn" type="submit">Simpan verifikasi</button></div></form></div></section>
    @endif
@endsection