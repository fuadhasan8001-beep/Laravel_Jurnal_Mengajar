<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Jurnal Guru</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <p class="eyebrow">Jurnal Guru</p>
            <h1>Daftar akun</h1>
            <p>Ajukan akun baru untuk ditinjau oleh admin.</p>
            @if ($errors->any())
                <div class="error-message">{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('register.store') }}" method="POST">
                @csrf
                <div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name') }}" required></div>
                <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required></div>
                <div class="field"><label for="role">Role yang didaftarkan</label><select id="role" name="role" required>@foreach (['guru' => 'Guru', 'siswa' => 'Siswa', 'sekretaris' => 'Sekretaris', 'piket' => 'Piket'] as $value => $label)<option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required></div>
                <div class="field"><label for="password_confirmation">Konfirmasi password</label><input id="password_confirmation" type="password" name="password_confirmation" required></div>
                <button class="btn" type="submit">Kirim pendaftaran</button>
            </form>
            <p><a href="{{ route('login') }}">Kembali ke login</a></p>
        </section>
    </main>
</body>
</html>
