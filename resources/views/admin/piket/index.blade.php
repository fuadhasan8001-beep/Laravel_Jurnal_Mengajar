@extends('layouts.app')

@section('title', 'Jadwal Piket Guru')

@section('content')
    <div class="page-head">
        <div><h1>Jadwal piket guru</h1><p>Atur guru yang bertugas piket berdasarkan jadwal harian.</p></div>
    </div>
    <section class="panel form-panel">
        <div class="panel-head"><h2>Tambah jadwal</h2></div>
        <div class="panel-body">
            <form action="{{ route('admin.piket.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id" required>
                        <option value="">Pilih guru</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}" @selected(old('guru_id') == $guru->id)>{{ $guru->nama_guru }}</option>
                        @endforeach
                    </select>@error('guru_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="tanggal">Tanggal piket</label><input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" min="{{ today()->toDateString() }}" required>@error('tanggal')<small class="error">{{ $message }}</small>@enderror</div>
                </div>
                <div class="form-actions"><button class="btn" type="submit">Simpan jadwal</button></div>
            </form>
        </div>
    </section>
    <section class="panel panel-spaced">
        <div class="panel-head"><h2>Jadwal mendatang</h2></div>
        <div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Guru piket</th><th></th></tr></thead><tbody>
            @forelse ($jadwals as $jadwal)
                <tr><td>{{ $jadwal->tanggal->format('d/m/Y') }}</td><td>{{ $jadwal->guru->nama_guru }}</td><td><form action="{{ route('admin.piket.destroy', $jadwal) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-muted" type="submit">Hapus</button></form></td></tr>
            @empty
                <tr><td colspan="3">Belum ada jadwal piket yang akan datang.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>
@endsection
