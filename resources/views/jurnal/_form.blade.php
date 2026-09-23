@php
    $isEdit = isset($jurnal);
    $jurnal = $jurnal ?? null;
    $date = $jurnal?->tanggal ?? today();
    $hari = $date->copy()->locale('id')->translatedFormat('l');
    $startId = $jurnal?->jam_mulai_id ?? $activeSession['jam_mulai_id'];
    $endId = $jurnal?->jam_selesai_id ?? $activeSession['jam_selesai_id'];
    $start = $jamPelajarans->firstWhere('id', $startId) ?? $jurnal?->jamMulai;
    $end = $jamPelajarans->firstWhere('id', $endId) ?? $jurnal?->jamSelesai;
    $attendance = old('absensi', $jurnal?->absensis->keyBy('siswa_id')->toArray() ?? []);
@endphp
<form method="POST" action="{{ $isEdit ? route('jurnal.update', $jurnal) : route('jurnal.store') }}" id="journal-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @else
        <input type="hidden" name="jadwal_id" value="{{ $activeSession['id'] }}">
    @endif
    <div class="form-grid">
        <div class="field"><label for="guru_nama">Guru</label><input id="guru_nama" value="{{ auth()->user()->name }}" readonly></div>
        <div class="field"><label for="tanggal">Tanggal</label><input id="tanggal" value="{{ $date->translatedFormat('l, d F Y') }}" readonly></div>
        @if (! $isEdit && $sessions->where('active', true)->count() > 1)
            <div class="field full"><label for="jadwal_search">Cari dan pilih kelas</label>
                <input id="jadwal_search" type="search" placeholder="Cari kelas atau mata pelajaran" autocomplete="off">
                <select id="jadwal_picker" aria-label="Pilih jadwal mengajar">
                    @foreach ($sessions->where('active', true) as $availableSession)
                        <option value="{{ $availableSession['id'] }}" data-search="{{ strtolower($availableSession['kelas'].' '.$availableSession['mapel']) }}" @selected($availableSession['id'] === $activeSession['id'])>{{ $availableSession['kelas'] }} - {{ $availableSession['mapel'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="field"><label for="kelas_nama">Kelas</label><input id="kelas_nama" value="{{ $jurnal?->kelas->nama_kelas ?? $activeSession['kelas'] }}" readonly></div>
        <div class="field"><label for="mapel_nama">Mata pelajaran</label><input id="mapel_nama" value="{{ $jurnal?->mapel->nama_mapel ?? $activeSession['mapel'] }}" readonly></div>
        <div class="field"><label for="jam_mulai">Jam mulai</label><input id="jam_mulai" value="{{ substr($start->timesForDay($hari)[0], 0, 5) }} (jam ke-{{ $start->jam_ke }})" readonly></div>
        <div class="field"><label for="jam_selesai">Jam selesai</label><input id="jam_selesai" value="{{ substr($end->timesForDay($hari)[1], 0, 5) }} (jam ke-{{ $end->jam_ke }})" readonly></div>
        <div class="field"><label for="status_guru">Kehadiran guru</label><select id="status_guru" name="status_guru">
            <option value="">Default: Hadir</option>
            @foreach (['Hadir', 'Izin', 'Sakit'] as $status)
                <option @selected(old('status_guru', $jurnal?->status_guru) === $status)>{{ $status }}</option>
            @endforeach
        </select>@error('status_guru')<small class="error">{{ $message }}</small>@enderror</div>
    </div>
    @error('jadwal_id')<p class="error" role="alert">{{ $message }}</p>@enderror
    <details @if($isEdit || $errors->any()) open @endif>
        <summary>Detail pembelajaran dan absensi</summary>
        @foreach (['materi' => 'Materi pembelajaran', 'tujuan_pembelajaran' => 'Tujuan pembelajaran', 'kegiatan' => 'Kegiatan pembelajaran', 'tugas' => 'Tugas', 'catatan' => 'Catatan'] as $field => $label)
            <div class="field"><label for="{{ $field }}">{{ $label }}</label>
                @if ($field === 'materi')
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $jurnal?->$field) }}" maxlength="200">
                @else
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" maxlength="5000">{{ old($field, $jurnal?->$field) }}</textarea>
                @endif
                @error($field)<small class="error">{{ $message }}</small>@enderror
            </div>
        @endforeach
        <div class="journal-card-header"><h3>Absensi siswa</h3><button type="button" class="btn btn-muted" id="btn-hadir-semua">Tandai hadir semua</button></div>
        <div class="table-wrap"><table><thead><tr><th>No</th><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead><tbody>
            @forelse ($kelas->first()?->siswas ?? [] as $student)
                @php
                    $dispensed = $approvedDispensasis->contains(fn ($item) => $item->siswa_id === $student->id
                        && $item->jamMulai->jam_ke <= $end->jam_ke && $item->jamSelesai->jam_ke >= $start->jam_ke);
                    $saved = collect($attendance)->firstWhere('siswa_id', $student->id) ?? $attendance[$student->id] ?? [];
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}<input type="hidden" name="absensi[{{ $student->id }}][siswa_id]" value="{{ $student->id }}"></td>
                    <td>{{ $student->nama_siswa }} ({{ $student->nis }})</td>
                    <td>
                        @if ($dispensed)
                            <span class="status approved">Dispen</span><input type="hidden" name="absensi[{{ $student->id }}][status]" value="D">
                        @else
                            <select name="absensi[{{ $student->id }}][status]" class="attendance-status" aria-label="Status {{ $student->nama_siswa }}">
                                @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($saved['status'] ?? 'H') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </td>
                    <td><input name="absensi[{{ $student->id }}][catatan]" value="{{ $dispensed ? 'Dispensasi disetujui.' : ($saved['catatan'] ?? '') }}" maxlength="1000" aria-label="Catatan {{ $student->nama_siswa }}" @readonly($dispensed)></td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada siswa di kelas ini.</td></tr>
            @endforelse
        </tbody></table></div>
    </details>
    <section class="signature-card" aria-labelledby="signature-title">
        <div class="journal-card-header">
            <div>
                <h3 id="signature-title">Tanda tangan guru</h3>
                <p>Bubuhkan tanda tangan dengan mouse atau jari Anda.</p>
            </div>
            <button class="btn btn-muted" type="button" id="clear-signature">Bersihkan</button>
        </div>
        <div class="signature-space">
            @if ($isEdit && $jurnal->tanda_tangan)
                <div class="signature-info">
                    <strong>Tanda tangan tersimpan</strong>
                    <img class="saved-signature" src="{{ route('jurnal.signature', $jurnal) }}" alt="Tanda tangan {{ $jurnal->guru->nama_guru }}">
                </div>
            @endif
            <canvas id="signature-canvas" class="signature-canvas" width="900" height="250" aria-label="Area tanda tangan"></canvas>
            <input id="tanda_tangan" name="tanda_tangan" type="hidden">
            @error('tanda_tangan')<small class="error">{{ $message }}</small>@enderror
        </div>
    </section>

    <div class="form-actions"><a class="btn btn-muted" href="{{ route('jurnal.index') }}">Batal</a><button class="btn" type="button" id="confirm-save-trigger">{{ $isEdit ? 'Simpan perubahan' : 'Simpan jurnal' }}</button></div>
</form>

<div id="journal-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(11,18,32,.62); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div style="width:min(900px,100%); background:#fff; border-radius:18px; box-shadow:0 20px 50px rgba(0,0,0,.2); padding:1.5rem; max-height:80vh; overflow:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem;">
            <h3 style="margin:0;">Konfirmasi simpan jurnal</h3>
            <button type="button" class="btn btn-muted" data-close-confirmation>Keluar</button>
        </div>
        <div id="confirmation-summary" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:0.75rem 1rem; line-height:1.6;">
            <div><strong>Guru:</strong> <span id="confirm-guru">{{ auth()->user()->name }}</span></div>
            <div><strong>Kehadiran:</strong> <span id="confirm-status">{{ old('status_guru', $jurnal?->status_guru) ?: 'Hadir' }}</span></div>
            <div><strong>Kelas:</strong> <span id="confirm-kelas">{{ $jurnal?->kelas->nama_kelas ?? $activeSession['kelas'] }}</span></div>
            <div><strong>Mata pelajaran:</strong> <span id="confirm-mapel">{{ $jurnal?->mapel->nama_mapel ?? $activeSession['mapel'] }}</span></div>
            <div style="grid-column:1/-1;"><strong>Materi:</strong> <span id="confirm-materi">{{ old('materi', $jurnal?->materi) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Tujuan pembelajaran:</strong> <span id="confirm-tujuan">{{ old('tujuan_pembelajaran', $jurnal?->tujuan_pembelajaran) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Kegiatan:</strong> <span id="confirm-kegiatan">{{ old('kegiatan', $jurnal?->kegiatan) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Tugas:</strong> <span id="confirm-tugas">{{ old('tugas', $jurnal?->tugas) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Catatan:</strong> <span id="confirm-catatan">{{ old('catatan', $jurnal?->catatan) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Absensi siswa:</strong> <span id="confirm-absensi">Menunggu update</span></div>
            <div style="grid-column:1/-1;"><strong>Tanda tangan:</strong> <span id="confirm-signature">Belum ada tanda tangan</span></div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
            <button type="button" class="btn btn-muted" data-close-confirmation>Batal</button>
            <button type="button" class="btn" id="final-submit-journal">Simpan sekarang</button>
        </div>
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('journal-form');
    const canvas = document.getElementById('signature-canvas');
    const hiddenInput = document.getElementById('tanda_tangan');
    const clearButton = document.getElementById('clear-signature');
    const saveTrigger = document.getElementById('confirm-save-trigger');
    const finalSubmit = document.getElementById('final-submit-journal');
    const confirmModal = document.getElementById('journal-confirm-modal');

    if (!form || !canvas || !hiddenInput) {
        return;
    }

    const context = canvas.getContext('2d');
    if (!context) {
        return;
    }

    function updateSummary() {
        const status = document.getElementById('status_guru')?.value || 'Hadir';
        const materi = document.getElementById('materi')?.value?.trim() || 'Belum diisi';
        const tujuan = document.getElementById('tujuan_pembelajaran')?.value?.trim() || 'Belum diisi';
        const kegiatan = document.getElementById('kegiatan')?.value?.trim() || 'Belum diisi';
        const tugas = document.getElementById('tugas')?.value?.trim() || 'Belum diisi';
        const catatan = document.getElementById('catatan')?.value?.trim() || 'Belum diisi';

        const counts = { H: 0, S: 0, I: 0, A: 0, D: 0 };
        form.querySelectorAll('.attendance-status').forEach((select) => {
            const value = select.value || 'H';
            if (counts[value] !== undefined) counts[value] += 1;
        });

        const absensiText = `Hadir ${counts.H}, Sakit ${counts.S}, Izin ${counts.I}, Alpa ${counts.A}, Dispen ${counts.D}`;
        document.getElementById('confirm-status').textContent = status;
        document.getElementById('confirm-materi').textContent = materi;
        document.getElementById('confirm-tujuan').textContent = tujuan;
        document.getElementById('confirm-kegiatan').textContent = kegiatan;
        document.getElementById('confirm-tugas').textContent = tugas;
        document.getElementById('confirm-catatan').textContent = catatan;
        document.getElementById('confirm-absensi').textContent = absensiText;
        document.getElementById('confirm-signature').textContent = hiddenInput.value ? 'Sudah ada tanda tangan' : 'Belum ada tanda tangan';
    }

    const point = (event) => {
        const bounds = canvas.getBoundingClientRect();
        return {
            x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
            y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
        };
    };

    let drawing = false;
    context.strokeStyle = '#15213b';
    context.lineWidth = 4;
    context.lineCap = 'round';
    context.lineJoin = 'round';
    canvas.style.touchAction = 'none';

    canvas.addEventListener('pointerdown', (event) => {
        drawing = true;
        const start = point(event);
        context.beginPath();
        context.moveTo(start.x, start.y);
        canvas.setPointerCapture(event.pointerId);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!drawing) {
            return;
        }
        const next = point(event);
        context.lineTo(next.x, next.y);
        context.stroke();
    });

    const stopDrawing = () => {
        drawing = false;
    };

    canvas.addEventListener('pointerup', stopDrawing);
    canvas.addEventListener('pointerleave', stopDrawing);
    canvas.addEventListener('pointercancel', stopDrawing);

    clearButton?.addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        hiddenInput.value = '';
        updateSummary();
    });

    form.addEventListener('submit', () => {
        const hasSignature = hiddenInput.value.trim().length > 0;
        if (hasSignature) {
            hiddenInput.value = canvas.toDataURL('image/png');
            return;
        }

        hiddenInput.value = canvas.toDataURL('image/png');
    });

    const openConfirmModal = () => {
        updateSummary();
        if (confirmModal) {
            confirmModal.style.display = 'flex';
        }
    };

    const closeConfirmModal = () => {
        if (confirmModal) {
            confirmModal.style.display = 'none';
        }
    };

    saveTrigger?.addEventListener('click', openConfirmModal);
    finalSubmit?.addEventListener('click', () => {
        hiddenInput.value = canvas.toDataURL('image/png');
        form.submit();
    });

    confirmModal?.addEventListener('click', (event) => {
        if (event.target === confirmModal) {
            closeConfirmModal();
        }
    });

    document.querySelectorAll('[data-close-confirmation]').forEach((button) => {
        button.addEventListener('click', closeConfirmModal);
    });

    ['status_guru', 'materi', 'tujuan_pembelajaran', 'kegiatan', 'tugas', 'catatan'].forEach((id) => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateSummary);
            element.addEventListener('change', updateSummary);
        }
    });

    form.querySelectorAll('.attendance-status').forEach((select) => {
        select.addEventListener('change', updateSummary);
    });

    document.getElementById('btn-hadir-semua')?.addEventListener('click', () => {
        document.querySelectorAll('#journal-form .attendance-status').forEach((select) => {
            select.value = 'H';
        });
        updateSummary();
    });

    updateSummary();
})();
</script>
@if (! $isEdit && $sessions->where('active', true)->count() > 1)
<script>
const scheduleSearch = document.getElementById('jadwal_search');
const schedulePicker = document.getElementById('jadwal_picker');
scheduleSearch.addEventListener('input', () => {
    const query = scheduleSearch.value.toLowerCase();
    Array.from(schedulePicker.options).forEach(option => {
        option.hidden = !option.dataset.search.includes(query);
    });
});
schedulePicker.addEventListener('change', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('jadwal_id', schedulePicker.value);
    window.location.href = url;
});
</script>
@endif
