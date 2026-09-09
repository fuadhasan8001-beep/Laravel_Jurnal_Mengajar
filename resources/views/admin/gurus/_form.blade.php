@php($isEdit = isset($guru))
<form action="{{ $isEdit ? route('admin.gurus.update', $guru) : route('admin.gurus.store') }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <div class="form-grid">
        <div class="field"><label for="nama_guru">Nama guru</label><input id="nama_guru" name="nama_guru" value="{{ old('nama_guru', $guru->nama_guru ?? '') }}" required></div>
        <div class="field"><label for="nip">NIP/NUPTK</label><input id="nip" name="nip" value="{{ old('nip', $guru->nip ?? '') }}" required></div>
        <div class="field"><label for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" value="{{ old('no_hp', $guru->no_hp ?? '') }}"></div>
        <div class="field"><label for="status_kepegawaian">Status kepegawaian</label><select id="status_kepegawaian" name="status_kepegawaian" required>@foreach (['PNS', 'PPPK', 'Honorer'] as $status)<option value="{{ $status }}" @selected(old('status_kepegawaian', $guru->status_kepegawaian ?? 'Honorer') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="field"><label for="email">Email akun</label><input id="email" type="email" name="email" value="{{ old('email', $guru->user->email ?? '') }}" required></div>
        <div class="field"><label for="password">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label><input id="password" type="password" name="password" {{ $isEdit ? '' : 'required' }}></div>
        @if ($isEdit)
            <div class="field"><label for="is_active">Status akun</label><select id="is_active" name="is_active" required><option value="1" @selected(old('is_active', $guru->user->is_active) === true || old('is_active', $guru->user->is_active) === '1')>Aktif</option><option value="0" @selected(old('is_active', $guru->user->is_active) === false || old('is_active', $guru->user->is_active) === '0')>Nonaktif</option></select></div>
        @endif
    </div>
    <div class="form-actions"><a class="btn btn-muted" href="{{ route('admin.gurus.index') }}">Batal</a><button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah guru' }}</button></div>
</form>
