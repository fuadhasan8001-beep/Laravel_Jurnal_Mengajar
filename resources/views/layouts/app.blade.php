<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Jurnal Mengajar')</title>
</head>
<body>

    <header>
        <h1>Jurnal Mengajar</h1>

        @auth
            <p>
                Login sebagai: {{ auth()->user()->name }}
                ({{ auth()->user()->role }})
            </p>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @endauth
    </header>

    <hr>

    <main>
        @yield('content')
    </main>

</body>
</html>