@php
    $isEdit = isset($jurnal);
@endphp

<form
    action="{{ $isEdit ? route('jurnal.update', $jurnal) : route('jurnal.store') }}"
    method="POST"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input
                id="tanggal"
                type="date"
                name="tanggal"
                value="{{ old('tanggal', isset($jurnal) ? $jurnal->tanggal->format('Y-m-d') : now()->format('Y-m-d')) }}"
                required
            >
        </div>

        <div class="field">
            <label for="status_guru">Status guru</label>
            <select id="status_guru" name="status_guru" required>
                @foreach (['Hadir', 'Izin', 'Sakit', 'Dinas', 'Tanpa Keterangan'] as $status)
                    <option
                        value="{{ $status }}"
                        @selected(old('status_guru', $jurnal->status_guru ?? 'Hadir') === $status)
                    >
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="kelas_id">Kelas</label>
            <select id="kelas_id" name="kelas_id" required>
                <option value="">Pilih kelas</option>
                @foreach ($kelas as $item)
                    <option
                        value="{{ $item->id }}"
                        @selected((string) old('kelas_id', $jurnal->kelas_id ?? '') === (string) $item->id)
                    >
                        {{ $item->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="mapel_id">Mata pelajaran</label>
            <select id="mapel_id" name="mapel_id" required>
                <option value="">Pilih mata pelajaran</option>
                @foreach ($mapels as $mapel)
                    <option
                        value="{{ $mapel->id }}"
                        @selected((string) old('mapel_id', $jurnal->mapel_id ?? '') === (string) $mapel->id)
                    >
                        {{ $mapel->nama_mapel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="jam_mulai_id">Jam mulai</label>
            <select id="jam_mulai_id" name="jam_mulai_id" required>
                <option value="">Pilih jam mulai</option>
                @foreach ($jamPelajarans as $jam)
                    <option
                        value="{{ $jam->id }}"
                        @selected((string) old('jam_mulai_id', $jurnal->jam_mulai_id ?? '') === (string) $jam->id)
                    >
                        Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="jam_selesai_id">Jam selesai</label>
            <select id="jam_selesai_id" name="jam_selesai_id" required>
                <option value="">Pilih jam selesai</option>
                @foreach ($jamPelajarans as $jam)
                    <option
                        value="{{ $jam->id }}"
                        @selected((string) old('jam_selesai_id', $jurnal->jam_selesai_id ?? '') === (string) $jam->id)
                    >
                        Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field full">
            <label for="materi">Materi</label>
            <input
                id="materi"
                type="text"
                name="materi"
                value="{{ old('materi', $jurnal->materi ?? '') }}"
                maxlength="200"
                required
            >
        </div>

        <div class="field full">
            <label for="tujuan_pembelajaran">Tujuan pembelajaran</label>
            <textarea id="tujuan_pembelajaran" name="tujuan_pembelajaran" rows="3">{{ old('tujuan_pembelajaran', $jurnal->tujuan_pembelajaran ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="kegiatan">Kegiatan pembelajaran</label>
            <textarea id="kegiatan" name="kegiatan" rows="4">{{ old('kegiatan', $jurnal->kegiatan ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="tugas">Tugas</label>
            <textarea id="tugas" name="tugas" rows="3">{{ old('tugas', $jurnal->tugas ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="catatan">Catatan</label>
            <textarea id="catatan" name="catatan" rows="3">{{ old('catatan', $jurnal->catatan ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        <a
            class="btn btn-muted"
            href="{{ $isEdit ? route('jurnal.show', $jurnal) : route('jurnal.index') }}"
        >Batal</a>
        <button class="btn" type="submit">
            {{ $isEdit ? 'Simpan perubahan' : 'Simpan jurnal' }}
        </button>
    </div>
</form>
