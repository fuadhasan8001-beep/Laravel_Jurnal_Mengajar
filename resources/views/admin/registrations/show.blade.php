@extends('layouts.app')
@section('title', 'Detail Pendaftaran')
@section('content')
    <div class="page-head"><div><h1>Detail pendaftar</h1><p>Periksa data sebelum mengambil keputusan.</p></div><a class="btn btn-muted" href="{{ route('admin.registrations.index') }}">Kembali</a></div>
    <section class="panel"><div class="panel-body detail-grid"><div><span class="eyebrow">Nama lengkap</span><strong>{{ $registration->name }}</strong></div><div><span class="eyebrow">Email</span><strong>{{ $registration->email }}</strong></div><div><span class="eyebrow">Role</span><strong>{{ ucfirst($registration->role) }}</strong></div><div><span class="eyebrow">Tanggal pendaftaran</span><strong>{{ $registration->created_at->format('d/m/Y H:i') }}</strong></div><div><span class="eyebrow">Status</span><strong>{{ ucfirst($registration->status) }}</strong></div>@if ($registration->rejection_reason)<div><span class="eyebrow">Alasan penolakan</span><strong>{{ $registration->rejection_reason }}</strong></div>@endif</div></section>
    @if ($registration->status === 'pending')
        <section class="panel"><div class="panel-body"><div class="form-actions"><form action="{{ route('admin.registrations.approve', $registration) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran ini?')">@csrf<button class="btn" type="submit">Terima</button></form></div><form action="{{ route('admin.registrations.reject', $registration) }}" method="POST">@csrf<div class="field"><label for="rejection_reason">Alasan penolakan</label><textarea id="rejection_reason" name="rejection_reason" required></textarea></div><button class="btn btn-danger" type="submit">Tolak</button></form></div></section>
    @endif
@endsection
