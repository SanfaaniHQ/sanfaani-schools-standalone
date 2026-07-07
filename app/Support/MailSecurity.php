<?php

namespace App\Support;

use App\Exceptions\MailConfigurationException;
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

    public static function diagnostic(Throwable|string $error): array
    {
        if ($error instanceof MailConfigurationException) {
            return [
                'category' => $error->category,
                'message' => $error->getMessage(),
            ];
        }

        $message = self::sanitizeError($error) ?? '';
        $lower = Str::lower($message);

        return match (true) {
            Str::contains($lower, ['decrypt', 'mac is invalid', 'payload is invalid']) => [
                'category' => 'password_decryption_failed',
                'message' => 'The saved SMTP password cannot be decrypted. Re-enter and save the password.',
            ],
            Str::contains($lower, ['authentication failed', 'auth failed', '535 ', 'invalid credentials', 'username and password not accepted']) => [
                'category' => 'authentication_failed',
                'message' => 'SMTP authentication failed. Confirm the full email address and mailbox or App Password.',
            ],
            Str::contains($lower, ['getaddrinfo', 'php_network_getaddresses', 'name or service not known', 'nodename nor servname']) => [
                'category' => 'dns_failed',
                'message' => 'The SMTP hostname could not be resolved. Confirm the outgoing server hostname.',
            ],
            Str::contains($lower, ['timed out', 'timeout']) => [
                'category' => 'connection_timeout',
                'message' => 'The SMTP connection timed out. Confirm the host, port, and provider firewall settings.',
            ],
            Str::contains($lower, ['certificate', 'peer name', 'hostname mismatch', 'does not match']) => [
                'category' => 'certificate_mismatch',
                'message' => 'The SMTP certificate does not match the hostname. Use the exact outgoing server hostname shown by the provider or cPanel.',
            ],
            Str::contains($lower, ['starttls', 'tls', 'ssl operation failed', 'crypto']) => [
                'category' => 'tls_failed',
                'message' => 'TLS negotiation failed. Confirm whether the provider requires SSL on 465 or TLS on 587.',
            ],
            Str::contains($lower, ['connection refused', 'actively refused', 'could not connect', 'unable to connect', 'network is unreachable']) => [
                'category' => 'connection_failed',
                'message' => 'Could not connect to the SMTP host on the selected port.',
            ],
            Str::contains($lower, ['sender address rejected', 'mail from', 'sender rejected']) => [
                'category' => 'sender_rejected',
                'message' => 'The SMTP server rejected the sender address. Use the authenticated mailbox or an authorised alias.',
            ],
            Str::contains($lower, ['relay access denied', 'relay denied', 'not permitted to relay']) => [
                'category' => 'relay_denied',
                'message' => 'The SMTP server denied relaying. Confirm the authenticated account and From Address.',
            ],
            Str::contains($lower, ['recipient address rejected', 'rcpt to', 'recipient rejected']) => [
                'category' => 'recipient_rejected',
                'message' => 'The SMTP server rejected the test recipient address.',
            ],
            Str::contains($lower, ['message rejected', 'transaction failed']) => [
                'category' => 'message_rejected',
                'message' => 'The SMTP server rejected the message.',
            ],
            default => [
                'category' => 'smtp_failed',
                'message' => 'School SMTP failed. Confirm the server details and try again.',
            ],
        };
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
