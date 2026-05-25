<?php

namespace App\Services\Marketing;

use App\Models\MarketingSuppression;
use App\Models\MarketingUnsubscribe;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Throwable;

class UnsubscribeService
{
    public function tokenForEmail(string $email): string
    {
        $encrypted = Crypt::encryptString(json_encode([
            'email' => $this->normalize($email),
            'purpose' => 'marketing_unsubscribe',
        ]));

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public function recordFromToken(string $token, array $metadata = []): bool
    {
        $email = null;

        try {
            $encrypted = base64_decode(strtr($token, '-_', '+/'), true);
            $payload = json_decode(Crypt::decryptString($encrypted ?: $token), true, flags: JSON_THROW_ON_ERROR);
            $email = filter_var($payload['email'] ?? null, FILTER_VALIDATE_EMAIL)
                ? $this->normalize($payload['email'])
                : null;
        } catch (Throwable) {
            //
        }

        $this->record($email, 'unsubscribed', 'public_unsubscribe', $token, $metadata);

        return $email !== null;
    }

    public function record(?string $email, string $reason = 'unsubscribed', string $source = 'marketing', ?string $token = null, array $metadata = []): MarketingUnsubscribe
    {
        $email = $email ? $this->normalize($email) : null;
        $tokenHash = $token ? hash('sha256', $token) : null;
        $emailHash = $email ? $this->emailHash($email) : null;

        if ($email) {
            MarketingSuppression::updateOrCreate(
                ['email' => $email],
                [
                    'reason' => $reason,
                    'source' => $source,
                    'suppressed_at' => now(),
                    'metadata' => $metadata,
                ]
            );
        }

        $lookup = $emailHash ? ['email_hash' => $emailHash] : ['token_hash' => $tokenHash];

        return MarketingUnsubscribe::updateOrCreate(
            $lookup,
            [
                'email' => $email,
                'email_hash' => $emailHash,
                'token_hash' => $tokenHash,
                'reason' => $reason,
                'source' => $source,
                'unsubscribed_at' => now(),
                'metadata' => $metadata,
            ]
        );
    }

    public function isUnsubscribed(?string $email): bool
    {
        if (! $email) {
            return true;
        }

        $email = $this->normalize($email);

        return MarketingSuppression::where('email', $email)->exists()
            || MarketingUnsubscribe::where('email_hash', $this->emailHash($email))->exists();
    }

    private function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    private function emailHash(string $email): string
    {
        return hash('sha256', $this->normalize($email));
    }
}
