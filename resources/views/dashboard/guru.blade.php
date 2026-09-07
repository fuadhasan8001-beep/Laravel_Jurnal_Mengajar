@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')

    <h1 class="page-title">Dashboard Guru</h1>
    <p class="page-subtitle">Ringkasan aktivitas jurnal mengajar Anda.</p>

    <div class="stats-grid">

        <div class="stat-card">
            <div>
                <p class="stat-label">Jurnal Hari Ini</p>
                <h2 class="stat-number">2</h2>
                <p class="stat-unit">jurnal</p>
            </div>
            <div class="stat-icon">▣</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Jurnal Minggu Ini</p>
                <h2 class="stat-number">8</h2>
                <p class="stat-unit">jurnal</p>
            </div>
            <div class="stat-icon">▣</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Belum Diverifikasi</p>
                <h2 class="stat-number warning">3</h2>
                <p class="stat-unit">menunggu</p>
            </div>
            <div class="stat-icon warning-icon">⌛</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Perlu Revisi</p>
                <h2 class="stat-number danger">1</h2>
                <p class="stat-unit">jurnal</p>
            </div>
            <div class="stat-icon danger-icon">!</div>
        </div>

    </div>


    <div class="quick-section">

        <div>
            <h3>Jurnal Terbaru</h3>
            <p>Jurnal mengajar yang terakhir Anda buat.</p>
        </div>

        <a href="#" class="add-btn">
            + Tambah Jurnal
        </a>

    </div>


    <div class="table-card">

        <div class="table-header">
            <h3>Jurnal Saya</h3>

            <a href="#">
                Lihat Semua →
            </a>
        </div>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>JAM</th>
                        <th>KELAS</th>
                        <th>MATA PELAJARAN</th>
                        <th>MATERI / KEGIATAN</th>
                        <th>KEHADIRAN</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>07 Sep 2026</td>

                        <td>
                            Jam 1 - 3
                        </td>

                        <td>
                            XI RPL 2
                        </td>

                        <td>
                            Pemrograman Web
                        </td>

                        <td>
                            Routing dan Controller Laravel
                        </td>

                        <td>
                            <span class="attendance hadir">
                                Hadir
                            </span>
                        </td>

                        <td>
                            <span class="status pending">
                                Menunggu
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" title="Lihat Detail">◉</a>
                        </td>
                    </tr>


                    <tr>
                        <td>06 Sep 2026</td>

                        <td>
                            Jam 4 - 6
                        </td>

                        <td>
                            XI RPL 1
                        </td>

                        <td>
                            Basis Data
                        </td>

                        <td>
                            Normalisasi Basis Data
                        </td>

                        <td>
                            <span class="attendance hadir">
                                Hadir
                            </span>
                        </td>

                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" title="Lihat Detail">◉</a>
                        </td>
                    </tr>


                    <tr>
                        <td>05 Sep 2026</td>

                        <td>
                            Jam 7 - 8
                        </td>

                        <td>
                            X RPL 2
                        </td>

                        <td>
                            Pemrograman Dasar
                        </td>

                        <td>
                            Percabangan dan Perulangan
                        </td>

                        <td>
                            <span class="attendance tidak-hadir">
                                Tidak Hadir
                            </span>
                        </td>

                        <td>
                            <span class="status revision">
                                Perlu Revisi
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" title="Lihat Detail">◉</a>
                        </td>
                    </tr>


                    <tr>
                        <td>04 Sep 2026</td>

                        <td>
                            Jam 1 - 2
                        </td>

                        <td>
                            XII RPL 1
                        </td>

                        <td>
                            Pemrograman Berorientasi Objek
                        </td>

                        <td>
                            Inheritance dan Polymorphism
                        </td>

                        <td>
                            <span class="attendance hadir">
                                Hadir
                            </span>
                        </td>

                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" title="Lihat Detail">◉</a>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

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
        background: #ffffff;
        border: 1px solid #e1e6ed;
        border-radius: 9px;
        padding: 18px;
        min-height: 120px;

        display: flex;
        align-items: flex-start;
        justify-content: space-between;
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
        font-size: 12px;
        color: #9ca3af;
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

    .danger {
        color: #e5484d;
    }

    .danger-icon {
        background: #ffe9e9;
        color: #e5484d;
    }


    .quick-section {
        display: flex;
        justify-content: space-between;
        align-items: center;

        margin-bottom: 13px;
    }

    .quick-section h3 {
        font-size: 16px;
        margin-bottom: 3px;
        color: #1f2937;
    }

    .quick-section p {
        color: #6b7280;
        font-size: 12px;
    }

    .add-btn {
        text-decoration: none;
        background: #3478f6;
        color: white;

        padding: 9px 14px;
        border-radius: 6px;

        font-size: 12px;
        font-weight: 600;
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
        border-collapse: collapse;
        min-width: 1000px;
    }

    thead {
        background: #f8fafc;
    }

    th {
        text-align: left;

        padding: 11px 13px;

        font-size: 10px;
        font-weight: 600;

        color: #6b7280;

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

    .status.approved {
        background: #e7f8ef;
        color: #15945f;
    }

    .status.revision {
        background: #ffe7e7;
        color: #d64545;
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

    .action-cell a {
        text-decoration: none;
        color: #64748b;
        font-size: 15px;
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

        .quick-section {
            align-items: flex-start;
            gap: 14px;
            flex-direction: column;
        }

    }

</style>

@endpush