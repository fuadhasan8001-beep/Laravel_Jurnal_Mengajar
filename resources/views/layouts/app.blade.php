```blade
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Jurnal Guru') | Jurnal Guru</title>

    <style>
        :root {
            --ink: #15213b;
            --muted: #71819d;
            --blue: #2864e8;
            --navy: #14264a;
            --pale: #f4f7fb;
            --line: #e5ebf3;
            --green: #11865b;
            --amber: #b16b00;
            --red: #c43b45;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Trebuchet MS", Arial, sans-serif;
        }

        body {
            background: var(--pale);
            color: var(--ink);
        }

        a {
            color: var(--blue);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        /* =========================================
           APP
        ========================================= */

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;

            display: flex;
            flex-direction: column;

            width: 250px;
            height: 100vh;

            padding: 28px 18px;

            background: var(--navy);
            color: #fff;

            overflow-y: auto;

            transition: transform .3s ease;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;

            padding: 0 12px 32px;

            color: #fff;
            font-size: 20px;
            font-weight: 700;
        }

        .brand:hover {
            color: #fff;
            text-decoration: none;
        }

        .brand-mark {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 39px;
            height: 39px;

            border-radius: 10px;

            background: var(--blue);
        }

        .nav-label {
            padding: 0 12px 10px;

            color: #8fa4cc;
            font-size: 11px;
            font-weight: 700;

            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 12px;

            border-radius: 9px;

            color: #c7d3e9;

            font-size: 14px;
            font-weight: 600;

            transition: .2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #223b6d;
            color: #fff;
            text-decoration: none;
        }

        .nav-link svg {
            width: 19px;
            height: 19px;
            flex: none;
        }

        /* =========================================
           SIDEBAR FOOTER
        ========================================= */

        .sidebar-footer {
            margin-top: auto;

            padding: 18px 10px 0;

            border-top: 1px solid #29416e;
        }

        .user-mini {
            display: flex;
            align-items: center;
            gap: 10px;

            color: #dbe5f5;
        }

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 35px;
            height: 35px;

            border-radius: 50%;

            background: #dce9ff;
            color: var(--blue);

            font-weight: 700;
        }

        .user-mini strong {
            display: block;

            max-width: 140px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            font-size: 13px;
        }

        .user-mini small {
            color: #91a5c8;
            font-size: 11px;
            text-transform: capitalize;
        }

        .logout {
            width: 100%;

            margin-top: 16px;

            border: 1px solid #385482;
            border-radius: 8px;

            padding: 9px;

            background: transparent;
            color: #c7d3e9;

            cursor: pointer;

            font-size: 12px;
            font-weight: 700;
        }

        .logout:hover {
            border-color: #6f91cc;
            color: #fff;
        }

        /* =========================================
           MAIN
        ========================================= */

        .main {
            width: calc(100% - 250px);
            min-height: 100vh;

            margin-left: 250px;
        }

        /* =========================================
           TOPBAR
        ========================================= */

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;

            min-height: 78px;

            padding: 0 42px;

            border-bottom: 1px solid var(--line);

            background: #fff;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .eyebrow {
            color: var(--muted);
            font-size: 12px;
        }

        .topbar-title {
            margin-top: 3px;

            font-size: 18px;
            font-weight: 700;
        }

        .date-chip {
            display: flex;
            align-items: center;
            gap: 8px;

            color: var(--muted);
            font-size: 13px;
        }

        /* =========================================
           MOBILE MENU
        ========================================= */

        .menu-toggle {
            display: none;

            border: 0;
            background: transparent;

            color: var(--ink);

            font-size: 25px;

            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;

            position: fixed;
            inset: 0;

            z-index: 999;

            background: rgba(0, 0, 0, .35);

            opacity: 0;
            visibility: hidden;

            transition: opacity .3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* =========================================
           NOTIFICATION
        ========================================= */

        .notification-menu {
            position: relative;
        }

        .notification-button {
            border: 1px solid var(--line);
            border-radius: 8px;

            padding: 8px 10px;

            background: #fff;
            color: var(--ink);

            cursor: pointer;

            font-size: 12px;
            font-weight: 700;
        }

        .notification-button:hover {
            background: #f7f9fc;
        }

        .notification-list {
            position: absolute;

            z-index: 30;

            top: calc(100% + 8px);
            right: 0;

            width: 300px;

            padding: 8px;

            border: 1px solid var(--line);
            border-radius: 10px;

            background: #fff;

            box-shadow: 0 12px 30px #203b6420;
        }

        .notification-item {
            display: block;

            width: 100%;

            border: 0;
            border-radius: 7px;

            padding: 10px;

            background: transparent;

            color: var(--ink);

            text-align: left;

            cursor: pointer;

            font-size: 12px;
        }

        .notification-item:hover {
            background: var(--pale);
        }

        .notification-empty {
            padding: 12px;

            color: var(--muted);

            font-size: 12px;
        }

        /* =========================================
           CONTENT
        ========================================= */

        .content {
            max-width: 1320px;

            margin: 0 auto;

            padding: 38px 42px 56px;
        }

        .page-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 28px;
        }

        .page-head h1 {
            margin: 0;

            font-size: 29px;

            letter-spacing: -.03em;
        }

        .page-head p {
            margin-top: 7px;

            color: var(--muted);

            font-size: 14px;
        }

        /* =========================================
           BUTTON
        ========================================= */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            border: 0;
            border-radius: 8px;

            padding: 11px 16px;

            background: var(--blue);
            color: #fff;

            cursor: pointer;

            font-size: 13px;
            font-weight: 700;

            box-shadow: 0 5px 12px #2864e82b;
        }

        .btn:hover {
            background: #1f55cd;
            color: #fff;

            text-decoration: none;
        }

        .btn-muted {
            border: 1px solid var(--line);

            background: #fff;
            color: var(--ink);

            box-shadow: none;
        }

        .btn-muted:hover {
            background: #f7f9fc;
            color: var(--ink);
        }

        /* =========================================
           STATISTICS
        ========================================= */

        .stats {
            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 16px;

            margin-bottom: 24px;
        }

        .stat-card,
        .panel {
            border: 1px solid var(--line);
            border-radius: 12px;

            background: #fff;

            box-shadow: 0 8px 24px #203b6410;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 14px;

            padding: 18px;
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 42px;
            height: 42px;

            border-radius: 10px;

            background: #eaf1ff;
            color: var(--blue);
        }

        .stat-icon.green {
            background: #e8f8f0;
            color: var(--green);
        }

        .stat-icon.amber {
            background: #fff4df;
            color: var(--amber);
        }

        .stat-icon.red {
            background: #fff0f1;
            color: var(--red);
        }

        .stat-card small {
            display: block;

            color: var(--muted);

            font-size: 12px;
        }

        .stat-card strong {
            display: block;

            margin-top: 4px;

            font-size: 23px;
        }

        /* =========================================
           PANEL
        ========================================= */

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 14px;

            padding: 18px 20px;

            border-bottom: 1px solid var(--line);
        }

        .panel-head h2,
        .panel-head h3 {
            margin: 0;

            font-size: 16px;
        }

        .panel-body {
            padding: 20px;
        }

        /* =========================================
           TABLE
        ========================================= */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            font-size: 13px;
        }

        th {
            background: #f8fafd;

            color: #71819d;

            font-size: 11px;

            letter-spacing: .04em;

            text-align: left;
            text-transform: uppercase;
        }

        th,
        td {
            padding: 14px 16px;

            border-bottom: 1px solid var(--line);

            white-space: nowrap;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        tbody tr:hover {
            background: #fbfcff;
        }

        /* =========================================
           STATUS
        ========================================= */

        .status {
            display: inline-flex;

            border-radius: 99px;

            padding: 5px 9px;

            font-size: 11px;
            font-weight: 700;
        }

        .status.pending {
            background: #fff4df;
            color: var(--amber);
        }

        .status.approved {
            background: #e8f8f0;
            color: var(--green);
        }

        .status.rejected {
            background: #fff0f1;
            color: var(--red);
        }

        /* =========================================
           ALERT
        ========================================= */

        .alert {
            margin-bottom: 18px;

            border-radius: 9px;

            padding: 12px 15px;

            font-size: 13px;
        }

        .alert.success {
            border: 1px solid #b9e7d0;

            background: #edfbf4;

            color: #126b4c;
        }

        .alert.error {
            border: 1px solid #f4c5ca;

            background: #fff1f2;

            color: #a42e39;
        }

        /* =========================================
           EMPTY
        ========================================= */

        .empty {
            padding: 38px 20px;

            color: var(--muted);

            text-align: center;
        }

        /* =========================================
           FORM
        ========================================= */

        .form-panel {
            max-width: 820px;
        }

        .form-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;

            gap: 7px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            color: #4b5c75;

            font-size: 13px;
            font-weight: 700;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;

            border: 1px solid #dce4ef;
            border-radius: 8px;

            padding: 11px 12px;

            outline: 0;

            background: #fff;
            color: var(--ink);

            font-size: 14px;
        }

        .field textarea {
            min-height: 120px;

            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--blue);

            box-shadow: 0 0 0 4px #dceaff;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;

            gap: 10px;

            margin-top: 24px;
        }

        /* =========================================
           DETAIL
        ========================================= */

        .detail-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 0 32px;
        }

        .detail-item {
            padding: 15px 0;

            border-bottom: 1px solid var(--line);
        }

        .detail-item dt {
            color: var(--muted);

            font-size: 12px;
        }

        .detail-item dd {
            margin-top: 5px;

            font-size: 14px;
            font-weight: 600;
        }

        /* =========================================
           QUICK CARD
        ========================================= */

        .quick-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 14px;
        }

        .quick-card {
            display: flex;
            flex-direction: column;

            gap: 7px;

            border: 1px solid var(--line);
            border-radius: 10px;

            padding: 18px;

            background: #fbfcff;
        }

        .quick-card:hover {
            border-color: #bfd1f5;

            background: #f5f8ff;

            text-decoration: none;
        }

        .quick-card strong {
            color: var(--ink);

            font-size: 14px;
        }

        .quick-card span {
            color: var(--muted);

            font-size: 12px;
        }

        /* =========================================
           RESPONSIVE TABLET
        ========================================= */

        @media (max-width: 900px) {

            .sidebar {
                width: 230px;

                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main {
                width: 100%;

                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .sidebar-overlay {
                display: block;
            }

            .topbar {
                padding: 0 24px;
            }

            .content {
                padding: 28px 24px 40px;
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* =========================================
           RESPONSIVE MOBILE
        ========================================= */

        @media (max-width: 680px) {

            .topbar {
                min-height: 70px;

                padding: 0 16px;
            }

            .topbar-title {
                font-size: 16px;
            }

            .date-chip {
                display: none;
            }

            .content {
                padding: 24px 16px 40px;
            }

            .page-head {
                align-items: flex-start;

                flex-direction: column;

                margin-bottom: 22px;
            }

            .page-head h1 {
                font-size: 25px;
            }

            .stats {
                grid-template-columns: 1fr 1fr;

                gap: 10px;
            }

            .stat-card {
                padding: 14px 12px;
            }

            .stat-card strong {
                font-size: 20px;
            }

            .form-grid,
            .detail-grid,
            .quick-grid {
                grid-template-columns: 1fr;
            }

            .notification-list {
                position: fixed;

                top: 70px;
                right: 12px;
                left: 12px;

                width: auto;
            }
        }
    </style>
</head>

<body>

@auth

<div class="app-shell">

    {{-- =========================================
         SIDEBAR
    ========================================== --}}

    <aside class="sidebar" id="sidebar">

        <a
            href="{{ url('/' . auth()->user()->role) }}"
            class="brand"
        >
            <span class="brand-mark">
                <svg
                    width="22"
                    height="22"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/>
                    <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"/>
                </svg>
            </span>

            Jurnal Guru
        </a>

        <div class="nav-label">
            Menu utama
        </div>

        <nav class="nav-list">

            {{-- Dashboard --}}
            <a
                class="nav-link {{ request()->is(auth()->user()->role) ? 'active' : '' }}"
                href="{{ url('/' . auth()->user()->role) }}"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-9Z"/>
                    <path d="M9 21v-6h6v6"/>
                </svg>

                Dashboard
            </a>


            {{-- ABSENSI --}}
            @if (in_array(auth()->user()->role, ['admin', 'guru', 'piket'], true))

                <a
                    class="nav-link {{ request()->is('absensi*') ? 'active' : '' }}"
                    href="{{ route('absensi.index') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="4" y="3" width="16" height="18" rx="2"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>

                    Kelola absensi
                </a>

            @endif


            {{-- JURNAL --}}
            @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris'], true))

                <a
                    class="nav-link {{ request()->is('jurnal*') ? 'active' : '' }}"
                    href="{{ route('jurnal.index') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M5 4h14v16H5z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>

                    Jurnal mengajar
                </a>

            @endif


            {{-- REKAP --}}
            @if (in_array(auth()->user()->role, ['admin', 'guru', 'sekretaris', 'piket'], true))

                <a
                    class="nav-link {{ request()->is('rekap*') ? 'active' : '' }}"
                    href="{{ route('laporan.jurnal') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 5h16v14H4z"/>
                        <path d="M8 9h8M8 13h5M8 17h4"/>
                    </svg>

                    Rekap laporan
                </a>

            @endif


            {{-- DATA MASTER --}}
            @if (auth()->user()->role === 'admin')

                <a
                    class="nav-link {{ request()->is('admin/data/guru*') ? 'active' : '' }}"
                    href="{{ route('admin.gurus.index') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20a6 6 0 0 1 12 0M17 11v6M14 14h6"/>
                    </svg>

                    Guru
                </a>

                <a
                    class="nav-link {{ request()->is('admin/data/siswa*') ? 'active' : '' }}"
                    href="{{ route('admin.siswas.index') }}"
                >
                    Siswa
                </a>

                <a
                    class="nav-link {{ request()->is('admin/data/kelas*') ? 'active' : '' }}"
                    href="{{ route('admin.kelas.index') }}"
                >
                    Kelas
                </a>

                <a
                    class="nav-link {{ request()->is('admin/data/mapel*') ? 'active' : '' }}"
                    href="{{ route('admin.mapel.index') }}"
                >
                    Mata pelajaran
                </a>

                <a
                    class="nav-link {{ request()->is('admin/data/jam*') ? 'active' : '' }}"
                    href="{{ route('admin.jam.index') }}"
                >
                    Jam pelajaran
                </a>

            @endif


            {{-- DISPENSASI --}}
            @if (in_array(auth()->user()->role, ['siswa', 'piket', 'admin'], true))

                <a
                    class="nav-link {{ request()->is('dispensasi*') ? 'active' : '' }}"
                    href="{{ route('dispensasi.index') }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 3h12a2 2 0 0 1 2 2v16l-8-4-8 4V5a2 2 0 0 1 2-2Z"/>
                        <path d="M8 8h8M8 12h5"/>
                    </svg>

                    Dispensasi
                </a>

            @endif

        </nav>


        {{-- USER --}}
        <div class="sidebar-footer">

            <div class="user-mini">

                <span class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>

                <span>
                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>
                        {{ auth()->user()->role }}
                    </small>
                </span>

            </div>


            {{-- LOGOUT --}}
            <form
                action="{{ route('logout') }}"
                method="POST"
            >
                @csrf

                <button
                    class="logout"
                    type="submit"
                >
                    Keluar dari akun
                </button>

            </form>

        </div>

    </aside>


    {{-- OVERLAY MOBILE --}}
    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>


    {{-- =========================================
         MAIN
    ========================================== --}}

    <div class="main">

        {{-- TOPBAR --}}
        <header class="topbar">

            <div class="topbar-left">

                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                    aria-label="Buka menu"
                >
                    ☰
                </button>

                <div>

                    <div class="eyebrow">
                        Ruang kerja digital
                    </div>

                    <div class="topbar-title">
                        Jurnal Guru
                    </div>

                </div>

            </div>


            {{-- DATE + NOTIFICATION --}}
            <div class="date-chip">

                <div class="notification-menu">

                    <details>

                        <summary class="notification-button">
                            Notifikasi
                            ({{ auth()->user()->unreadNotifications()->count() }})
                        </summary>

                        <div class="notification-list">

                            @forelse (
                                auth()->user()
                                    ->unreadNotifications()
                                    ->latest()
                                    ->limit(5)
                                    ->get()
                                as $notification
                            )

                                <form
                                    action="{{ route('notifications.read', $notification->id) }}"
                                    method="POST"
                                >
                                    @csrf

                                    <button
                                        class="notification-item"
                                        type="submit"
                                    >
                                        {{ $notification->data['message'] ?? 'Ada notifikasi baru.' }}
                                    </button>

                                </form>

                            @empty

                                <div class="notification-empty">
                                    Tidak ada notifikasi baru.
                                </div>

                            @endforelse


                            @if (auth()->user()->unreadNotifications()->exists())

                                <form
                                    action="{{ route('notifications.read-all') }}"
                                    method="POST"
                                >
                                    @csrf

                                    <button
                                        class="notification-button"
                                        type="submit"
                                    >
                                        Tandai semua dibaca
                                    </button>

                                </form>

                            @endif

                        </div>

                    </details>

                </div>


                <svg
                    width="17"
                    height="17"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>

                {{ now()->translatedFormat('l, d F Y') }}

            </div>

        </header>


        {{-- CONTENT --}}
        <main class="content">

            {{-- SUCCESS --}}
            @if (session('success'))

                <div class="alert success">
                    {{ session('success') }}
                </div>

            @endif


            {{-- ERROR --}}
            @if ($errors->any())

                <div class="alert error">
                    {{ $errors->first() }}
                </div>

            @endif


            @yield('content')

        </main>

    </div>

</div>

@else

    {{-- HALAMAN LOGIN / GUEST --}}
    @yield('content')

@endauth


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (!menuToggle || !sidebar || !sidebarOverlay) {
            return;
        }

        function openSidebar() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        }

        menuToggle.addEventListener('click', function () {

            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }

        });

        sidebarOverlay.addEventListener('click', function () {
            closeSidebar();
        });


        const menuLinks = sidebar.querySelectorAll('.nav-link');

        menuLinks.forEach(function (link) {

            link.addEventListener('click', function () {

                if (window.innerWidth <= 900) {
                    closeSidebar();
                }

            });

        });


        window.addEventListener('resize', function () {

            if (window.innerWidth > 900) {
                closeSidebar();
            }

        });

    });
</script>

</body>
</html>
```
