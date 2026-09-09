@php
    $isEdit = isset($jurnal);
    $selectedKelasId = old('kelas_id', $jurnal->kelas_id ?? request('kelas_id'));
@endphp

<form action="{{ $isEdit ? route('jurnal.update', $jurnal) : route('jurnal.store') }}" method="POST">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="form-grid">
        <div class="field">
            <label>Hari, tanggal</label>
            <div class="field-readonly">{{ ($jurnal->tanggal ?? now())->locale('id')->translatedFormat('l, d F Y') }}</div>
            <input type="hidden" name="tanggal" value="{{ old('tanggal', $jurnal->tanggal?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        </div>

        <div class="field">
            <label for="status_guru">Kehadiran guru</label>
            <select id="status_guru" name="status_guru" required>
                @foreach (['Hadir', 'Izin', 'Sakit', 'Dinas', 'Tanpa Keterangan'] as $status)
                    <option value="{{ $status }}" @selected(old('status_guru', $jurnal->status_guru ?? 'Hadir') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="kelas_id">Kelas</label>
            <select id="kelas_id" name="kelas_id" required @if (! $isEdit) onchange="if (this.value) window.location.href = '{{ route('jurnal.create') }}?kelas_id=' + this.value" @endif>
                <option value="">Pilih kelas</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}" @selected((string) $selectedKelasId === (string) $item->id)>{{ $item->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="mapel_id">Mata pelajaran</label>
            <select id="mapel_id" name="mapel_id" required>
                <option value="">Pilih mata pelajaran</option>
                @foreach ($mapels as $mapel)
                    <option value="{{ $mapel->id }}" @selected((string) old('mapel_id', $jurnal->mapel_id ?? request('mapel_id', '')) === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="jam_mulai_id">Jam ke</label>
            <select id="jam_mulai_id" name="jam_mulai_id" required onchange="this.form.querySelector('[name=jam_selesai_id]').value = this.value">
                <option value="">Pilih jam ke</option>
                @foreach ($jamPelajarans as $jam)
                    <option value="{{ $jam->id }}" @selected((string) old('jam_mulai_id', $jurnal->jam_mulai_id ?? request('jam_mulai_id', '')) === (string) $jam->id)>Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})</option>
                @endforeach
            </select>
            <input type="hidden" name="jam_selesai_id" value="{{ old('jam_selesai_id', $jurnal->jam_selesai_id ?? request('jam_selesai_id', request('jam_mulai_id', ''))) }}">
        </div>

        <div class="field full">
            <label for="materi">Materi pembelajaran</label>
            <input id="materi" type="text" name="materi" value="{{ old('materi', $jurnal->materi ?? '') }}" maxlength="200" required>
        </div>

        <div class="field full">
            <label for="tujuan_pembelajaran">Tujuan pembelajaran <span class="field-help">Opsional</span></label>
            <textarea id="tujuan_pembelajaran" name="tujuan_pembelajaran" rows="3">{{ old('tujuan_pembelajaran', $jurnal->tujuan_pembelajaran ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="kegiatan">Kegiatan pembelajaran <span class="field-help">Opsional</span></label>
            <textarea id="kegiatan" name="kegiatan" rows="3">{{ old('kegiatan', $jurnal->kegiatan ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="tugas">Keterangan tugas <span class="field-help">Wajib bila guru tidak hadir</span></label>
            <textarea id="tugas" name="tugas" rows="3" placeholder="Tuliskan tugas yang diberikan kepada siswa">{{ old('tugas', $jurnal->tugas ?? '') }}</textarea>
        </div>

        <div class="field full">
            <label for="catatan">Catatan</label>
            <textarea id="catatan" name="catatan" rows="3">{{ old('catatan', $jurnal->catatan ?? '') }}</textarea>
        </div>
    </div>

    @if ($selectedKelas)
        <section class="attendance-section">
            <div class="attendance-head">
                <div><h3>Absensi siswa</h3><p>H = Hadir, S = Sakit, I = Izin, A = Alpa.</p></div>
                <button class="btn btn-muted" type="button" onclick="this.closest('.attendance-section').querySelectorAll('[data-attendance-status]').forEach((input) => input.value = 'H')">Tandai hadir semua</button>
            </div>

            @if ($selectedKelas->siswas->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>No.</th><th>NISN</th><th>Nama siswa</th><th>Status</th><th>Alasan/catatan</th></tr></thead>
                        <tbody>
                            @foreach ($selectedKelas->siswas as $siswa)
                                @php($existingAbsensi = $isEdit ? $jurnal->absensis->firstWhere('siswa_id', $siswa->id) : null)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $siswa->nis }}</td>
                                    <td><strong>{{ $siswa->nama_siswa }}</strong></td>
                                    <td>
                                        <input type="hidden" name="absensi[{{ $siswa->id }}][siswa_id]" value="{{ $siswa->id }}">
                                        @if ($existingAbsensi?->status === 'D')
                                            <input type="hidden" name="absensi[{{ $siswa->id }}][status]" value="D">
                                            <span class="status approved">D — Dispensasi</span>
                                        @else
                                            <select data-attendance-status name="absensi[{{ $siswa->id }}][status]">
                                                @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa'] as $status => $label)
                                                    <option value="{{ $status }}" @selected(old('absensi.' . $siswa->id . '.status', $existingAbsensi->status ?? 'H') === $status)>{{ $status }} — {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td><input type="text" name="absensi[{{ $siswa->id }}][catatan]" value="{{ old('absensi.' . $siswa->id . '.catatan', $existingAbsensi->catatan ?? '') }}" maxlength="1000" placeholder="Contoh: sakit demam"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty">Belum ada siswa pada kelas ini.</div>
            @endif
        </section>
    @else
        <div class="empty attendance-empty">Pilih kelas untuk menampilkan daftar absensi siswa.</div>
    @endif

    <section class="signature-space">
        <div><span>Mengetahui,</span><strong>Guru mata pelajaran</strong></div>
        <div class="signature-line">Ruang tanda tangan</div>
    </section>

    <div class="form-actions">
        <a class="btn btn-muted" href="{{ $isEdit ? route('jurnal.show', $jurnal) : route('jurnal.index') }}">Batal</a>
        <button class="btn" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Simpan jurnal dan absensi' }}</button>
    </div>
</form>
