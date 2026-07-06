<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ValidatesSchoolMailSettings;
use App\Http\Controllers\Controller;
use App\Models\MailSetting;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\MailDeliveryAttemptService;
use App\Services\MailSettingService;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class LocalMailSettingController extends Controller
{
    use ValidatesSchoolMailSettings;

    public function edit(MailSettingService $mailSettings): View
    {
        $school = $this->localSchool();
        $setting = $mailSettings->current($school->id);

        return view('admin.local-mail-settings.edit', [
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
        $data = $request->validate($this->schoolMailValidationRules($request, $setting, $mailSettings));
        $passwordChanged = $this->smtpPasswordChanged($data['password'] ?? null);
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
        $testConfiguration = $request->input('test_mode') === 'temporary' ? 'temporary' : 'saved';

        if (! $mailSettings->schoolCustomSmtpAllowed()) {
            return back()->with('error', 'School SMTP override is currently disabled by the platform mail policy.');
        }

        if ($testConfiguration === 'temporary') {
            $request->merge($this->schoolMailSettingsPayload($request, $setting));
        }

        $rules = [
            'test_email' => ['required', 'email:rfc', 'max:255'],
            'test_mode' => ['nullable', 'in:saved,temporary'],
        ];

        if ($testConfiguration === 'temporary') {
            $rules = array_merge($this->schoolMailValidationRules($request, $setting, $mailSettings), $rules);
        }

        $data = $request->validate($rules);
        $settingsData = $testConfiguration === 'temporary' ? Arr::except($data, ['test_email', 'test_mode']) : [];
        $candidate = $testConfiguration === 'temporary'
            ? $mailSettings->candidateForSchool($school, $settingsData, $setting)
            : $setting;

        try {
            $delivery = $testConfiguration === 'temporary'
                ? $mailSettings->sendSchoolTestUsingData($school, $settingsData, $data['test_email'], $setting)
                : $mailSettings->sendSchoolTest($school, $data['test_email']);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('Local school SMTP test failed.', [
                'school_id' => $school->id,
                'host' => $candidate->host,
                'port' => $candidate->port,
                'encryption' => $candidate->encryption,
                'mailer' => 'school_smtp',
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);
            $mailSettings->recordTestResult($setting, 'failed', 'school_smtp', $diagnostic['category'], $testConfiguration);
            $mailSettings->recordDeliveryAttempt([
                'school_id' => $school->id,
                'initiating_user_id' => $request->user()->id,
                'transport' => 'smtp',
                'host' => $candidate->host,
                'port' => $candidate->port,
                'encryption' => $candidate->encryption,
                'sender' => $candidate->from_address,
                'recipient' => $data['test_email'],
                'status' => app(MailDeliveryAttemptService::class)->statusForCategory($diagnostic['category']),
                'safe_error_category' => $diagnostic['category'],
                'sanitized_error_message' => $diagnostic['message'],
                'configuration' => $testConfiguration,
                'external_delivery_attempted' => true,
            ]);

            $this->recordTestAudit($auditLog, 'local_school_mail_settings_test_failed', $candidate, $school, [
                'mailer' => 'school_smtp',
                'is_enabled' => $candidate->is_enabled,
                'error_category' => $diagnostic['category'],
            ], $request);

            return back()
                ->withInput($request->except('password'))
                ->with('error', $diagnostic['message']);
        }

        $mailSettings->recordTestResult(
            $setting,
            'accepted_by_smtp',
            $delivery['mailer'],
            configuration: $testConfiguration,
            providerMessageId: $delivery['provider_message_id'],
            smtpAccepted: true,
        );
        $mailSettings->recordDeliveryAttempt([
            'school_id' => $school->id,
            'initiating_user_id' => $request->user()->id,
            'transport' => 'smtp',
            'host' => $delivery['host'],
            'port' => $delivery['port'],
            'encryption' => $delivery['encryption'],
            'sender' => $delivery['sender'],
            'recipient' => $delivery['recipient'],
            'status' => 'accepted_by_smtp',
            'provider_message_id' => $delivery['provider_message_id'],
            'configuration' => $testConfiguration,
            'external_delivery_attempted' => true,
        ]);
        $this->recordTestAudit($auditLog, 'local_school_mail_settings_test_sent', $candidate, $school, [
            'mailer' => $delivery['mailer'],
            'is_enabled' => $candidate->is_enabled,
            'configuration' => $testConfiguration,
            'fallback_used' => false,
        ], $request);

        $message = 'School SMTP accepted the test message for delivery. SMTP acceptance means the sending server accepted the message. It does not guarantee inbox placement.';

        if ($testConfiguration === 'temporary') {
            $message .= ' Save settings to keep these SMTP details.';
        }

        return back()
            ->withInput($request->except('password'))
            ->with('success', $message)
            ->with('mail_test_result', array_merge($delivery, [
                'configuration' => $testConfiguration,
                'timestamp' => $delivery['accepted_at'],
            ]));
    }

    public function testFallback(
        Request $request,
        MailSettingService $mailSettings,
        AuditLogService $auditLog
    ): RedirectResponse {
        $school = $this->localSchool();
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        try {
            $delivery = $mailSettings->sendPlatformTest($data['test_email']);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('Platform fallback test failed.', [
                'school_id' => $school->id,
                'mailer' => $mailSettings->platformMailerStatus()['driver'],
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);
            $this->recordTestAudit($auditLog, 'platform_fallback_test_failed', $mailSettings->current($school->id), $school, [
                'error_category' => $diagnostic['category'],
            ], $request);
            $status = $mailSettings->platformMailerStatus();
            $mailSettings->recordDeliveryAttempt([
                'school_id' => $school->id,
                'initiating_user_id' => $request->user()->id,
                'transport' => $status['driver'],
                'recipient' => $data['test_email'],
                'status' => app(MailDeliveryAttemptService::class)->statusForCategory($diagnostic['category']),
                'safe_error_category' => $diagnostic['category'],
                'sanitized_error_message' => $diagnostic['message'],
                'fallback_used' => true,
                'external_delivery_attempted' => $status['external_delivery'],
            ]);

            return back()->withInput()->with('error', 'Platform fallback failed: '.$diagnostic['message']);
        }

        $this->recordTestAudit($auditLog, 'platform_fallback_test_completed', $mailSettings->current($school->id), $school, [
            'transport' => $delivery['transport'],
            'logged_only' => $delivery['logged_only'],
        ], $request);
        $mailSettings->recordDeliveryAttempt([
            'school_id' => $school->id,
            'initiating_user_id' => $request->user()->id,
            'transport' => $delivery['transport'],
            'recipient' => $data['test_email'],
            'status' => $delivery['logged_only'] ? 'fallback_non_delivery' : 'fallback_accepted',
            'provider_message_id' => $delivery['provider_message_id'] ?? null,
            'fallback_used' => true,
            'external_delivery_attempted' => ! $delivery['logged_only'],
        ]);

        $message = match (true) {
            ! $delivery['logged_only'] => 'Platform fallback accepted the test message for delivery. Transport: '.$delivery['transport'].'. Inbox placement is not guaranteed.',
            $delivery['transport'] === 'log' => 'No external email was sent because the platform fallback uses a non-delivery transport (LOG).',
            default => 'No external email was sent because the platform fallback uses a non-delivery transport ('.strtoupper($delivery['transport']).').',
        };

        return back()->withInput()->with($delivery['logged_only'] ? 'warning' : 'success', $message);
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
                'exception' => $exception::class,
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
