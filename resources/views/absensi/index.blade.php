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
        <section
            class="panel"
            style="margin-bottom:18px"
        >
            <div class="panel-head">
                <div>
                    <h2>{{ $jurnal->tanggal->format('d M Y') }}</h2>
                    <span class="eyebrow">Kelas {{ $jurnal->kelas->nama_kelas }} · {{ $jurnal->mapel->nama_mapel }}</span>
                </div><span class="eyebrow">{{ $jurnal->absensis->count() }} siswa tercatat</span>
            </div>
            @if ($jurnal->kelas->siswas->count())
                <div class="panel-body">
                    <button
                        class="btn btn-muted"
                        type="button"
                        onclick="this.closest('section').querySelectorAll('select[data-absence-status]').forEach((select) => { if (!select.disabled) select.value = 'H'; })"
                    >Hadir semua</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jurnal->kelas->siswas as $siswa)
                                @php
                                    $absensi = $jurnal->absensis->firstWhere('siswa_id', $siswa->id);
                                @endphp
                                <tr>
                                    @php
                                        $formId = 'absence-form-' . $jurnal->id . '-' . $siswa->id;
                                    @endphp
                                    <td>
                                        <form
                                            id="{{ $formId }}"
                                            action="{{ route('absensi.store') }}"
                                            method="POST"
                                        >
                                            @csrf
                                        </form>
                                        <input form="{{ $formId }}" type="hidden" name="jurnal_id" value="{{ $jurnal->id }}">
                                        <input form="{{ $formId }}" type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                        <strong>{{ $siswa->nama_siswa }}</strong>
                                    </td>
                                    <td>
                                            @if ($absensi?->status === 'D')
                                                <input form="{{ $formId }}" type="hidden" name="status" value="D">
                                            @endif
                                            <select
                                                data-absence-status
                                                form="{{ $formId }}"
                                                name="status"
                                                @disabled($absensi?->status === 'D')
                                            >
                                                @foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $status => $label)
                                                    <option value="{{ $status }}" @selected(($absensi->status ?? 'H') === $status)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                    </td>
                                    <td>
                                        <input form="{{ $formId }}" type="text" name="catatan" value="{{ $absensi->catatan ?? '' }}" placeholder="Catatan">
                                    </td>
                                    <td>
                                        <button class="btn" form="{{ $formId }}" type="submit">Simpan</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
            @else
                <div class="empty">Belum ada siswa dalam kelas jurnal ini.</div>
            @endif
        </section>
    @empty
        <section class="panel">
            <div class="empty">Belum ada jurnal yang tersedia.</div>
        </section>
    @endforelse
@endsection
