<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();
        $url = $item->data['url'] ?? null;
        $parts = is_string($url) ? parse_url($url) : false;
        $path = $parts['path'] ?? '';
        $allowedHosts = [$request->getHost(), parse_url(config('app.url'), PHP_URL_HOST)];

        if ($parts !== false && (! isset($parts['scheme']) || in_array($parts['scheme'], ['http', 'https'], true))
            && (! isset($parts['host']) || in_array($parts['host'], $allowedHosts, true))
            && str_starts_with($path, '/') && ! str_starts_with($path, '//') && ! str_contains($path, '\\')) {
            return redirect($path.(isset($parts['query']) ? '?'.$parts['query'] : ''));
        }

        return redirect('/');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->back();
    }
}
