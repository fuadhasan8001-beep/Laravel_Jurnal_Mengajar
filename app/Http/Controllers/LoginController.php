<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $user = Auth::user();

        switch ($user->role) {
            case 'admin':
                return redirect('/admin');

            case 'guru':
                return redirect('/guru');

            case 'siswa':
                return redirect('/siswa');

            case 'sekretaris':
                return redirect('/sekretaris');

            case 'piket':
                return redirect('/piket');

            default:
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Role akun tidak valid.',
                ]);
        }
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->onlyInput('email');
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}