<?php

namespace App\Http\Middleware;

use App\Services\PlatformSettingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IdleTimeoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $timeoutMinutes = $this->timeoutMinutes();

        if ($timeoutMinutes <= 0) {
            return $next($request);
        }

        $lastActivity = (int) $request->session()->get('last_activity_at', now()->timestamp);

        if ($lastActivity < now()->subMinutes($timeoutMinutes)->timestamp) {
            $user = $request->user();
            $loginRoute = $user?->hasRole('super_admin') ? 'admin.login' : 'login';

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route($loginRoute)
                ->with('status', 'Your session expired due to inactivity. Please sign in again.');
        }

        $request->session()->put('last_activity_at', now()->timestamp);

        return $next($request);
    }

    private function timeoutMinutes(): int
    {
        $fallback = (int) config('sanfaani.idle_timeout_minutes', 30);

        try {
            $settings = app(PlatformSettingService::class)->get();
        } catch (Throwable) {
            return $fallback;
        }

        $metadata = $settings->metadata ?: [];

        if (is_string($metadata)) {
            $metadata = json_decode($metadata, true) ?: [];
        }

        return (int) ($metadata['idle_timeout_minutes'] ?? $fallback);
    }
}
