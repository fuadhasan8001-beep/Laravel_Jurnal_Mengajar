@extends('layouts.app')
@section('title', 'Tambah Siswa')
@section('content')<div class="page-head"><div><h1>Tambah siswa</h1><p>Buat data siswa dan akun login.</p></div></div><section class="panel form-panel"><div class="panel-head"><h2>Data siswa</h2></div><div class="panel-body">@include('admin.siswas._form')</div></section>@endsection
