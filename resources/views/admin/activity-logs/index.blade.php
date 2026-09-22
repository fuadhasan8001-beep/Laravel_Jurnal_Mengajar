@extends('layouts.app')

@section('title', 'Riwayat Aktivitas')

@section('content')
    <div class="page-head">
        <div>
            <h1>Riwayat aktivitas</h1>
            <p>Catatan perubahan penting pada data guru dan operasional akun.</p>
        </div>
    </div>

    <section class="panel">
        <div class="panel-head">
            <h2>Log sistem</h2>
            <span class="eyebrow">{{ $logs->total() }} catatan</span>
        </div>
        <div class="panel-body table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Belum ada riwayat aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-body">
            {{ $logs->links() }}
        </div>
    </section>
@endsection
