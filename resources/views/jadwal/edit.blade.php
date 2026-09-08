@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
    <div class="page-head"><div><h1>Edit jadwal</h1><p>Perbarui jadwal mengajar.</p></div></div>
    <section class="panel form-panel">
        <div class="panel-head"><h2>Detail jadwal</h2><span class="eyebrow">{{ $jadwal->hari }}</span></div>
        <div class="panel-body">@include('jadwal._form')</div>
    </section>
@endsection
