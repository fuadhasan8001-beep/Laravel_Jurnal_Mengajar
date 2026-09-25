@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
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
                    <button
                        class="btn btn-muted"
                        type="button"
                        data-mark-present
                    >Hadir semua</button>
                </div>
                <div class="table-wrap">
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
                                <tr>
                                    <td>
                                        <input type="hidden" name="absensis[{{ $siswa->id }}][siswa_id]" value="{{ $siswa->id }}">
                                        <strong>{{ $siswa->nama_siswa }}</strong>
                                    </td>
                                    <td>
                                            @if ($absensi?->status === 'D')
                                                <input type="hidden" name="absensis[{{ $siswa->id }}][status]" value="D">
                                            @endif
                                            <select
                                                data-absence-status
                                                name="absensis[{{ $siswa->id }}][status]"
                                                @disabled($absensi?->status === 'D')
                                            >
                                                @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $status => $label)
                                                    <option value="{{ $status }}" @selected(($absensi->status ?? 'H') === $status)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                    </td>
                                    <td>
                                        <input type="text" name="absensis[{{ $siswa->id }}][catatan]" value="{{ $absensi->catatan ?? '' }}" placeholder="Catatan">
                                        @if ($absensi?->surat_izin_path)
                                            <a href="{{ route('absensi.parent-letter', $absensi) }}">Lihat surat izin</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
            form.querySelector('[data-mark-present]')?.addEventListener('click', () => {
                form.querySelectorAll('[data-absence-status]').forEach((select) => {
                    select.value = 'H';
                });
            });
        });
    </script>
@endsection
