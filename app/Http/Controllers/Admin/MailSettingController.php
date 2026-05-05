<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\MailSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MailSettingController extends Controller
{
    public function edit(MailSettingService $mailSettings)
    {
        return view('admin.mail-settings.edit', [
            'setting' => $mailSettings->current(),
            'masker' => $mailSettings,
        ]);
    }

    public function update(Request $request, MailSettingService $mailSettings, AuditLogService $auditLog)
    {
        $setting = $mailSettings->current();
        $data = $request->validate([
            'mailer' => ['required', Rule::in(['log', 'smtp'])],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:2000'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);
        $setting->update($data);

        $auditLog->log('mail_settings_updated', $setting, null, metadata: [
            'mailer' => $setting->mailer,
            'is_enabled' => $setting->is_enabled,
        ], request: $request);

        return back()->with('success', 'Mail settings saved successfully.');
    }

    public function test(Request $request, MailSettingService $mailSettings, AuditLogService $auditLog)
    {
        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $setting = $mailSettings->current();

        try {
            $mailSettings->sendTest($setting, $data['test_email']);
        } catch (\Throwable $exception) {
            Log::warning('Mail settings test failed.', [
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Mail test could not be sent. Check the settings and try again.');
        }

        $auditLog->log('mail_settings_test_sent', $setting, null, metadata: [
            'mailer' => $setting->mailer,
        ], request: $request);

        return back()->with('success', 'Test email sent successfully.');
    }
}
