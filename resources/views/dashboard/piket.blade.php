@extends('layouts.app')

@section('title', 'Dashboard Piket')

@section('content')

    <h1 class="page-title">Dashboard Piket</h1>
    <p class="page-subtitle">
        Pantau kehadiran guru dan pengajuan dispensasi siswa hari ini.
    </p>

    <div class="stats-grid">

        <div class="stat-card">
            <div>
                <p class="stat-label">Guru Hadir</p>
                <h2 class="stat-number success">18</h2>
                <p class="stat-unit">guru</p>
            </div>
            <div class="stat-icon success-icon">✓</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Guru Tidak Hadir</p>
                <h2 class="stat-number danger">3</h2>
                <p class="stat-unit">guru</p>
            </div>
            <div class="stat-icon danger-icon">!</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Meninggalkan Tugas</p>
                <h2 class="stat-number">2</h2>
                <p class="stat-unit">guru</p>
            </div>
            <div class="stat-icon">▣</div>
        </div>

        <div class="stat-card">
            <div>
                <p class="stat-label">Dispensasi Hari Ini</p>
                <h2 class="stat-number warning">5</h2>
                <p class="stat-unit">pengajuan</p>
            </div>
            <div class="stat-icon warning-icon">⌛</div>
        </div>

    </div>


    <div class="section-header">

        <div>
            <h3>Kehadiran Guru Hari Ini</h3>
            <p>
                Rekap kehadiran guru berdasarkan jurnal mengajar hari ini.
            </p>
        </div>

        <span class="date-badge">
            07 Sep 2026
        </span>

    </div>


    <div class="table-card">

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>GURU</th>
                        <th>JAM</th>
                        <th>KELAS</th>
                        <th>MATA PELAJARAN</th>
                        <th>KEHADIRAN</th>
                        <th>ALASAN</th>
                        <th>TUGAS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>Sutrisno, S.Kom</td>
                        <td>Jam 1 - 3</td>
                        <td>XI RPL 2</td>
                        <td>Basis Data</td>

                        <td>
                            <span class="status hadir">
                                Hadir
                            </span>
                        </td>

                        <td>-</td>

                        <td>
                            <span class="status neutral">
                                -
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
                        </td>
                    </tr>


                    <tr>
                        <td>Rahmawati, S.Kom</td>
                        <td>Jam 4 - 6</td>
                        <td>XI RPL 1</td>
                        <td>Pemrograman Web</td>

                        <td>
                            <span class="status tidak-hadir">
                                Tidak Hadir
                            </span>
                        </td>

                        <td>Sakit</td>

                        <td>
                            <span class="status tugas">
                                Ada Tugas
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
                        </td>
                    </tr>


                    <tr>
                        <td>Hendra Gunawan, S.Kom</td>
                        <td>Jam 7 - 8</td>
                        <td>X RPL 2</td>
                        <td>Pemrograman Dasar</td>

                        <td>
                            <span class="status hadir">
                                Hadir
                            </span>
                        </td>

                        <td>-</td>

                        <td>
                            <span class="status neutral">
                                -
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="detail-btn">Detail</a>
                        </td>
                    </tr>


                    <tr>
                        <td>Yusuf Hidayat, S.Kom</td>
                        <td>Jam 9 - 10</td>
                        <td>XII RPL 1</td>
                        <td>Pemrograman Berorientasi Objek</td>

                        <td>
                            <span class="status tidak-hadir">
                                Tidak Hadir
                            </span>
                        </td>

                        <td>Dinas Luar</td>

                        <td>
                            <span class="status tugas">
                                Ada Tugas
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


    <div class="section-header dispensasi-header">

        <div>
            <h3>Dispensasi Siswa Hari Ini</h3>
            <p>
                Pengajuan dispensasi yang perlu diperiksa oleh petugas piket.
            </p>
        </div>

        <a href="#" class="view-all">
            Lihat Semua →
        </a>

    </div>


    <div class="table-card">

        <div class="table-wrapper">

            <table class="dispensasi-table">

                <thead>
                    <tr>
                        <th>SISWA</th>
                        <th>KELAS</th>
                        <th>JAM</th>
                        <th>KEGIATAN / ALASAN</th>
                        <th>BUKTI</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>Skandinavia</td>
                        <td>XI RPL 2</td>
                        <td>Jam 3 - 5</td>
                        <td>Rapat OSIS</td>

                        <td>
                            <span class="file-badge">
                                Ada
                            </span>
                        </td>

                        <td>
                            <span class="status menunggu">
                                Menunggu
                            </span>
                        </td>

                        <td class="action-cell">
                            <a href="#" class="verify-btn">Periksa</a>
                        </td>
                    </tr>


                    <tr>
                        <td>Nadia Putri</td>
                        <td>XI RPL 1</td>
                        <td>Jam 1 - 4</td>
                        <td>Lomba LKS</td>

                        <td>
                            <span class="file-badge">
                                Ada
                            </span>
                        </td>

                        <td>
                            <span class="status disetujui">
                                Disetujui
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

    .warning {
        color: #d97706;
    }

    .warning-icon {
        background: #fff4dd;
        color: #d97706;
    }


    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin: 5px 0 13px;
    }

    .section-header h3 {
        color: #1f2937;
        font-size: 16px;
        margin-bottom: 3px;
    }

    .section-header p {
        font-size: 12px;
        color: #6b7280;
    }

    .date-badge {
        background: white;
        border: 1px solid #e1e6ed;

        padding: 8px 12px;
        border-radius: 6px;

        color: #536170;
        font-size: 11px;
    }

    .dispensasi-header {
        margin-top: 28px;
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

    .dispensasi-table {
        min-width: 850px;
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


    .status,
    .file-badge {
        display: inline-block;

        padding: 4px 8px;
        border-radius: 20px;

        font-size: 10px;
        font-weight: 600;
    }

    .status.hadir,
    .status.disetujui {
        background: #e7f8ef;
        color: #15945f;
    }

    .status.tidak-hadir {
        background: #ffe7e7;
        color: #d64545;
    }

    .status.tugas {
        background: #e8f5ff;
        color: #2973b9;
    }

    .status.menunggu {
        background: #fff4dd;
        color: #d97706;
    }

    .status.neutral {
        background: #f2f3f5;
        color: #667085;
    }

    .file-badge {
        background: #e8f5ff;
        color: #2973b9;
    }


    .action-cell {
        text-align: center;
    }

    .detail-btn,
    .verify-btn {
        display: inline-block;

        text-decoration: none;

        padding: 6px 10px;
        border-radius: 5px;

        font-size: 10px;
        font-weight: 600;
    }

    .detail-btn {
        background: #eef3f8;
        color: #435466;
    }

    .verify-btn {
        background: #3478f6;
        color: white;
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

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

    }

</style>

@endpush
