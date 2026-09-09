<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Jurnal Guru</title>
    @vite('resources/css/login.css')
</head>
<body>
    <main class="login-page">
        <section class="left-section">
            <div class="brand">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span>Jurnal Guru</span>
            </div>

            <div class="left-content">
                <div class="illustration">
                    <svg viewBox="0 0 160 130" fill="none">
                        <path d="m24 35 56-28 56 28-56 29-56-29Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M43 47v24c0 13 17 23 37 23s37-10 37-23V47" stroke="currentColor" stroke-width="3" />
                        <path d="M136 36v43" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="left-text">
                    <h1>Jurnal Guru</h1>
                    <p>Digitalisasi Presensi dan Jurnal Mengajar</p>
                </div>
            </div>
        </section>

        <section class="right-section">
            <div class="login-container">
                <div class="login-heading">
                    <span class="heading-kicker">Akses Akun</span>
                    <h2>Lupa Password</h2>
                </div>

                @if ($errors->any())
                    <div class="error-message">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="success-message">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="login">
                            Username atau email
                        </label>

                        <div class="input-container">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>

                            <input type="text" id="login" name="login" value="{{ old('login') }}" placeholder="Masukkan NIS atau email" required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        Cari Akun
                    </button>

                    <div class="forgot-password">
                        <a href="{{ route('login') }}">
                            Kembali ke login
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
