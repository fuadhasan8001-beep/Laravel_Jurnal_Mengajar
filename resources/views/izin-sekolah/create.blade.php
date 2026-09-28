@extends('layouts.app')

@section('title', 'Catat Izin Sekolah')

@section('content')
    <div class="page-head">
        <div>
            <h1>Catat Izin atau Sakit Seharian</h1>
            <p>Catat siswa yang tidak masuk sekolah dan unggah surat dari orang tua.</p>
        </div>
        <a class="btn btn-muted" href="{{ route('piket.izin-sekolah.index') }}">Riwayat izin</a>
    </div>

    <section class="panel form-panel">
        <div class="panel-head"><h2>Data izin siswa</h2></div>
        <div class="panel-body">
            <form action="{{ route('piket.izin-sekolah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="status">Status siswa</label>
                        <select id="status" name="status" required>
                            <option value="I" @selected(old('status', 'I') === 'I')>Izin</option>
                            <option value="S" @selected(old('status') === 'S')>Sakit</option>
                        </select>
                        @error('status')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field">
                        <label for="tanggal">Tanggal izin</label>
                        <input id="tanggal" type="date" value="{{ today()->toDateString() }}" readonly>
                    </div>
                    <div class="field full">
                        <label for="student-search">Cari siswa</label>
                        <input id="student-search" type="search" placeholder="Nama, NIS, atau kelas" autocomplete="off">
                        <div class="table-wrap"><table><thead><tr><th></th><th>Siswa</th><th>Kelas</th></tr></thead><tbody>
                            @foreach ($siswas as $siswa)
                                <tr data-student-row="{{ mb_strtolower($siswa->nama_siswa.' '.$siswa->nis.' '.$siswa->kelas->nama_kelas) }}">
                                    <td><input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" @checked(collect(old('siswa_ids', []))->contains((string) $siswa->id))></td>
                                    <td>{{ $siswa->nama_siswa }} <small>{{ $siswa->nis }}</small></td>
                                    <td>{{ $siswa->kelas->nama_kelas }}</td>
                                </tr>
                            @endforeach
                        </tbody></table></div>
                        @error('siswa_ids')<small class="error">{{ $message }}</small>@enderror
                        @error('siswa_ids.*')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field full">
                        <label for="alasan">Catatan (opsional)</label>
                        <textarea id="alasan" name="alasan" rows="3" maxlength="5000">{{ old('alasan') }}</textarea>
                        @error('alasan')<small class="error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field full">
                        <label for="surat_izin">Foto surat izin orang tua (JPG, PNG, WEBP; maksimal 5 MB)</label>
                        <input id="surat_izin" type="file" name="surat_izin" accept="image/jpeg,image/png,image/webp" required>
                        @error('surat_izin')<small class="error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('piket.izin-sekolah.index') }}">Batal</a>
                    <button class="btn" type="submit">Simpan izin seharian</button>
                </div>
            </form>
        </div>
    </section>
    <script>
        document.getElementById('student-search').addEventListener('input', (event) => {
            const query = event.target.value.trim().toLocaleLowerCase('id');
            document.querySelectorAll('[data-student-row]').forEach((row) => {
                row.hidden = !row.dataset.studentRow.includes(query);
            });
        });
    </script>
@endsection