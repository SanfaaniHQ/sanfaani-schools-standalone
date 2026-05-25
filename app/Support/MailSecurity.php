<?php

namespace App\Support;

use App\Models\MarketingCampaignRecipient;
use App\Services\Security\SecretRedactionService;
use Illuminate\Support\Str;
use Throwable;

class MailSecurity
{
    public static function sanitizeError(Throwable|string|null $error, int $limit = 500): ?string
    {
        if (! filled($error)) {
            return null;
        }

        $message = app(SecretRedactionService::class)->redact($error, $limit) ?? '';

        return Str::limit(trim($message), $limit, '');
    }

    public static function trackingToken(MarketingCampaignRecipient $recipient): string
    {
        $seed = implode('|', [
            $recipient->getKey(),
            $recipient->marketing_campaign_id,
            Str::lower((string) $recipient->email),
            optional($recipient->created_at)->timestamp,
        ]);

        return hash_hmac('sha256', $seed, self::key());
    }

    public static function fingerprint(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return hash_hmac('sha256', trim((string) $value), self::key());
    }

    public static function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;
        $html = preg_replace('/<(script|style|iframe|object|embed)\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $html) ?? $html;
        $html = preg_replace('/\s+(href|src)\s*=\s*([\'"])\s*(javascript|data):.*?\2/is', '', $html) ?? $html;
        $html = preg_replace('/\s+(href|src)\s*=\s*(javascript|data):[^\s>]+/is', '', $html) ?? $html;

        return trim($html);
    }

    private static function key(): string
    {
        return (string) config('app.key', 'sanfaani-schools');
    }
}
