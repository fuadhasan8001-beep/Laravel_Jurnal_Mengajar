@extends('layouts.app')
@section('title', 'Edit Siswa')
@section('content')<div class="page-head"><div><h1>Edit siswa</h1><p>Perbarui data kelas dan status akun siswa.</p></div></div><section class="panel form-panel"><div class="panel-head"><h2>{{ $siswa->nama_siswa }}</h2></div><div class="panel-body">@include('admin.siswas._form')</div></section>@endsection
