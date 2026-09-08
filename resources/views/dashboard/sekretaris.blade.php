@extends('layouts.app')

@section('title', 'Dashboard Sekretaris')

@section('content')
    <h2>Dashboard Sekretaris</h2>
    <p>Halo, {{ auth()->user()->name }} 👋</p>
@endsection


@push('styles')

<style>

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: white;
        border: 1px solid #e1e6ed;
        border-radius: 9px;
        padding: 18px;
        min-height: 120px;

        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .stat-label {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 9px;
    }

    .stat-number {
        font-size: 30px;
        line-height: 1;
        margin-bottom: 7px;
        color: #182234;
    }

    .stat-unit {
        color: #9ca3af;
        font-size: 12px;
    }

    .stat-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #edf4ff;
        color: #3976ee;
        font-weight: 700;
    }

    .warning {
        color: #f59e0b;
    }

    .warning-icon {
        background: #fff4dd;
        color: #f59e0b;
    }

    .success {
        color: #15945f;
    }

    .success-icon {
        background: #e7f8ef;
        color: #15945f;
    }

    .danger {
        color: #d64545;
    }

    .danger-icon {
        background: #ffe7e7;
        color: #d64545;
    }


    .table-card {
        background: white;
        border: 1px solid #e1e6ed;
        border-radius: 8px;
        overflow: hidden;
    }

    .table-header {
        padding: 15px 17px;

        display: flex;
        justify-content: space-between;
        align-items: center;

        border-bottom: 1px solid #e5e7eb;
    }

    .table-header h3 {
        font-size: 14px;
        color: #1f2937;
        margin-bottom: 3px;
    }

    .table-header p {
        font-size: 11px;
        color: #6b7280;
    }

    .table-header a {
        text-decoration: none;
        color: #3976ee;
        font-size: 11px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        min-width: 1000px;
        border-collapse: collapse;
    }

    thead {
        background: #f8fafc;
    }

    th {
        text-align: left;

        padding: 11px 13px;

        color: #6b7280;
        font-size: 10px;
        font-weight: 600;

        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 13px;

        font-size: 12px;
        color: #4b5563;

        border-bottom: 1px solid #edf0f3;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #fafbfd;
    }


    .status,
    .attendance {
        display: inline-block;
        padding: 4px 8px;

        border-radius: 20px;

        font-size: 10px;
        font-weight: 600;
    }

    .status.pending {
        background: #fff4dd;
        color: #d97706;
    }

    .attendance.hadir {
        background: #e8f5ff;
        color: #2973b9;
    }

    .attendance.tidak-hadir {
        background: #f2f3f5;
        color: #667085;
    }


    .action-cell {
        text-align: center;
    }

    .detail-btn {
        display: inline-block;

        background: #3478f6;
        color: white;

        text-decoration: none;

        font-size: 10px;
        font-weight: 600;

        padding: 6px 10px;
        border-radius: 5px;
    }


    @media(max-width: 1150px) {

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media(max-width: 700px) {

        .stats-grid {
            grid-template-columns: 1fr;
        }

    }

</style>

@endpush