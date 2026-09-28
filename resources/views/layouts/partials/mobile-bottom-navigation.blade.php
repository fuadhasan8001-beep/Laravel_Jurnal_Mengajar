@php
    $user = auth()->user();
    $role = $user->role;
    $homeUrl = $role === 'waka' ? url('/admin') : url('/'.$role);
    $homeActive = $role === 'waka' ? request()->is('admin') : request()->is($role);
    $isPiketToday = $role === 'piket' || ($role === 'guru' && $user->isPiketHariIni());
    $isHomeroomTeacher = $role === 'guru' && $user->kelasWali()->exists();
    $makeNavItem = fn (string $key, string $label, string $url, string $icon, bool $active): array => compact('key', 'label', 'url', 'icon', 'active');
    $makeMoreItem = fn (string $key, string $group, string $label, string $url, string $icon, bool $active): array => compact('key', 'group', 'label', 'url', 'icon', 'active');
    $mobileNavItems = [$makeNavItem('home', $role === 'piket' ? 'Meja piket' : 'Beranda', $homeUrl, 'home', $homeActive)];
    $mobileMoreItems = [];

    if (in_array($role, ['admin', 'waka'], true)) {
        $mobileNavItems[] = $makeNavItem('jurnal', 'Jurnal', route('jurnal.index'), 'journal', request()->is('jurnal*'));
        $mobileNavItems[] = $makeNavItem('dispensasi', 'Verifikasi', route('dispensasi.index'), 'approval', request()->is('dispensasi*'));
        $mobileNavItems[] = $makeNavItem('absensi', 'Absensi', route('absensi.index'), 'attendance', request()->is('absensi*'));
        $mobileMoreItems = [
            $makeMoreItem('admin-guru', 'Data & akun', 'Guru', route('admin.gurus.index'), 'person', request()->is('admin/data/guru*')),
            $makeMoreItem('admin-siswa', 'Data & akun', 'Siswa', route('admin.siswas.index'), 'people', request()->is('admin/data/siswa*')),
            $makeMoreItem('admin-kelas', 'Data & akun', 'Kelas', route('admin.kelas.index'), 'class', request()->is('admin/data/kelas*')),
            $makeMoreItem('admin-mapel', 'Data & akun', 'Mata pelajaran', route('admin.mapel.index'), 'journal', request()->is('admin/data/mapel*')),
            $makeMoreItem('admin-jam', 'Data & akun', 'Jam pelajaran', route('admin.jam.index'), 'clock', request()->is('admin/data/jam*')),
            $makeMoreItem('admin-pengurus', 'Data & akun', 'Pengurus kelas', route('admin.secretaries.index'), 'people', request()->is('admin/secretaries*')),
            $makeMoreItem('admin-pendaftaran', 'Data & akun', 'Pendaftaran akun', route('admin.registrations.index'), 'approval', request()->is('admin/registrations*')),
            $makeMoreItem('admin-laporan', 'Operasional', 'Rekap laporan', route('laporan.jurnal'), 'report', request()->is('rekap*')),
            $makeMoreItem('admin-piket', 'Operasional', 'Jadwal piket guru', route('admin.piket.index'), 'calendar', request()->is('admin/jadwal-piket*')),
            $makeMoreItem('admin-log', 'Operasional', 'Riwayat aktivitas', route('admin.activity-logs'), 'activity', request()->is('admin/activity-logs*')),
        ];
    } elseif ($role === 'guru') {
        $mobileNavItems[] = $makeNavItem('jurnal-own', 'Jurnal saya', route('jurnal.index'), 'journal', request()->routeIs('jurnal.index', 'jurnal.show'));
        if ($isPiketToday) {
            $mobileNavItems[] = $makeNavItem('piket', 'Piket', url('/piket'), 'clipboard', request()->is('piket') || request()->is('dispensasi*'));
        } elseif ($isHomeroomTeacher) {
            $mobileNavItems[] = $makeNavItem('homeroom', 'Rekap kelas', route('wali-kelas.jurnal.index'), 'class', request()->routeIs('wali-kelas.jurnal.*'));
        }
        $mobileMoreItems[] = $makeMoreItem('teacher-journal-create', 'Menu lainnya', 'Isi jurnal', route('jurnal.create'), 'journal-add', request()->routeIs('jurnal.create', 'jurnal.store', 'jurnal.edit', 'jurnal.update'));
        $mobileMoreItems[] = $makeMoreItem('teacher-attendance', 'Menu lainnya', 'Perbarui absensi', route('absensi.index'), 'attendance', request()->is('absensi*'));
        if ($isPiketToday) {
            $mobileMoreItems[] = $makeMoreItem('teacher-piket-recap', 'Menu lainnya', 'Rekap jurnal semua guru', route('piket.rekap-jurnal'), 'report', request()->is('piket/rekap-jurnal*'));
            $mobileMoreItems[] = $makeMoreItem('teacher-dispensasi', 'Menu lainnya', 'Dispensasi', route('dispensasi.index'), 'approval', request()->is('dispensasi*'));
            if ($isHomeroomTeacher) {
                $mobileMoreItems[] = $makeMoreItem('teacher-homeroom', 'Menu lainnya', 'Rekap jurnal kelas', route('wali-kelas.jurnal.index'), 'class', request()->routeIs('wali-kelas.jurnal.*'));
            }
        }
    } elseif ($role === 'siswa') {
        $mobileNavItems[] = $makeNavItem('dispensasi-create', 'Ajukan', route('dispensasi.create'), 'plus', request()->routeIs('dispensasi.create', 'dispensasi.store'));
        $mobileNavItems[] = $makeNavItem('dispensasi-history', 'Riwayat', route('dispensasi.index'), 'history', request()->routeIs('dispensasi.index', 'dispensasi.show'));
    } elseif ($role === 'sekretaris') {
        $mobileNavItems[] = $makeNavItem('jurnal-verify', 'Verifikasi', route('jurnal.index'), 'approval', request()->is('jurnal*'));
        $mobileNavItems[] = $makeNavItem('absensi', 'Absensi', route('absensi.index'), 'attendance', request()->is('absensi*'));
        $mobileMoreItems[] = $makeMoreItem('secretary-report', 'Menu lainnya', 'Rekap laporan', route('laporan.jurnal'), 'report', request()->is('rekap*'));
    } elseif ($role === 'piket') {
        $mobileNavItems[] = $makeNavItem('dispensasi', 'Dispensasi', route('dispensasi.index'), 'approval', request()->is('dispensasi*'));
        $mobileNavItems[] = $makeNavItem('izin', 'Izin', route('piket.izin-sekolah.index'), 'calendar', request()->is('piket/izin-sekolah*'));
        $mobileNavItems[] = $makeNavItem('piket-recap', 'Rekap jurnal', route('piket.rekap-jurnal'), 'report', request()->is('piket/rekap-jurnal*'));
        $mobileMoreItems[] = $makeMoreItem('piket-create-dispensasi', 'Menu lainnya', 'Ajukan dispensasi', route('dispensasi.create'), 'plus', request()->routeIs('dispensasi.create'));
        $mobileMoreItems[] = $makeMoreItem('piket-create-izin', 'Menu lainnya', 'Catat izin atau sakit', route('piket.izin-sekolah.create'), 'calendar', request()->routeIs('piket.izin-sekolah.create'));
        $mobileMoreItems[] = $makeMoreItem('piket-report', 'Menu lainnya', 'Rekap laporan', route('laporan.jurnal'), 'report', request()->is('rekap*'));
    }

    $mobileMoreItems[] = $makeMoreItem('profile', 'Akun', 'Profil', route('profile'), 'person', request()->is('profile*'));
    $mobileMoreIsActive = collect($mobileMoreItems)->contains(fn (array $item): bool => $item['active']);
@endphp

<nav class="mobile-bottom-nav" data-mobile-bottom-nav aria-label="Navigasi utama" style="--mobile-nav-count: {{ count($mobileNavItems) + 1 }}">
    @foreach ($mobileNavItems as $item)
        <a class="mobile-nav-item{{ $item['active'] ? ' is-active' : '' }}" href="{{ $item['url'] }}" data-mobile-nav-destination="{{ $item['key'] }}" @if ($item['active']) aria-current="page" @endif>
            <span class="mobile-nav-icon">@include('layouts.partials.mobile-nav-icon', ['icon' => $item['icon']])</span>
            <span class="mobile-nav-label">{{ $item['label'] }}</span>
        </a>
    @endforeach
    <button class="mobile-nav-item{{ $mobileMoreIsActive ? ' is-active' : '' }}" type="button" data-mobile-more-open aria-haspopup="dialog" aria-controls="mobile-more-sheet" aria-expanded="false" @if ($mobileMoreIsActive) aria-current="page" @endif>
        <span class="mobile-nav-icon">@include('layouts.partials.mobile-nav-icon', ['icon' => 'more'])</span>
        <span class="mobile-nav-label">Lainnya</span>
    </button>
</nav>

<dialog class="mobile-more-sheet" id="mobile-more-sheet" data-mobile-more-sheet aria-labelledby="mobile-more-title">
    <div class="mobile-more-heading"><h2 id="mobile-more-title">Lainnya</h2><form method="dialog"><button class="mobile-more-close" type="submit" aria-label="Tutup menu lainnya">&times;</button></form></div>
    @php($currentMoreGroup = null)
    <nav class="mobile-more-list" aria-label="Menu lainnya">
        @foreach ($mobileMoreItems as $item)
            @if ($currentMoreGroup !== $item['group'])
                @php($currentMoreGroup = $item['group'])
                <h3 class="mobile-more-group">{{ $currentMoreGroup }}</h3>
            @endif
            <a class="mobile-more-link{{ $item['active'] ? ' is-active' : '' }}" href="{{ $item['url'] }}" data-mobile-more-destination="{{ $item['key'] }}" @if ($item['active']) aria-current="page" @endif>
                <span class="mobile-more-icon">@include('layouts.partials.mobile-nav-icon', ['icon' => $item['icon']])</span><span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        @if ($role !== 'admin' && filled(config('app.admin_whatsapp')))
            <a class="mobile-more-link" href="https://wa.me/{{ preg_replace('/\\D+/', '', config('app.admin_whatsapp')) }}" target="_blank" rel="noopener noreferrer" data-mobile-more-destination="contact-admin">
                <span class="mobile-more-icon">@include('layouts.partials.mobile-nav-icon', ['icon' => 'chat'])</span><span>Hubungi Admin</span>
            </a>
        @endif
    </nav>
    <form class="mobile-more-logout" action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="mobile-logout-button" data-mobile-logout-button type="submit">@include('layouts.partials.mobile-nav-icon', ['icon' => 'logout']) Keluar dari akun</button>
    </form>
</dialog>