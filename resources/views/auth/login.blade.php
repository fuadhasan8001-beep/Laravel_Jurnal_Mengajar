<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f7f4">
    <title>Masuk - Jurnal Guru</title>
    @vite('resources/css/login.css')
</head>
<body class="signin-body">
    <main class="signin-page">
        <a class="signin-brand" href="{{ url('/') }}" aria-label="Jurnal Guru">
            <span class="signin-brand-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" />
                    <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" />
                </svg>
            </span>
            <span>Jurnal Guru</span>
        </a>

        <header class="signin-heading">
            <h1 id="signin-title">Selamat datang</h1>
            <p>Masuk untuk melanjutkan ke jurnal mengajar Anda.</p>
        </header>

        <section class="signin-card" aria-labelledby="signin-title">

            @if ($errors->any())
                <div class="signin-error" role="alert">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="signin-form">
                @csrf
                <div class="signin-field">
                    <label for="login">NISN, username, atau email</label>
                    <div class="signin-input-wrap">
                        <svg class="signin-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M5 21a7 7 0 0 1 14 0" />
                        </svg>
                        <input type="text" id="login" name="login" value="{{ old('login', old('email')) }}" placeholder="Masukkan NISN, username, atau email" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="signin-field">
                    <div class="signin-label-row"><label for="password">Password</label><a href="{{ route('password.request') }}">Lupa password?</a></div>
                    <div class="signin-input-wrap">
                        <svg class="signin-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2" />
                            <path d="M8 10V7a4 4 0 1 1 8 0v3" />
                        </svg>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" id="toggle-password" class="signin-password-toggle" aria-label="Tampilkan password" title="Tampilkan password">
                            <svg id="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg id="eye-closed" class="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8" /><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.9M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7c1 0 1.9-.2 2.8-.5" /></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="signin-submit">Masuk<span aria-hidden="true"></span></button>
            </form>
        </section>

        <p class="signin-footer">Jurnal Guru <span aria-hidden="true">·</span> Sistem Jurnal Mengajar Digital</p>
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
