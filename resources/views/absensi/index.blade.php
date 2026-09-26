@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
    @include('absensi._styles')
    <div class="page-head">
        <div>
            <h1>Kelola absensi</h1>
            <p>Rekap kehadiran siswa berdasarkan jurnal mengajar.</p>
        </div><span class="status approved">Status D = dispensasi</span>
    </div>
    @forelse ($jurnals as $jurnal)
        <section class="panel panel-spaced">
            <div class="panel-head">
                <div>
                    <h2>{{ $jurnal->tanggal->format('d M Y') }}</h2>
                    <span class="eyebrow">Kelas {{ $jurnal->kelas->nama_kelas }} · {{ $jurnal->mapel->nama_mapel }}</span>
                </div><span class="eyebrow">{{ $jurnal->absensis->count() }} siswa tercatat</span>
            </div>
            @if ($jurnal->kelas->siswas->count())
                <form action="{{ route('absensi.store') }}" method="POST" data-absence-form>
                    @csrf
                    <input type="hidden" name="jurnal_id" value="{{ $jurnal->id }}">
                <div class="panel-body">
                    <label for="attendance-search-{{ $jurnal->id }}">Cari siswa</label>
                    <input id="attendance-search-{{ $jurnal->id }}" type="search" data-absence-search placeholder="Nama atau NIS" autocomplete="off">
                    <button
                        class="btn btn-muted"
                        type="button"
                        data-mark-present
                    >Hadir semua</button>
                </div>
                <div class="table-wrap attendance-editor">
                    <table>
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jurnal->kelas->siswas as $siswa)
                                @php
                                    $absensi = $jurnal->absensis->firstWhere('siswa_id', $siswa->id);
                                @endphp
                                <tr data-student-search="{{ $siswa->nama_siswa }} {{ $siswa->nis }}">
                                    <td class="attendance-name">
                                        <input type="hidden" name="absensis[{{ $siswa->id }}][siswa_id]" value="{{ $siswa->id }}">
                                        <strong>{{ $loop->iteration }}. {{ $siswa->nama_siswa }}</strong><small>{{ $siswa->nis }}</small>
                                    </td>
                                    <td>
                                            @if ($absensi?->status === 'D')
                                                <input type="hidden" name="absensis[{{ $siswa->id }}][status]" value="D">
                                            @endif
                                            <div class="attendance-options" role="group" aria-label="Status {{ $siswa->nama_siswa }}">
                                                @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $status => $label)
                                                    <label @if ($status === 'D') title="Dispensasi mengikuti persetujuan admin" @endif>
                                                        <input type="radio" data-absence-status name="absensis[{{ $siswa->id }}][status]" value="{{ $status }}" style="width:18px;height:18px;margin:0;" @checked(($absensi?->status === 'D' ? 'D' : old('absensis.'.$siswa->id.'.status', $absensi->status ?? 'H')) === $status) @disabled($absensi?->status === 'D' || $status === 'D')>
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                    </td>
                                    <td>
                                        <input type="text" data-absence-note name="absensis[{{ $siswa->id }}][catatan]" value="{{ old('absensis.'.$siswa->id.'.catatan', $absensi->catatan ?? '') }}" placeholder="Catatan" aria-label="Catatan {{ $siswa->nama_siswa }}" @readonly($absensi?->status === 'D')>
                                        @if ($absensi?->surat_izin_path)
                                            <a href="{{ route('absensi.parent-letter', $absensi) }}">Lihat surat izin</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
                    <div class="attendance-toolbar">
                        <button type="button" class="btn btn-muted" data-previous aria-label="Halaman sebelumnya">&larr;</button>
                        <span data-page-status role="status" aria-live="polite"></span>
                        <button type="button" class="btn btn-muted" data-next aria-label="Halaman berikutnya">&rarr;</button>
                    </div>
                    <div class="form-actions">
                        <button class="btn" type="submit">Simpan absensi</button>
                    </div>
                </form>
            @else
                <div class="empty">Belum ada siswa dalam kelas jurnal ini.</div>
            @endif
        </section>
    @empty
        <section class="panel">
            <div class="empty">Belum ada jurnal yang tersedia.</div>
        </section>
    @endforelse
    {{ $jurnals->links() }}
    <script>
        document.querySelectorAll('[data-absence-form]').forEach((form) => {
            const rows = Array.from(form.querySelectorAll('[data-student-search]'));
            const search = form.querySelector('[data-absence-search]');
            let page = 0;
            const renderPage = () => {
                const matching = rows.filter(row => row.dataset.studentSearch.toLocaleLowerCase('id').includes(search.value.trim().toLocaleLowerCase('id')));
                page = Math.max(0, Math.min(page, Math.ceil(matching.length / 10) - 1));
                const visible = new Set(matching.slice(page * 10, page * 10 + 10));
                rows.forEach(row => { row.style.display = visible.has(row) ? '' : 'none'; });
                form.querySelector('[data-previous]').disabled = page === 0;
                form.querySelector('[data-next]').disabled = (page + 1) * 10 >= matching.length;
                form.querySelector('[data-page-status]').textContent = matching.length ? `${page * 10 + 1}-${Math.min(page * 10 + 10, matching.length)} dari ${matching.length} siswa` : 'Tidak ada siswa yang cocok';
            };
            search.addEventListener('input', () => { page = 0; renderPage(); });
            form.querySelector('[data-previous]').addEventListener('click', () => { page--; renderPage(); });
            form.querySelector('[data-next]').addEventListener('click', () => { page++; renderPage(); });
            renderPage();
            const updateNotes = () => {
                form.querySelectorAll('[data-absence-note]').forEach(note => {
                    const present = note.closest('tr').querySelector('[data-absence-status]:checked')?.value === 'H';
                    note.hidden = present;
                    note.style.display = present ? 'none' : '';
                    note.disabled = present;
                });
            };
            form.addEventListener('change', updateNotes);
            form.querySelector('[data-mark-present]')?.addEventListener('click', () => {
                form.querySelectorAll('[data-absence-status][value="H"]:not(:disabled)').forEach((radio) => {
                    radio.checked = true;
                });
                updateNotes();
            });
            updateNotes();
        });
    </script>
@endsection
