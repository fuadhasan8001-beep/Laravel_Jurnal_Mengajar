@php($editing = isset($event))
<form method="POST" action="{{ $editing ? route('admin.calendar.update', $event) : route('admin.calendar.store') }}" class="form-stack">
    @csrf
    @if ($editing) @method('PUT') @endif
    <div class="form-grid">
        <div class="field"><label>Tanggal kegiatan</label><input type="date" name="event_date" value="{{ old('event_date', $event?->event_date?->toDateString() ?? today()->toDateString()) }}" required></div>
        <div class="field"><label>Nama kegiatan</label><input name="title" maxlength="150" value="{{ old('title', $event?->title) }}" required></div>
        <div class="field"><label>Jenis kegiatan</label><input name="event_type" maxlength="100" placeholder="Rapat, upacara, pelatihan…" value="{{ old('event_type', $event?->event_type ?? 'Kegiatan sekolah') }}" required></div>
        <div class="field"><label>Lokasi kegiatan</label><select name="location_mode" data-location-mode><option value="school" @selected(old('location_mode', $event?->location_mode ?? 'school') === 'school')>Lokasi sekolah</option><option value="custom" @selected(old('location_mode', $event?->location_mode) === 'custom')>Lokasi khusus</option></select></div>
    </div>
    <div class="field"><label>Keterangan</label><textarea name="description" rows="2">{{ old('description', $event?->description) }}</textarea></div>
    <div class="form-grid" data-custom-location @if(old('location_mode', $event?->location_mode ?? 'school') !== 'custom') hidden @endif>
        <div class="field"><label>Latitude lokasi</label><input type="number" step="any" name="location_latitude" value="{{ old('location_latitude', $event?->location_latitude) }}"></div>
        <div class="field"><label>Longitude lokasi</label><input type="number" step="any" name="location_longitude" value="{{ old('location_longitude', $event?->location_longitude) }}"></div>
        <div class="field"><label>Radius geofence (meter)</label><input type="number" min="1" max="50000" name="location_radius_meters" value="{{ old('location_radius_meters', $event?->location_radius_meters ?? 100) }}"></div>
    </div>
    <div class="form-grid">
        <div class="field"><label>Peserta kegiatan</label><select name="participant_scope" data-participant-scope required>
            @foreach (['semua_guru' => 'Semua guru', 'guru_tertentu' => 'Guru tertentu', 'semua_siswa' => 'Semua siswa', 'kelas_tertentu' => 'Kelas tertentu'] as $value => $label)
                <option value="{{ $value }}" @selected(old('participant_scope', $event?->participant_scope ?? 'semua_guru') === $value)>{{ $label }}</option>
            @endforeach
        </select></div>
        <div class="field" data-target-gurus @if(old('participant_scope', $event?->participant_scope ?? 'semua_guru') !== 'guru_tertentu') hidden @endif>
            <label>Guru peserta</label><select name="participant_ids[]" multiple size="5" @disabled(old('participant_scope', $event?->participant_scope ?? 'semua_guru') !== 'guru_tertentu')>@foreach ($gurus as $guru)<option value="{{ $guru->id }}" @selected(in_array($guru->id, (array) old('participant_ids', $event?->participant_ids ?? [])))>{{ $guru->nama_guru }}</option>@endforeach</select>
        </div>
        <div class="field" data-target-classes @if(old('participant_scope', $event?->participant_scope ?? 'semua_guru') !== 'kelas_tertentu') hidden @endif>
            <label>Kelas peserta</label><select name="participant_ids[]" multiple size="5" @disabled(old('participant_scope', $event?->participant_scope ?? 'semua_guru') !== 'kelas_tertentu')>@foreach ($kelas as $class)<option value="{{ $class->id }}" @selected(in_array($class->id, (array) old('participant_ids', $event?->participant_ids ?? [])))>{{ $class->nama_kelas }}</option>@endforeach</select>
        </div>
    </div>
    <div class="form-grid">
        <div class="field"><label>Mulai kegiatan</label><input type="time" name="activity_start" value="{{ old('activity_start', $event?->activity_start ?? '07:00') }}" required></div>
        <div class="field"><label>Selesai kegiatan</label><input type="time" name="activity_end" value="{{ old('activity_end', $event?->activity_end ?? '15:00') }}" required></div>
        <div class="field"><label>Waktu pulang lebih awal (opsional)</label><input type="time" name="early_dismissal_at" value="{{ old('early_dismissal_at', $event?->early_dismissal_at) }}"><small>Jadwal setelah waktu ini tidak perlu diisi pada hari kegiatan.</small></div>
        <div class="field"><label>Mode absensi</label><select name="attendance_mode" data-attendance-mode required>
            @foreach (['normal' => 'Normal sesuai jadwal', 'morning_evening' => 'Pagi & sore', 'once' => 'Sekali saja', 'none' => 'Tanpa absensi'] as $value => $label)
                <option value="{{ $value }}" @selected(old('attendance_mode', $event?->attendance_mode ?? 'normal') === $value)>{{ $label }}</option>
            @endforeach
        </select></div>
    </div>
    <div class="form-grid" data-once-times @if(old('attendance_mode', $event?->attendance_mode ?? 'normal') !== 'once') hidden @endif>
        <div class="field"><label>Mulai absensi</label><input type="time" name="once_start" value="{{ old('once_start', $event?->once_start ?? '07:00') }}"></div>
        <div class="field"><label>Batas waktu absensi</label><input type="time" name="once_deadline" value="{{ old('once_deadline', $event?->once_deadline ?? '09:00') }}"></div>
    </div>
    <div class="form-grid" data-split-times @if(old('attendance_mode', $event?->attendance_mode ?? 'normal') !== 'morning_evening') hidden @endif>
        <div class="field"><label>Mulai pagi</label><input type="time" name="morning_start" value="{{ old('morning_start', $event?->morning_start ?? '06:00') }}"></div>
        <div class="field"><label>Batas pagi</label><input type="time" name="morning_deadline" value="{{ old('morning_deadline', $event?->morning_deadline ?? '08:00') }}"></div>
        <div class="field"><label>Mulai sore</label><input type="time" name="evening_start" value="{{ old('evening_start', $event?->evening_start ?? '14:00') }}"></div>
        <div class="field"><label>Batas sore</label><input type="time" name="evening_deadline" value="{{ old('evening_deadline', $event?->evening_deadline ?? '17:00') }}"></div>
    </div>
    <p class="eyebrow">Mode Pagi &amp; Sore dan Sekali saja mengganti absensi jurnal pada tanggal kegiatan. Lokasi tetap divalidasi sesuai geofence kegiatan.</p>
    <div class="form-actions"><button class="btn" type="submit">{{ $editing ? 'Simpan perubahan kegiatan' : 'Buat kegiatan' }}</button></div>
</form>
<script>
(() => {
    const form = document.currentScript.previousElementSibling;
    const mode = form.querySelector('[data-attendance-mode]');
    const locationMode = form.querySelector('[data-location-mode]');
    const scope = form.querySelector('[data-participant-scope]');
    const toggle = (selector, visible) => { const element = form.querySelector(selector); element.hidden = !visible; element.querySelectorAll('select').forEach((input) => input.disabled = !visible); };
    const updateMode = () => { toggle('[data-once-times]', mode.value === 'once'); toggle('[data-split-times]', mode.value === 'morning_evening'); };
    const updateLocation = () => toggle('[data-custom-location]', locationMode.value === 'custom');
    const updateScope = () => { toggle('[data-target-gurus]', scope.value === 'guru_tertentu'); toggle('[data-target-classes]', scope.value === 'kelas_tertentu'); };
    mode.addEventListener('change', updateMode); locationMode.addEventListener('change', updateLocation); scope.addEventListener('change', updateScope);
    updateMode(); updateLocation(); updateScope();
})();
</script>
