@extends('layouts.app')

@section('title', 'Rekap Jurnal')

@section('content')
    <div class="page-head">
        <div><h1>Rekap jurnal mengajar</h1><p>Gunakan filter untuk meninjau dan mengunduh jurnal.</p></div>
        <a class="btn" href="{{ route(request()->routeIs('piket.rekap-jurnal') ? 'piket.rekap-jurnal.export' : 'laporan.jurnal.export', request()->query()) }}">Export CSV</a>
    </div>
    <section class="panel">
        <div class="panel-body">
            <form method="GET" action="{{ route(request()->routeIs('piket.rekap-jurnal') ? 'piket.rekap-jurnal' : 'laporan.jurnal') }}">
                <div class="form-grid">
                    <div class="field"><label for="tanggal_mulai">Dari tanggal</label><input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"></div>
                    <div class="field"><label for="tanggal_selesai">Sampai tanggal</label><input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}"></div>
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id"><option value="">Semua guru</option>@foreach ($gurus as $guru)<option value="{{ $guru->id }}" @selected((string) request('guru_id') === (string) $guru->id)>{{ $guru->nama_guru }}</option>@endforeach</select></div>
                    @unless (auth()->user()->role === 'sekretaris')
                        <div class="field"><label for="kelas_id">Kelas</label><select id="kelas_id" name="kelas_id"><option value="">Semua kelas</option>@foreach ($kelas as $item)<option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>@endforeach</select></div>
                    @endunless
                    <div class="field"><label for="mapel_id">Mapel</label><select id="mapel_id" name="mapel_id"><option value="">Semua mapel</option>@foreach ($mapels as $mapel)<option value="{{ $mapel->id }}" @selected((string) request('mapel_id') === (string) $mapel->id)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div class="field"><label for="status_verifikasi">Status</label><select id="status_verifikasi" name="status_verifikasi"><option value="">Semua status</option>@foreach (['Menunggu', 'Disetujui', 'Ditolak'] as $status)<option value="{{ $status }}" @selected(request('status_verifikasi') === $status)>{{ $status }}</option>@endforeach</select></div>
                </div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route(request()->routeIs('piket.rekap-jurnal') ? 'piket.rekap-jurnal' : 'laporan.jurnal') }}">Reset</a><button class="btn" type="submit">Terapkan filter</button></div>
            </form>
        </div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Data jurnal</h2><span class="eyebrow">{{ $jurnals->total() }} data</span></div>
        @if ($jurnals->isNotEmpty())
            <div class="table-wrap responsive-card-table-wrap"><table class="responsive-card-table"><thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Jam</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach ($jurnals as $jurnal)
                    <tr>
                        <td data-label="Tanggal">{{ $jurnal->tanggal->format('d M Y') }}</td>
                        <td data-label="Guru">{{ $jurnal->guru->nama_guru }}</td>
                        <td data-label="Kelas">{{ $jurnal->kelas->nama_kelas }}</td>
                        <td data-label="Mapel">{{ $jurnal->mapel->nama_mapel }}</td>
                        <td data-label="Jam">{{ $jurnal->jamMulai->jam_ke }} - {{ $jurnal->jamSelesai->jam_ke }}</td>
                        <td data-label="Status">{{ $jurnal->status_verifikasi }}</td>
                        <td class="report-journal-action"><button class="btn btn-muted" type="button" data-open-dialog="report-jurnal-detail-{{ $jurnal->id }}">Tampilkan detail</button></td>
                    </tr>
                    <dialog class="journal-detail-dialog" id="report-jurnal-detail-{{ $jurnal->id }}" aria-labelledby="report-jurnal-title-{{ $jurnal->id }}">
                        <div class="journal-detail-head"><h2 id="report-jurnal-title-{{ $jurnal->id }}">Detail jurnal</h2><form method="dialog"><button class="btn btn-muted" aria-label="Tutup detail">Tutup</button></form></div>
                        <dl class="detail-grid">
                            <div class="detail-item"><dt>Tanggal</dt><dd>{{ $jurnal->tanggal->format('d M Y') }}</dd></div>
                            <div class="detail-item"><dt>Guru</dt><dd>{{ $jurnal->guru->nama_guru }}</dd></div>
                            <div class="detail-item"><dt>Kelas</dt><dd>{{ $jurnal->kelas->nama_kelas }}</dd></div>
                            <div class="detail-item"><dt>Mata pelajaran</dt><dd>{{ $jurnal->mapel->nama_mapel }}</dd></div>
                            <div class="detail-item"><dt>Jam</dt><dd>{{ $jurnal->jamMulai->jam_ke }} - {{ $jurnal->jamSelesai->jam_ke }}</dd></div>
                            <div class="detail-item"><dt>Status verifikasi</dt><dd>{{ $jurnal->status_verifikasi }}</dd></div>
                            <div class="detail-item detail-item-full"><dt>Materi</dt><dd>{{ $jurnal->materi ?: '-' }}</dd></div>
                            <div class="detail-item detail-item-full"><dt>Tugas</dt><dd>{{ $jurnal->tugas ?: '-' }}</dd></div>
                        </dl>
                    </dialog>
                @endforeach
            </tbody></table></div>
            <div class="panel-body">{{ $jurnals->links() }}</div>
        @else
            <div class="empty">Belum ada jurnal pada periode ini.</div>
        @endif
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Deteksi pengisian jurnal</h2><span class="eyebrow">{{ $monitoringDate->format('d M Y') }}</span></div>
        <div class="panel-body">
            <form method="GET" action="{{ route(request()->routeIs('piket.rekap-jurnal') ? 'piket.rekap-jurnal' : 'laporan.jurnal') }}">
                @foreach (request()->except('monitoring_date', 'page') as $key => $value)
                    @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                @endforeach
                <div class="form-actions" style="justify-content:flex-start;margin-top:0">
                    <div class="field"><label for="monitoring_date">Tanggal monitoring</label><input id="monitoring_date" type="date" name="monitoring_date" value="{{ $monitoringDate->toDateString() }}"></div>
                    <button class="btn" type="submit">Periksa jadwal</button>
                </div>
            </form>
        </div>
        @if ($monitoring->isNotEmpty())
            <div class="table-wrap responsive-card-table-wrap"><table class="responsive-card-table"><thead><tr><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Jam</th><th>Status</th></tr></thead><tbody>@foreach ($monitoring as $item)<tr><td data-label="Guru">{{ $item['guru'] }}</td><td data-label="Kelas">{{ $item['kelas'] }}</td><td data-label="Mapel">{{ $item['mapel'] }}</td><td data-label="Jam">{{ $item['jam'] }}</td><td data-label="Status">{{ $item['status'] }}</td></tr>@endforeach</tbody></table></div>
        @else
            <div class="empty">Tidak ada jadwal aktif pada tanggal ini.</div>
        @endif
    </section>
@endsection

@push('styles')
<style>
    @media (max-width: 720px) {
        .report-journal-action { grid-column: 1 / -1; }
        .report-journal-action::before { content: none !important; }
        .report-journal-action .btn { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal());
    });
</script>
@endpush
