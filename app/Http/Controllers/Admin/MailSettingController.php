<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ValidatesSchoolMailSettings;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\MailSettingService;
use App\Services\PlatformSettingService;
use App\Support\MailSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailSettingController extends Controller
{
    use ValidatesSchoolMailSettings;

    public function edit(MailSettingService $mailSettings)
    {
        return view('admin.mail-settings.edit', [
            'setting' => $mailSettings->current(),
            'masker' => $mailSettings,
            'mailGovernance' => $mailSettings->mailGovernance(),
        ]);
    }

    public function update(
        Request $request,
        MailSettingService $mailSettings,
        PlatformSettingService $platformSettings,
        AuditLogService $auditLog
    ) {
        $setting = $mailSettings->current();
        $data = $request->validate(array_merge(
            $this->schoolMailValidationRules($request, $setting, $mailSettings),
            [
                'school_custom_smtp_enabled' => ['nullable', 'boolean'],
                'force_platform_mailer' => ['nullable', 'boolean'],
                'platform_fallback_enabled' => ['nullable', 'boolean'],
            ]
        ));

        $governance = [
            'school_custom_smtp_enabled' => (bool) ($data['school_custom_smtp_enabled'] ?? false),
            'force_platform_mailer' => (bool) ($data['force_platform_mailer'] ?? false),
            'platform_fallback_enabled' => (bool) ($data['platform_fallback_enabled'] ?? false),
        ];

        $settingData = collect($data)
            ->except(['school_custom_smtp_enabled', 'force_platform_mailer', 'platform_fallback_enabled'])
            ->all();

        $setting = $mailSettings->updatePlatform($settingData);

        $platform = $platformSettings->get();
        $metadata = $platform->metadata ?? [];
        $metadata['mail'] = $governance;
        $platform->update(['metadata' => $metadata]);

        $auditLog->log('mail_settings_updated', $setting, null, metadata: [
            'mailer' => $setting->mailer,
            'is_enabled' => $setting->is_enabled,
            'governance' => $governance,
        ], request: $request);

        return back()->with('success', 'Mail settings saved successfully.');
    }

    public function test(Request $request, MailSettingService $mailSettings, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $setting = $mailSettings->current();

        try {
            $delivery = $mailSettings->sendTest($setting, $data['test_email']);
        } catch (\Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('Mail settings test failed.', [
                'mailer' => $mailSettings->platformMailerStatus($setting)['driver'],
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);

            return back()->with('error', 'Platform fallback failed: '.$diagnostic['message']);
        }

        $auditLog->log('mail_settings_test_sent', $setting, null, metadata: [
            'mailer' => $setting->mailer,
        ], request: $request);

        if ($delivery['logged_only']) {
            $message = $delivery['transport'] === 'log'
                ? 'Fallback is configured to log messages only; no external email was delivered.'
                : 'Fallback is configured to use the '.strtoupper($delivery['transport']).' test transport; no external email was delivered.';

            return back()->with('success', $message);
        }

        return back()->with('success', 'Platform mailer accepted the test email for delivery. Inbox delivery is not guaranteed.');
    }
}
