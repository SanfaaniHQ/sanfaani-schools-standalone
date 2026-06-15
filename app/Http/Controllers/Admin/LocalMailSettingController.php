<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\MailSettingService;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class LocalMailSettingController extends Controller
{
    public function edit(MailSettingService $mailSettings): View
    {
        $school = $this->localSchool();

        return view('admin.local-mail-settings.edit', [
            'school' => $school,
            'setting' => $mailSettings->current($school->id),
            'platformSetting' => $mailSettings->current(),
            'schoolScopeReady' => $mailSettings->schoolScopeIsReady(),
            'schoolCustomSmtpAllowed' => $mailSettings->schoolCustomSmtpAllowed(),
            'forcePlatformMailer' => $mailSettings->forcePlatformMailer(),
            'platformFallbackEnabled' => $mailSettings->platformFallbackEnabled(),
            'platformFallbackConfigured' => $mailSettings->platformMailerConfigured(),
            'masker' => $mailSettings,
        ]);
    }

    public function update(
        Request $request,
        MailSettingService $mailSettings,
        AuditLogService $auditLog
    ): RedirectResponse {
        $school = $this->localSchool();

        if (! $mailSettings->schoolScopeIsReady()) {
            return back()->with('error', 'Email delivery settings are not ready yet. Complete database setup and run the latest migrations first.');
        }

        if (! $mailSettings->schoolCustomSmtpAllowed() && $request->boolean('is_enabled')) {
            return back()->with('error', 'School SMTP override is currently disabled by the platform mail policy.');
        }

        $setting = $mailSettings->current($school->id);
        $data = $request->validate($this->validationRules($request, $setting));
        $passwordChanged = filled($data['password'] ?? null);
        $oldValues = $mailSettings->auditSnapshot($setting);

        $setting = $mailSettings->updateForSchool($school, $data);

        $auditLog->log('local_school_mail_settings_updated', $setting, $school, oldValues: $oldValues, newValues: $mailSettings->auditSnapshot($setting), metadata: [
            'mailer' => $setting->mailer,
            'is_enabled' => $setting->is_enabled,
            'password_changed' => $passwordChanged,
        ], request: $request);

        return back()->with('success', 'Email delivery settings saved.');
    }

    public function test(
        Request $request,
        MailSettingService $mailSettings,
        AuditLogService $auditLog
    ): RedirectResponse {
        $school = $this->localSchool();
        $setting = $mailSettings->current($school->id);
        $request->merge($this->settingsPayload($request, $setting));

        if (! $mailSettings->schoolCustomSmtpAllowed() && $request->boolean('is_enabled')) {
            return back()->with('error', 'School SMTP override is currently disabled by the platform mail policy.');
        }

        $data = $request->validate(array_merge($this->validationRules($request, $setting), [
            'test_email' => ['required', 'email', 'max:255'],
        ]));

        $settingsData = Arr::except($data, 'test_email');
        $candidate = $mailSettings->candidateForSchool($school, $settingsData, $setting);

        try {
            $delivery = $mailSettings->sendSchoolTestUsingData($school, $settingsData, $data['test_email'], $setting);
        } catch (Throwable $exception) {
            Log::warning('Local school mail settings test failed.', [
                'school_id' => $school->id,
                'message' => $exception->getMessage(),
            ]);

            $this->recordTestAudit($auditLog, 'local_school_mail_settings_test_failed', $candidate, $school, [
                'mailer' => $candidate->mailer,
                'is_enabled' => $candidate->is_enabled,
                'error' => MailSecurity::sanitizeError($exception),
            ], $request);

            return back()
                ->withInput($request->except('password'))
                ->with('error', 'Email test failed: '.MailSecurity::sanitizeError($exception));
        }

        $this->recordTestAudit($auditLog, 'local_school_mail_settings_test_sent', $candidate, $school, [
            'mailer' => $candidate->mailer,
            'is_enabled' => $candidate->is_enabled,
            'fallback_used' => $delivery['fallback_used'],
            'primary_error' => MailSecurity::sanitizeError($delivery['primary_error'] ?? null),
        ], $request);

        $message = $delivery['fallback_used']
            ? 'School SMTP failed; configured platform fallback sent the test email.'
            : 'Test email sent. Save settings to keep these SMTP details.';

        return back()
            ->withInput($request->except('password'))
            ->with('success', $message);
    }

    private function validationRules(Request $request, MailSetting $setting): array
    {
        $enabled = $request->boolean('is_enabled');
        $smtpEnabled = $enabled && $request->input('mailer') === 'smtp';
        $passwordRequired = $smtpEnabled && ! filled($setting->getRawOriginal('password'));

        return [
            'mailer' => ['required', Rule::in(['log', 'smtp'])],
            'host' => [$smtpEnabled ? 'required' : 'nullable', 'string', 'max:255'],
            'port' => [$smtpEnabled ? 'required' : 'nullable', 'integer', 'min:1', 'max:65535'],
            'username' => [$smtpEnabled ? 'required' : 'nullable', 'string', 'max:255'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'max:2000'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'from_address' => [$enabled ? 'required' : 'nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'email', 'max:255'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }

    private function settingsPayload(Request $request, MailSetting $setting): array
    {
        return [
            'mailer' => $request->input('mailer', $setting->mailer),
            'host' => $request->input('host', $setting->host),
            'port' => $request->input('port', $setting->port),
            'username' => $request->input('username', $setting->username),
            'password' => $request->filled('password') ? $request->input('password') : null,
            'encryption' => $request->input('encryption', $setting->encryption),
            'from_address' => $request->input('from_address', $setting->from_address),
            'from_name' => $request->input('from_name', $setting->from_name),
            'reply_to_email' => $request->input('reply_to_email', $setting->reply_to_email),
            'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : (bool) $setting->is_enabled,
        ];
    }

    private function recordTestAudit(
        AuditLogService $auditLog,
        string $action,
        MailSetting $setting,
        School $school,
        array $metadata,
        Request $request
    ): void {
        try {
            $auditLog->log($action, $setting->exists ? $setting : null, $school, metadata: $metadata, request: $request);
        } catch (Throwable $exception) {
            Log::warning('Local mail settings audit failed.', [
                'school_id' => $school->id,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function localSchool(): School
    {
        $school = School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        abort_unless($school, 404, 'Create the school profile before managing email delivery.');

        return $school;
    }
}
