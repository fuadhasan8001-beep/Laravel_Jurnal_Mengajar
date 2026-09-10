@extends('layouts.app')

@section('title', 'Edit Jurnal')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit jurnal</h1>
            <p>Perbarui data jurnal sebelum diverifikasi.</p>
        </div>
    </div>

    <section class="panel form-panel">
        <div class="panel-head">
            <h2>Detail jurnal</h2>
            <span class="eyebrow">Status: {{ $jurnal->status_verifikasi }}</span>
        </div>
        <div class="panel-body">
            @include('jurnal._form')
        </div>
    </section>
@endsection
