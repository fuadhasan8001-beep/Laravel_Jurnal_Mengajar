@extends('layouts.app')

@section('title', 'Ajukan Dispensasi')

@section('content')
    <div class="page-head">
        <div>
            <h1>Ajukan dispensasi</h1>
            <p>Lengkapi detail kegiatan dan bukti agar pengajuan dapat diverifikasi.</p>
        </div><a
            class="btn btn-muted"
            href="{{ route('dispensasi.index') }}"
        >Kembali</a>
    </div>
    <section class="panel form-panel">
        <div class="panel-head">
            <h2>Detail pengajuan</h2><span class="eyebrow">Semua field bertanda wajib diisi</span>
        </div>
        <div class="panel-body">
            <form
                action="{{ route('dispensasi.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                <div class="form-grid">
                    <div class="field"><label for="tanggal">Tanggal dispensasi</label><input
                            id="tanggal"
                            type="date"
                            name="tanggal"
                            value="{{ old('tanggal') }}"
                            required
                        ></div>
                    <div class="field"><label for="jam_mulai_id">Jam mulai</label><select
                            id="jam_mulai_id"
                            name="jam_mulai_id"
                            required
                        >
                            <option value="">Pilih jam mulai</option>
                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    @selected(old('jam_mulai_id') == $jam->id)
                                >
                                    Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} -
                                    {{ $jam->jam_selesai }})
                                </option>
                            @endforeach
                        </select></div>
                    <div class="field"><label for="jam_selesai_id">Jam selesai</label><select
                            id="jam_selesai_id"
                            name="jam_selesai_id"
                            required
                        >
                            <option value="">Pilih jam selesai</option>
                            @foreach ($jamPelajarans as $jam)
                                <option
                                    value="{{ $jam->id }}"
                                    @selected(old('jam_selesai_id') == $jam->id)
                                >
                                    Jam {{ $jam->jam_ke }} ({{ $jam->jam_mulai }} -
                                    {{ $jam->jam_selesai }})
                                </option>
                            @endforeach
                        </select></div>
                    <div class="field full"><label for="alasan">Alasan atau kegiatan</label>
                        <textarea
                            id="alasan"
                            name="alasan"
                            rows="5"
                            required
                        >{{ old('alasan') }}</textarea>
                    </div>
                    <div class="field full"><label for="bukti">Bukti atau surat <span
                                style="font-weight:400;color:var(--muted)"
                            >(PDF/JPG/PNG, maksimal 5 MB)</span></label><input
                            id="bukti"
                            type="file"
                            name="bukti"
                            accept=".pdf,.jpg,.jpeg,.png"
                        ></div>
                </div>
                <div class="form-actions"><a
                        class="btn btn-muted"
                        href="{{ route('dispensasi.index') }}"
                    >Batal</a><button
                        class="btn"
                        type="submit"
                    >Kirim pengajuan</button></div>
            </form>
        </div>
    </section>
@endsection
