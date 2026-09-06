@extends('layouts.app')

@section('title', 'Dashboard Piket')

@section('content')
    <h2>Dashboard Piket</h2>
    <p>Halo, {{ auth()->user()->name }} 👋</p>
@endsection