@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
    <div class="page-head"><div><h1>Pendaftaran</h1><p>Kelola permintaan akun baru.</p></div></div>
    @if (session('success'))<div class="success-message">{{ session('success') }}</div>@endif
    <div class="quick-grid">
        @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $status => $label)
            <section class="stat-card"><span>{{ $label }}</span><strong>{{ $counts[$status] ?? 0 }}</strong></section>
        @endforeach
    </div>
    <section class="panel"><div class="panel-body"><form method="GET"><div class="form-grid"><div class="field"><label for="q">Cari nama/email</label><input id="q" name="q" value="{{ request('q') }}"></div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Semua</option>@foreach (['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div></div><button class="btn" type="submit">Terapkan filter</button></form></div></section>
    <section class="panel"><div class="panel-body table-wrap"><table><thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Role</th><th>Tanggal daftar</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse ($registrations as $registration)<tr><td>{{ $registrations->firstItem() + $loop->index }}</td><td>{{ $registration->name }}</td><td>{{ $registration->email }}</td><td>{{ ucfirst($registration->role) }}</td><td>{{ $registration->created_at->format('d/m/Y H:i') }}</td><td><span class="badge badge-{{ $registration->status }}">{{ ucfirst($registration->status) }}</span></td><td><a href="{{ route('admin.registrations.show', $registration) }}">Detail</a></td></tr>@empty<tr><td colspan="7">Belum ada pendaftaran.</td></tr>@endforelse</tbody></table></div><div class="panel-body">{{ $registrations->links() }}</div></section>
@endsection
