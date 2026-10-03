@extends('layouts.app')

@section('title', 'Riwayat Kalender')

@section('content')
<div class="page-head"><div><h1>Riwayat perubahan kalender</h1><p>Jejak pembuatan, perubahan, dan penghapusan kegiatan sekolah.</p></div><a class="btn btn-muted" href="{{ route('admin.calendar.index') }}">Kembali ke kalender</a></div>
<section class="panel"><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Admin</th><th>Aksi</th><th>Kegiatan</th><th>Tanggal</th><th>Perubahan</th></tr></thead><tbody>
    @forelse ($audits as $audit)
        @php($snapshot = $audit->event_snapshot ?? [])
        <tr><td>{{ $audit->created_at->format('d/m/Y H:i') }}</td><td>{{ $audit->actor?->name ?? 'Pengguna dihapus' }}</td><td>{{ $audit->action }}</td><td>{{ $snapshot['title'] ?? $audit->event?->title ?? 'Kegiatan dihapus' }}</td><td>{{ $snapshot['event_date'] ?? '-' }}</td><td><details><summary>Lihat detail</summary><pre>{{ json_encode($audit->changes ?? $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>
    @empty
        <tr><td colspan="6">Belum ada riwayat perubahan.</td></tr>
    @endforelse
</tbody></table></div><div class="panel-body">{{ $audits->links() }}</div></section>
@endsection
