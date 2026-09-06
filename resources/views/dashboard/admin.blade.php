@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h2>Dashboard Admin</h2>

    <p>Halo, {{ auth()->user()->name }} 👋</p>
    <p>Selamat datang di sistem Jurnal Mengajar.</p>
@endsection