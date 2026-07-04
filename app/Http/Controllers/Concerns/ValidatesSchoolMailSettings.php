<?php

namespace App\Http\Controllers\Concerns;

use App\Models\MailSetting;
use App\Services\MailSettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait ValidatesSchoolMailSettings
{
    protected function schoolMailValidationRules(
        Request $request,
        MailSetting $setting,
        MailSettingService $mailSettings
    ): array {
        $enabled = $request->boolean('is_enabled');
        $smtpEnabled = $enabled && $request->input('mailer') === 'smtp';
        $passwordState = $mailSettings->passwordState($setting);
        $submittedPassword = $request->input('password');
        $newPassword = filled($submittedPassword)
            && preg_match('/^\*{6,}$/', trim((string) $submittedPassword)) !== 1;
        $passwordRequired = $smtpEnabled
            && filled($request->input('username'))
            && ! $newPassword
            && ! $passwordState['available'];

        return [
            'mailer' => ['required', Rule::in(['log', 'smtp'])],
            'host' => [
                $smtpEnabled ? 'required' : 'nullable',
                'string',
                'max:255',
                'not_regex:/[\s\/:\\\\\r\n]/',
            ],
            'port' => [$smtpEnabled ? 'required' : 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255', 'not_regex:/[\r\n]/', 'required_with:password'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:2000'],
            'encryption' => [$smtpEnabled ? 'required' : 'nullable', Rule::in(['tls', 'ssl', 'none'])],
            'from_address' => [$enabled ? 'required' : 'nullable', 'email:rfc', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'reply_to_email' => ['nullable', 'email:rfc', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:120'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function schoolMailSettingsPayload(Request $request, MailSetting $setting): array
    {
        return [
            'mailer' => $request->input('mailer', $setting->mailer),
            'host' => $request->input('host', $setting->host),
            'port' => $request->input('port', $setting->port),
            'username' => $request->input('username', $setting->username),
            'password' => $request->filled('password') ? $request->input('password') : null,
            'encryption' => $request->input('encryption', $setting->encryption ?: 'tls'),
            'from_address' => $request->input('from_address', $setting->from_address),
            'from_name' => $request->input('from_name', $setting->from_name),
            'reply_to_email' => $request->input('reply_to_email', $setting->reply_to_email),
            'timeout' => $request->input('timeout', data_get($setting->metadata, 'timeout', 10)),
            'is_enabled' => $request->has('is_enabled')
                ? $request->boolean('is_enabled')
                : (bool) $setting->is_enabled,
        ];
    }

    protected function smtpPasswordChanged(mixed $password): bool
    {
        return filled($password)
            && preg_match('/^\*{6,}$/', trim((string) $password)) !== 1;
    }
}
