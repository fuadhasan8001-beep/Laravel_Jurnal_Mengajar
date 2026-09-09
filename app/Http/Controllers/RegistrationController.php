<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Notifications\RegistrationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:guru,siswa,sekretaris,piket'],
        ]);

        if (User::where('email', $data['email'])->exists() || RegistrationRequest::where('email', $data['email'])->where('status', 'pending')->exists()) {
            return back()->withInput()->withErrors(['email' => 'Email sudah terdaftar atau sedang menunggu persetujuan.']);
        }

        $registration = RegistrationRequest::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => 'pending',
        ]);

        Notification::send(
            User::where('role', 'admin')->where('is_active', true)->get(),
            new RegistrationNotification($registration, 'submitted')
        );

        return redirect()->route('register.success');
    }

    public function success(): View
    {
        return view('auth.register-success');
    }
}
