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
                    <div class="field"><label for="guru_id">Guru</label><select id="guru_id" name="guru_id" required>
                        <option value="">Pilih guru</option>
                        @foreach ($allGurus as $guru)
                            <option value="{{ $guru->id }}" @selected(old('guru_id') == $guru->id)>{{ $guru->nama_guru }}</option>
                        @endforeach
                    </select>@error('guru_id')<small class="error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="tanggal">Tanggal piket</label><input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" min="{{ today()->toDateString() }}" required><small id="tanggal-hari">{{ today()->locale('id')->translatedFormat('l') }}</small>@error('tanggal')<small class="error">{{ $message }}</small>@enderror</div>
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
                        @if ($guru->jadwalPikets->isNotEmpty())<strong>Aktif piket</strong>@else Tidak ada jadwal @endif
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
        <div class="panel-head"><h2>Jadwal mendatang</h2></div>
        <div class="panel-body"><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Guru piket</th><th>Ubah jadwal</th><th></th></tr></thead><tbody>
            @forelse ($jadwals as $jadwal)
                <tr>
                    <td>{{ $jadwal->tanggal->locale('id')->translatedFormat('l, d/m/Y') }}</td><td>{{ $jadwal->guru->nama_guru }}</td>
                    <td><details><summary>Edit jadwal</summary><form action="{{ route('admin.piket.update', $jadwal) }}" method="POST">@csrf @method('PUT')
                        <input type="hidden" name="guru_id" value="{{ $jadwal->guru_id }}">
                        <label>Tanggal piket<input class="piket-edit-date" type="date" name="tanggal" value="{{ $jadwal->tanggal->toDateString() }}" min="{{ today()->toDateString() }}" required></label>
                        <small>Hari: <span data-day-output>{{ $jadwal->tanggal->locale('id')->translatedFormat('l') }}</span></small>
                        <button class="btn btn-muted" type="submit">Simpan perubahan</button>
                    </form></details></td>
                    <td><form action="{{ route('admin.piket.destroy', $jadwal) }}" method="POST" class="inline-form" data-confirm="Nonaktifkan jadwal piket {{ $jadwal->guru->nama_guru }} tanggal {{ $jadwal->tanggal->format('d/m/Y') }}?">@csrf @method('DELETE')<button class="btn btn-muted" type="submit">Nonaktifkan piket</button></form></td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada jadwal piket yang akan datang.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>
    <script>
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
