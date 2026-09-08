@extends('layouts.app')
@section('title', 'Edit Guru')
@section('content')<div class="page-head"><div><h1>Edit guru</h1><p>Perbarui profil dan status akun guru.</p></div></div><section class="panel form-panel"><div class="panel-head"><h2>{{ $guru->nama_guru }}</h2></div><div class="panel-body">@include('admin.gurus._form')</div></section>@endsection
