@php
    $isEdit = isset($jurnal);

    $jurnal = $jurnal ?? null;
    $jadwal = $jadwal ?? null;

    /*
    |--------------------------------------------------------------------------
    | Nilai pilihan form
    |--------------------------------------------------------------------------
    */

    $selectedKelasId = old(
        'kelas_id',
        $jurnal?->kelas_id ?? ($jadwal?->kelas_id ?? request('kelas_id', ''))
    );

    $selectedMapelId = old(
        'mapel_id',
        $jurnal?->mapel_id ?? ($jadwal?->mapel_id ?? '')
    );

    $selectedJamMulaiId = old(
        'jam_mulai_id',
        $jurnal?->jam_mulai_id ?? ($jadwal?->jam_pelajaran_id ?? '')
    );

    $selectedJamSelesaiId = old(
        'jam_selesai_id',
        $jurnal?->jam_selesai_id ?? ($jadwal?->jam_pelajaran_id ?? '')
    );

    /*
    |--------------------------------------------------------------------------
    | Kelas yang sedang dipilih
    |--------------------------------------------------------------------------
    */

    $selectedKelas = $kelas->firstWhere('id', $selectedKelasId);

    /*
    |--------------------------------------------------------------------------
    | Daftar siswa
    |--------------------------------------------------------------------------
    */

    $siswaList = $selectedKelas?->siswas ?? collect();

    if ($jadwal?->kelas) {
        $siswaList = $jadwal->kelas->siswas;
    }

    if ($isEdit) {
        $jurnal->loadMissing([
            'absensis',
            'kelas.siswas',
        ]);

        $siswaList = $jurnal->kelas?->siswas ?? collect();
    }

    /*
    |--------------------------------------------------------------------------
    | Absensi lama ketika edit
    |--------------------------------------------------------------------------
    */

    $absensiLama = [];

    if ($isEdit) {
        $absensiLama = $jurnal->absensis
            ->keyBy('siswa_id')
            ->map(fn ($absensi) => [
                'status' => $absensi->status,
                'catatan' => $absensi->catatan,
            ])
            ->toArray();
    }
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('jurnal.update', $jurnal) : route('jurnal.store') }}"
>
    @csrf

    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="journal-layout">

        {{-- ==========================================================
             BAGIAN UTAMA
        =========================================================== --}}
        <div class="journal-main">

            <section class="journal-card">

                <div class="journal-card-header">
                    <div>
                        <h3>Jurnal mengajar</h3>
                        <p>Isi informasi kegiatan pembelajaran hari ini.</p>
                    </div>
                </div>

                <div class="form-grid">

                    {{-- TANGGAL --}}
                    <div class="field">
                        <label for="tanggal">Tanggal</label>

                        <input
                            id="tanggal"
                            type="date"
                            value="{{ old('tanggal', $jurnal?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                            readonly
                        >

                        <input
                            type="hidden"
                            name="tanggal"
                            value="{{ old('tanggal', $jurnal?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                        >

                        @error('tanggal')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- STATUS GURU --}}
                    <div class="field">
                        <label for="status_guru">Status kehadiran guru</label>

                        <select id="status_guru" name="status_guru">
                            @foreach ([
                                'Hadir',
                                'Izin',
                                'Sakit',
                                'Dinas',
                                'Tanpa Keterangan'
                            ] as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(old('status_guru', $jurnal?->status_guru ?? 'Hadir') === $status)
                                >
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>

                        @error('status_guru')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- KELAS --}}
                    <div class="field">
                        <label for="kelas_id">Kelas</label>

                        <select
                            id="kelas_id"
                            name="kelas_id"
                            onchange="window.location.href='{{ route('jurnal.create') }}?kelas_id=' + this.value"
                        >
                            <option value="">Pilih kelas</option>

                            @foreach ($kelas as $item)
                                <option
                                    value="{{ $item->id }}"
                                    @selected((string) $selectedKelasId === (string) $item->id)
                                >
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>

                        @error('kelas_id')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- MAPEL --}}
                    <div class="field">
                        <label for="mapel_id">Mata pelajaran</label>

                        <select id="mapel_id" name="mapel_id">
                            <option value="">Pilih mata pelajaran</option>

                            @foreach ($mapels as $mapel)
                                <option
                                    value="{{ $mapel->id }}"
                                    @selected((string) $selectedMapelId === (string) $mapel->id)
                                >
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>

                        @error('mapel_id')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- JAM MULAI --}}
                    <div class="field">
                        <label for="jam_mulai_id">Jam mulai</label>

                        <select
                            id="jam_mulai_id"
                            name="jam_mulai_id"
                            onchange="syncJamSelesai()"
                        >
                            <option value="">Pilih jam</option>

                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    @selected((string) $selectedJamMulaiId === (string) $jam->id)
                                >
                                    Jam ke-{{ $jam->jam_ke }}
                                    ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                </option>
                            @endforeach
                        </select>

                        @error('jam_mulai_id')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- JAM SELESAI --}}
                    <div class="field">
                        <label for="jam_selesai_id">Jam selesai</label>

                        <select
                            id="jam_selesai_id"
                            name="jam_selesai_id"
                        >
                            <option value="">Pilih jam</option>

                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    @selected((string) $selectedJamSelesaiId === (string) $jam->id)
                                >
                                    Jam ke-{{ $jam->jam_ke }}
                                    ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                </option>
                            @endforeach
                        </select>

                        @error('jam_selesai_id')
                            <small class="error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>


                {{-- ==================================================
                     MATERI
                =================================================== --}}
                <div class="field">
                    <label for="materi">Materi pembelajaran</label>

                    <input
                        id="materi"
                        type="text"
                        name="materi"
                        value="{{ old('materi', $jurnal?->materi) }}"
                        placeholder="Contoh: Dasar-dasar HTML"
                        maxlength="200"
                    >

                    @error('materi')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>


                {{-- TUJUAN PEMBELAJARAN --}}
                <div class="field">
                    <label for="tujuan_pembelajaran">Tujuan pembelajaran</label>

                    <textarea
                        id="tujuan_pembelajaran"
                        name="tujuan_pembelajaran"
                        rows="4"
                        placeholder="Tuliskan tujuan pembelajaran..."
                    >{{ old('tujuan_pembelajaran', $jurnal?->tujuan_pembelajaran) }}</textarea>

                    @error('tujuan_pembelajaran')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>


                {{-- KEGIATAN --}}
                <div class="field">
                    <label for="kegiatan">Kegiatan pembelajaran</label>

                    <textarea
                        id="kegiatan"
                        name="kegiatan"
                        rows="5"
                        placeholder="Tuliskan kegiatan pembelajaran yang dilakukan..."
                    >{{ old('kegiatan', $jurnal?->kegiatan) }}</textarea>

                    @error('kegiatan')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>


                {{-- TUGAS --}}
                <div class="field">
                    <label for="tugas">
                        Tugas
                        <span class="field-hint">
                            Wajib diisi jika guru tidak hadir
                        </span>
                    </label>

                    <textarea
                        id="tugas"
                        name="tugas"
                        rows="5"
                        placeholder="Tuliskan tugas untuk siswa..."
                    >{{ old('tugas', $jurnal?->tugas) }}</textarea>

                    @error('tugas')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>


                {{-- CATATAN --}}
                <div class="field">
                    <label for="catatan">Catatan</label>

                    <textarea
                        id="catatan"
                        name="catatan"
                        rows="4"
                        placeholder="Catatan tambahan jika ada..."
                    >{{ old('catatan', $jurnal?->catatan) }}</textarea>

                    @error('catatan')
                        <small class="error">{{ $message }}</small>
                    @enderror
                </div>

            </section>


            {{-- ======================================================
                 ABSENSI SISWA
            ======================================================= --}}
            <section class="journal-card attendance-card">

                <div class="journal-card-header">
                    <div>
                        <h3>Absensi siswa</h3>
                        <p>Semua siswa otomatis hadir. Ubah hanya siswa yang tidak hadir.</p>
                    </div>

                    @if ($siswaList->isNotEmpty())
                        <button
                            type="button"
                            class="btn btn-muted"
                            id="btn-hadir-semua"
                        >
                            Tandai hadir semua
                        </button>
                    @endif
                </div>


                @if ($siswaList->isNotEmpty())

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama siswa</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($siswaList as $index => $siswa)

                                    @php
                                        $oldAbsensi = old(
                                            "absensi.$siswa->id",
                                            $absensiLama[$siswa->id] ?? [
                                                'status' => 'H',
                                                'catatan' => '',
                                            ]
                                        );

                                        $statusAbsensi = $oldAbsensi['status'] ?? 'H';
                                        $catatanAbsensi = $oldAbsensi['catatan'] ?? '';
                                    @endphp

                                    <tr>

                                        {{-- NO --}}
                                        <td>
                                            {{ $index + 1 }}

                                            <input
                                                type="hidden"
                                                name="absensi[{{ $siswa->id }}][siswa_id]"
                                                value="{{ $siswa->id }}"
                                            >
                                        </td>


                                        {{-- NAMA SISWA --}}
                                        <td>
                                            <strong>
                                                {{ $siswa->nama_siswa }}
                                            </strong>

                                            @if (!empty($siswa->nis))
                                                <small style="display:block;">
                                                    NIS: {{ $siswa->nis }}
                                                </small>
                                            @endif
                                        </td>


                                        {{-- STATUS --}}
                                        <td>

                                            @if ($statusAbsensi === 'D')

                                                <input
                                                    type="hidden"
                                                    name="absensi[{{ $siswa->id }}][status]"
                                                    value="D"
                                                >

                                                <span class="status">
                                                    D
                                                </span>

                                                <small style="display:block;">
                                                    Dispensasi
                                                </small>

                                            @else

                                                <select
                                                    name="absensi[{{ $siswa->id }}][status]"
                                                    class="attendance-status"
                                                >
                                                    <option
                                                        value="H"
                                                        @selected($statusAbsensi === 'H')
                                                    >
                                                        H
                                                    </option>

                                                    <option
                                                        value="S"
                                                        @selected($statusAbsensi === 'S')
                                                    >
                                                        S
                                                    </option>

                                                    <option
                                                        value="I"
                                                        @selected($statusAbsensi === 'I')
                                                    >
                                                        I
                                                    </option>

                                                    <option
                                                        value="A"
                                                        @selected($statusAbsensi === 'A')
                                                    >
                                                        A
                                                    </option>
                                                </select>

                                            @endif

                                        </td>


                                        {{-- CATATAN --}}
                                        <td>

                                            <input
                                                type="text"
                                                name="absensi[{{ $siswa->id }}][catatan]"
                                                value="{{ $catatanAbsensi }}"
                                                placeholder="Opsional"
                                            >

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>
                        </table>
                    </div>

                @else

                    <div class="empty">
                        @if ($selectedKelas)
                            Belum ada data siswa untuk kelas
                            <strong>{{ $selectedKelas->nama_kelas }}</strong>.
                        @else
                            Pilih kelas terlebih dahulu untuk menampilkan daftar siswa.
                        @endif
                    </div>

                @endif

            </section>

        </div>


        {{-- ==========================================================
             SIDEBAR KANAN
        =========================================================== --}}
        <aside class="journal-side">

            {{-- ======================================================
                 RINGKASAN JADWAL
            ======================================================= --}}
            <section class="journal-card">

                <div class="journal-card-header">
                    <div>
                        <h3>Informasi kelas</h3>
                        <p>Ringkasan data pembelajaran.</p>
                    </div>
                </div>

                <div class="journal-info">

                    <div>
                        <span>Kelas</span>

                        <strong>
                            {{ $selectedKelas?->nama_kelas ?? '-' }}
                        </strong>
                    </div>


                    <div>
                        <span>Mata pelajaran</span>

                        <strong>
                            @php
                                $selectedMapel = $mapels->firstWhere('id', $selectedMapelId);
                            @endphp

                            {{ $selectedMapel?->nama_mapel ?? '-' }}
                        </strong>
                    </div>


                    <div>
                        <span>Tanggal</span>

                        <strong>
                            {{ old('tanggal', $jurnal?->tanggal?->format('d M Y') ?? today()->format('d M Y')) }}
                        </strong>
                    </div>

                </div>

            </section>


            {{-- ======================================================
                 TANDA TANGAN
            ======================================================= --}}
            <section class="journal-card signature-card">

                <div class="journal-card-header">
                    <div>
                        <h3>Tanda tangan</h3>
                        <p>Bagian tanda tangan guru.</p>
                    </div>
                </div>

                <div class="signature-space">

                    <div class="signature-info">

                        <strong>
                            {{ auth()->user()->name ?? 'Guru' }}
                        </strong>

                        <span>
                            Guru pengajar
                        </span>

                    </div>


                    <div class="signature-box">
                        TTD akan tersedia di tahap berikutnya
                    </div>

                </div>

            </section>

        </aside>

    </div>


    {{-- ==============================================================
         BUTTON
    =============================================================== --}}
    <div class="form-actions">

        <a
            href="{{ route('jurnal.index') }}"
            class="btn btn-muted"
        >
            Batal
        </a>

        <button
            type="submit"
            class="btn"
        >
            {{ $isEdit ? 'Simpan perubahan' : 'Simpan jurnal dan absensi' }}
        </button>

    </div>

</form>


<script>
    /*
    |--------------------------------------------------------------------------
    | Jam selesai mengikuti jam mulai
    |--------------------------------------------------------------------------
    */

    function syncJamSelesai() {
        const jamMulai = document.getElementById('jam_mulai_id');
        const jamSelesai = document.getElementById('jam_selesai_id');

        if (!jamMulai || !jamSelesai) {
            return;
        }

        const selectedValue = jamMulai.value;

        if (selectedValue) {
            jamSelesai.value = selectedValue;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Tandai semua siswa hadir
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const tombolHadirSemua = document.getElementById('btn-hadir-semua');

        if (tombolHadirSemua) {

            tombolHadirSemua.addEventListener('click', function () {

                document
                    .querySelectorAll('.attendance-status')
                    .forEach(function (select) {

                        select.value = 'H';

                    });

            });

        }

    });
</script>