@extends('layouts.app')

@section('title', 'Profil')

@section('content')

    <div class="profile-heading">
        <div>
            <h1 class="page-title">Profil Saya</h1>
            <p class="page-subtitle">Kelola informasi akun dan keamanan akses kamu.</p>
        </div>
    </div>

    <div class="profile-wrapper">
        <section class="profile-card profile-overview" aria-labelledby="profile-overview-title">
            <div class="profile-top">
                <div class="profile-avatar" aria-hidden="true">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="profile-identity">
                    <span class="profile-kicker">Akun aktif</span>
                    <h2 id="profile-overview-title">{{ auth()->user()->name }}</h2>
                    <span class="role-badge">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>

            <div class="profile-info">
                <div class="info-row">
                    <span class="info-label">Nama lengkap</span>
                    <span class="info-value">{{ auth()->user()->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ auth()->user()->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status akun</span>
                    <span class="status-active">Aktif</span>
                </div>
            </div>
        </section>

        <section class="account-card profile-panel" aria-labelledby="edit-profile-title">
            <div class="panel-heading">
                <span class="panel-index">01</span>
                <div>
                    <h2 id="edit-profile-title">Informasi dasar</h2>
                    <p>Perbarui nama dan email yang digunakan.</p>
                </div>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <div class="form-actions"><button class="btn" type="submit">Simpan perubahan</button></div>
            </form>
        </section>

        <section class="account-card profile-panel" aria-labelledby="password-title">
            <div class="panel-heading">
                <span class="panel-index">02</span>
                <div>
                    <h2 id="password-title">Keamanan akun</h2>
                    <p>Gunakan password yang kuat dan mudah kamu ingat.</p>
                </div>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="current_password">Password saat ini</label>
                    <input id="current_password" type="password" name="current_password" required>
                </div>
                <div class="field">
                    <label for="password">Password baru</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="field">
                    <label for="password_confirmation">Konfirmasi password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
                <div class="form-actions"><button class="btn" type="submit">Perbarui password</button></div>
            </form>
        </section>

        <section class="account-card profile-panel account-meta" aria-labelledby="account-info-title">
            <div class="panel-heading">
                <span class="panel-index">03</span>
                <div>
                    <h2 id="account-info-title">Detail akun</h2>
                    <p>Ringkasan identitas akun di sistem.</p>
                </div>
            </div>
            <div class="account-detail">
                <div>
                    <span>ID pengguna</span>
                    <strong>#{{ auth()->user()->id }}</strong>
                </div>
                <div>
                    <span>Terdaftar sejak</span>
                    <strong>{{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '-' }}</strong>
                </div>
                <div>
                    <span>Role akses</span>
                    <strong class="role-text">{{ ucfirst(auth()->user()->role) }}</strong>
                </div>
            </div>
        </section>
    </div>

@endsection


@push('styles')

<style>

    .profile-wrapper {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 14px;
        width: min(100%, 1080px);
    }

    .profile-card,
    .account-card {
        background: var(--warm-white);
        border: 1px solid var(--line);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(74, 64, 50, .08);
    }

    .profile-card {
        overflow: hidden;
    }

    .profile-top {
        display: flex;
        align-items: center;
        flex-direction: column;
        gap: 12px;

        padding: 24px 20px 20px;

        border-bottom: 1px solid var(--line);
        text-align: center;
    }

    .profile-avatar {
        width: 58px;
        height: 58px;

        border-radius: 50%;

        background: var(--cream);
        color: var(--gold-dark);

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 22px;
        font-weight: 700;
    }

    .profile-identity {
        min-width: 0;
    }

    .profile-kicker {
        display: block;
        margin-bottom: 5px;
        color: var(--text-muted);
        font-size: 11px;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .profile-top h2 {
        font-size: 20px;
        color: var(--text-dark);
        margin: 0 0 9px;
        overflow-wrap: anywhere;
    }

    .role-badge {
        display: inline-block;

        padding: 4px 9px;

        border-radius: 20px;

        background: #f4e7bd;
        color: var(--orange-dark);

        font-size: 10px;
        font-weight: 600;
    }


    .profile-info {
        padding: 4px 20px 12px;
    }

    .info-row {
        display: flex;
        min-height: 52px;
        align-items: center;
        justify-content: space-between;
        gap: 14px;

        border-bottom: 1px solid var(--line);
        flex-wrap: wrap;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-muted);
        font-size: 12px;
    }

    .info-value {
        min-width: 0;
        color: var(--text-dark);
        font-size: 12px;
        font-weight: 500;
        overflow-wrap: anywhere;
        text-align: right;
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
        padding: 20px;
        height: fit-content;
    }

    .account-card form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .panel-heading {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }

    .panel-index {
        display: inline-flex;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: var(--cream);
        color: var(--gold-dark);
        font-size: 11px;
        font-weight: 700;
    }

    .panel-heading h2 {
        margin: 0 0 4px;
        color: var(--text-dark);
        font-size: 16px;
    }

    .panel-heading p {
        margin: 0;
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .account-card h3 {
        font-size: 15px;
        color: var(--text-dark);
        margin: 0 0 12px;
    }

    .profile-panel .field input {
        min-height: 46px;
        border-color: var(--line);
        background: var(--warm-white);
    }

    .profile-panel .field input:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(168, 137, 74, .2);
    }

    .profile-panel .form-actions {
        margin-top: 4px;
    }

    .profile-panel .form-actions .btn {
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
    }

    .account-card > p {
        color: var(--text-muted);
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
        background: var(--cream);
        border: 1px solid var(--line);

        padding: 13px;

        border-radius: 6px;
    }

    .account-detail span {
        display: block;

        color: var(--text-muted);

        font-size: 10px;

        margin-bottom: 5px;
    }

    .account-detail strong {
        color: var(--text-dark);
        font-size: 12px;
    }

    .account-meta {
        background: var(--cream);
    }


    @media(min-width: 901px) {

        .profile-wrapper {
            grid-template-columns: minmax(280px, .85fr) minmax(0, 1.15fr);
            gap: 20px;
        }

        .profile-overview {
            grid-row: span 2;
        }

        .profile-top {
            align-items: flex-start;
            flex-direction: row;
            padding: 28px 24px 24px;
            text-align: left;
        }

        .profile-info {
            padding-right: 24px;
            padding-left: 24px;
        }

        .account-card {
            padding: 24px;
        }

    }

    @media(max-width: 600px) {

        .profile-wrapper {
            gap: 16px;
        }

        .account-card {
            padding: 18px;
        }

        .info-row {
            align-items: flex-start;
            flex-direction: column;
            gap: 4px;
            padding: 12px 0;
        }

        .info-value {
            text-align: left;
        }

        .profile-panel .form-actions,
        .profile-panel .form-actions .btn {
            width: 100%;
        }

    }

</style>

@endpush
