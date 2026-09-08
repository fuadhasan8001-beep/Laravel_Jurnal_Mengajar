@extends('layouts.app')
@section('title', 'Tambah Guru')
@section('content')<div class="page-head"><div><h1>Tambah guru</h1><p>Buat profil dan akun login guru.</p></div></div><section class="panel form-panel"><div class="panel-head"><h2>Data guru</h2></div><div class="panel-body">@include('admin.gurus._form')</div></section>@endsection
