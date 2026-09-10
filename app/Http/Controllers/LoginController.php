<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
        $studentUserId = Siswa::query()
            ->where('nis', $login)
            ->value('user_id');

        $authCredentials = [
            $studentUserId !== null ? 'id' : (str_contains($login, '@') ? 'email' : 'username') => $studentUserId ?? $login,
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
            $field => 'NISN, username, atau password salah.',
        ])->onlyInput($field);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $identity = trim($request->input('login'));
        $user = User::query()
            ->where('username', $identity)
            ->orWhere('email', $identity)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'login' => 'Akun tidak ditemukan. Masukkan NIS siswa atau email yang benar.',
            ]);
        }

        return back()->with('status', 'Silakan hubungi admin untuk menyiapkan ulang password akun Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
