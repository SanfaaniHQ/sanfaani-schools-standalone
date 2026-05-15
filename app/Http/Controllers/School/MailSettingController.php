<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\MailSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class MailSettingController extends Controller
{
    public function edit(Request $request, MailSettingService $mailSettings, CurrentSchoolService $currentSchool)
    {
        $school = $this->schoolAdminSchool($request, $currentSchool);

        return view('school.mail-settings.edit', [
            'school' => $school,
            'setting' => $mailSettings->current($school->id),
            'platformSetting' => $mailSettings->current(),
            'schoolScopeReady' => $mailSettings->schoolScopeIsReady(),
            'masker' => $mailSettings,
        ]);
    }

    public function update(
        Request $request,
        MailSettingService $mailSettings,
        CurrentSchoolService $currentSchool,
        AuditLogService $auditLog
    ) {
        $school = $this->schoolAdminSchool($request, $currentSchool);

        if (! $mailSettings->schoolScopeIsReady()) {
            return back()->with('error', 'School mail settings are not ready yet. Run migrations first.');
        }

        $setting = $mailSettings->current($school->id);
        $data = $request->validate($this->validationRules($request, $setting));
        $passwordChanged = filled($data['password'] ?? null);
        $oldValues = $mailSettings->auditSnapshot($setting);

        $setting = $mailSettings->updateForSchool($school, $data);
        $newValues = $mailSettings->auditSnapshot($setting);

        $auditLog->log('school_mail_settings_updated', $setting, $school, oldValues: $oldValues, newValues: $newValues, metadata: [
            'mailer' => $setting->mailer,
            'is_enabled' => $setting->is_enabled,
            'password_changed' => $passwordChanged,
        ], request: $request);

        return back()->with('success', 'School mail settings saved successfully.');
    }

    public function test(
        Request $request,
        MailSettingService $mailSettings,
        CurrentSchoolService $currentSchool,
        AuditLogService $auditLog
    ) {
        $school = $this->schoolAdminSchool($request, $currentSchool);

        $setting = $mailSettings->current($school->id);
        $request->merge($this->settingsPayload($request, $setting));

        $data = $request->validate(array_merge($this->validationRules($request, $setting), [
            'test_email' => ['required', 'email', 'max:255'],
        ]));

        $settingsData = Arr::except($data, 'test_email');
        $candidate = $mailSettings->candidateForSchool($school, $settingsData, $setting);

        try {
            $delivery = $mailSettings->sendSchoolTestUsingData($school, $settingsData, $data['test_email'], $setting);
        } catch (Throwable $exception) {
            Log::warning('School mail settings test failed.', [
                'school_id' => $school->id,
                'message' => $exception->getMessage(),
            ]);

            $this->recordTestAudit($auditLog, 'school_mail_settings_test_failed', $candidate, $school, [
                'mailer' => $candidate->mailer,
                'is_enabled' => $candidate->is_enabled,
                'validated_before_save' => true,
                'error' => $this->safeMailError($exception),
            ], $request);

            return back()
                ->withInput($request->except('password'))
                ->with('error', 'Mail test failed. Settings were not saved.');
        }

        $this->recordTestAudit($auditLog, 'school_mail_settings_test_sent', $candidate, $school, [
            'mailer' => $candidate->mailer,
            'is_enabled' => $candidate->is_enabled,
            'validated_before_save' => true,
            'fallback_used' => $delivery['fallback_used'],
            'primary_error' => $this->safeMailError($delivery['primary_error'] ?? null),
        ], $request);

        $message = $delivery['fallback_used']
            ? 'School SMTP failed; platform fallback sent the test email.'
            : 'SMTP test email sent successfully. Settings are not saved until you click Save Settings.';

        return back()
            ->withInput($request->except('password'))
            ->with('success', $message);
    }

    private function schoolAdminSchool(Request $request, CurrentSchoolService $currentSchool): School
    {
        abort_unless($request->user()?->hasRole('school_admin'), 403);

        $school = $currentSchool->get();
        abort_if(! $school, 403);

        return $school;
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
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
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
            Log::warning('School mail settings test audit failed.', [
                'school_id' => $school->id,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function safeMailError(Throwable|string|null $error): ?string
    {
        if (! filled($error)) {
            return null;
        }

        $message = $error instanceof Throwable ? $error->getMessage() : (string) $error;

        return Str::limit(preg_replace('/(password|secret|token)=([^\\s]+)/i', '$1=***', $message), 500);
    }
}
