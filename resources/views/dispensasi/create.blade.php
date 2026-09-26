@extends('layouts.app')

@section('title', 'Ajukan Dispensasi')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ ($uploadMode ?? false) ? 'Upload surat izin siswa' : (auth()->user()->isPiketHariIni() ? 'Buat pernyataan dispensasi' : 'Ajukan dispensasi') }}</h1>
            <p>{{ ($uploadMode ?? false) ? 'Pilih siswa dan unggah surat izin dari orang tua.' : 'Lengkapi detail kegiatan dan bukti agar pengajuan dapat diverifikasi.' }}</p>
        </div><a
            class="btn btn-muted"
            href="{{ route('dispensasi.index') }}"
        >Kembali</a>
    </div>
    <section class="panel form-panel">
        <div class="panel-head">
            <h2>Detail pengajuan</h2><span class="eyebrow">Semua field bertanda wajib diisi</span>
        </div>
        <div class="panel-body">
            @if ($errors->has('siswa_ids'))
                <div class="alert error" role="alert">{{ $errors->first('siswa_ids') }}</div>
            @endif
            <form
                action="{{ route('dispensasi.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @if (auth()->user()->isPiketHariIni())
                    <div class="field">
                        <label for="student-picker">Pilih siswa</label><select id="student-picker"></select>
                        <button type="button" class="btn btn-muted" id="add-student">+ Tambahkan siswa</button>
                        <p>Siswa meminta dispensasi kepada piket. Tambahkan siswa satu per satu, lalu kirim pernyataan untuk diverifikasi admin.</p>
                        <ul id="selected-students"></ul><p id="student-count" aria-live="polite"></p>
                        @error('siswa_ids')<small class="error">{{ $message }}</small>@enderror
                    </div>
                @endif
                <div class="form-grid">
                    <div class="field"><label for="tanggal">Tanggal dispensasi</label><input
                            id="tanggal"
                            type="date"
                            name="tanggal"
                            value="{{ today()->toDateString() }}"
                            class="field-readonly"
                            readonly
                        ></div>
                    <div class="field"><label for="jam_mulai_id">Jam mulai</label><select
                            id="jam_mulai_id"
                            name="jam_mulai_id"
                            required
                        >
                            <option value="">Pilih jam mulai</option>
                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    data-jam-ke="{{ $jam->jam_ke }}"
                                    data-start-time="{{ $jam->timesForDay($hariIni)[0] }}"
                                    data-end-time="{{ $jam->timesForDay($hariIni)[1] }}"
                                    data-unavailable="{{ in_array($jam->id, $jamTidakTersediaIds, true) ? 'true' : 'false' }}"
                                    @disabled(in_array($jam->id, $jamTidakTersediaIds, true))
                                    @selected(old('jam_mulai_id') == $jam->id)
                                >
                                    Jam {{ $jam->jam_ke }} ({{ $jam->timesForDay($hariIni)[0] }} -
                                    {{ $jam->timesForDay($hariIni)[1] }})
                                </option>
                            @endforeach
                        </select>@error('jam_mulai_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="jam_selesai_id">Jam selesai</label><select
                            id="jam_selesai_id"
                            name="jam_selesai_id"
                            required
                        >
                            <option value="">Pilih jam selesai</option>
                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    data-jam-ke="{{ $jam->jam_ke }}"
                                    data-start-time="{{ $jam->timesForDay($hariIni)[0] }}"
                                    data-end-time="{{ $jam->timesForDay($hariIni)[1] }}"
                                    data-unavailable="{{ in_array($jam->id, $jamTidakTersediaIds, true) ? 'true' : 'false' }}"
                                    @disabled(in_array($jam->id, $jamTidakTersediaIds, true))
                                    @selected(old('jam_selesai_id') == $jam->id)
                                >
                                    Jam {{ $jam->jam_ke }} ({{ $jam->timesForDay($hariIni)[0] }} -
                                    {{ $jam->timesForDay($hariIni)[1] }})
                                </option>
                            @endforeach
                        </select>@error('jam_selesai_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field full"><label for="alasan">Alasan atau kegiatan</label>
                        <textarea
                            id="alasan"
                            name="alasan"
                            rows="5"
                            required
                        >{{ old('alasan') }}</textarea>
                    </div>
                    @unless (auth()->user()->isPiketHariIni())
                    <div class="field full"><label for="bukti">Bukti foto <span class="field-help">(JPG, PNG, atau WEBP, maksimal 5 MB)</span></label><input
                            id="bukti"
                            type="file"
                            name="bukti"
                            accept="image/jpeg,image/png,image/webp"
                        >@error('bukti')<small class="error">{{ $message }}</small>@enderror</div>
                    @endunless
                    @if (auth()->user()->isPiketHariIni())
                        <div class="field"><label for="surat_izin">Foto surat izin dari orang tua <span class="field-help">(JPG, PNG, atau WEBP, maksimal 5 MB)</span></label><input id="surat_izin" type="file" name="surat_izin" accept="image/jpeg,image/png,image/webp">@error('surat_izin')<small class="error">{{ $message }}</small>@enderror</div>
                    @endif
                </div>
                <div class="form-actions"><a
                        class="btn btn-muted"
                        href="{{ route('dispensasi.index') }}"
                    >Batal</a><button
                        class="btn"
                        type="submit"
                    >Kirim pengajuan</button></div>
            </form>
        </div>
    </section>
    @if (auth()->user()->isPiketHariIni())
    <script>
    (() => {
        const students = {{ Illuminate\Support\Js::from($siswas) }};
        const selected = new Set({{ Illuminate\Support\Js::from(old('siswa_ids', [])) }}.map(String));
        const picker = document.getElementById('student-picker');
        const list = document.getElementById('selected-students');
        const label = student => `${student.nama_siswa} · ${student.nis} · ${student.kelas?.nama_kelas ?? ''}`;
        function render() {
            picker.replaceChildren(new Option('Pilih siswa', ''));
            students.filter(student => !selected.has(String(student.id)))
                .forEach(student => picker.add(new Option(label(student), student.id)));
            list.replaceChildren();
            students.filter(student => selected.has(String(student.id))).forEach(student => {
                const row = document.createElement('li');
                const text = document.createElement('span'); text.textContent = label(student) + ' ';
                const input = document.createElement('input'); input.type = 'hidden'; input.name = 'siswa_ids[]'; input.value = student.id;
                const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn btn-muted'; remove.textContent = 'Hapus'; remove.setAttribute('aria-label', 'Hapus ' + student.nama_siswa);
                remove.addEventListener('click', () => { selected.delete(String(student.id)); render(); });
                row.append(text, input, remove); list.append(row);
            });
            document.getElementById('student-count').textContent = selected.size + ' siswa ditambahkan';
        }
        document.getElementById('add-student').addEventListener('click', () => { if (picker.value) { selected.add(picker.value); render(); } });
        render();
    })();
    </script>
    @endif
    <script>
    (() => {
        const start = document.getElementById('jam_mulai_id');
        const end = document.getElementById('jam_selesai_id');

        const updateEndTimes = () => {
            const selectedStart = start.options[start.selectedIndex];
            const selectedStartTime = selectedStart?.dataset.startTime;

            [...end.options].forEach((option) => {
                if (!option.value) {
                    return;
                }

                option.disabled = option.dataset.unavailable === 'true'
                    || (selectedStartTime && option.dataset.endTime <= selectedStartTime);
            });

            if (end.selectedOptions[0]?.disabled) {
                end.value = '';
            }
        };

        start.addEventListener('change', updateEndTimes);
        updateEndTimes();
    })();
    </script>
@endsection
