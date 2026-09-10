@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
    <div class="page-head"><div><h1>Tambah jadwal</h1><p>Buat jadwal mengajar baru.</p></div></div>
    <section class="panel form-panel">
        <div class="panel-head"><h2>Detail jadwal</h2><span class="eyebrow">Pastikan guru dan kelas tidak bentrok</span></div>
        <div class="panel-body">@include('jadwal._form')</div>
    </section>
@endsection