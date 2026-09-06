@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
    <h2>Dashboard Guru</h2>
    <p>Halo, {{ auth()->user()->name }} 👋</p>
@endsection