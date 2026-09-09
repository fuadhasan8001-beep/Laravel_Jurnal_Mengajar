<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Notifications\RegistrationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $query = RegistrationRequest::query()
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($builder) => $builder->where(fn ($search) => $search
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->latest();

        return view('admin.registrations.index', [
            'registrations' => $query->paginate(15)->withQueryString(),
            'counts' => RegistrationRequest::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
        ]);
    }

    public function show(RegistrationRequest $registration): View
    {
        return view('admin.registrations.show', compact('registration'));
    }

    public function approve(RegistrationRequest $registration): RedirectResponse
    {
        abort_if($registration->status !== 'pending', 422, 'Pendaftaran sudah diproses.');

        $user = DB::transaction(function () use ($registration): User {
            $baseUsername = Str::of($registration->name)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '.')
                ->trim('.')
                ->toString();
            $username = $baseUsername;
            $suffix = 2;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername.'.'.$suffix++;
            }

            $user = User::create([
                'name' => $registration->name,
                'username' => $username,
                'email' => $registration->email,
                'password' => $registration->password,
                'role' => $registration->role,
                'is_active' => true,
            ]);

            $registration->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return $user;
        });

        $user->notify(new RegistrationNotification($registration, 'approved'));

        return redirect()->route('admin.registrations.index')->with('success', 'Pendaftaran berhasil disetujui.');
    }

    public function reject(Request $request, RegistrationRequest $registration): RedirectResponse
    {
        abort_if($registration->status !== 'pending', 422, 'Pendaftaran sudah diproses.');
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $registration->notify(new RegistrationNotification($registration, 'rejected'));

        return redirect()->route('admin.registrations.index')->with('success', 'Pendaftaran ditolak.');
    }
}
