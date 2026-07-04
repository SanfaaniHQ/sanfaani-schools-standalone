<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Concerns\ValidatesSchoolMailSettings;
use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\MailSettingService;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class MailSettingController extends Controller
{
    use ValidatesSchoolMailSettings;

    public function edit(Request $request, MailSettingService $mailSettings, CurrentSchoolService $currentSchool): View
    {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $setting = $mailSettings->current($school->id);

        return view('school.mail-settings.edit', [
            'school' => $school,
            'setting' => $setting,
            'platformSetting' => $mailSettings->current(),
            'schoolScopeReady' => $mailSettings->schoolScopeIsReady(),
            'schoolCustomSmtpAllowed' => $mailSettings->schoolCustomSmtpAllowed(),
            'forcePlatformMailer' => $mailSettings->forcePlatformMailer(),
            'platformFallbackEnabled' => $mailSettings->platformFallbackEnabled(),
            'platformFallbackConfigured' => $mailSettings->platformMailerConfigured(),
            'platformStatus' => $mailSettings->platformMailerStatus(),
            'schoolStatus' => $mailSettings->schoolMailerStatus($setting),
            'masker' => $mailSettings,
        ]);
    }

    public function update(
        Request $request,
        MailSettingService $mailSettings,
        CurrentSchoolService $currentSchool,
        AuditLogService $auditLog
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);

        if (! $mailSettings->schoolScopeIsReady()) {
            return back()->with('error', 'School mail settings are not ready yet. Run migrations first.');
        }

        if (! $mailSettings->schoolCustomSmtpAllowed() && $request->boolean('is_enabled')) {
            return back()->with('error', 'Custom school SMTP is currently disabled by the platform administrator.');
        }

        $setting = $mailSettings->current($school->id);
        $data = $request->validate($this->schoolMailValidationRules($request, $setting, $mailSettings));
        $passwordChanged = $this->smtpPasswordChanged($data['password'] ?? null);
        $oldValues = $mailSettings->auditSnapshot($setting);

        $setting = $mailSettings->updateForSchool($school, $data);
        $auditLog->log('school_mail_settings_updated', $setting, $school, oldValues: $oldValues, newValues: $mailSettings->auditSnapshot($setting), metadata: [
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
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $setting = $mailSettings->current($school->id);
        $request->merge($this->schoolMailSettingsPayload($request, $setting));

        if (! $mailSettings->schoolCustomSmtpAllowed() && $request->boolean('is_enabled')) {
            return back()->with('error', 'Custom school SMTP is currently disabled by the platform administrator.');
        }

        $data = $request->validate(array_merge($this->schoolMailValidationRules($request, $setting, $mailSettings), [
            'test_email' => ['required', 'email:rfc', 'max:255'],
        ]));
        $settingsData = Arr::except($data, 'test_email');
        $candidate = $mailSettings->candidateForSchool($school, $settingsData, $setting);
        $testConfiguration = $mailSettings->candidateMatchesSaved($candidate, $setting) ? 'saved' : 'temporary';

        try {
            $delivery = $mailSettings->sendSchoolTestUsingData($school, $settingsData, $data['test_email'], $setting);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('School SMTP test failed.', [
                'school_id' => $school->id,
                'host' => $candidate->host,
                'port' => $candidate->port,
                'encryption' => $candidate->encryption,
                'mailer' => 'school_smtp',
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);
            $mailSettings->recordTestResult($setting, 'failed', 'school_smtp', $diagnostic['category'], $testConfiguration);

            $this->recordTestAudit($auditLog, 'school_mail_settings_test_failed', $candidate, $school, [
                'mailer' => 'school_smtp',
                'is_enabled' => $candidate->is_enabled,
                'validated_before_save' => true,
                'error_category' => $diagnostic['category'],
            ], $request);

            return back()
                ->withInput($request->except('password'))
                ->with('error', $diagnostic['message']);
        }

        $mailSettings->recordTestResult($setting, 'accepted', $delivery['mailer'], configuration: $testConfiguration);
        $this->recordTestAudit($auditLog, 'school_mail_settings_test_sent', $candidate, $school, [
            'mailer' => $delivery['mailer'],
            'is_enabled' => $candidate->is_enabled,
            'validated_before_save' => true,
            'configuration' => $testConfiguration,
            'fallback_used' => false,
        ], $request);

        $message = 'School SMTP accepted the test email for delivery. Transport: school_smtp. Inbox delivery is not guaranteed.';

        if ($testConfiguration === 'temporary') {
            $message .= ' Save settings to keep these SMTP details.';
        }

        return back()
            ->withInput($request->except('password'))
            ->with('success', $message);
    }

    public function testFallback(
        Request $request,
        MailSettingService $mailSettings,
        CurrentSchoolService $currentSchool,
        AuditLogService $auditLog
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        try {
            $delivery = $mailSettings->sendPlatformTest($data['test_email']);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('School platform fallback test failed.', [
                'school_id' => $school->id,
                'mailer' => $mailSettings->platformMailerStatus()['driver'],
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);

            return back()->withInput()->with('error', 'Platform fallback failed: '.$diagnostic['message']);
        }

        $this->recordTestAudit($auditLog, 'school_platform_fallback_test_completed', $mailSettings->current($school->id), $school, [
            'transport' => $delivery['transport'],
            'logged_only' => $delivery['logged_only'],
        ], $request);

        $message = match (true) {
            ! $delivery['logged_only'] => 'Platform fallback accepted the test email for delivery. Transport: '.$delivery['transport'].'. Inbox delivery is not guaranteed.',
            $delivery['transport'] === 'log' => 'Fallback is configured to log messages only; no external email was delivered.',
            default => 'Fallback is configured to use the '.strtoupper($delivery['transport']).' test transport; no external email was delivered.',
        };

        return back()->withInput()->with('success', $message);
    }

    private function schoolAdminSchool(Request $request, CurrentSchoolService $currentSchool): School
    {
        abort_unless($currentSchool->roleContext($request->user()) === 'school_admin', 403);

        $school = $currentSchool->get();
        abort_if(! $school, 403);

        return $school;
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
                'exception' => $exception::class,
            ]);
        }
    }
}
