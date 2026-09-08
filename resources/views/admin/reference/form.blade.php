@extends('layouts.app')
@section('title', $title)
@section('content')
    @php($isEdit = isset($item))
    <div class="page-head"><div><h1>{{ $title }}</h1><p>Lengkapi data dengan benar.</p></div></div>
    <section class="panel form-panel"><div class="panel-head"><h2>Form data</h2></div><div class="panel-body">
        <form action="{{ $isEdit ? route('admin.' . $type . '.update', $item) : route('admin.' . $type . '.store') }}" method="POST">
            @csrf
            @if ($isEdit) @method('PUT') @endif
            <div class="form-grid">
                @if ($type === 'kelas')
                    <div class="field"><label for="nama_kelas">Nama kelas</label><input id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas', $item->nama_kelas ?? '') }}" required></div>
                    <div class="field"><label for="tingkat">Tingkat</label><input id="tingkat" name="tingkat" value="{{ old('tingkat', $item->tingkat ?? '') }}" required></div>
                @elseif ($type === 'mapel')
                    <div class="field"><label for="kode_mapel">Kode mapel</label><input id="kode_mapel" name="kode_mapel" value="{{ old('kode_mapel', $item->kode_mapel ?? '') }}" required></div>
                    <div class="field"><label for="nama_mapel">Nama mapel</label><input id="nama_mapel" name="nama_mapel" value="{{ old('nama_mapel', $item->nama_mapel ?? '') }}" required></div>
                    <div class="field full"><label for="keterangan">Keterangan</label><textarea id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $item->keterangan ?? '') }}</textarea></div>
                @else
                    <div class="field"><label for="jam_ke">Nomor jam</label><input id="jam_ke" type="number" min="1" name="jam_ke" value="{{ old('jam_ke', $item->jam_ke ?? '') }}" required></div>
                    <div class="field"><label for="jam_mulai">Jam mulai</label><input id="jam_mulai" type="time" name="jam_mulai" value="{{ old('jam_mulai', $item->jam_mulai ?? '') }}" required></div>
                    <div class="field"><label for="jam_selesai">Jam selesai</label><input id="jam_selesai" type="time" name="jam_selesai" value="{{ old('jam_selesai', $item->jam_selesai ?? '') }}" required></div>
                    <div class="field"><label for="is_active">Status</label><select id="is_active" name="is_active" required><option value="1" @selected(old('is_active', $item->is_active ?? true) === true || old('is_active', $item->is_active ?? true) === '1')>Aktif</option><option value="0" @selected(old('is_active', $item->is_active ?? true) === false || old('is_active', $item->is_active ?? true) === '0')>Nonaktif</option></select></div>
                @endif
            </div>
            <div class="form-actions"><a class="btn btn-muted" href="{{ route('admin.' . $type . '.index') }}">Batal</a><button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah data' }}</button></div>
        </form>
    </div></section>
@endsection
