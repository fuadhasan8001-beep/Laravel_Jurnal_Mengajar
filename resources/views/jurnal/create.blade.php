@extends('layouts.app')

@section('title', 'Tambah Jurnal')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tambah jurnal</h1>
            <p>Catat kegiatan pembelajaran hari ini.</p>
        </div>
    </div>

    <section class="panel form-panel">
        <div class="panel-head">
            <h2>Detail jurnal</h2>
            <span class="eyebrow">Lengkapi data pembelajaran</span>
        </div>
        <div class="panel-body">
            @include('jurnal._form')
        </div>
    </section>
@endsection
