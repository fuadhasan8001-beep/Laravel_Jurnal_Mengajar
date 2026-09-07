@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

    <h1 class="page-title">Dashboard Admin</h1>
    <p class="page-subtitle">
        Kelola data utama dan pantau aktivitas sistem jurnal mengajar.
    </p>

    <div class="stats-grid">

        <div class="stat-card">
            <div>
                <p class="stat-label">Total Guru</p>
                <h2 class="stat-number">32</h2>
                <p class="stat-unit">guru terdaftar</p>
            </div>
            <div class="stat-icon">♙</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Total Siswa</p>
                <h2 class="stat-number">108</h2>
                <p class="stat-unit">siswa terdaftar</p>
            </div>
            <div class="stat-icon">♙</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Jurnal Hari Ini</p>
                <h2 class="stat-number success">18</h2>
                <p class="stat-unit">jurnal masuk</p>
            </div>
            <div class="stat-icon success-icon">▣</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Dispensasi Menunggu</p>
                <h2 class="stat-number warning">3</h2>
                <p class="stat-unit">perlu verifikasi</p>
            </div>
            <div class="stat-icon warning-icon">⌛</div>
        </div>

    </div>


    <div class="admin-grid">

        <div class="panel">

            <div class="panel-header">
                <div>
                    <h3>Ringkasan Data</h3>
                    <p>Data utama yang tersedia di sistem.</p>
                </div>
            </div>

            <div class="data-list">

                <a href="#" class="data-item">
                    <div>
                        <strong>Data Guru</strong>
                        <span>Kelola akun dan informasi guru</span>
                    </div>
                    <b>32</b>
                </a>

                <a href="#" class="data-item">
                    <div>
                        <strong>Data Siswa</strong>
                        <span>Kelola siswa dan kelas</span>
                    </div>
                    <b>108</b>
                </a>

                <a href="#" class="data-item">
                    <div>
                        <strong>Data Sekretaris</strong>
                        <span>Kelola sekretaris setiap kelas</span>
                    </div>
                    <b>6</b>
                </a>

                <a href="#" class="data-item">
                    <div>
                        <strong>Data Piket</strong>
                        <span>Kelola akun petugas piket</span>
                    </div>
                    <b>5</b>
                </a>

                <a href="#" class="data-item">
                    <div>
                        <strong>Kelas</strong>
                        <span>Kelola kelas RPL</span>
                    </div>
                    <b>6</b>
                </a>

                <a href="#" class="data-item">
                    <div>
                        <strong>Mata Pelajaran</strong>
                        <span>Kelola data mata pelajaran</span>
                    </div>
                    <b>14</b>
                </a>

            </div>

        </div>


        <div class="panel">

            <div class="panel-header">
                <div>
                    <h3>Akses Cepat</h3>
                    <p>Menu administrasi yang sering digunakan.</p>
                </div>
            </div>

            <div class="quick-menu">

                <a href="#" class="quick-card">
                    <div class="quick-icon">▣</div>
                    <div>
                        <strong>Data Jurnal</strong>
                        <span>Lihat seluruh jurnal guru</span>
                    </div>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon">▤</div>
                    <div>
                        <strong>Rekapitulasi</strong>
                        <span>Harian, mingguan, bulanan dan tahunan</span>
                    </div>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon">◷</div>
                    <div>
                        <strong>Jam Pelajaran</strong>
                        <span>Atur jam pelajaran sekolah</span>
                    </div>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon">✓</div>
                    <div>
                        <strong>Verifikasi Dispensasi</strong>
                        <span>Periksa pengajuan yang disetujui Piket</span>
                    </div>
                </a>

            </div>

        </div>

    </div>


    <div class="section-header">

        <div>
            <h3>Dispensasi Menunggu Verifikasi Admin</h3>
            <p>
                Pengajuan siswa yang sudah diverifikasi oleh petugas piket.
            </p>
        </div>

        <a href="#" class="view-all">
            Lihat Semua →
        </a>

    </div>


    <div class="table-card">

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>SISWA</th>
                        <th>KELAS</th>
                        <th>TANGGAL</th>
                        <th>JAM</th>
                        <th>KEGIATAN / ALASAN</th>
                        <th>STATUS PIKET</th>
                        <th>STATUS ADMIN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>Skandinavia</td>
                        <td>XI RPL 2</td>
                        <td>07 Sep 2026</td>
                        <td>Jam 3 - 5</td>
                        <td>Rapat OSIS</td>

                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>

                        <td>
                            <span class="status pending">
                                Menunggu
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="verify-btn">
                                Periksa
                            </a>
                        </td>
                    </tr>


                    <tr>
                        <td>Nabila Putri</td>
                        <td>X RPL 1</td>
                        <td>07 Sep 2026</td>
                        <td>Jam 1 - 4</td>
                        <td>Lomba Kompetensi Siswa</td>

                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>

                        <td>
                            <span class="status pending">
                                Menunggu
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="verify-btn">
                                Periksa
                            </a>
                        </td>
                    </tr>


                    <tr>
                        <td>Raka Pratama</td>
                        <td>XII RPL 2</td>
                        <td>06 Sep 2026</td>
                        <td>Jam 6 - 8</td>
                        <td>Kegiatan Sekolah</td>

                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>

                        <td>
                            <span class="status pending">
                                Menunggu
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="verify-btn">
                                Periksa
                            </a>
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
        justify-content: center;
        align-items: center;

        background: #edf4ff;
        color: #3976ee;

        font-weight: bold;
    }

    .success {
        color: #15945f;
    }

    .success-icon {
        background: #e7f8ef;
        color: #15945f;
    }

    .warning {
        color: #d97706;
    }

    .warning-icon {
        background: #fff4dd;
        color: #d97706;
    }


    .admin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;

        margin-bottom: 28px;
    }

    .panel {
        background: white;
        border: 1px solid #e1e6ed;
        border-radius: 8px;
        overflow: hidden;
    }

    .panel-header {
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .panel-header h3 {
        font-size: 15px;
        color: #1f2937;
        margin-bottom: 3px;
    }

    .panel-header p {
        color: #6b7280;
        font-size: 11px;
    }


    .data-list {
        padding: 7px 0;
    }

    .data-item {
        display: flex;
        align-items: center;
        justify-content: space-between;

        text-decoration: none;

        padding: 12px 18px;

        border-bottom: 1px solid #f0f2f5;
    }

    .data-item:last-child {
        border-bottom: none;
    }

    .data-item:hover {
        background: #fafbfd;
    }

    .data-item div {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .data-item strong {
        font-size: 12px;
        color: #2c3748;
    }

    .data-item span {
        font-size: 10px;
        color: #8b95a3;
    }

    .data-item b {
        color: #3976ee;
        font-size: 16px;
    }


    .quick-menu {
        padding: 14px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .quick-card {
        min-height: 95px;

        border: 1px solid #e6eaf0;
        border-radius: 7px;

        padding: 13px;

        display: flex;
        align-items: flex-start;
        gap: 10px;

        text-decoration: none;
    }

    .quick-card:hover {
        background: #fafbfd;
    }

    .quick-icon {
        width: 31px;
        height: 31px;
        min-width: 31px;

        border-radius: 7px;

        display: flex;
        justify-content: center;
        align-items: center;

        background: #edf4ff;
        color: #3478f6;
    }

    .quick-card strong {
        display: block;
        color: #2c3748;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .quick-card span {
        color: #8b95a3;
        font-size: 10px;
        line-height: 1.4;
    }


    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;

        margin-bottom: 13px;
    }

    .section-header h3 {
        font-size: 16px;
        color: #1f2937;
        margin-bottom: 3px;
    }

    .section-header p {
        color: #6b7280;
        font-size: 12px;
    }

    .view-all {
        text-decoration: none;
        color: #3976ee;
        font-size: 11px;
    }


    .table-card {
        background: white;
        border: 1px solid #e1e6ed;
        border-radius: 8px;

        overflow: hidden;
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
        padding: 11px 13px;
        text-align: left;

        color: #6b7280;
        font-size: 10px;
        font-weight: 600;

        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 13px;

        color: #4b5563;
        font-size: 12px;

        border-bottom: 1px solid #edf0f3;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #fafbfd;
    }


    .status {
        display: inline-block;

        padding: 4px 8px;
        border-radius: 20px;

        font-size: 10px;
        font-weight: 600;
    }

    .status.approved {
        background: #e7f8ef;
        color: #15945f;
    }

    .status.pending {
        background: #fff4dd;
        color: #d97706;
    }


    .action-cell {
        text-align: center;
    }

    .verify-btn {
        display: inline-block;

        background: #3478f6;
        color: white;

        text-decoration: none;

        padding: 6px 10px;
        border-radius: 5px;

        font-size: 10px;
        font-weight: 600;
    }


    @media(max-width: 1100px) {

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .admin-grid {
            grid-template-columns: 1fr;
        }

    }

    @media(max-width: 700px) {

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .quick-menu {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

    }

</style>

@endpush