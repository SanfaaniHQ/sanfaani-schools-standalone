<?php

namespace App\Services;

use App\Models\MailDeliveryAttempt;
use App\Support\MailSecurity;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MailDeliveryAttemptService
{
    public function record(array $attributes): ?MailDeliveryAttempt
    {
        if (! $this->tableIsReady()) {
            return null;
        }

        $safe = [
            'school_id' => $attributes['school_id'] ?? null,
            'initiating_user_id' => $attributes['initiating_user_id'] ?? auth()->id(),
            'provider_profile_id' => filled($attributes['provider_profile_id'] ?? null) ? (int) $attributes['provider_profile_id'] : null,
            'provider_name' => $this->limited($attributes['provider_name'] ?? null, 160),
            'provider_type' => $this->limited($attributes['provider_type'] ?? null, 40),
            'provider_position' => $this->limited($attributes['provider_position'] ?? null, 20),
            'attempt_sequence' => filled($attributes['attempt_sequence'] ?? null) ? (int) $attributes['attempt_sequence'] : null,
            'transport' => $this->limited($attributes['transport'] ?? 'unknown', 50),
            'host' => $this->limited($attributes['host'] ?? null, 255),
            'port' => filled($attributes['port'] ?? null) ? (int) $attributes['port'] : null,
            'encryption' => $this->limited($attributes['encryption'] ?? null, 20),
            'sender' => $this->limited($attributes['sender'] ?? null, 255),
            'recipient' => $this->limited($attributes['recipient'] ?? null, 255),
            'status' => $this->limited($attributes['status'] ?? 'failed', 50),
            'safe_error_category' => $this->limited($attributes['safe_error_category'] ?? null, 80),
            'sanitized_error_message' => MailSecurity::sanitizeError($attributes['sanitized_error_message'] ?? null),
            'provider_message_id' => $this->limited($attributes['provider_message_id'] ?? null, 255),
            'configuration' => ($attributes['configuration'] ?? null) === 'temporary' ? 'temporary' : 'saved',
            'message_kind' => ($attributes['message_kind'] ?? null) === 'test' ? 'test' : 'transactional',
            'fallback_used' => (bool) ($attributes['fallback_used'] ?? false),
            'external_delivery_attempted' => (bool) ($attributes['external_delivery_attempted'] ?? false),
        ];

        try {
            return MailDeliveryAttempt::create($safe);
        } catch (Throwable $exception) {
            logger()->warning('Safe mail delivery attempt could not be recorded.', [
                'school_id' => $safe['school_id'],
                'status' => $safe['status'],
                'exception' => $exception::class,
            ]);

            return null;
        }
    }

    public function latestForSchool(?int $schoolId): ?MailDeliveryAttempt
    {
        if (! $this->tableIsReady()) {
            return null;
        }

        return MailDeliveryAttempt::query()
            ->where('school_id', $schoolId)
            ->latest('id')
            ->first();
    }

    public function statusForCategory(?string $category): string
    {
        return match ($category) {
            'missing_configuration', 'unsupported_encryption' => 'configuration_invalid',
            'missing_password', 'password_decryption_failed' => 'password_unavailable',
            'dns_failed' => 'dns_failed',
            'connection_failed' => 'connection_failed',
            'connection_timeout', 'timeout' => 'timeout',
            'tls_failed' => 'tls_failed',
            'certificate_mismatch', 'certificate_failed' => 'certificate_failed',
            'authentication_failed' => 'authentication_failed',
            'sender_rejected' => 'sender_rejected',
            'recipient_rejected' => 'recipient_rejected',
            'relay_denied' => 'relay_denied',
            'message_rejected' => 'message_rejected',
            default => 'failed',
        };
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('mail_delivery_attempts');
        } catch (Throwable) {
            return false;
        }
    }

    private function limited(mixed $value, int $limit): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return mb_substr(trim((string) $value), 0, $limit);
    }
}
