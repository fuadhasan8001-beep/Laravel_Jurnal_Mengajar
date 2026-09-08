<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Jurnal Mengajar')</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f5f7fb;
            color: #1f2937;
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 230px;
            background: #0f2438;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .brand {
            padding: 22px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .brand-title {
            font-size: 17px;
            font-weight: 700;
        }

        .brand-subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: #aab7c4;
            text-transform: capitalize;
        }

        .menu {
            padding: 14px 12px;
            flex: 1;
        }

        .menu-link {
            display: block;
            padding: 11px 14px;
            margin-bottom: 4px;
            text-decoration: none;
            color: #c8d2dc;
            font-size: 14px;
            border-radius: 5px;
        }

        .menu-link:hover {
            background: #20384f;
            color: white;
        }

        .menu-link.active {
            background: #263f57;
            color: white;
        }

        .sidebar-bottom {
            padding: 14px 12px 18px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .logout-btn {
            width: 100%;
            border: none;
            background: transparent;
            color: #d5dde5;
            text-align: left;
            padding: 10px 14px;
            cursor: pointer;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background: #20384f;
        }

        .main {
            margin-left: 230px;
            width: calc(100% - 230px);
            min-height: 100vh;
        }

        .topbar {
            height: 62px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            gap: 15px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .menu-toggle {
            display: none;
            border: none;
            background: transparent;
            font-size: 25px;
            cursor: pointer;
            color: #1f2937;
            line-height: 1;
        }

        .search-box {
            width: 270px;
            background: #f5f7fb;
            border: 1px solid #e3e8ef;
            border-radius: 8px;
            padding: 9px 12px;
            color: #6b7280;
            font-size: 13px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 13px;
            color: #4b5563;
        }

        .profile-mini {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dbe4ee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #27425b;
        }

        .content {
            padding: 26px 28px;
        }

        .page-title {
            font-size: 27px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #172033;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .sidebar-overlay {
            display: none;
        }

        @media (max-width: 900px) {

            .sidebar {
                width: 230px;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main {
                margin-left: 0;
                width: 100%;
            }

            .menu-toggle {
                display: block;
            }

            .sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.35);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .topbar {
                padding: 0 18px;
            }

            .search-box {
                width: 180px;
            }
        }

        @media (max-width: 600px) {

            .topbar {
                padding: 0 14px;
            }

            .search-box {
                display: none;
            }

            .content {
                padding: 20px 16px;
            }

            .page-title {
                font-size: 23px;
            }

            .profile-mini span {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

@auth

<div class="app">

    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-title">📘 Jurnal Mengajar</div>

            <div class="brand-subtitle">
                {{ ucfirst(auth()->user()->role) }} Portal
            </div>
        </div>


        <nav class="menu">

            {{-- GURU --}}
            @if(auth()->user()->role === 'guru')

                <a
                    href="{{ url('/guru') }}"
                    class="menu-link {{ request()->is('guru') ? 'active' : '' }}"
                >
                    ▦ Dashboard
                </a>

                <a href="#" class="menu-link">
                    ＋ Tambah Jurnal
                </a>

                <a href="#" class="menu-link">
                    ▣ Jurnal Saya
                </a>

                <a
                    href="{{ route('profile') }}"
                    class="menu-link {{ request()->is('profile') ? 'active' : '' }}"
                >
                    ♙ Profil
                </a>

            @endif


            {{-- SEKRETARIS --}}
            @if(auth()->user()->role === 'sekretaris')

                <a
                    href="{{ url('/sekretaris') }}"
                    class="menu-link {{ request()->is('sekretaris') ? 'active' : '' }}"
                >
                    ▦ Dashboard
                </a>

                <a href="#" class="menu-link">
                    ☑ Verifikasi Jurnal
                </a>

                <a href="#" class="menu-link">
                    ↶ Riwayat Verifikasi
                </a>

                <a
                    href="{{ route('profile') }}"
                    class="menu-link {{ request()->is('profile') ? 'active' : '' }}"
                >
                    ♙ Profil
                </a>

            @endif


            {{-- ADMIN --}}
            @if(auth()->user()->role === 'admin')

                <a
                    href="{{ url('/admin') }}"
                    class="menu-link {{ request()->is('admin') ? 'active' : '' }}"
                >
                    ▦ Dashboard
                </a>

                <a href="#" class="menu-link">
                    ♙ Data Guru
                </a>

                <a href="#" class="menu-link">
                    ♙ Data Siswa
                </a>

                <a href="#" class="menu-link">
                    ♙ Data Sekretaris
                </a>

                <a href="#" class="menu-link">
                    ♙ Data Piket
                </a>

                <a href="#" class="menu-link">
                    ▦ Data Kelas
                </a>

                <a href="#" class="menu-link">
                    ▣ Mata Pelajaran
                </a>

                <a href="#" class="menu-link">
                    ◷ Jam Pelajaran
                </a>

                <a href="#" class="menu-link">
                    ▣ Data Jurnal
                </a>

                <a href="#" class="menu-link">
                    ✓ Verifikasi Dispensasi
                </a>

                <a href="#" class="menu-link">
                    ▤ Rekapitulasi
                </a>

                <a
                    href="{{ route('profile') }}"
                    class="menu-link {{ request()->is('profile') ? 'active' : '' }}"
                >
                    ♙ Profil
                </a>

            @endif


            {{-- SISWA --}}
            @if(auth()->user()->role === 'siswa')

                <a
                    href="{{ url('/siswa') }}"
                    class="menu-link {{ request()->is('siswa') ? 'active' : '' }}"
                >
                    ▦ Dashboard
                </a>

                <a href="#" class="menu-link">
                    ＋ Ajukan Dispensasi
                </a>

                <a href="#" class="menu-link">
                    ↶ Riwayat Dispensasi
                </a>

                <a
                    href="{{ route('profile') }}"
                    class="menu-link {{ request()->is('profile') ? 'active' : '' }}"
                >
                    ♙ Profil
                </a>

            @endif


            {{-- PIKET --}}
            @if(auth()->user()->role === 'piket')

                <a
                    href="{{ url('/piket') }}"
                    class="menu-link {{ request()->is('piket') ? 'active' : '' }}"
                >
                    ▦ Dashboard
                </a>

                <a href="#" class="menu-link">
                    ▤ Kehadiran Guru
                </a>

                <a href="#" class="menu-link">
                    ☑ Verifikasi Dispensasi
                </a>

                <a
                    href="{{ route('profile') }}"
                    class="menu-link {{ request()->is('profile') ? 'active' : '' }}"
                >
                    ♙ Profil
                </a>

            @endif

        </nav>


        <div class="sidebar-bottom">

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button type="submit" class="logout-btn">
                    ↪ Logout
                </button>

            </form>

        </div>

    </aside>


    <div class="sidebar-overlay" id="sidebarOverlay"></div>


    <main class="main">

        <div class="topbar">

            <div class="topbar-left">

                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                    aria-label="Buka menu"
                >
                    ☰
                </button>

                <input
                    type="text"
                    class="search-box"
                    placeholder="Cari jurnal..."
                    disabled
                >

            </div>

            <div class="topbar-right">

                <span>🔔</span>

                <div class="profile-mini">

                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <span>
                        {{ ucfirst(auth()->user()->role) }}
                    </span>

                </div>

            </div>

        </div>


        <section class="content">
            @yield('content')
        </section>

    </main>

</div>

@else

    @yield('content')

@endauth


@stack('scripts')


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

        const menuLinks = sidebar.querySelectorAll('.menu-link');

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