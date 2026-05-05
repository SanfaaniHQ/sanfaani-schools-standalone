<?php

namespace App\Services;

use App\Models\MailSetting;
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

    public function current(): MailSetting
    {
        return MailSetting::firstOrCreate([], [
            'mailer' => config('mail.default', 'log'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'is_enabled' => false,
        ]);
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

        if ($setting->mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $setting->host);
            Config::set('mail.mailers.smtp.port', $setting->port ?: 587);
            Config::set('mail.mailers.smtp.username', $setting->username);
            Config::set('mail.mailers.smtp.password', $setting->password);
            Config::set('mail.mailers.smtp.encryption', $setting->encryption ?: null);
        }
    }

    public function sendTest(MailSetting $setting, string $recipient): void
    {
        $this->apply($setting);

        Mail::raw('Sanfaani Schools mail settings test completed successfully.', function ($message) use ($recipient) {
            $message->to($recipient)->subject('Sanfaani Schools Mail Test');
        });
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
