@php($isEdit = isset($siswa))
<form action="{{ $isEdit ? route('admin.siswas.update', $siswa) : route('admin.siswas.store') }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <div class="form-grid">
        <div class="field"><label for="nama_siswa">Nama siswa</label><input id="nama_siswa" name="nama_siswa" value="{{ old('nama_siswa', $siswa->nama_siswa ?? '') }}" required></div>
        <div class="field"><label for="nis">NIS</label><input id="nis" name="nis" value="{{ old('nis', $siswa->nis ?? '') }}" required></div>
        <div class="field"><label for="jenis_kelamin">Jenis kelamin</label><select id="jenis_kelamin" name="jenis_kelamin" required><option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L')>Laki-laki</option><option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P')>Perempuan</option></select></div>
        <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id" required>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) old('kelas_id', $siswa->kelas_id ?? '') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
        <div class="field"><label for="email">Email akun</label><input id="email" type="email" name="email" value="{{ old('email', $siswa->user->email ?? '') }}" required></div>
        <div class="field"><label for="password">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label><input id="password" type="password" name="password" {{ $isEdit ? '' : 'required' }}></div>
        @if ($isEdit)<div class="field"><label for="is_active">Status akun</label><select id="is_active" name="is_active" required><option value="1" @selected(old('is_active', $siswa->user->is_active) === true || old('is_active', $siswa->user->is_active) === '1')>Aktif</option><option value="0" @selected(old('is_active', $siswa->user->is_active) === false || old('is_active', $siswa->user->is_active) === '0')>Nonaktif</option></select></div>@endif
    </div>
    <div class="form-actions"><a class="btn btn-muted" href="{{ route('admin.siswas.index') }}">Batal</a><button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah siswa' }}</button></div>
</form>
