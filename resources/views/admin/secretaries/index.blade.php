@extends('layouts.app')

@section('title', 'Pengurus Kelas')

@section('content')
    <div class="page-head">
        <div>
            <h1>Pengurus kelas</h1>
            <p>Kelola akun yang mengisi absensi dan memverifikasi jurnal berdasarkan kelas.</p>
        </div>
    </div>

    @if (session('secretary_credentials'))
        <section class="panel credential-panel" role="status">
            <div class="panel-head">
                <h2>Kredensial siap dibagikan</h2>
                <span class="eyebrow">{{ session('secretary_credentials.class') }}</span>
            </div>
            <div class="panel-body credential-grid">
                <div><span class="eyebrow">Username</span><code>{{ session('secretary_credentials.username') }}</code></div>
                <div><span class="eyebrow">Password</span><code>{{ session('secretary_credentials.password') }}</code></div>
            </div>
            <div class="panel-body credential-warning">Password hanya ditampilkan setelah dibuat atau di-reset. Bagikan ke pengurus kelas melalui kanal yang aman.</div>
        </section>
    @endif

    <section class="panel">
        <div class="panel-body">
            <form method="GET" class="filter-form">
                <div class="field"><label for="q">Cari kelas</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Nama kelas"></div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('admin.secretaries.index') }}">Reset</a><button class="btn" type="submit">Cari</button></div>
            </form>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Daftar akun kelas</h2><span class="eyebrow">{{ $classes->total() }} kelas terjadwal</span></div>
        @if ($classes->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Kelas</th><th>Jadwal</th><th>Username</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($classes as $class)
                            @php($secretary = $class->sekretarisUsers->first())
                            <tr>
                                <td><strong>{{ $class->nama_kelas }}</strong><br><span class="eyebrow">Tingkat {{ $class->tingkat }}</span></td>
                                <td>{{ $class->jadwals_count }}</td>
                                <td>{{ $secretary?->username ?? 'Belum dibuat' }}</td>
                                <td><span class="status {{ $secretary?->is_active ? 'approved' : 'pending' }}">{{ $secretary?->is_active ? 'Aktif' : 'Belum aktif' }}</span></td>
                                <td>
                                    <form action="{{ route('admin.secretaries.reset', $class) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-muted" type="submit">{{ $secretary ? 'Reset password' : 'Buat akun' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel-body">{{ $classes->links() }}</div>
        @else
            <div class="empty">Belum ada kelas yang memiliki jadwal.</div>
        @endif
    </section>
@endsection
