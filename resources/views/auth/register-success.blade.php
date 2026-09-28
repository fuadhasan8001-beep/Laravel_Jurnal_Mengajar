<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi berhasil | Jurnal Guru</title>
    @vite('resources/css/login.css')
    <style>
        .registration-result {
            max-width: 640px;
            margin: 80px auto;
            padding: 32px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <main class="registration-result">
        <p>Jurnal Guru</p>
        <h1>Registrasi berhasil</h1>
        <p>Data pendaftaran Anda telah dikirim dan sedang menunggu persetujuan admin.</p>
        <p>Anda belum dapat login sampai pendaftaran disetujui oleh admin.</p>
        <a href="{{ route('login') }}">Kembali ke login</a>
    </main>
</body>
</html>
