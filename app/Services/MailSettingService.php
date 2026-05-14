<?php

namespace App\Services;

use App\Models\MailSetting;
use App\Models\School;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class MailSettingService
{
    public function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('mail_settings');
        } catch (Throwable) {
            return false;
        }
    }

    public function schoolScopeIsReady(): bool
    {
        try {
            return $this->tableIsReady() && Schema::hasColumn('mail_settings', 'school_id');
        } catch (Throwable) {
            return false;
        }
    }

    public function replyToColumnIsReady(): bool
    {
        try {
            return $this->tableIsReady() && Schema::hasColumn('mail_settings', 'reply_to_email');
        } catch (Throwable) {
            return false;
        }
    }

    public function current(?int $schoolId = null): MailSetting
    {
        if (! $this->tableIsReady()) {
            return new MailSetting([
                'school_id' => $schoolId,
                'mailer' => config('mail.default', 'log'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'reply_to_email' => null,
                'is_enabled' => false,
            ]);
        }

        $hasSchoolScope = $this->schoolScopeIsReady();
        $attributes = $hasSchoolScope ? ['school_id' => $schoolId] : [];

        return MailSetting::firstOrCreate($attributes, $this->defaultAttributes($schoolId));
    }

    public function configured(?int $schoolId = null): ?MailSetting
    {
        if (! $this->tableIsReady()) {
            return null;
        }

        try {
            return MailSetting::query()
                ->when($this->schoolScopeIsReady(), fn ($query) => $query->where('school_id', $schoolId))
                ->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function applyConfigured(): void
    {
        $setting = $this->configured();

        try {
            if (! $setting) {
                return;
            }

            $this->apply($setting);
        } catch (Throwable) {
            return;
        }
    }

    private function defaultAttributes(?int $schoolId = null): array
    {
        $attributes = [
            'mailer' => config('mail.default', 'log'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'is_enabled' => false,
        ];

        if ($this->schoolScopeIsReady()) {
            $attributes['school_id'] = $schoolId;
        }

        if ($this->replyToColumnIsReady()) {
            $attributes['reply_to_email'] = null;
        }

        return $attributes;
    }

    public function resolveForSchool(?School $school): MailSetting
    {
        $schoolSetting = $school && $this->schoolScopeIsReady()
            ? $this->current($school->id)
            : null;

        if ($schoolSetting && $schoolSetting->is_enabled) {
            return $schoolSetting;
        }

        return $this->current();
    }

    public function updateForSchool(School $school, array $data): MailSetting
    {
        $setting = $this->current($school->id);
        $setting->fill($this->normalizedUpdateData($data, $setting));
        $setting->save();

        return $setting->fresh() ?? $setting;
    }

    public function apply(?MailSetting $setting = null): void
    {
        if (! $setting && ! $this->tableIsReady()) {
            return;
        }

        $setting ??= $this->current();

        if (! $setting->is_enabled) {
            return;
        }

        Config::set('mail.default', $setting->mailer);
        Config::set('mail.from.address', $setting->from_address ?: config('mail.from.address'));
        Config::set('mail.from.name', $setting->from_name ?: config('mail.from.name'));

        if ($this->replyToColumnIsReady()) {
            Config::set('mail.reply_to.address', $setting->reply_to_email ?: null);
        }

        if ($setting->mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $setting->host);
            Config::set('mail.mailers.smtp.port', $setting->port ?: 587);
            Config::set('mail.mailers.smtp.username', $setting->username);
            Config::set('mail.mailers.smtp.password', $setting->password);
            Config::set('mail.mailers.smtp.scheme', $this->smtpScheme($setting));
            Config::set('mail.mailers.smtp.encryption', $setting->encryption ?: null);
            Config::set('mail.mailers.smtp.timeout', $this->smtpTimeout());
        }

        app(MailManager::class)->forgetMailers();
    }

    public function applyForSchool(?School $school): void
    {
        $this->apply($this->resolveForSchool($school));
    }

    public function sendTest(MailSetting $setting, string $recipient): void
    {
        $this->withMailSettingContext($setting, fn () => $this->sendTestMessage($recipient));
    }

    public function sendSchoolTest(School $school, string $recipient): array
    {
        return $this->sendSchoolSettingTest($school, $this->resolveForSchool($school), $recipient);
    }

    public function sendSchoolTestUsingData(School $school, array $data, string $recipient, ?MailSetting $existing = null): array
    {
        $setting = $this->candidateForSchool($school, $data, $existing);

        return $this->sendSchoolSettingTest($school, $setting, $recipient);
    }

    public function candidateForSchool(School $school, array $data, ?MailSetting $existing = null): MailSetting
    {
        $existing ??= $this->current($school->id);
        $data = $this->normalizedUpdateData($data, $existing);

        if (! array_key_exists('password', $data) && filled($existing->getRawOriginal('password'))) {
            $data['password'] = $existing->password;
        }

        $setting = new MailSetting(array_merge([
            'school_id' => $school->id,
            'mailer' => $existing->mailer,
            'host' => $existing->host,
            'port' => $existing->port,
            'username' => $existing->username,
            'encryption' => $existing->encryption,
            'from_address' => $existing->from_address,
            'from_name' => $existing->from_name,
            'reply_to_email' => $this->replyToColumnIsReady() ? $existing->reply_to_email : null,
            'is_enabled' => $existing->is_enabled,
            'metadata' => $existing->metadata,
        ], $data));

        $setting->exists = false;

        return $setting;
    }

    private function sendSchoolSettingTest(School $school, MailSetting $setting, string $recipient): array
    {
        try {
            $this->withMailSettingContext($setting, fn () => $this->sendTestMessage($recipient));

            return [
                'fallback_used' => false,
                'primary_error' => null,
                'mailer' => $setting->mailer,
            ];
        } catch (Throwable $primaryException) {
            if (! $this->shouldTryPlatformFallback($setting)) {
                throw $primaryException;
            }

            try {
                $this->withPlatformMailContext(fn () => $this->sendTestMessage($recipient));
            } catch (Throwable $fallbackException) {
                throw new RuntimeException(
                    'School SMTP test failed: '.$primaryException->getMessage().' Platform fallback failed: '.$fallbackException->getMessage(),
                    previous: $fallbackException
                );
            }

            return [
                'fallback_used' => true,
                'primary_error' => $primaryException->getMessage(),
                'mailer' => $setting->mailer,
            ];
        }
    }

    public function withMailSettingContext(MailSetting $setting, callable $callback): mixed
    {
        $original = config('mail');

        try {
            $this->apply($setting);

            return $callback();
        } finally {
            Config::set('mail', $original);
            app(MailManager::class)->forgetMailers();
        }
    }

    public function withSchoolMailContext(?School $school, callable $callback): mixed
    {
        return $this->withMailSettingContext($this->resolveForSchool($school), $callback);
    }

    public function withPlatformMailContext(callable $callback): mixed
    {
        return $this->withSchoolMailContext(null, $callback);
    }

    public function hasEnabledSchoolMailer(?School $school): bool
    {
        if (! $school || ! $this->schoolScopeIsReady()) {
            return false;
        }

        try {
            return MailSetting::where('school_id', $school->id)
                ->where('is_enabled', true)
                ->exists();
        } catch (Throwable) {
            return false;
        }
    }

    public function mask(?string $value): string
    {
        if (! filled($value)) {
            return 'Not set';
        }

        $value = (string) $value;

        return str_repeat('*', max(8, min(12, strlen($value))));
    }

    public function maskedPassword(MailSetting $setting): string
    {
        return filled($setting->getRawOriginal('password')) ? '************' : 'Not set';
    }

    public function auditSnapshot(MailSetting $setting): array
    {
        return [
            'school_id' => $setting->school_id,
            'mailer' => $setting->mailer,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => filled($setting->username) ? $this->mask($setting->username) : null,
            'encryption' => $setting->encryption,
            'from_address' => $setting->from_address,
            'from_name' => $setting->from_name,
            'reply_to_email' => $this->replyToColumnIsReady() ? $setting->reply_to_email : null,
            'is_enabled' => $setting->is_enabled,
            'password_set' => filled($setting->getRawOriginal('password')),
        ];
    }

    public function normalizedUpdateData(array $data, ?MailSetting $setting = null): array
    {
        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($setting && array_key_exists('password', $data) && ! filled($data['password'])) {
            unset($data['password']);
        }

        if (! $this->replyToColumnIsReady()) {
            unset($data['reply_to_email']);
        }

        return $data;
    }

    private function shouldTryPlatformFallback(MailSetting $setting): bool
    {
        return filled($setting->school_id)
            && $setting->is_enabled
            && $setting->mailer === 'smtp';
    }

    private function smtpScheme(MailSetting $setting): ?string
    {
        if ($setting->encryption === 'ssl' || (int) $setting->port === 465) {
            return 'smtps';
        }

        if ($setting->encryption === 'tls') {
            return 'smtp';
        }

        return null;
    }

    private function smtpTimeout(): int
    {
        return max(1, (int) config('mail.mailers.smtp.timeout', 10));
    }

    private function sendTestMessage(string $recipient): void
    {
        Mail::raw('Sanfaani Schools mail settings test completed successfully.', function ($message) use ($recipient) {
            $message->to($recipient)->subject('Sanfaani Schools Mail Test');
        });
    }
}
