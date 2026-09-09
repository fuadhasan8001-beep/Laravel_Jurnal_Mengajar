@extends('layouts.app')

@section('title', 'Profil')

@section('content')

    <h1 class="page-title">Profil Saya</h1>
    <p class="page-subtitle">
        Informasi akun yang sedang digunakan.
    </p>

    <div class="profile-wrapper">

        <div class="profile-card">

            <div class="profile-top">

                <div class="profile-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div>
                    <h2>{{ auth()->user()->name }}</h2>
                    <span class="role-badge">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>

            </div>


            <div class="profile-info">

                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">
                        {{ auth()->user()->name }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        {{ auth()->user()->email }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Role</span>
                    <span class="info-value role-text">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status Akun</span>
                    <span class="status-active">
                        Aktif
                    </span>
                </div>

            </div>

        </div>

        <div class="account-card">
            <h3>Edit profil</h3>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="field"><label for="name">Nama</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required></div>
                <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></div>
                <div class="form-actions"><button class="btn" type="submit">Simpan profil</button></div>
            </form>
        </div>

        <div class="account-card">
            <h3>Ganti password</h3>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="field"><label for="current_password">Password saat ini</label><input id="current_password" type="password" name="current_password" required></div>
                <div class="field"><label for="password">Password baru</label><input id="password" type="password" name="password" required></div>
                <div class="field"><label for="password_confirmation">Konfirmasi password</label><input id="password_confirmation" type="password" name="password_confirmation" required></div>
                <div class="form-actions"><button class="btn" type="submit">Ganti password</button></div>
            </form>
        </div>


        <div class="account-card">

            <h3>Informasi Akun</h3>

            <p>
                Akun ini digunakan untuk mengakses sistem Jurnal Mengajar
                sesuai dengan hak akses role yang dimiliki.
            </p>

            <div class="account-detail">

                <div>
                    <span>ID Pengguna</span>
                    <strong>#{{ auth()->user()->id }}</strong>
                </div>

                <div>
                    <span>Terdaftar Sejak</span>
                    <strong>
                        {{ auth()->user()->created_at
                            ? auth()->user()->created_at->format('d M Y')
                            : '-' }}
                    </strong>
                </div>

            </div>

        </div>

    </div>

@endsection


@push('styles')

<style>

    .profile-wrapper {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 20px;
        max-width: 1000px;
    }

    .profile-card,
    .account-card {
        background: white;
        border: 1px solid #e1e6ed;
        border-radius: 9px;
    }

    .profile-card {
        overflow: hidden;
    }

    .profile-top {
        display: flex;
        align-items: center;
        gap: 16px;

        padding: 24px;

        border-bottom: 1px solid #edf0f3;
    }

    .profile-avatar {
        width: 65px;
        height: 65px;

        border-radius: 50%;

        background: #e5eef8;
        color: #27425b;

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 25px;
        font-weight: 700;
    }

    .profile-top h2 {
        font-size: 19px;
        color: #1f2937;
        margin-bottom: 7px;
    }

    .role-badge {
        display: inline-block;

        padding: 4px 9px;

        border-radius: 20px;

        background: #edf4ff;
        color: #3478f6;

        font-size: 10px;
        font-weight: 600;
    }


    .profile-info {
        padding: 5px 24px 18px;
    }

    .info-row {
        min-height: 58px;

        display: grid;
        grid-template-columns: 150px 1fr;
        align-items: center;

        border-bottom: 1px solid #f0f2f5;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #8b95a3;
        font-size: 12px;
    }

    .info-value {
        color: #374151;
        font-size: 12px;
        font-weight: 500;
    }

    .role-text {
        text-transform: capitalize;
    }

    .status-active {
        width: fit-content;

        padding: 4px 9px;

        border-radius: 20px;

        background: #e7f8ef;
        color: #15945f;

        font-size: 10px;
        font-weight: 600;
    }


    .account-card {
        padding: 22px;
        height: fit-content;
    }

    .account-card h3 {
        font-size: 15px;
        color: #1f2937;
        margin-bottom: 7px;
    }

    .account-card > p {
        color: #7b8492;
        font-size: 11px;
        line-height: 1.6;
        margin-bottom: 22px;
    }

    .account-detail {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .account-detail div {
        background: #f8fafc;
        border: 1px solid #edf0f3;

        padding: 13px;

        border-radius: 6px;
    }

    .account-detail span {
        display: block;

        color: #8b95a3;

        font-size: 10px;

        margin-bottom: 5px;
    }

    .account-detail strong {
        color: #374151;
        font-size: 12px;
    }


    @media(max-width: 900px) {

        .profile-wrapper {
            grid-template-columns: 1fr;
        }

    }

    @media(max-width: 600px) {

        .info-row {
            grid-template-columns: 1fr;
            gap: 5px;

            padding: 13px 0;
        }

    }

</style>

@endpush