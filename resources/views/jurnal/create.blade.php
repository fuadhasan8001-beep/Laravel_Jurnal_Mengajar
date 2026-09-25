@extends('layouts.app')

@section('title', 'Isi Jurnal')

@section('content')
    <div class="page-head">
        <div>
            <h1>Isi jurnal</h1>
            <p>{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

    <section class="panel form-panel">
        <div class="panel-head">
            <h2>Detail jurnal</h2>
            <span class="eyebrow">{{ $activeSession ? 'Sedang berlangsung' : 'Jadwal hari ini' }}</span>
        </div>
        <div class="panel-body">
            @if ($scheduleConflict)
                <p role="alert">Ada jadwal mengajar yang bertabrakan. Pilih kelas yang benar atau hubungi admin untuk memperbaiki jadwal.</p>
            @endif
            @if ($existingJournal)
                <p role="status">Jurnal untuk sesi ini sudah diisi.</p>
                <p>{{ $activeSession['kelas'] }} · {{ $activeSession['mapel'] }} · {{ substr($activeSession['jam_mulai'], 0, 5) }} - {{ substr($activeSession['jam_selesai'], 0, 5) }}</p>
                <div class="form-actions">
                    <a class="btn btn-muted" href="{{ route('jurnal.show', $existingJournal) }}">Lihat jurnal</a>
                    @if ($existingJournal->status_verifikasi === 'Menunggu')
                        <a class="btn" href="{{ route('jurnal.edit', $existingJournal) }}">Edit jurnal</a>
                    @endif
                </div>
                @foreach ($sessions->where('active', true)->where('id', '!=', $activeSession['id']) as $otherSession)
                    <p><a href="{{ route('jurnal.create', ['jadwal_id' => $otherSession['id']]) }}">{{ $otherSession['kelas'] }} · {{ $otherSession['mapel'] }}</a></p>
                @endforeach
            @elseif ($activeSession)
                @include('jurnal._form')
            @else
                <p role="status">{{ $scheduleConflict ? 'Ada jadwal mengajar yang bertabrakan. Hubungi admin untuk memperbaiki jadwal.' : 'Tidak ada jadwal mengajar yang sedang berlangsung.' }}</p>
                @if ($sessions->isNotEmpty())
                    <div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Kelas</th><th>Mata pelajaran</th></tr></thead><tbody>
                        @foreach ($sessions as $session)
                            <tr><td>{{ substr($session['jam_mulai'], 0, 5) }} - {{ substr($session['jam_selesai'], 0, 5) }}</td><td>{{ $session['kelas'] }}</td><td>{{ $session['mapel'] }}</td></tr>
                        @endforeach
                    </tbody></table></div>
                @endif
                <div class="form-actions"><a class="btn btn-muted" href="{{ route('jurnal.index') }}">Kembali ke jurnal</a><a class="btn" href="{{ route('jurnal.create') }}">Perbarui</a></div>
            @endif
        </div>
    </section>
@endsection
