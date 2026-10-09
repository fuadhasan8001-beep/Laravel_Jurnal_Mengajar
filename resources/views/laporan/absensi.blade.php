@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
    <div class="page-head">
        <div><h1>Rekap absensi & statistik</h1><p>Ringkasan kehadiran guru dan siswa berdasarkan jurnal, dari keseluruhan hingga kelas.</p></div>
        <a class="btn" href="{{ route('laporan.absensi.export', request()->query()) }}">Export CSV</a>
    </div>
    <div class="stats">
        @foreach ($studentSummary as $status => $total)
            <div class="stat-card"><span><small>{{ $status }}</small><strong>{{ $total }}</strong><small>{{ number_format($studentSummaryPercentages[$status] ?? 0, 1, ',', '.') }}% dari absensi tercatat</small></span></div>
        @endforeach
    </div>
    <section class="panel">
        <div class="panel-head"><h2>Statistik kehadiran guru</h2><span class="eyebrow">Sesuai filter aktif</span></div>
        <div class="stats">
            @foreach ($teacherSummary as $status => $total)
                <div class="stat-card"><span><small>{{ $status }}</small><strong>{{ $total }}</strong><small>{{ number_format($teacherSummaryPercentages[$status] ?? 0, 1, ',', '.') }}% dari jurnal tercatat</small></span></div>
            @endforeach
        </div>
    </section>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route('laporan.absensi') }}">
                <div class="form-grid">
                    <div class="field"><label for="tanggal_mulai">Dari tanggal</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"></div>
                    <div class="field"><label for="tanggal_selesai">Sampai tanggal</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"></div>
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id"><option value="">Semua guru</option>@foreach ($gurus as $guru)<option value="{{ $guru->id }}" @selected((string) request('guru_id') === (string) $guru->id)>{{ $guru->nama_guru }}</option>@endforeach</select></div>
                    <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
                    <div class="field"><label for="program">Jurusan / program</label><select id="program" name="program"><option value="">Semua jurusan / program</option>@foreach ($programs as $program)<option value="{{ $program }}" @selected(request('program') === $program)>{{ $program }}</option>@endforeach</select></div>
                    <div class="field"><label for="mapel_id">Mapel</label><select id="mapel_id" name="mapel_id"><option value="">Semua mapel</option>@foreach ($mapels as $mapel)<option value="{{ $mapel->id }}" @selected((string) request('mapel_id') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div class="field"><label for="siswa_id">Siswa</label><select id="siswa_id" name="siswa_id"><option value="">Semua siswa</option>@foreach ($siswas as $siswa)<option value="{{ $siswa->id }}" @selected((string) request('siswa_id') === (string) $siswa->id)>{{ $siswa->nama_siswa }}</option>@endforeach</select></div>
                    <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Semua status</option>@foreach (['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('laporan.absensi') }}">Reset</a><button class="btn" type="submit">Terapkan filter</button></div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Statistik bertingkat</h2><span class="eyebrow">Keseluruhan → jurusan / program → kelas · persentase memakai catatan absensi aktif</span></div>
        <div class="panel-body">
            @if ($attendanceGroups->isEmpty())
                <div class="empty">Belum ada data statistik untuk filter ini.</div>
            @else
                @foreach (['siswa' => 'Siswa', 'guru' => 'Guru'] as $audience => $audienceLabel)
                    <h3>Kehadiran {{ strtolower($audienceLabel) }}</h3>
                    <div class="table-wrap"><table>
                        <thead><tr><th>Jurusan / kelas</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Dispensasi</th>@if ($audience === 'guru')<th>Dinas</th><th>Tanpa keterangan</th>@endif</tr></thead>
                        <tbody>
                            @php($overall = array_fill_keys(['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi', 'Dinas', 'Tanpa Keterangan'], 0))
                            @foreach ($attendanceGroups as $jurusan => $group)
                                @foreach ($group[$audience] as $status => $count)
                                    @php($overall[$status] += $count)
                                @endforeach
                            @endforeach
                            @php($overallTotal = array_sum($overall))
                            <tr><th>Keseluruhan</th>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $overall[$status] }} <small>({{ $overallTotal ? number_format($overall[$status] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td>@endforeach @if ($audience === 'guru')<td>{{ $overall['Dinas'] }} <small>({{ $overallTotal ? number_format($overall['Dinas'] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td><td>{{ $overall['Tanpa Keterangan'] }} <small>({{ $overallTotal ? number_format($overall['Tanpa Keterangan'] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td>@endif</tr>
                            @foreach ($attendanceGroups as $jurusan => $group)
                                <tr><th>{{ $jurusan }}</th>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $group[$audience][$status] }} <small>({{ number_format($group[$audience.'_persen'][$status], 1, ',', '.') }}%)</small></td>@endforeach @if ($audience === 'guru')<td>{{ $group[$audience]['Dinas'] }} <small>({{ number_format($group[$audience.'_persen']['Dinas'], 1, ',', '.') }}%)</small></td><td>{{ $group[$audience]['Tanpa Keterangan'] }} <small>({{ number_format($group[$audience.'_persen']['Tanpa Keterangan'], 1, ',', '.') }}%)</small></td>@endif</tr>
                                @foreach ($group['classes'] as $classStat)
                                    <tr><td>&nbsp;&nbsp;{{ $classStat['kelas']->nama_kelas }}</td>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $classStat[$audience][$status] }} <small>({{ number_format($classStat[$audience.'_persen'][$status], 1, ',', '.') }}%)</small></td>@endforeach @if ($audience === 'guru')<td>{{ $classStat[$audience]['Dinas'] }} <small>({{ number_format($classStat[$audience.'_persen']['Dinas'], 1, ',', '.') }}%)</small></td><td>{{ $classStat[$audience]['Tanpa Keterangan'] }} <small>({{ number_format($classStat[$audience.'_persen']['Tanpa Keterangan'], 1, ',', '.') }}%)</small></td>@endif</tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table></div>
                @endforeach
            @endif
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data absensi</h2><span class="eyebrow">{{ $absensis->total() }} data</span></div>
        @if ($absensis->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Guru</th><th>Mapel</th><th>Status</th><th>Catatan</th></tr></thead><tbody>@foreach ($absensis as $absensi)<tr><td>{{ $absensi->jurnal->tanggal->format('d M Y') }}</td><td>{{ $absensi->siswa->nama_siswa }}</td><td>{{ $absensi->jurnal->kelas->nama_kelas }}</td><td>{{ $absensi->jurnal->guru->nama_guru }}</td><td>{{ $absensi->jurnal->mapel->nama_mapel }}</td><td>{{ ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'][$absensi->status] ?? $absensi->status }}</td><td>{{ $absensi->catatan ?: '-' }}</td></tr>@endforeach</tbody></table></div>
            <div class="panel-body">{{ $absensis->links() }}</div>
        @else
            <div class="empty">Belum ada data absensi pada filter ini.</div>
        @endif
    </section>
@endsection
