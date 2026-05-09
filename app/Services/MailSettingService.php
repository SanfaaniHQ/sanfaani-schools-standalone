<?php

namespace App\Services;

use App\Models\MailSetting;
use App\Models\School;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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

    public function current(?int $schoolId = null): MailSetting
    {
        return MailSetting::firstOrCreate(['school_id' => $schoolId], [
            'mailer' => config('mail.default', 'log'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'reply_to_email' => null,
            'is_enabled' => false,
        ]);
    }

    public function resolveForSchool(?School $school): MailSetting
    {
        $schoolSetting = $school ? $this->current($school->id) : null;

        if ($schoolSetting && $schoolSetting->is_enabled) {
            return $schoolSetting;
        }

        return $this->current();
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
        Config::set('mail.reply_to.address', $setting->reply_to_email ?: null);

        if ($setting->mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $setting->host);
            Config::set('mail.mailers.smtp.port', $setting->port ?: 587);
            Config::set('mail.mailers.smtp.username', $setting->username);
            Config::set('mail.mailers.smtp.password', $setting->password);
            Config::set('mail.mailers.smtp.encryption', $setting->encryption ?: null);
        }

        app(MailManager::class)->forgetMailers();
    }

    public function applyForSchool(?School $school): void
    {
        $this->apply($this->resolveForSchool($school));
    }

    public function sendTest(MailSetting $setting, string $recipient): void
    {
        $this->apply($setting);

        Mail::raw('Sanfaani Schools mail settings test completed successfully.', function ($message) use ($recipient) {
            $message->to($recipient)->subject('Sanfaani Schools Mail Test');
        });
    }

    public function withSchoolMailContext(?School $school, callable $callback): mixed
    {
        $original = config('mail');

        $this->applyForSchool($school);

        try {
            return $callback();
        } finally {
            Config::set('mail', $original);
            app(MailManager::class)->forgetMailers();
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
}
