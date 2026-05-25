<?php

namespace App\Services\Security;

use App\Support\Security\SecurityCheckResult;
use Illuminate\Support\Facades\Route;

class TokenSafetyService
{
    public function checks(): array
    {
        $expiry = (int) config('security.token_default_expiry_minutes', 60);
        $signedRoutes = collect([
            'marketing.track.open',
            'marketing.track.click',
            'marketing.unsubscribe',
            'verification.verify',
        ])->filter(fn (string $route): bool => Route::has($route))->values()->all();

        return array_map(fn (SecurityCheckResult $check): array => $check->toArray(), [
            $expiry > 0
                ? SecurityCheckResult::pass('token_expiry', 'Token expiry', "Default token expiry guidance is {$expiry} minutes.")
                : SecurityCheckResult::fail('token_expiry', 'Token expiry', 'Token expiry must be greater than zero.'),
            ! empty($signedRoutes)
                ? SecurityCheckResult::pass('signed_routes', 'Signed URL routes', 'Signed URL routes are present for marketing tracking and verification flows.', ['routes' => $signedRoutes])
                : SecurityCheckResult::warning('signed_routes', 'Signed URL routes', 'Review public token routes and use signed or encrypted tokens.'),
            SecurityCheckResult::info('public_unsubscribe', 'Public unsubscribe safety', 'The public unsubscribe token is encrypted and does not reveal whether a contact exists.'),
            SecurityCheckResult::info('storage_links', 'Storage/file links', 'Use signed download routes for private files and avoid exposing storage/app/private paths.'),
        ]);
    }
}
