@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
    <div class="page-head">
        <div><h1>Rekap absensi &amp; statistik</h1><p>Periode aktif: {{ $periodLabel }} · Kehadiran siswa dihitung per jurnal/sesi.</p></div>
        <a class="btn" href="{{ route('laporan.absensi.export', request()->query()) }}">Export CSV</a>
    </div>
    <section class="panel">
        <div class="panel-head"><h2>{{ $statisticsScopeLabel }}</h2><span class="eyebrow">{{ $periodLabel }}</span></div>
        <div class="panel-body">
            @if (! $hasAttendanceData)
                <div class="empty">Belum ada catatan kehadiran pada periode ini.</div>
            @else
                <h3>Kehadiran siswa</h3>
                @if ($studentSummary !== [])
                    <div class="stats">
                        @foreach ($studentSummary as $status => $total)
                            <div class="stat-card"><span><small>{{ $status }}</small><strong>{{ $total }}</strong><small>{{ number_format($studentSummaryPercentages[$status] ?? 0, 1, ',', '.') }}% dari catatan</small></span></div>
                        @endforeach
                        @if ($studentAttendanceRate !== null)<div class="stat-card"><span><small>Tingkat hadir siswa</small><strong>{{ number_format($studentAttendanceRate, 1, ',', '.') }}%</strong><small>Hadir ÷ sesi non-dispensasi</small></span></div>@endif
                    </div>
                @else
                    <div class="empty">Belum ada catatan kehadiran siswa pada periode ini.</div>
                @endif
                <h3>Statistik kehadiran guru</h3>
                @if ($teacherSummary !== [])
                    <div class="stats">
                        @foreach ($teacherSummary as $status => $total)
                            <div class="stat-card"><span><small>{{ $status }}</small><strong>{{ $total }}</strong><small>{{ number_format($teacherSummaryPercentages[$status] ?? 0, 1, ',', '.') }}% dari jurnal berstatus</small></span></div>
                        @endforeach
                    </div>
                    <p class="eyebrow">Jurnal yang belum diisi bukan bukti guru tidak hadir. Verifikasi jurnal terpisah dari status kehadiran.</p>
                @else
                    <div class="empty">Belum ada status kehadiran guru yang tercatat pada periode ini.</div>
                @endif
            @endif
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
            <nav class="statistics-breadcrumb" aria-label="Tingkat statistik">
                <a href="{{ route('laporan.absensi', array_merge(request()->query(), ['program' => null, 'kelas_id' => null])) }}">{{ in_array(auth()->user()->role, ['admin', 'waka'], true) ? 'Sekolah' : 'Lingkup akses' }}</a>
                @if ($selectedProgram !== '')<span aria-hidden="true">›</span><a href="{{ route('laporan.absensi', array_merge(request()->query(), ['program' => $selectedProgram, 'kelas_id' => null])) }}">{{ $selectedProgram }}</a>@endif
                @if ($selectedClass)<span aria-hidden="true">›</span><strong>{{ $selectedClass->nama_kelas }}</strong>@endif
            </nav>
            <p class="eyebrow">Satu catatan siswa mewakili satu jurnal/sesi. Dispensasi ditampilkan terpisah dan tidak masuk penyebut tingkat hadir.</p>
            @if ($attendanceGroups->isEmpty())
                <div class="empty">Belum ada data statistik untuk filter ini.</div>
            @else
                @foreach (['siswa' => 'Siswa', 'guru' => 'Guru'] as $audience => $audienceLabel)
                    <h3>Kehadiran {{ strtolower($audienceLabel) }}</h3>
                    @php($audienceHasData = $audience === 'siswa' ? $hasStudentAttendance : $hasTeacherAttendance)
                    @if ($audienceHasData)
                    <div class="table-wrap"><table>
                        <thead><tr><th>Jurusan / kelas</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Dispensasi</th>@if ($audience === 'siswa')<th>Tingkat hadir</th>@endif @if ($audience === 'guru')<th>Dinas</th><th>Tanpa keterangan</th>@endif</tr></thead>
                        <tbody>
                            @php($overall = array_fill_keys(['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi', 'Dinas', 'Tanpa Keterangan'], 0))
                            @foreach ($attendanceGroups as $jurusan => $group)
                                @foreach ($group[$audience] as $status => $count)
                                    @php($overall[$status] += $count)
                                @endforeach
                            @endforeach
                            @php($overallTotal = array_sum($overall))
                            @php($eligibleTotal = $overallTotal - $overall['Dispensasi'])
                            <tr><th>{{ $selectedClass?->nama_kelas ?? ($selectedProgram !== '' ? $selectedProgram : 'Sekolah') }}</th>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $overall[$status] }} <small>({{ $overallTotal ? number_format($overall[$status] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td>@endforeach @if ($audience === 'siswa')<td>{{ $eligibleTotal > 0 ? number_format($overall['Hadir'] * 100 / $eligibleTotal, 1, ',', '.') .'%' : 'Belum ada catatan' }}</td>@endif @if ($audience === 'guru')<td>{{ $overall['Dinas'] }} <small>({{ $overallTotal ? number_format($overall['Dinas'] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td><td>{{ $overall['Tanpa Keterangan'] }} <small>({{ $overallTotal ? number_format($overall['Tanpa Keterangan'] * 100 / $overallTotal, 1, ',', '.') : '0,0' }}%)</small></td>@endif</tr>
                            @foreach ($attendanceGroups as $jurusan => $group)
                                @if ($group[$audience] !== [])
                                <tr><th><a href="{{ route('laporan.absensi', array_merge(request()->query(), ['program' => $jurusan, 'kelas_id' => null])) }}">{{ $jurusan }}</a></th>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $group[$audience][$status] ?? 0 }} <small>({{ number_format($group[$audience.'_persen'][$status] ?? 0, 1, ',', '.') }}%)</small></td>@endforeach @if ($audience === 'siswa')<td>{{ $group['siswa_hadir_persen'] !== null ? number_format($group['siswa_hadir_persen'], 1, ',', '.') .'%' : 'Belum ada catatan' }}</td>@endif @if ($audience === 'guru')<td>{{ $group[$audience]['Dinas'] ?? 0 }} <small>({{ number_format($group[$audience.'_persen']['Dinas'] ?? 0, 1, ',', '.') }}%)</small></td><td>{{ $group[$audience]['Tanpa Keterangan'] ?? 0 }} <small>({{ number_format($group[$audience.'_persen']['Tanpa Keterangan'] ?? 0, 1, ',', '.') }}%)</small></td>@endif</tr>
                                @foreach ($group['classes'] as $classStat)
                                    @if ($classStat[$audience] !== [])
                                    <tr><td>&nbsp;&nbsp;<a href="{{ route('laporan.absensi', array_merge(request()->query(), ['program' => $jurusan, 'kelas_id' => $classStat['kelas']->id])) }}">{{ $classStat['kelas']->nama_kelas }}</a></td>@foreach (['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'] as $status)<td>{{ $classStat[$audience][$status] ?? 0 }} <small>({{ number_format($classStat[$audience.'_persen'][$status] ?? 0, 1, ',', '.') }}%)</small></td>@endforeach @if ($audience === 'siswa')<td>{{ $classStat['siswa_hadir_persen'] !== null ? number_format($classStat['siswa_hadir_persen'], 1, ',', '.') .'%' : 'Belum ada catatan' }}</td>@endif @if ($audience === 'guru')<td>{{ $classStat[$audience]['Dinas'] ?? 0 }} <small>({{ number_format($classStat[$audience.'_persen']['Dinas'] ?? 0, 1, ',', '.') }}%)</small></td><td>{{ $classStat[$audience]['Tanpa Keterangan'] ?? 0 }} <small>({{ number_format($classStat[$audience.'_persen']['Tanpa Keterangan'] ?? 0, 1, ',', '.') }}%)</small></td>@endif</tr>
                                    @endif
                                @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table></div>
                    @else
                        <div class="empty">Belum ada catatan kehadiran {{ strtolower($audienceLabel) }} pada periode ini.</div>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Catatan ketidakhadiran</h2><span class="eyebrow">{{ $periodLabel }} · maksimal 10</span></div>
        <div class="panel-body">
            <h3>Siswa</h3>
            @if ($studentAbsenceLeaders->isNotEmpty())
                <div class="table-wrap"><table><thead><tr><th>Siswa</th><th>Catatan tidak hadir</th><th>Sesi non-dispensasi tercatat</th><th>Persentase tidak hadir</th></tr></thead><tbody>@foreach ($studentAbsenceLeaders as $student)<tr><td>{{ $student->nama_siswa }}</td><td>{{ $student->absence_count }}</td><td>{{ $student->eligible_sessions }}</td><td>{{ $student->eligible_sessions > 0 ? number_format($student->absence_count * 100 / $student->eligible_sessions, 1, ',', '.') .'%' : 'Belum dapat dihitung' }}</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="empty">Belum ada catatan siswa sakit, izin, atau alpa pada periode ini.</div>
            @endif
            <h3>Guru</h3>
            @if ($teacherAbsenceLeaders->isNotEmpty())
                <div class="table-wrap"><table><thead><tr><th>Guru</th><th>Izin / sakit / tanpa keterangan</th><th>Jurnal berstatus tercatat</th></tr></thead><tbody>@foreach ($teacherAbsenceLeaders as $teacher)<tr><td>{{ $teacher->nama_guru }}</td><td>{{ $teacher->absence_count }}</td><td>{{ $teacher->recorded_journals }}</td></tr>@endforeach</tbody></table></div>
            @else
                <div class="empty">Belum ada catatan status izin, sakit, atau tanpa keterangan untuk guru.</div>
            @endif
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data absensi</h2><span class="eyebrow">{{ $absensis->total() }} data</span></div>
        @if ($absensis->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Guru</th><th>Mapel</th><th>Kehadiran siswa</th><th>Verifikasi jurnal</th><th>Catatan</th></tr></thead><tbody>@foreach ($absensis as $absensi)<tr><td>{{ $absensi->jurnal->tanggal->format('d M Y') }}</td><td>{{ $absensi->siswa->nama_siswa }}</td><td>{{ $absensi->jurnal->kelas->nama_kelas }}</td><td>{{ $absensi->jurnal->guru->nama_guru }}</td><td>{{ $absensi->jurnal->mapel->nama_mapel }}</td><td>{{ ['H' => 'Hadir', 'S' => 'Sakit', 'I' => 'Izin', 'A' => 'Alpa', 'D' => 'Dispensasi'][$absensi->status] ?? $absensi->status }}</td><td><span class="status {{ $absensi->jurnal->status_verifikasi === 'Disetujui' ? 'approved' : ($absensi->jurnal->status_verifikasi === 'Ditolak' ? 'rejected' : 'pending') }}">{{ $absensi->jurnal->status_verifikasi }}</span></td><td>{{ $absensi->catatan ?: '-' }}</td></tr>@endforeach</tbody></table></div>
            <div class="panel-body">{{ $absensis->links() }}</div>
        @else
            <div class="empty">Belum ada data absensi pada filter ini.</div>
        @endif
    </section>
@endsection

@push('styles')
<style>
    .statistics-breadcrumb { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 12px; }
    .statistics-breadcrumb a { font-weight: 600; }
    .statistics-breadcrumb strong { color: var(--ink); }
</style>
@endpush
