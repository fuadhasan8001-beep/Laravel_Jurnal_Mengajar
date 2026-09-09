<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $field = $request->filled('login') ? 'login' : 'email';
        $credentials = $request->validate([
            $field => [$field === 'email' ? 'email' : 'string', 'required'],
            'password' => ['required'],
        ]);

        $login = $credentials[$field];
        $authCredentials = [
            str_contains($login, '@') ? 'email' : 'username' => $login,
            'password' => $credentials['password'],
            'is_active' => true,
        ];

        if (Auth::attempt($authCredentials)) {
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
            $field => 'Username/email atau password salah.',
        ])->onlyInput($field);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
