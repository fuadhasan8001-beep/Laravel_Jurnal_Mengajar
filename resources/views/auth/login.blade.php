<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Jurnal Guru</title>
    <style>
        :root { font-family: "Trebuchet MS", Arial, sans-serif; color: #111b33; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #fff; }
        main { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; }
        main > section:first-child { display: flex; flex-direction: column; min-height: 100vh; overflow: hidden; padding: 56px 64px; background: #edf5ff; }
        main > section:first-child > div:first-child { display: flex; align-items: center; gap: 12px; }
        main > section:first-child > div:first-child span:first-child { display: flex; width: 45px; height: 45px; align-items: center; justify-content: center; border-radius: 10px; background: #2864e8; color: white; }
        main > section:first-child > div:first-child span:last-child { font-size: 20px; font-weight: 700; }
        main > section:first-child > div:nth-child(2) { display: flex; flex: 1; flex-direction: column; align-items: center; justify-content: center; padding: 64px 0; text-align: center; }
        main > section:first-child > div:nth-child(2) > div { display: flex; width: 288px; height: 288px; align-items: center; justify-content: center; margin-bottom: 32px; border-radius: 50%; background: white; box-shadow: 0 18px 35px rgba(71, 103, 153, .08); }
        main > section:first-child > div:nth-child(2) h2 { max-width: 440px; margin: 0; font-size: 26px; line-height: 1.2; }
        main > section:first-child > div:nth-child(2) p { max-width: 440px; margin: 8px 0 0; color: #526682; font-size: 16px; line-height: 1.25; }
        main > section:first-child > p { margin: 0; color: #91a4c2; font-size: 14px; text-align: center; }
        main > section:last-child { display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 48px 64px; }
        main > section:last-child > div { width: 100%; max-width: 452px; }
        main > section:last-child > div > div:first-child { margin-bottom: 112px; }
        main > section:last-child h1 { margin: 0; font-size: 34px; line-height: 1.15; }
        main > section:last-child h1 + p { margin: 8px 0 0; color: #92a4c1; font-size: 16px; }
        form { display: flex; flex-direction: column; gap: 20px; }
        form > div > label { display: block; margin-bottom: 8px; color: #4b5c75; font-size: 14px; font-weight: 700; }
        form > div > div { position: relative; }
        form input { width: 100%; height: 54px; border: 1px solid #dce4ef; border-radius: 8px; outline: none; background: white; color: #111b33; font: inherit; font-size: 16px; }
        form input:focus { border-color: #2864e8; box-shadow: 0 0 0 4px #dceaff; }
        form input::placeholder { color: #9aabc5; }
        form > div:first-child input { padding: 0 16px 0 48px; }
        form > div:nth-child(2) input { padding: 0 56px 0 48px; }
        form > div > div > span { position: absolute; top: 0; bottom: 0; left: 16px; display: flex; align-items: center; color: #91a4c2; }
        form > div > div > button { position: absolute; top: 0; right: 16px; bottom: 0; display: flex; align-items: center; border: 0; background: transparent; color: #91a4c2; cursor: pointer; }
        form > div:nth-child(3) { padding-top: 2px; text-align: right; }
        a { color: #2864e8; font-weight: 700; text-decoration: none; }
        a:hover { text-decoration: underline; }
        form > button { height: 54px; margin-top: 80px; border: 0; border-radius: 8px; background: #2864e8; color: white; cursor: pointer; font: inherit; font-size: 16px; font-weight: 700; box-shadow: 0 8px 16px rgba(40, 100, 232, .18); }
        form > button:hover { background: #1f55cd; }
        main > section:last-child > div > p:last-child { margin-top: 20px; color: #91a4c2; font-size: 16px; text-align: center; }
        main > section:last-child > div > p:last-child a { color: #2864e8; }
        [role="alert"] { margin-bottom: 20px; padding: 12px 16px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #b91c1c; font-size: 14px; }
        @media (max-width: 1023px) {
            main { display: block; }
            main > section:first-child { min-height: auto; padding: 32px 28px; }
            main > section:first-child > div:nth-child(2) { padding: 48px 0 40px; }
            main > section:first-child > div:nth-child(2) > div { width: 190px; height: 190px; margin-bottom: 24px; }
            main > section:first-child > div:nth-child(2) svg { width: 88px; height: 88px; }
            main > section:first-child > p { display: none; }
            main > section:last-child { min-height: auto; padding: 48px 28px 56px; }
            main > section:last-child > div > div:first-child { margin-bottom: 56px; }
            form > button { margin-top: 36px; }
        }
    </style>
</head>
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
    <main class="min-h-screen lg:grid lg:grid-cols-[1fr_1fr]">
        <section class="relative flex min-h-[320px] flex-col overflow-hidden bg-[#edf5ff] px-8 py-10 sm:px-14 lg:min-h-screen lg:px-16 lg:py-14">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-[10px] bg-[#2864e8] text-white shadow-sm">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H12v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H12v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-[-0.02em]">Jurnal Guru</span>
            </div>

            <div class="flex flex-1 flex-col items-center justify-center py-10 text-center lg:py-16">
                <div class="mb-8 flex h-64 w-64 items-center justify-center rounded-full bg-white shadow-[0_18px_35px_rgba(71,103,153,0.08)] sm:h-72 sm:w-72">
                    <svg class="h-32 w-32 text-[#2864e8]" viewBox="0 0 160 130" fill="none" aria-hidden="true">
                        <path d="m24 35 56-28 56 28-56 29-56-29Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M43 47v24c0 13 17 23 37 23s37-10 37-23V47" stroke="currentColor" stroke-width="3" />
                        <path d="M136 36v43" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        <circle cx="136" cy="82" r="3" fill="currentColor" />
                    </svg>
                </div>
                <h2 class="max-w-md text-2xl font-bold leading-tight tracking-[-0.02em] sm:text-[26px]">Pantau Aktivitas Mengajar Anda</h2>
                <p class="mt-2 max-w-md text-base leading-snug text-[#526682]">Catat topik pembelajaran, absensi, dan progres kelas dalam satu platform praktis.</p>
            </div>

            <p class="text-center text-sm text-[#91a4c2]">Mendukung peningkatan mutu pendidikan di Indonesia</p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-8 py-12 sm:px-14 lg:px-16">
            <div class="w-full max-w-[452px]">
                <div class="mb-28 sm:mb-32">
                    <h1 class="text-3xl font-bold tracking-[-0.03em] sm:text-[34px]">Masuk Akun</h1>
                    <p class="mt-2 text-base text-[#92a4c1]">Sistem Jurnal Mengajar Guru Berbasis Digital</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[#4b5c75]">Email atau NIP</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#91a4c2]" aria-hidden="true">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m4 7 8 6 8-6" />
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@sekolah.sch.id" required autofocus class="h-[54px] w-full rounded-lg border border-[#dce4ef] bg-white pl-12 pr-4 text-base text-slate-900 outline-none transition placeholder:text-[#9aabc5] focus:border-[#2864e8] focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-[#4b5c75]">Password</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#91a4c2]" aria-hidden="true">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="m14.5 6.5 3-3 3 3-3 3" />
                                    <path d="m17.5 6.5-7.2 7.2a3.3 3.3 0 1 1-2-2l7.2-7.2" />
                                    <path d="m6.5 17.5 2 2" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Masukkan password" required class="h-[54px] w-full rounded-lg border border-[#dce4ef] bg-white pl-12 pr-14 text-base text-slate-900 outline-none transition placeholder:text-[#9aabc5] focus:border-[#2864e8] focus:ring-4 focus:ring-blue-100">
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-4 flex items-center text-[#91a4c2] transition hover:text-[#2864e8]" aria-label="Tampilkan password">
                                <svg id="eye-open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                                <svg id="eye-closed" class="hidden h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="m3 3 18 18" />
                                    <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a17.8 17.8 0 0 1-3.1 3.7M6.2 6.3C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7c1.5 0 2.8-.4 4-1" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-0.5 text-right">
                        <a href="#" class="text-sm font-semibold text-[#2864e8] hover:underline">Lupa Password?</a>
                    </div>

                    <button type="submit" class="mt-20 h-[54px] w-full rounded-lg bg-[#2864e8] text-base font-bold text-white shadow-[0_8px_16px_rgba(40,100,232,0.18)] transition hover:bg-[#1f55cd] focus:outline-none focus:ring-4 focus:ring-blue-200">
                        Masuk
                    </button>
                </form>

                <p class="mt-5 text-center text-base text-[#91a4c2]">Belum punya akun? <a href="#" class="font-bold text-[#2864e8] hover:underline">Daftar Sekarang</a></p>
            </div>
        </section>
    </main>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const password = document.getElementById('password');
            const isPassword = password.type === 'password';

            password.type = isPassword ? 'text' : 'password';
            document.getElementById('eye-open').classList.toggle('hidden', isPassword);
            document.getElementById('eye-closed').classList.toggle('hidden', !isPassword);
            this.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>
</body>
</html>