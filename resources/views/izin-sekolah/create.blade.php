@extends('layouts.app')

@section('title', 'Catat Izin Sekolah')

@section('content')
    <div class="page-head">
        <div>
            <h1>Catat Izin atau Sakit Seharian</h1>
            <p>Catat siswa yang tidak masuk sekolah dan unggah surat dari orang tua.</p>
        </div>
        <a class="btn btn-muted" href="{{ route('piket.izin-sekolah.index') }}">Riwayat izin</a>
    </div>

    <section class="panel form-panel">
        <div class="panel-head"><h2>Data izin siswa</h2></div>
        <div class="panel-body">
            <form action="{{ route('piket.izin-sekolah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="status">Status siswa</label>
                        <select id="status" name="status" required>
                            <option value="I" @selected(old('status', 'I') === 'I')>Izin</option>
                            <option value="S" @selected(old('status') === 'S')>Sakit</option>
                        </select>
                        @error('status')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="tanggal">Tanggal izin</label>
                        <input id="tanggal" type="date" value="{{ today()->toDateString() }}" readonly>
                    </div>
                    <div class="field full">
                        <label for="student-search">Cari siswa</label>
                            <input id="student-search" type="search" placeholder="Ketik nama, NIS, atau kelas" autocomplete="off">
                            <p class="field-help">Ketik untuk menampilkan siswa yang sesuai.</p>
                            <div id="student-results" style="display:none;max-height:280px;overflow:auto;padding:.5rem;border:1px solid var(--line);border-radius:8px;"></div>
                            <div id="selected-students" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;"></div>
                            <div id="student-inputs">
                            @foreach ($siswas as $siswa)
                                    @if (collect(old('siswa_ids', []))->contains((string) $siswa->id))
                                        <input type="hidden" name="siswa_ids[]" value="{{ $siswa->id }}" data-student-input data-student-name="{{ $siswa->nama_siswa }} · {{ $siswa->nis }} · {{ $siswa->kelas->nama_kelas }}">
                                    @endif
                            @endforeach
                            </div>
                        @error('siswa_ids')<small class="error">{{ $message }}</small>@enderror
                        @error('siswa_ids.*')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field full">
                        <label for="alasan">Catatan (opsional)</label>
                        <textarea id="alasan" name="alasan" rows="3" maxlength="5000">{{ old('alasan') }}</textarea>
                        @error('alasan')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field full">
                        <label for="surat_izin">Foto surat izin orang tua (JPG, PNG, WEBP; maksimal 5 MB)</label>
                        <input id="surat_izin" type="file" name="surat_izin" accept="image/jpeg,image/png,image/webp" required>
                        @error('surat_izin')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('piket.izin-sekolah.index') }}">Batal</a>
                    <button class="btn" type="button" id="review-izin">Tinjau sebelum simpan</button>
                </div>
            </form>
        </div>
    </section>
    <div id="izin-confirm" hidden style="position:fixed;inset:0;background:rgba(11,18,32,.62);z-index:1000;align-items:center;justify-content:center;padding:1rem;">
        <div style="width:min(680px,100%);background:#fff;border-radius:18px;padding:1.5rem;max-height:80vh;overflow:auto;">
            <h2>Periksa data sebelum simpan</h2>
            <dl class="detail-grid">
                <div class="detail-item"><dt>Status</dt><dd id="confirm-izin-status"></dd></div>
                <div class="detail-item"><dt>Tanggal</dt><dd>{{ today()->format('d M Y') }}</dd></div>
                <div class="detail-item detail-item-full"><dt>Siswa</dt><dd id="confirm-izin-students"></dd></div>
                <div class="detail-item detail-item-full"><dt>Catatan</dt><dd id="confirm-izin-note"></dd></div>
                <div class="detail-item detail-item-full"><dt>Surat</dt><dd id="confirm-izin-file"></dd></div>
            </dl>
            <div class="form-actions"><button type="button" class="btn btn-muted" id="cancel-izin">Kembali</button><button type="button" class="btn" id="submit-izin">Simpan sekarang</button></div>
        </div>
    </div>
    <script>
        const izinForm = document.querySelector('form[action="{{ route('piket.izin-sekolah.store') }}"]');
        const students = @json($siswas->map(fn ($siswa) => ['id' => $siswa->id, 'label' => $siswa->nama_siswa.' · '.$siswa->nis.' · '.$siswa->kelas->nama_kelas])->values());
        const studentSearch = document.getElementById('student-search');
        const studentResults = document.getElementById('student-results');
        const selectedStudents = document.getElementById('selected-students');
        const studentInputs = document.getElementById('student-inputs');
        const selectedIds = () => new Set([...studentInputs.querySelectorAll('[data-student-input]')].map((input) => String(input.value)));
        const renderSelected = () => {
            selectedStudents.replaceChildren();
            studentInputs.querySelectorAll('[data-student-input]').forEach((input) => {
                const chip = document.createElement('span');
                chip.className = 'status approved';
                chip.textContent = input.dataset.studentName;
                const remove = document.createElement('button');
                remove.type = 'button'; remove.textContent = ' x'; remove.title = 'Hapus siswa';
                remove.addEventListener('click', () => { input.remove(); renderSelected(); renderResults(); });
                chip.append(remove); selectedStudents.append(chip);
            });
        };
        const renderResults = () => {
            const query = studentSearch.value.trim().toLocaleLowerCase('id');
            const selected = selectedIds();
            const matches = students.filter((student) => !selected.has(String(student.id)) && student.label.toLocaleLowerCase('id').includes(query)).slice(0, 20);
            studentResults.replaceChildren();
            if (!query || !matches.length) { studentResults.style.display = 'none'; return; }
            matches.forEach((student) => {
                const button = document.createElement('button');
                button.type = 'button'; button.className = 'btn btn-muted'; button.style.display = 'block'; button.style.width = '100%'; button.style.textAlign = 'left'; button.style.margin = '.25rem 0'; button.textContent = student.label;
                button.addEventListener('click', () => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'siswa_ids[]'; input.value = student.id; input.dataset.studentInput = ''; input.dataset.studentName = student.label;
                    studentInputs.append(input); studentSearch.value = ''; renderSelected(); renderResults();
                });
                studentResults.append(button);
            });
            studentResults.style.display = 'block';
        };
        studentSearch.addEventListener('input', renderResults);
        renderSelected();
        const izinConfirm = document.getElementById('izin-confirm');
        const selectedStudentNames = () => [...studentInputs.querySelectorAll('[data-student-input]')].map((input) => input.dataset.studentName);
        document.getElementById('review-izin').addEventListener('click', () => {
            const file = document.getElementById('surat_izin').files[0];
            document.getElementById('confirm-izin-status').textContent = document.getElementById('status').selectedOptions[0].textContent;
            document.getElementById('confirm-izin-students').textContent = selectedStudentNames().join(', ') || 'Belum ada siswa';
            document.getElementById('confirm-izin-note').textContent = document.getElementById('alasan').value.trim() || '-';
            document.getElementById('confirm-izin-file').textContent = file?.name || 'Belum dipilih';
            izinConfirm.hidden = false;
            izinConfirm.style.display = 'flex';
        });
        document.getElementById('cancel-izin').addEventListener('click', () => { izinConfirm.hidden = true; izinConfirm.style.display = 'none'; });
        document.getElementById('submit-izin').addEventListener('click', () => izinForm.submit());
    </script>
@endsection