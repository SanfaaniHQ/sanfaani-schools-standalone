<?php

namespace App\Http\Middleware;

use App\Services\CurrentSchoolService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    public function handle(Request $request, Closure $next, string $roles, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if (! $user) {
            abort(403);
        }

        $allowedRoles = collect(explode('|', $roles))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values();

        if ($allowedRoles->isEmpty()) {
            abort(403);
        }

        $activeRole = app(CurrentSchoolService::class)->roleContext($user);

        if ($activeRole && $allowedRoles->contains($activeRole)) {
            return $next($request);
        }

        abort(403, 'This workspace is not available for your active role.');
    }
}
