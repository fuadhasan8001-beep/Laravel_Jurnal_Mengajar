<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Jurnal Guru</title>
    @vite('resources/css/login.css')
</head>
<body>
    <main class="login-page">
        <section class="left-section">
            <div class="brand">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" />
                    </svg>
                </span>
                <span>Jurnal Guru</span>
            </div>
            <div class="left-content">
                <div class="illustration" aria-hidden="true">
                    <svg viewBox="0 0 160 130" fill="none">
                        <path d="m24 35 56-28 56 28-56 29-56-29Z" stroke="currentColor" stroke-width="3" />
                        <path d="M43 47v24c0 13 17 23 37 23s37-10 37-23V47M136 36v43" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        <circle cx="136" cy="82" r="3" fill="currentColor" />
                    </svg>
                </div>
                <h2>Pantau Aktivitas Mengajar Anda</h2>
                <p>Catat topik pembelajaran, absensi, dan progres kelas dalam satu platform praktis.</p>
            </div>
            <p class="left-footer">Mendukung peningkatan mutu pendidikan di Indonesia</p>
        </section>

        <section class="right-section">
            <div class="login-container">
                <div class="login-heading">
                    <h1>Masuk Akun</h1>
                    <p>Sistem Jurnal Mengajar Guru Berbasis Digital</p>
                </div>
                @if ($errors->any())
                    <div class="error-message" role="alert">{{ $errors->first() }}</div>
                @endif
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="login">NISN, username, atau email</label>
                        <div class="input-container">
                            <span class="input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <input type="text" id="login" name="login" value="{{ old('login', old('email')) }}" placeholder="NISN siswa, username guru, atau email" autocomplete="username" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-container">
                            <span class="input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m14.5 6.5 3-3 3 3-3 3" />
                                    <path d="m17.5 6.5-7.2 7.2a3.3 3.3 0 1 1-2-2l7.2-7.2" />
                                    <path d="m6.5 17.5 2 2" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                            <button type="button" id="toggle-password" class="password-toggle" aria-label="Tampilkan password" title="Tampilkan password">
                                <span id="eye-open">&#128065;</span>
                                <span id="eye-closed" class="hidden">&#128584;</span>
                            </button>
                        </div>
                    </div>
                    <div class="forgot-password"><a href="{{ route('password.request') }}">Lupa Password?</a></div>
                    <button type="submit" class="login-button">Masuk</button>
                </form>
            </div>
        </section>
    </main>
    <script>
        const togglePassword = document.getElementById('toggle-password');
        const password = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');
        togglePassword.addEventListener('click', () => {
            const showPassword = password.type === 'password';
            password.type = showPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', showPassword);
            eyeClosed.classList.toggle('hidden', !showPassword);
            togglePassword.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            togglePassword.setAttribute('title', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>
</body>
</html>
