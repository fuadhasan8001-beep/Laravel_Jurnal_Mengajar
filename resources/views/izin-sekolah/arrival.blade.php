@extends('layouts.app')

@section('title', 'Surat Izin Masuk')

@section('content')
    <div class="page-head">
        <div>
            <h1>Surat izin masuk</h1>
            <p>Catat siswa yang datang terlambat dan jam pelajaran saat tiba.</p>
        </div>
        <a class="btn btn-muted" href="{{ url('/piket') }}">Kembali ke menu piket</a>
    </div>

    @if (session('success'))<p class="status approved" role="status">{{ session('success') }}</p>@endif
    <section class="panel form-panel">
        <div class="panel-head"><h2>Data kedatangan siswa</h2></div>
        <div class="panel-body">
            <form method="POST" action="{{ route('piket.izin-masuk.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label for="siswa_id">Siswa</label>
                        <select id="siswa_id" name="siswa_id" required>
                            <option value="">Pilih siswa</option>
                            @foreach ($siswas as $siswa)
                                <option value="{{ $siswa->id }}" @selected(old('siswa_id') == $siswa->id)>{{ $siswa->nama_siswa }} · {{ $siswa->nis }} · {{ $siswa->kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @error('siswa_id')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="jam_masuk_ke">Masuk mulai jam ke-</label>
                        <select id="jam_masuk_ke" name="jam_masuk_ke" required>
                            <option value="">Pilih jam pelajaran</option>
                            @foreach ($jamPelajarans as $jam)
                                <option value="{{ $jam->jam_ke }}" @selected(old('jam_masuk_ke') == $jam->jam_ke)>Jam ke-{{ $jam->jam_ke }} ({{ substr($jam->jam_mulai, 0, 5) }})</option>
                            @endforeach
                        </select>
                        @error('jam_masuk_ke')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="waktu_masuk">Waktu dicatat</label>
                        <input id="waktu_masuk" value="{{ now()->format('H:i') }}" readonly>
                    </div>
                    <div class="field full">
                        <label for="alasan">Keterangan (opsional)</label>
                        <textarea id="alasan" name="alasan" rows="3" maxlength="1000">{{ old('alasan') }}</textarea>
                        @error('alasan')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn" type="submit">Buat surat dan perbarui absensi</button>
                </div>
            </form>
        </div>
    </section>
@endsection