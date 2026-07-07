<?php

namespace App\Services;

use App\Exceptions\MailConfigurationException;
use App\Models\MailSetting;
use App\Models\School;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Throwable;

class SchoolSmtpService
{
    public const MAILER = 'school_smtp';

    public const PLATFORM_MAILER = 'platform_smtp';

    /**
     * Normalize stored or temporary mail settings into one predictable structure.
     */
    public function normalize(array $settings, ?School $school = null): array
    {
        $from = is_array($settings['from'] ?? null) ? $settings['from'] : [];
        $replyTo = is_array($settings['reply_to'] ?? null) ? $settings['reply_to'] : [];
        $encryption = strtolower(trim((string) ($settings['encryption'] ?? '')));
        $encryption = $encryption === '' ? 'none' : $encryption;

        if (! in_array($encryption, ['ssl', 'tls', 'none'], true)) {
            throw new MailConfigurationException(
                'unsupported_encryption',
                'The selected SMTP encryption mode is not supported. Choose SSL, TLS, or None.'
            );
        }

        $senderAddress = $this->firstFilled([
            $settings['from_address'] ?? null,
            $settings['mail_from_address'] ?? null,
            $from['address'] ?? null,
            $school?->sender_email,
            $school?->email,
            config('mail.from.address'),
        ]);

        $senderName = $this->firstFilled([
            $settings['from_name'] ?? null,
            $settings['mail_from_name'] ?? null,
            $settings['name'] ?? null,
            $from['name'] ?? null,
            $school?->sender_name,
            $school?->name,
            config('mail.from.name'),
            config('app.name', 'Sanfaani Schools'),
        ]) ?? '';

        $replyAddress = $this->firstFilled([
            $settings['reply_to_email'] ?? null,
            $settings['reply_to_address'] ?? null,
            $replyTo['address'] ?? null,
        ]);

        return [
            'transport' => 'smtp',
            'enabled' => (bool) ($settings['is_enabled'] ?? $settings['enabled'] ?? false),
            'mailer' => strtolower((string) ($settings['mailer'] ?? 'smtp')),
            'host' => trim((string) ($settings['host'] ?? '')),
            'port' => (int) ($settings['port'] ?? ($encryption === 'ssl' ? 465 : 587)),
            'username' => $this->nullableTrim($settings['username'] ?? null),
            'password' => isset($settings['password']) ? (string) $settings['password'] : null,
            'password_available' => filled($settings['password'] ?? null),
            'encryption' => $encryption,
            'timeout' => max(1, min(120, (int) ($settings['timeout'] ?? 10))),
            'from' => [
                'address' => $senderAddress,
                'name' => $senderName,
            ],
            'reply_to' => $replyAddress ? [
                'address' => $replyAddress,
                'name' => $this->firstFilled([
                    $settings['reply_to_name'] ?? null,
                    $replyTo['name'] ?? null,
                    $senderName,
                ]) ?? '',
            ] : null,
            'configuration' => ($settings['configuration'] ?? null) === 'temporary' ? 'temporary' : 'saved',
        ];
    }

    public function normalizeSetting(
        MailSetting $setting,
        ?School $school = null,
        ?string $passwordOverride = null
    ): array {
        $password = $passwordOverride;

        if (! filled($password)) {
            $passwordState = $this->passwordState($setting);

            if ($passwordState['unusable']) {
                throw new MailConfigurationException(
                    'password_decryption_failed',
                    'The saved SMTP password cannot be decrypted. Re-enter and save the password.'
                );
            }

            $password = $passwordState['password'];
        }

        $metadata = is_array($setting->metadata) ? $setting->metadata : [];

        $normalized = $this->normalize([
            'is_enabled' => $setting->is_enabled,
            'mailer' => $setting->mailer,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => $setting->username,
            'password' => $password,
            'encryption' => $setting->encryption,
            'timeout' => data_get($metadata, 'timeout', 10),
            'from_address' => $setting->from_address,
            'from_name' => $setting->from_name,
            'reply_to_email' => $setting->reply_to_email,
        ], $school);

        $this->assertSmtpReady($normalized);

        return $normalized;
    }

    public function passwordState(MailSetting $setting): array
    {
        $raw = $setting->getAttributes()['password'] ?? $setting->getRawOriginal('password');

        if (! filled($raw)) {
            return ['available' => false, 'unusable' => false, 'password' => null];
        }

        try {
            return [
                'available' => true,
                'unusable' => false,
                'password' => (string) $setting->password,
            ];
        } catch (Throwable) {
            return ['available' => false, 'unusable' => true, 'password' => null];
        }
    }

    public function assertSmtpReady(array $settings): void
    {
        if (($settings['mailer'] ?? null) !== 'smtp') {
            throw new MailConfigurationException('missing_configuration', 'School SMTP is not selected.');
        }

        if (! ($settings['enabled'] ?? false)) {
            throw new MailConfigurationException('missing_configuration', 'School SMTP is disabled. Enable it before testing.');
        }

        if (! filled($settings['host'] ?? null) || ! filled(data_get($settings, 'from.address'))) {
            throw new MailConfigurationException(
                'missing_configuration',
                'School SMTP is incomplete. Enter the host and From Address.'
            );
        }

        if ((int) ($settings['port'] ?? 0) < 1 || (int) ($settings['port'] ?? 0) > 65535) {
            throw new MailConfigurationException('missing_configuration', 'Enter a valid SMTP port.');
        }

        if (filled($settings['username'] ?? null) && ! filled($settings['password'] ?? null)) {
            throw new MailConfigurationException(
                'missing_password',
                'Enter the SMTP password for the configured username.'
            );
        }
    }

    public function configure(array $settings, string $mailerName = self::MAILER, bool $makeDefault = true): void
    {
        $this->assertSmtpReady($settings);

        Config::set("mail.mailers.{$mailerName}", $this->mailerConfig($settings));
        Config::set('mail.from', $settings['from']);
        Config::set('mail.reply_to', $settings['reply_to']);

        if ($makeDefault) {
            Config::set('mail.default', $mailerName);
        }

        app(MailManager::class)->purge($mailerName);
    }

    public function mailerConfig(array $settings): array
    {
        return [
            'transport' => 'smtp',
            'scheme' => $settings['encryption'] === 'ssl' ? 'smtps' : 'smtp',
            'host' => $settings['host'],
            'port' => $settings['port'],
            'username' => $settings['username'],
            'password' => $settings['password'],
            'timeout' => $settings['timeout'],
            'auto_tls' => $settings['encryption'] !== 'none',
            'require_tls' => $settings['encryption'] === 'tls',
            'local_domain' => config('mail.mailers.smtp.local_domain'),
        ];
    }

    public function forgetRuntimeMailers(): void
    {
        $manager = app(MailManager::class);
        $manager->purge(self::MAILER);
        $manager->purge(self::PLATFORM_MAILER);
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function nullableTrim(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
