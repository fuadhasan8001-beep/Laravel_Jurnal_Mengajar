@extends('layouts.app')

@section('title', 'Dashboard Sekretaris')

@section('content')
    <h2>Dashboard Sekretaris</h2>
    <p>Halo, {{ auth()->user()->name }} 👋</p>
@endsection