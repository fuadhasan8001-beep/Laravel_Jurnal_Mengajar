@include('absensi._styles')
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
    <fieldset id="status_guru" style="border:0;padding:0;margin:0 0 24px;">
        <legend style="font-weight:600;margin-bottom:12px;">Kehadiran guru</legend>
        <div style="display:flex;flex-wrap:wrap;gap:20px;">
            @foreach (['Hadir', 'Izin', 'Sakit'] as $status)
                <label style="display:flex;align-items:center;gap:8px;min-height:44px;cursor:pointer;">
                    <input type="radio" name="status_guru" value="{{ $status }}" style="width:20px;height:20px;margin:0;" @checked((old('status_guru', $jurnal?->status_guru) ?: 'Hadir') === $status) required>
                    {{ $status === 'Hadir' ? 'Hadir di sekolah' : $status }}
                </label>
            @endforeach
        </div>
        @error('status_guru')<small class="error">{{ $message }}</small>@enderror
    </fieldset>
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
    </div>
    @error('jadwal_id')<p class="error" role="alert">{{ $message }}</p>@enderror
    <section class="journal-detail-panel" aria-label="Detail pembelajaran dan absensi">
        <div class="journal-card-header"><h3>Detail pembelajaran dan absensi</h3></div>
        @foreach (['materi' => 'Materi pembelajaran', 'kegiatan' => 'Kegiatan pembelajaran'] as $field => $label)
            <div class="field"><label for="{{ $field }}">{{ $label }}</label>
                @if ($field === 'materi')
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $jurnal?->$field) }}" maxlength="200">
                @else
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" maxlength="5000">{{ old($field, $jurnal?->$field) }}</textarea>
                @endif
                @error($field)<small class="error">{{ $message }}</small>@enderror
            </div>
        @endforeach
        <div class="field" id="teacher-assignment" @if ((old('status_guru', $jurnal?->status_guru) ?: 'Hadir') === 'Hadir') hidden @endif>
            <label for="tugas">Tugas untuk siswa (opsional)</label>
            <textarea id="tugas" name="tugas" rows="3" maxlength="5000">{{ old('tugas', $jurnal?->tugas) }}</textarea>
            @error('tugas')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div class="journal-card-header"><h3>Absensi siswa</h3><button type="button" class="btn btn-muted" id="btn-hadir-semua">Tandai hadir semua</button></div>
        <div class="field"><label for="attendance-search">Cari siswa</label><input type="search" id="attendance-search" placeholder="Nama atau NIS" autocomplete="off"></div>
        <div class="table-wrap attendance-editor"><table><thead><tr><th class="attendance-number">No</th><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead><tbody>
            @forelse ($kelas->first()?->siswas ?? [] as $student)
                @php
                    [$journalStart] = $start->timesForDay($hari);
                    [, $journalEnd] = $end->timesForDay($hari);
                    $dispensed = $approvedDispensasis->contains(function ($item) use ($student, $hari, $journalStart, $journalEnd) {
                        [$dispensationStart] = $item->jamMulai->timesForDay($hari);
                        [, $dispensationEnd] = $item->jamSelesai->timesForDay($hari);

                        return $item->siswa_id === $student->id
                            && $dispensationStart < $journalEnd && $dispensationEnd > $journalStart;
                    });
                    $saved = collect($attendance)->firstWhere('siswa_id', $student->id) ?? $attendance[$student->id] ?? [];
                @endphp
                <tr data-attendance-student="{{ $student->nama_siswa }} {{ $student->nis }}">
                    <td class="attendance-number">{{ $loop->iteration }}<input type="hidden" name="absensi[{{ $student->id }}][siswa_id]" value="{{ $student->id }}"></td>
                    <td class="attendance-name"><strong>{{ $student->nama_siswa }}</strong><small>{{ $student->nis }}</small></td>
                    <td>
                        @if ($dispensed)
                            <input type="hidden" name="absensi[{{ $student->id }}][status]" value="D" class="attendance-dispensed">
                        @endif
                        <div class="attendance-options" role="group" aria-label="Status {{ $student->nama_siswa }}">
                            @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $value => $label)
                                <label @if ($value === 'D') title="Dispensasi mengikuti persetujuan admin" @endif>
                                    <input type="radio" class="attendance-status" name="absensi[{{ $student->id }}][status]" value="{{ $value }}" style="width:18px;height:18px;margin:0;" @checked(($dispensed ? 'D' : ($saved['status'] ?? 'H')) === $value) @disabled($dispensed || $value === 'D')>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </td>
                    <td><input data-attendance-note name="absensi[{{ $student->id }}][catatan]" value="{{ $dispensed ? 'Dispensasi disetujui.' : ($saved['catatan'] ?? '') }}" maxlength="1000" aria-label="Catatan {{ $student->nama_siswa }}" @readonly($dispensed)></td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada siswa di kelas ini.</td></tr>
            @endforelse
        </tbody></table></div>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-top:12px;">
            <button type="button" class="btn btn-muted" id="attendance-prev" aria-label="Halaman siswa sebelumnya" title="Halaman sebelumnya">&larr;</button>
            <span id="attendance-page" role="status" aria-live="polite"></span>
            <button type="button" class="btn btn-muted" id="attendance-next" aria-label="Halaman siswa berikutnya" title="Halaman berikutnya">&rarr;</button>
        </div>
    </section>
    <section class="journal-detail-panel" aria-labelledby="location-title" data-school-latitude="{{ config('school.latitude') }}" data-school-longitude="{{ config('school.longitude') }}" data-school-radius="{{ config('school.radius_meters') }}" data-max-gps-accuracy="{{ config('school.max_gps_accuracy') }}">
        <div class="journal-card-header"><div><h3 id="location-title">Verifikasi lokasi sekolah</h3><p>Status Hadir memerlukan verifikasi GPS di area sekolah.</p></div></div>
        <p id="location-status" role="status" aria-live="polite">Pilih Hadir untuk memeriksa lokasi.</p>
        <input type="hidden" name="latitude" id="location-latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="location-longitude" value="{{ old('longitude') }}">
        <input type="hidden" name="location_accuracy" id="location-accuracy" value="{{ old('location_accuracy') }}">
        <button type="button" class="btn btn-muted" id="check-location">Periksa lokasi</button>
        @error('location')<small class="error">{{ $message }}</small>@enderror
        @error('location_latitude')<small class="error">{{ $message }}</small>@enderror
        @error('location_longitude')<small class="error">{{ $message }}</small>@enderror
        @error('location_accuracy')<small class="error">{{ $message }}</small>@enderror
    </section>
    <div class="form-actions"><a class="btn btn-muted" href="{{ route('jurnal.index') }}">Batal</a><button class="btn" type="button" id="confirm-save-trigger" disabled>{{ $isEdit ? 'Simpan perubahan' : 'Kirim Jurnal' }}</button></div>
</form>

<div id="journal-confirm-modal" style="display:none; position:fixed; inset:0; background:rgba(11,18,32,.62); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div style="width:min(900px,100%); background:#fff; border-radius:18px; box-shadow:0 20px 50px rgba(0,0,0,.2); padding:1.5rem; max-height:80vh; overflow:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem;">
            <h3 style="margin:0;">Konfirmasi simpan jurnal <span class="sr-only">Ringkasan jurnal</span></h3>
            <button type="button" class="btn btn-muted" data-close-confirmation>Keluar</button>
        </div>
        <div id="confirmation-summary" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:0.75rem 1rem; line-height:1.6;">
            <div><strong>Guru:</strong> <span id="confirm-guru">{{ auth()->user()->name }}</span></div>
            <div><strong>Kehadiran:</strong> <span id="confirm-status">{{ old('status_guru', $jurnal?->status_guru) ?: 'Hadir' }}</span></div>
            <div><strong>Kelas:</strong> <span id="confirm-kelas">{{ $jurnal?->kelas->nama_kelas ?? $activeSession['kelas'] }}</span></div>
            <div><strong>Mata pelajaran:</strong> <span id="confirm-mapel">{{ $jurnal?->mapel->nama_mapel ?? $activeSession['mapel'] }}</span></div>
            <div style="grid-column:1/-1;"><strong>Materi:</strong> <span id="confirm-materi">{{ old('materi', $jurnal?->materi) ?: 'Belum diisi' }}</span></div>
            <div style="grid-column:1/-1;"><strong>Kegiatan:</strong> <span id="confirm-kegiatan">{{ old('kegiatan', $jurnal?->kegiatan) ?: 'Belum diisi' }}</span></div>
            <div id="confirm-assignment-row" style="grid-column:1/-1;"><strong>Tugas:</strong> <span id="confirm-tugas"></span></div>
            <div style="grid-column:1/-1;"><strong>Absensi siswa:</strong> <span id="confirm-absensi">Menunggu update</span></div>
            <div style="grid-column:1/-1;"><strong>Verifikasi lokasi:</strong> <span id="confirm-location">Belum diperiksa</span></div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1.5rem;">
            <button type="button" class="btn btn-muted" data-close-confirmation>Batal</button>
            <button type="button" class="btn" id="final-submit-journal" disabled>Kirim Jurnal</button>
        </div>
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('journal-form');
    const statusSelect = document.getElementById('status_guru');
    const selectedStatus = () => form.querySelector('input[name="status_guru"]:checked')?.value;
    const locationPanel = document.querySelector('[data-school-latitude]');
    const locationStatus = document.getElementById('location-status');
    const checkLocationButton = document.getElementById('check-location');
    const saveTrigger = document.getElementById('confirm-save-trigger');
    const finalSubmit = document.getElementById('final-submit-journal');
    const confirmModal = document.getElementById('journal-confirm-modal');
    const latitudeInput = document.getElementById('location-latitude');
    const longitudeInput = document.getElementById('location-longitude');
    const accuracyInput = document.getElementById('location-accuracy');
    if (!form) return;

    const studentRows = Array.from(form.querySelectorAll('[data-attendance-student]'));
    const search = document.getElementById('attendance-search');
    const previousPage = document.getElementById('attendance-prev');
    const nextPage = document.getElementById('attendance-next');
    const pageLabel = document.getElementById('attendance-page');
    let studentPage = 0;
    function renderStudents() {
        const query = search.value.trim().toLocaleLowerCase('id');
        const matching = studentRows.filter(row => row.dataset.attendanceStudent.toLocaleLowerCase('id').includes(query));
        const pages = Math.max(1, Math.ceil(matching.length / 10));
        studentPage = Math.min(studentPage, pages - 1);
        const visible = new Set(matching.slice(studentPage * 10, (studentPage + 1) * 10));
        studentRows.forEach(row => { row.style.display = visible.has(row) ? '' : 'none'; });
        previousPage.disabled = studentPage === 0;
        nextPage.disabled = studentPage >= pages - 1;
        pageLabel.textContent = matching.length
            ? `${studentPage * 10 + 1}-${Math.min((studentPage + 1) * 10, matching.length)} dari ${matching.length} siswa`
            : 'Tidak ada siswa yang cocok';
    }
    search.addEventListener('input', () => { studentPage = 0; renderStudents(); });
    previousPage.addEventListener('click', () => { studentPage--; renderStudents(); });
    nextPage.addEventListener('click', () => { studentPage++; renderStudents(); });
    renderStudents();

    let locationValid = false;
    const schoolLatitude = Number(locationPanel.dataset.schoolLatitude);
    const schoolLongitude = Number(locationPanel.dataset.schoolLongitude);
    const radius = Number(locationPanel.dataset.schoolRadius);
    const maximumAccuracy = Number(locationPanel.dataset.maxGpsAccuracy);
    const hasSchoolConfig = locationPanel.dataset.schoolLatitude.trim() !== '' && locationPanel.dataset.schoolLongitude.trim() !== '' && locationPanel.dataset.schoolRadius.trim() !== '' && locationPanel.dataset.maxGpsAccuracy.trim() !== '' && Number.isFinite(schoolLatitude) && Math.abs(schoolLatitude) <= 90 && Number.isFinite(schoolLongitude) && Math.abs(schoolLongitude) <= 180 && Number.isFinite(radius) && radius > 0 && Number.isFinite(maximumAccuracy) && maximumAccuracy >= 0;

    function updateButtons() {
        const assignment = document.getElementById('teacher-assignment');
        assignment.hidden = selectedStatus() === 'Hadir';
        assignment.style.display = selectedStatus() === 'Hadir' ? 'none' : '';
        const canSubmit = selectedStatus() !== 'Hadir' || locationValid;
        saveTrigger.disabled = !canSubmit;
        finalSubmit.disabled = !canSubmit;
        locationPanel.hidden = selectedStatus() !== 'Hadir';
        locationPanel.style.display = selectedStatus() === 'Hadir' ? '' : 'none';
        checkLocationButton.hidden = selectedStatus() !== 'Hadir';
        document.getElementById('confirm-location').parentElement.hidden = selectedStatus() !== 'Hadir';
    }

    function distanceMeters(latitude, longitude) {
        const radians = (degrees) => degrees * Math.PI / 180;
        const deltaLatitude = radians(schoolLatitude - latitude);
        const deltaLongitude = radians(schoolLongitude - longitude);
        const a = Math.sin(deltaLatitude / 2) ** 2 + Math.cos(radians(latitude)) * Math.cos(radians(schoolLatitude)) * Math.sin(deltaLongitude / 2) ** 2;
        return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function checkLocation() {
        locationValid = false;
        latitudeInput.value = '';
        longitudeInput.value = '';
        accuracyInput.value = '';
        updateButtons();
        if (!hasSchoolConfig) {
            locationStatus.textContent = 'Koordinat sekolah belum dikonfigurasi.';
            return;
        }
        if (!navigator.geolocation) {
            locationStatus.textContent = 'GPS tidak tersedia pada browser ini.';
            return;
        }
        locationStatus.textContent = 'Memeriksa lokasi...';
        navigator.geolocation.getCurrentPosition((position) => {
            if (selectedStatus() !== 'Hadir') return;
            const { latitude, longitude, accuracy } = position.coords;
            if (!Number.isFinite(latitude) || Math.abs(latitude) > 90 || !Number.isFinite(longitude) || Math.abs(longitude) > 180 || !Number.isFinite(accuracy) || accuracy < 0) {
                locationStatus.textContent = 'Koordinat GPS tidak valid. Coba periksa lokasi kembali.';
                return;
            }
            if (accuracy > maximumAccuracy) {
                locationStatus.textContent = `Akurasi GPS buruk (${Math.round(accuracy)} meter). Batas akurasi ${maximumAccuracy} meter. Coba periksa kembali.`;
                return;
            }
            const distance = distanceMeters(latitude, longitude);
            if (distance > radius) {
                locationStatus.textContent = `Di luar area sekolah — Jarak ${Math.round(distance)} meter, batas ${Math.round(radius)} meter.`;
                return;
            }
            latitudeInput.value = latitude;
            longitudeInput.value = longitude;
            accuracyInput.value = accuracy;
            locationValid = true;
            locationStatus.textContent = 'Lokasi valid — Anda berada di area sekolah.';
            updateButtons();
        }, (error) => {
            const messages = {
                1: 'Izin lokasi ditolak. Aktifkan izin lokasi browser untuk mengirim jurnal Hadir.',
                2: 'GPS tidak tersedia. Periksa pengaturan lokasi perangkat dan coba kembali.',
                3: 'Waktu pemeriksaan GPS habis. Coba periksa lokasi kembali.',
            };
            locationStatus.textContent = messages[error.code] || 'Lokasi tidak dapat diperiksa. Coba kembali.';
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    }

    function updateSummary() {
        const status = selectedStatus() || 'Hadir';
        const materi = document.getElementById('materi')?.value?.trim() || 'Belum diisi';
        const kegiatan = document.getElementById('kegiatan')?.value?.trim() || 'Belum diisi';
        const counts = { H: 0, S: 0, I: 0, A: 0, D: 0 };
        form.querySelectorAll('.attendance-status:checked').forEach((select) => {
            const value = select.value || 'H';
            if (counts[value] !== undefined) counts[value] += 1;
        });
        document.getElementById('confirm-status').textContent = status === 'Hadir' ? 'Hadir di Sekolah' : status;
        document.getElementById('confirm-materi').textContent = materi;
        document.getElementById('confirm-kegiatan').textContent = kegiatan;
        document.getElementById('confirm-assignment-row').hidden = status === 'Hadir';
        document.getElementById('confirm-tugas').textContent = document.getElementById('tugas').value.trim() || 'Tidak ada tugas';
        document.getElementById('confirm-absensi').textContent = `Hadir ${counts.H}, Sakit ${counts.S}, Izin ${counts.I}, Alpa ${counts.A}, Dispen ${counts.D}`;
        document.getElementById('confirm-location').textContent = status !== 'Hadir' ? 'Tidak diwajibkan untuk status ini' : (locationValid ? locationStatus.textContent : 'Belum valid');
    }

    function openConfirmModal() {
        updateSummary();
        if (confirmModal) confirmModal.style.display = 'flex';
    }
    function closeConfirmModal() {
        if (confirmModal) confirmModal.style.display = 'none';
    }

    statusSelect.addEventListener('change', () => {
        if (selectedStatus() === 'Hadir') checkLocation();
        else {
            locationValid = false;
            latitudeInput.value = '';
            longitudeInput.value = '';
            accuracyInput.value = '';
            locationStatus.textContent = 'GPS tidak diwajibkan untuk status Izin atau Sakit.';
        }
        updateButtons();
        updateSummary();
    });
    checkLocationButton.addEventListener('click', checkLocation);
    saveTrigger.addEventListener('click', openConfirmModal);
    finalSubmit.addEventListener('click', () => {
        if (selectedStatus() === 'Hadir' && !locationValid) return;
        form.submit();
    });
    confirmModal?.addEventListener('click', (event) => { if (event.target === confirmModal) closeConfirmModal(); });
    document.querySelectorAll('[data-close-confirmation]').forEach((button) => button.addEventListener('click', closeConfirmModal));
    ['materi', 'kegiatan', 'tugas'].forEach((id) => document.getElementById(id)?.addEventListener('input', updateSummary));
    function updateAttendanceNotes() {
        studentRows.forEach(row => {
            const note = row.querySelector('[data-attendance-note]');
            const present = row.querySelector('.attendance-status:checked')?.value === 'H';
            note.hidden = present;
            note.style.display = present ? 'none' : '';
            note.disabled = present;
        });
    }
    form.querySelectorAll('.attendance-status').forEach((radio) => radio.addEventListener('change', () => { updateAttendanceNotes(); updateSummary(); }));
    document.getElementById('btn-hadir-semua')?.addEventListener('click', () => {
        form.querySelectorAll('.attendance-status[value="H"]:not(:disabled)').forEach((radio) => { radio.checked = true; });
        updateAttendanceNotes();
        updateSummary();
    });
    updateAttendanceNotes();
    updateButtons();
    updateSummary();
    if (selectedStatus() === 'Hadir') checkLocation();
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
