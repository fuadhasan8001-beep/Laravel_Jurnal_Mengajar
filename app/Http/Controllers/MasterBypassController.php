<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MasterBypassController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isMaster(), 403);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'guru_id' => ['nullable', 'required_if:enabled,1', 'exists:gurus,id'],
        ]);

        $request->session()->put('master_bypass_enabled', (bool) $data['enabled']);
        $request->session()->put('master_bypass_guru_id', $data['enabled'] ? (int) $data['guru_id'] : null);

        return back()->with('success', $data['enabled'] ? 'Bypass master diaktifkan.' : 'Bypass master dinonaktifkan.');
    }
}
