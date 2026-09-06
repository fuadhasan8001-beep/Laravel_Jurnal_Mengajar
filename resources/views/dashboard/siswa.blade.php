@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
    <h2>Dashboard Siswa</h2>
    <p>Halo, {{ auth()->user()->name }} 👋</p>
@endsection