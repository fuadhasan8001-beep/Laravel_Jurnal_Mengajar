@extends('layouts.app')

@section('title', 'Jadwal Piket Guru')

@section('content')
    <div class="page-head">
        <div><h1>Jadwal piket guru</h1><p>Atur jadwal guru dan pantau siapa saja yang memiliki jadwal piket aktif.</p></div>
    </div>
    <section class="panel form-panel">
        <div class="panel-head"><h2>Tambah jadwal piket</h2></div>
        <div class="panel-body">
            <form action="{{ route('admin.piket.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="field"><label for="jenis_tugas">Jenis tugas</label><select id="jenis_tugas" name="jenis_tugas" required><option value="kbm" @selected(old('jenis_tugas', 'kbm') === 'kbm')>Piket KBM</option><option value="waka" @selected(old('jenis_tugas') === 'waka')>Piket Waka</option></select>@error('jenis_tugas')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field" data-assignment-field="kbm"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id">
                        <option value="">Pilih guru</option>
                        @foreach ($allGurus as $guru)
                            <option value="{{ $guru->id }}" @selected(old('guru_id') == $guru->id)>{{ $guru->nama_guru }}</option>
                        @endforeach
                    </select>@error('guru_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field" data-assignment-field="waka" hidden><label for="user_id">Petugas Waka</label><select id="user_id" name="user_id" disabled>
                        <option value="">Pilih petugas Waka</option>
                        @foreach ($allWakas as $waka)
                            <option value="{{ $waka->id }}" @selected(old('user_id') == $waka->id)>{{ $waka->name }}</option>
                        @endforeach
                    </select>@error('user_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="tanggal">Tanggal piket</label><input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" min="{{ today()->toDateString() }}" required><small id="tanggal-hari">{{ today()->locale('id')->translatedFormat('l') }}</small>@error('tanggal')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field" data-assignment-field="kbm"><label for="shift">Shift KBM</label><select id="shift" name="shift"><option value="pagi" @selected(old('shift', 'pagi') === 'pagi')>Pagi · 07.00–11.00</option><option value="siang" @selected(old('shift') === 'siang')>Siang · 11.00–15.00</option></select>@error('shift')<small class="error">{{ $message }}</small>@enderror</div>
                </div>
                <div class="field" data-assignment-field="kbm">
                    <label><input type="checkbox" name="is_koordinator" value="1" @checked(old('is_koordinator'))> Koordinator shift</label>
                    @error('is_koordinator')<small class="error">{{ $message }}</small>@enderror
                </div>
                <div class="form-actions"><button class="btn" type="submit">Simpan jadwal</button></div>
            </form>
        </div>
    </section>
    <section class="panel panel-spaced">
        <div class="panel-head"><h2>Status jadwal piket</h2><span>{{ $gurus->total() }} guru ditemukan</span></div>
        <div class="panel-body">
            <form class="piket-search" method="GET" action="{{ route('admin.piket.index') }}">
                <div class="field"><label for="q">Cari guru</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau NIP"></div>
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('admin.piket.index') }}">Reset</a><button class="btn" type="submit">Cari guru</button></div>
            </form>
            <div class="table-wrap"><table><thead><tr><th>Nama guru</th><th>NIP</th><th>Status jadwal</th><th>Jadwal piket terdekat</th></tr></thead><tbody>
            @forelse ($gurus as $guru)
                <tr>
                    <td>{{ $guru->nama_guru }}</td><td>{{ $guru->nip }}</td>
                    <td>
                        @if ($guru->jadwalPikets->isNotEmpty())<strong>Terjadwal</strong>@else Tidak ada jadwal @endif
                    </td>
                    <td>{{ $guru->jadwalPikets->first()?->tanggal->locale('id')->translatedFormat('l, d F Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Guru tidak ditemukan.</td></tr>
            @endforelse
        </tbody></table></div>
            <div class="piket-pagination">{{ $gurus->links() }}</div>
        </div>
    </section>
    <section class="panel panel-spaced">
        <div class="panel-head"><h2>Jadwal piket per bulan</h2><span class="eyebrow">KBM, koordinator, dan Piket Waka</span></div>
        <div class="panel-body">
        <form class="piket-search" method="GET" action="{{ route('admin.piket.index') }}">
            @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <div class="field"><label for="bulan">Bulan jadwal</label><input type="month" id="bulan" name="bulan" value="{{ $bulan }}"></div>
            <div class="form-actions"><button class="btn" type="submit">Tampilkan bulan</button></div>
        </form>
        <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Petugas piket</th><th>Ubah jadwal</th><th></th></tr></thead><tbody>
            @forelse ($jadwals as $jadwal)
                <tr>
                    <td>{{ $jadwal->tanggal->locale('id')->translatedFormat('l, d/m/Y') }}<br><span class="eyebrow">{{ $jadwal->shiftLabel() }}</span></td><td>{{ $jadwal->guru?->nama_guru ?? $jadwal->user?->name ?? 'Petugas tidak ditemukan' }}@if ($jadwal->is_koordinator)<br><strong>Koordinator {{ $jadwal->shift }}</strong>@endif</td>
                    <td><details><summary>Edit jadwal</summary><form action="{{ route('admin.piket.update', $jadwal) }}" method="POST">@csrf @method('PUT')
                        @if ($jadwal->guru_id)
                            <input type="hidden" name="jenis_tugas" value="kbm">
                            <input type="hidden" name="guru_id" value="{{ $jadwal->guru_id }}">
                            <input type="hidden" name="is_koordinator" value="0">
                            <label><input type="checkbox" name="is_koordinator" value="1" @checked($jadwal->is_koordinator)> Koordinator shift</label>
                            <label>Shift KBM<select name="shift" required><option value="pagi" @selected($jadwal->shift === 'pagi')>Pagi · 07.00–11.00</option><option value="siang" @selected($jadwal->shift === 'siang')>Siang · 11.00–15.00</option></select></label>
                        @else
                            <input type="hidden" name="jenis_tugas" value="waka">
                            <input type="hidden" name="user_id" value="{{ $jadwal->user_id }}">
                        @endif
                        <input type="hidden" name="tanggal" value="{{ $jadwal->tanggal->toDateString() }}">
                        <label>Tanggal piket<input type="date" value="{{ $jadwal->tanggal->toDateString() }}" min="{{ today()->toDateString() }}" readonly aria-readonly="true"></label>
                        <small>Hari: <span data-day-output>{{ $jadwal->tanggal->locale('id')->translatedFormat('l') }}</span></small>
                        <button class="btn btn-muted" type="submit">Simpan perubahan</button>
                    </form></details></td>
                    <td><form action="{{ route('admin.piket.destroy', $jadwal) }}" method="POST" class="inline-form" data-confirm="Nonaktifkan jadwal piket {{ $jadwal->guru?->nama_guru ?? $jadwal->user?->name }} tanggal {{ $jadwal->tanggal->format('d/m/Y') }}?">@csrf @method('DELETE')<button class="btn btn-muted" type="submit">Nonaktifkan piket</button></form></td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada jadwal piket pada bulan ini.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>
    <script>
        const assignmentType = document.getElementById('jenis_tugas');
        const assignmentFields = document.querySelectorAll('[data-assignment-field]');
        const syncAssignmentFields = () => {
            assignmentFields.forEach((field) => {
                const visible = field.dataset.assignmentField === assignmentType.value;
                field.hidden = !visible;
                field.querySelectorAll('select').forEach((select) => {
                    select.disabled = !visible;
                    select.required = visible;
                });
                field.querySelectorAll('input').forEach((input) => { input.disabled = !visible; });
            });
        };
        assignmentType.addEventListener('change', syncAssignmentFields);
        syncAssignmentFields();

        const scheduleDate = document.getElementById('tanggal');
        const scheduleDay = document.getElementById('tanggal-hari');
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        scheduleDate.addEventListener('change', () => {
            const selectedDate = scheduleDate.value ? new Date(`${scheduleDate.value}T00:00:00`) : null;
            scheduleDay.textContent = selectedDate ? dayNames[selectedDate.getDay()] : '';
        });

        document.querySelectorAll('.piket-edit-date').forEach((input) => {
            input.addEventListener('change', () => {
                const selectedDate = input.value ? new Date(`${input.value}T00:00:00`) : null;
                input.closest('form').querySelector('[data-day-output]').textContent = selectedDate ? dayNames[selectedDate.getDay()] : '';
            });
        });
    </script>
@endsection
