@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')

    <h1 class="page-title">Dashboard Siswa</h1>
    <p class="page-subtitle">
        Kelola pengajuan dispensasi dan lihat status pengajuan Anda.
    </p>

    <div class="stats-grid">

        <div class="stat-card">
            <div>
                <p class="stat-label">Menunggu Verifikasi</p>
                <h2 class="stat-number warning">2</h2>
                <p class="stat-unit">pengajuan</p>
            </div>
            <div class="stat-icon warning-icon">⌛</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Disetujui</p>
                <h2 class="stat-number success">5</h2>
                <p class="stat-unit">pengajuan</p>
            </div>
            <div class="stat-icon success-icon">✓</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Ditolak</p>
                <h2 class="stat-number danger">1</h2>
                <p class="stat-unit">pengajuan</p>
            </div>
            <div class="stat-icon danger-icon">!</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Total Pengajuan</p>
                <h2 class="stat-number">8</h2>
                <p class="stat-unit">dispensasi</p>
            </div>
            <div class="stat-icon">▣</div>
        </div>

    </div>


    <div class="quick-section">

        <div>
            <h3>Pengajuan Dispensasi</h3>
            <p>
                Ajukan dispensasi jika Anda mengikuti kegiatan atau memiliki keperluan tertentu.
            </p>
        </div>

        <a href="#" class="add-btn">
            + Ajukan Dispensasi
        </a>

    </div>


    <div class="table-card">

        <div class="table-header">
            <div>
                <h3>Riwayat Dispensasi Terbaru</h3>
                <p>Daftar pengajuan dispensasi yang terakhir Anda buat.</p>
            </div>

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
                        <th>KEGIATAN / ALASAN</th>
                        <th>BUKTI</th>
                        <th>STATUS PIKET</th>
                        <th>STATUS ADMIN</th>
                        <th>STATUS AKHIR</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>07 Sep 2026</td>
                        <td>Jam 3 - 5</td>
                        <td>Rapat OSIS</td>
                        <td>
                            <span class="file-badge">Ada</span>
                        </td>
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
                        <td>
                            <span class="status pending">
                                Menunggu
                            </span>
                        </td>
                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
                        </td>
                    </tr>


                    <tr>
                        <td>03 Sep 2026</td>
                        <td>Jam 1 - 4</td>
                        <td>Lomba LKS Tingkat Kabupaten</td>
                        <td>
                            <span class="file-badge">Ada</span>
                        </td>
                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>
                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>
                        <td>
                            <span class="status approved">
                                Disetujui
                            </span>
                        </td>
                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
                        </td>
                    </tr>


                    <tr>
                        <td>28 Agu 2026</td>
                        <td>Jam 6 - 8</td>
                        <td>Keperluan Keluarga</td>
                        <td>
                            <span class="file-badge empty">Tidak Ada</span>
                        </td>
                        <td>
                            <span class="status rejected">
                                Ditolak
                            </span>
                        </td>
                        <td>
                            <span class="status rejected">
                                Ditolak
                            </span>
                        </td>
                        <td>
                            <span class="status rejected">
                                Ditolak
                            </span>
                        </td>
                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
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
        min-width: 1100px;
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
    .file-badge {
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

    .status.rejected {
        background: #ffe7e7;
        color: #d64545;
    }

    .file-badge {
        background: #e8f5ff;
        color: #2973b9;
    }

    .file-badge.empty {
        background: #f2f3f5;
        color: #667085;
    }


    .action-cell {
        text-align: center;
    }

    .detail-btn {
        display: inline-block;

        text-decoration: none;

        background: #eef3f8;
        color: #435466;

        padding: 6px 10px;
        border-radius: 5px;

        font-size: 10px;
        font-weight: 600;
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
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
        }

    }

</style>

@endpush