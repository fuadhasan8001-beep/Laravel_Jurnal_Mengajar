@php
    $isEdit = isset($jadwal);
@endphp

<form action="{{ $isEdit ? route('jadwal.update', $jadwal) : route('jadwal.store') }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="hari">Hari</label>
            <select id="hari" name="hari" required>
                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                    <option value="{{ $hari }}" @selected(old('hari', $jadwal->hari ?? '') === $hari)>{{ $hari }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="jam_pelajaran_id">Jam pelajaran</label>
            <select id="jam_pelajaran_id" name="jam_pelajaran_id" required>
                <option value="">Pilih jam</option>
                @foreach ($jamPelajarans as $jam)
                    <option value="{{ $jam->id }}" @selected((string) old('jam_pelajaran_id', $jadwal->jam_pelajaran_id ?? '') === (string) $jam->id)>
                        Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="guru_id">Guru</label>
            <select id="guru_id" name="guru_id" required>
                <option value="">Pilih guru</option>
                @foreach ($gurus as $guru)
                    <option value="{{ $guru->id }}" @selected((string) old('guru_id', $jadwal->guru_id ?? '') === (string) $guru->id)>{{ $guru->nama_guru }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="kelas_id">Kelas</label>
            <select id="kelas_id" name="kelas_id" required>
                <option value="">Pilih kelas</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" @selected((string) old('kelas_id', $jadwal->kelas_id ?? '') === (string) $item->id)>{{ $item->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="mapel_id">Mata pelajaran</label>
            <select id="mapel_id" name="mapel_id" required>
                <option value="">Pilih mata pelajaran</option>
                @foreach ($mapels as $mapel)
                    <option value="{{ $mapel->id }}" @selected((string) old('mapel_id', $jadwal->mapel_id ?? '') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="is_active">Status</label>
            <select id="is_active" name="is_active" required>
                <option value="1" @selected((string) old('is_active', $jadwal->is_active ?? true) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', $jadwal->is_active ?? true) === '0')>Nonaktif</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-muted" href="{{ route('jadwal.index') }}">Batal</a>
        <button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Simpan jadwal' }}</button>
    </div>
</form>
