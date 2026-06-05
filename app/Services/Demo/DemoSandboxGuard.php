<?php

namespace App\Services\Demo;

use App\Models\DemoCredential;
use App\Models\DemoSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DemoSandboxGuard
{
    public function isDemoUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->demoCredentials()
            ->where('status', DemoCredential::STATUS_ACTIVE)
            ->whereHas('demoSession', function ($query): void {
                $query->where('status', DemoSession::STATUS_ACTIVE)
                    ->where(function ($query): void {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    });
            })
            ->exists();
    }

    public function shouldBlock(Request $request): bool
    {
        if (! (bool) config('demo.marketplace.safe_mode', true)) {
            return false;
        }

        if (! $this->isDemoUser($request->user())) {
            return false;
        }

        $routeName = (string) $request->route()?->getName();

        if ($routeName !== '' && $this->routeIsBlocked($routeName)) {
            return true;
        }

        return $this->looksLikeHighRiskWrite($request);
    }

    private function routeIsBlocked(string $routeName): bool
    {
        return collect(config('demo.marketplace.blocked_routes', []))
            ->contains(fn (string $pattern): bool => Str::is($pattern, $routeName));
    }

    private function looksLikeHighRiskWrite(Request $request): bool
    {
        if ($request->isMethodSafe()) {
            return false;
        }

        return Str::contains($request->path(), [
            'backup',
            'license',
            'mail-settings',
            'payment',
            'update',
            'bulk/send',
            'confirm-payment',
            'revoke',
            'archive',
            'destroy',
        ]);
    }
}
