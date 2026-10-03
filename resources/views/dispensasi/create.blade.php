@extends('layouts.app')

@section('title', 'Ajukan Dispensasi')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ auth()->user()->isPiketHariIni() ? 'Buat pernyataan dispensasi' : 'Ajukan dispensasi' }}</h1>
            <p>Lengkapi detail kegiatan dan alasan agar pengajuan dapat diverifikasi.</p>
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
                </div>
                <div class="form-actions"><a
                        class="btn btn-muted"
                        href="{{ route('dispensasi.index') }}"
                    >Batal</a><button
                        class="btn"
                        type="submit"
                    >Tinjau sebelum kirim</button></div>
            </form>
        </div>
    </section>
    <div id="dispensasi-confirm" hidden style="position:fixed;inset:0;background:rgba(11,18,32,.62);z-index:1000;align-items:center;justify-content:center;padding:1rem;">
        <div style="width:min(680px,100%);background:#fff;border-radius:18px;padding:1.5rem;max-height:80vh;overflow:auto;">
            <h2>Periksa pengajuan sebelum kirim</h2>
            <dl class="detail-grid">
                <div class="detail-item"><dt>Tanggal</dt><dd>{{ today()->format('d M Y') }}</dd></div>
                <div class="detail-item"><dt>Waktu</dt><dd id="confirm-dispensasi-time"></dd></div>
                <div class="detail-item detail-item-full"><dt>Siswa</dt><dd id="confirm-dispensasi-students"></dd></div>
                <div class="detail-item detail-item-full"><dt>Alasan/kegiatan</dt><dd id="confirm-dispensasi-reason"></dd></div>
            </dl>
            <div class="form-actions"><button type="button" class="btn btn-muted" id="cancel-dispensasi">Kembali</button><button type="button" class="btn" id="submit-dispensasi">Kirim sekarang</button></div>
        </div>
    </div>
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
        const dispensasiForm = document.querySelector('form[action="{{ route('dispensasi.store') }}"]');
        const dispensasiConfirm = document.getElementById('dispensasi-confirm');
        const submitButton = dispensasiForm.querySelector('button[type="submit"]');
        submitButton.type = 'button';
        submitButton.addEventListener('click', () => {
            const startField = document.getElementById('jam_mulai_id');
            const endField = document.getElementById('jam_selesai_id');
            const students = [...dispensasiForm.querySelectorAll('input[name="siswa_ids[]"]')].map((input) => input.previousElementSibling?.textContent?.trim() || input.value);
            document.getElementById('confirm-dispensasi-time').textContent = `${startField?.selectedOptions[0]?.textContent.trim() || '-'} sampai ${endField?.selectedOptions[0]?.textContent.trim() || '-'}`;
            document.getElementById('confirm-dispensasi-students').textContent = students.join(', ') || 'Siswa yang sedang login';
            document.getElementById('confirm-dispensasi-reason').textContent = document.getElementById('alasan').value.trim() || '-';
            dispensasiConfirm.hidden = false;
            dispensasiConfirm.style.display = 'flex';
        });
        document.getElementById('cancel-dispensasi').addEventListener('click', () => { dispensasiConfirm.hidden = true; dispensasiConfirm.style.display = 'none'; });
        document.getElementById('submit-dispensasi').addEventListener('click', () => dispensasiForm.submit());
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
