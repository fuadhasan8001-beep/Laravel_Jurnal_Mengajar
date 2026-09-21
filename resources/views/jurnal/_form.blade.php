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
        <div class="field"><label for="status_guru">Kehadiran guru</label><select id="status_guru" name="status_guru" required>
            <option value="">Pilih status</option>
            @foreach (['Hadir', 'Izin', 'Sakit', 'Dinas', 'Tanpa Keterangan'] as $status)
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
    <div class="form-actions"><a class="btn btn-muted" href="{{ route('jurnal.index') }}">Batal</a><button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Simpan jurnal' }}</button></div>
</form>
<script>
document.getElementById('btn-hadir-semua')?.addEventListener('click', () => {
    document.querySelectorAll('#journal-form .attendance-status').forEach(select => select.value = 'H');
});

(() => {
    const canvas = document.getElementById('signature-canvas');
    const hiddenInput = document.getElementById('tanda_tangan');
    const clearButton = document.getElementById('clear-signature');
    const context = canvas.getContext('2d');
    let drawing = false;
    let hasSignature = false;

    context.strokeStyle = '#15213b';
    context.lineWidth = 4;
    context.lineCap = 'round';
    context.lineJoin = 'round';

    const point = (event) => {
        const bounds = canvas.getBoundingClientRect();
        return {
            x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
            y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
        };
    };

    canvas.addEventListener('pointerdown', (event) => {
        drawing = true;
        hasSignature = true;
        canvas.setPointerCapture(event.pointerId);
        const start = point(event);
        context.beginPath();
        context.moveTo(start.x, start.y);
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!drawing) {
            return;
        }

        const next = point(event);
        context.lineTo(next.x, next.y);
        context.stroke();
    });

    const stopDrawing = () => { drawing = false; };
    canvas.addEventListener('pointerup', stopDrawing);
    canvas.addEventListener('pointercancel', stopDrawing);
    clearButton.addEventListener('click', () => {
        context.clearRect(0, 0, canvas.width, canvas.height);
        hasSignature = false;
        hiddenInput.value = '';
    });

    document.getElementById('journal-form').addEventListener('submit', () => {
        if (hasSignature) {
            hiddenInput.value = canvas.toDataURL('image/png');
        }
    });
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
