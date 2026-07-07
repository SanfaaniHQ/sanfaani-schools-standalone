<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Concerns\ValidatesSchoolMailSettings;
use App\Http\Controllers\Controller;
use App\Models\MailDeliveryAttempt;
use App\Models\MailSetting;
use App\Models\School;
use App\Models\SchoolMailProviderProfile;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\MailDeliveryAttemptService;
use App\Services\MailSettingService;
use App\Services\SchoolMailDeliveryOrchestrator;
use App\Services\SchoolMailProviderService;
use App\Support\MailSecurity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class MailSettingController extends Controller
{
    use ValidatesSchoolMailSettings;

    public function edit(Request $request, MailSettingService $mailSettings, CurrentSchoolService $currentSchool): View
    {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $setting = $mailSettings->current($school->id);
        $providerService = app(SchoolMailProviderService::class);
        $providers = $providerService->forSchool($school);
        $editingProvider = null;

        if ($request->filled('provider')) {
            $editingProvider = $providers->firstWhere('id', (int) $request->integer('provider'));
            abort_unless($editingProvider, 404);
        }

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
            'latestDeliveryAttempt' => $mailSettings->latestDeliveryAttempt($school->id),
            'masker' => $mailSettings,
            'providers' => $providers,
            'providerTypes' => SchoolMailProviderService::TYPES,
            'editingProvider' => $editingProvider,
            'providerService' => $providerService,
            'recentAttempts' => MailDeliveryAttempt::query()
                ->where('school_id', $school->id)
                ->with(['providerProfile', 'initiatingUser'])
                ->latest('id')
                ->limit(10)
                ->get(),
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
        $testMode = $request->input('test_mode') === 'temporary' ? 'temporary' : 'saved';

        if (! $mailSettings->schoolCustomSmtpAllowed()) {
            return back()->with('error', 'Custom school SMTP is currently disabled by the platform administrator.');
        }

        if ($testMode === 'temporary') {
            $request->merge($this->schoolMailSettingsPayload($request, $setting));
        }

        $rules = [
            'test_email' => ['required', 'email:rfc', 'max:255'],
            'test_mode' => ['nullable', 'in:saved,temporary'],
        ];

        if ($testMode === 'temporary') {
            $rules = array_merge($this->schoolMailValidationRules($request, $setting, $mailSettings), $rules);
        }

        $data = $request->validate($rules);
        $settingsData = $testMode === 'temporary' ? Arr::except($data, ['test_email', 'test_mode']) : [];
        $candidate = $testMode === 'temporary'
            ? $mailSettings->candidateForSchool($school, $settingsData, $setting)
            : $setting;
        $testConfiguration = $testMode;

        try {
            $delivery = $testMode === 'temporary'
                ? $mailSettings->sendSchoolTestUsingData($school, $settingsData, $data['test_email'], $setting)
                : $mailSettings->sendSchoolTest($school, $data['test_email']);
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
            $mailSettings->recordTestResult($setting, 'failed', 'school_smtp', $diagnostic['category'], $testConfiguration, externalDeliveryAttempted: true);
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

        $mailSettings->recordTestResult(
            $setting,
            'accepted_by_smtp',
            $delivery['mailer'],
            configuration: $testConfiguration,
            providerMessageId: $delivery['provider_message_id'],
            smtpAccepted: true,
            externalDeliveryAttempted: true,
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
        $this->recordTestAudit($auditLog, 'school_mail_settings_test_sent', $candidate, $school, [
            'mailer' => $delivery['mailer'],
            'is_enabled' => $candidate->is_enabled,
            'validated_before_save' => true,
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

        $this->recordTestAudit($auditLog, 'school_platform_fallback_test_completed', $mailSettings->current($school->id), $school, [
            'transport' => $delivery['transport'],
            'logged_only' => $delivery['logged_only'],
        ], $request);

        $message = match (true) {
            ! $delivery['logged_only'] => 'Platform fallback accepted the test message for delivery. Transport: '.$delivery['transport'].'. Inbox placement is not guaranteed.',
            $delivery['transport'] === 'log' => 'No external email was sent because the platform fallback uses a non-delivery transport (LOG).',
            default => 'No external email was sent because the platform fallback uses a non-delivery transport ('.strtoupper($delivery['transport']).').',
        };

        return back()->withInput()->with($delivery['logged_only'] ? 'warning' : 'success', $message);
    }

    public function storeProvider(
        Request $request,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $data = $this->validateProvider($request, $providers);
        $profile = $providers->save($school, $data);

        if ($request->input('save_action') === 'save_and_test') {
            $test = $request->validate([
                'test_email' => ['required', 'email:rfc', 'max:255'],
                'subject_label' => ['nullable', 'string', 'max:100', 'not_regex:/[\r\n]/'],
            ]);

            try {
                $result = app(SchoolMailDeliveryOrchestrator::class)->testProvider(
                    $school,
                    $profile,
                    $test['test_email'],
                    $test['subject_label'] ?? null
                );
            } catch (Throwable $exception) {
                return redirect()->route('school.mail-settings.edit', ['provider' => $profile->id])
                    ->with('error', $profile->name.' was saved, but its test failed: '.MailSecurity::diagnostic($exception)['message']);
            }

            return redirect()->route('school.mail-settings.edit')
                ->with('success', $profile->name.' was saved and its SMTP server accepted the test message.')
                ->with('mail_test_notice', 'SMTP acceptance does not guarantee Inbox placement.')
                ->with('mail_test_result', $result);
        }

        return redirect()->route('school.mail-settings.edit')
            ->with('success', $profile->name.' was added without changing any other provider.');
    }

    public function updateProvider(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $profile = $providers->save($school, $this->validateProvider($request, $providers, $provider), $provider);

        return redirect()->route('school.mail-settings.edit')
            ->with('success', $profile->name.' was updated. Other provider profiles were retained.');
    }

    public function destroyProvider(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $name = $provider->name;
        $providers->delete($school, $provider);

        return redirect()->route('school.mail-settings.edit')->with('success', $name.' was deleted.');
    }

    public function makeProviderPrimary(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $providers->makePrimary($school, $provider);

        return back()->with('success', $provider->name.' is now the primary provider.');
    }

    public function toggleProvider(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $updated = $providers->toggle($school, $provider);

        return back()->with('success', $updated->name.' is now '.($updated->is_enabled ? 'enabled.' : 'disabled.'));
    }

    public function moveProvider(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $data = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        $providers->move($school, $provider, $data['direction']);

        return back()->with('success', 'Secondary provider priority was updated.');
    }

    public function testProvider(
        Request $request,
        SchoolMailProviderProfile $provider,
        CurrentSchoolService $currentSchool,
        SchoolMailDeliveryOrchestrator $delivery
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $this->authorizeProvider($school, $provider);
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
            'subject_label' => ['nullable', 'string', 'max:100', 'not_regex:/[\r\n]/'],
        ]);

        try {
            $result = $delivery->testProvider($school, $provider, $data['test_email'], $data['subject_label'] ?? null);
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);

            return back()->withInput($request->except('password'))->with('error', $provider->name.': '.$diagnostic['message']);
        }

        return back()->with('success', $provider->name.' SMTP accepted the test message for delivery.')
            ->with('mail_test_notice', 'SMTP acceptance means the sending server accepted the message. It does not guarantee Inbox placement.')
            ->with('mail_test_result', $result);
    }

    public function testTemporaryProvider(
        Request $request,
        CurrentSchoolService $currentSchool,
        SchoolMailProviderService $providers,
        SchoolMailDeliveryOrchestrator $delivery
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $data = $this->validateProvider($request, $providers);
        $test = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
            'subject_label' => ['nullable', 'string', 'max:100', 'not_regex:/[\r\n]/'],
        ]);
        $candidate = new SchoolMailProviderProfile($providers->normalizedUpdateData($data));
        $candidate->school_id = $school->id;
        $candidate->setRelation('school', $school);

        try {
            $result = $delivery->testProvider(
                $school,
                $candidate,
                $test['test_email'],
                $test['subject_label'] ?? null,
                'temporary'
            );
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);

            return back()->withInput($request->except('password'))->with('error', $candidate->name.': '.$diagnostic['message']);
        }

        return back()->withInput($request->except('password'))
            ->with('success', $candidate->name.' SMTP accepted the temporary test message for delivery. The provider was not saved.')
            ->with('mail_test_notice', 'SMTP acceptance does not guarantee Inbox placement.')
            ->with('mail_test_result', $result);
    }

    public function testProviderChain(
        Request $request,
        CurrentSchoolService $currentSchool,
        MailSettingService $mailSettings
    ): RedirectResponse {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $data = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
            'subject_label' => ['nullable', 'string', 'max:100', 'not_regex:/[\r\n]/'],
        ]);

        try {
            $result = $mailSettings->deliverForSchool(
                $school,
                function () use ($data) {
                    $subject = filled($data['subject_label'] ?? null)
                        ? $data['subject_label'].' — Full Delivery Chain Test'
                        : 'Full Delivery Chain Test';

                    return Mail::mailer()->raw(
                        'This test follows the configured primary-to-secondary provider chain.',
                        fn ($message) => $message->to($data['test_email'])->subject($subject)
                    );
                },
                [
                    'recipient' => $data['test_email'],
                    'configuration' => 'saved',
                    'message_kind' => 'test',
                ]
            );
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', MailSecurity::diagnostic($exception)['message']);
        }

        $provider = $result['provider_name'] ?? strtoupper((string) ($result['transport'] ?? 'platform fallback'));

        return back()->with('success', $provider.' accepted the full-chain test. No later provider was attempted.')
            ->with('mail_test_notice', 'SMTP acceptance does not guarantee Inbox placement.')
            ->with('mail_test_result', $result);
    }

    public function history(Request $request, CurrentSchoolService $currentSchool): View
    {
        $school = $this->schoolAdminSchool($request, $currentSchool);
        $filters = $request->validate([
            'provider' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:50'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'message_kind' => ['nullable', Rule::in(['test', 'transactional'])],
            'provider_position' => ['nullable', Rule::in(['primary', 'secondary', 'platform'])],
            'error_category' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $attempts = MailDeliveryAttempt::query()
            ->where('school_id', $school->id)
            ->with(['providerProfile', 'initiatingUser'])
            ->when($filters['provider'] ?? null, fn ($query, $id) => $query->where('provider_profile_id', $id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['recipient'] ?? null, fn ($query, $recipient) => $query->where('recipient', 'like', '%'.$recipient.'%'))
            ->when($filters['message_kind'] ?? null, fn ($query, $kind) => $query->where('message_kind', $kind))
            ->when($filters['provider_position'] ?? null, fn ($query, $position) => $query->where('provider_position', $position))
            ->when($filters['error_category'] ?? null, fn ($query, $category) => $query->where('safe_error_category', $category))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('school.mail-settings.history', [
            'school' => $school,
            'attempts' => $attempts,
            'providers' => app(SchoolMailProviderService::class)->forSchool($school),
            'filters' => $filters,
        ]);
    }

    private function validateProvider(
        Request $request,
        SchoolMailProviderService $providers,
        ?SchoolMailProviderProfile $profile = null
    ): array {
        $password = $profile ? $providers->passwordState($profile) : ['available' => false];
        $newPassword = $request->filled('password') && preg_match('/^\*{6,}$/', trim((string) $request->input('password'))) !== 1;
        $needsPassword = $request->boolean('is_enabled')
            && $request->filled('username')
            && ! $newPassword
            && ! $password['available'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'provider_type' => ['required', Rule::in(array_keys(SchoolMailProviderService::TYPES))],
            'host' => ['required', 'string', 'max:255', 'not_regex:/[\s\/:\\\\\r\n]/'],
            'port' => ['required', 'integer', Rule::in([465, 587])],
            'username' => ['nullable', 'email:rfc', 'max:255'],
            'password' => [$needsPassword ? 'required' : 'nullable', 'string', 'max:2000'],
            'encryption' => ['required', Rule::in(['ssl', 'tls'])],
            'from_address' => ['required', 'email:rfc', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'reply_to_address' => ['nullable', 'email:rfc', 'max:255'],
            'reply_to_name' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
            'timeout' => ['required', 'integer', 'min:1', 'max:120'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_primary' => ['nullable', 'boolean'],
            'priority' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $validPair = ((int) $data['port'] === 465 && $data['encryption'] === 'ssl')
            || ((int) $data['port'] === 587 && $data['encryption'] === 'tls');

        if (! $validPair) {
            throw ValidationException::withMessages([
                'encryption' => 'Use SSL with port 465 or TLS with port 587.',
            ]);
        }

        if (in_array($data['provider_type'], ['gmail', 'google_workspace'], true)
            && strtolower($data['host']) !== 'smtp.gmail.com') {
            throw ValidationException::withMessages(['host' => 'Gmail and Google Workspace use smtp.gmail.com.']);
        }

        if ($data['provider_type'] === 'cpanel'
            && filled($data['username'] ?? null)
            && strcasecmp($data['username'], $data['from_address']) !== 0) {
            throw ValidationException::withMessages([
                'from_address' => 'For cPanel/Webmail, use the authenticated mailbox as the From Address.',
            ]);
        }

        return $data;
    }

    private function authorizeProvider(School $school, SchoolMailProviderProfile $provider): void
    {
        abort_unless((int) $provider->school_id === (int) $school->id, 403);
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
