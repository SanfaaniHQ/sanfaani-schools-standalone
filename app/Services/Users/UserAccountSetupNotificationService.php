<?php

namespace App\Services\Users;

use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\User;
use App\Services\CommunicationService;
use App\Services\PlatformSettingService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Throwable;

class UserAccountSetupNotificationService
{
    public const ACCOUNT_CREATED_SETUP_LINK = 'account_created_setup_link';

    public const ACCOUNT_SETUP_LINK_RESENT = 'account_setup_link_resent';

    public const ACCOUNT_DISABLED = 'account_disabled';

    public const ACCOUNT_ENABLED = 'account_enabled';

    public const ACCOUNT_ARCHIVED = 'account_archived';

    public const ACCOUNT_RESTORED = 'account_restored';

    public function __construct(
        private CommunicationService $communications,
        private PlatformSettingService $platformSettings
    ) {}

    /**
     * @return array{sent: bool, status: string, log: ?CommunicationLog, error: ?string}
     */
    public function sendSetupLink(
        User $user,
        ?School $school = null,
        string $eventKey = self::ACCOUNT_CREATED_SETUP_LINK,
        ?string $role = null,
        ?Authenticatable $sender = null
    ): array {
        $recipient = $user->getEmailForPasswordReset();
        $school ??= $this->schoolFor($user);

        if (! filled($recipient)) {
            Log::warning('Account setup email skipped because recipient email is missing.', [
                'user_id' => $user->id,
                'school_id' => $school?->id,
                'event_key' => $eventKey,
            ]);

            return $this->failedResult('missing_recipient');
        }

        $token = Password::broker()->createToken($user);
        $setupUrl = $this->setupUrl($token, $recipient);
        $roleLabel = $this->roleLabel($role ?: $this->roleFor($user, $school));
        $portalName = $this->portalName($school);
        $eventCopy = $this->setupCopy($eventKey, $portalName);

        try {
            $log = $this->communications->sendTransactionalEmail(
                $school,
                $recipient,
                $eventCopy['subject'],
                $eventCopy['headline'],
                $this->setupBody($user, $school, $roleLabel),
                $eventKey,
                [
                    'event_key' => $eventKey,
                    'user_id' => $user->id,
                    'role' => $role ?: $this->roleFor($user, $school),
                    'role_label' => $roleLabel,
                    'action_url' => $setupUrl,
                    'action_label' => __('ui.set_password'),
                    'expires_minutes' => $this->expiryMinutes(),
                ],
                CommunicationService::CATEGORY_STAFF_LIFECYCLE,
                $sender
            );

            if ($log->status !== CommunicationLog::STATUS_SENT) {
                Log::warning('Account setup email was not sent.', [
                    'user_id' => $user->id,
                    'school_id' => $school?->id,
                    'event_key' => $eventKey,
                    'communication_log_id' => $log->id,
                    'status' => $log->status,
                    'failure_reason' => $log->failure_reason,
                ]);

                return [
                    'sent' => false,
                    'status' => (string) $log->status,
                    'log' => $log,
                    'error' => $log->failure_reason,
                ];
            }

            return [
                'sent' => true,
                'status' => (string) $log->status,
                'log' => $log,
                'error' => null,
            ];
        } catch (Throwable $exception) {
            Log::warning('Account setup email failed unexpectedly.', [
                'user_id' => $user->id,
                'school_id' => $school?->id,
                'event_key' => $eventKey,
                'message' => $exception->getMessage(),
            ]);

            return $this->failedResult($exception->getMessage());
        }
    }

    /**
     * @return array{sent: bool, status: string, log: ?CommunicationLog, error: ?string}
     */
    public function sendLifecycleNotice(
        User $user,
        string $eventKey,
        ?School $school = null,
        ?string $role = null,
        ?Authenticatable $sender = null
    ): array {
        $recipient = $user->email;
        $school ??= $this->schoolFor($user);

        if (! filled($recipient)) {
            return $this->failedResult('missing_recipient');
        }

        $copy = $this->lifecycleCopy($eventKey, $this->portalName($school));

        try {
            $log = $this->communications->sendTransactionalEmail(
                $school,
                $recipient,
                $copy['subject'],
                $copy['headline'],
                $copy['body'],
                $eventKey,
                [
                    'event_key' => $eventKey,
                    'user_id' => $user->id,
                    'role' => $role ?: $this->roleFor($user, $school),
                    'role_label' => $this->roleLabel($role ?: $this->roleFor($user, $school)),
                    'action_url' => $copy['action_url'],
                    'action_label' => $copy['action_label'],
                ],
                CommunicationService::CATEGORY_STAFF_LIFECYCLE,
                $sender
            );

            if ($log->status !== CommunicationLog::STATUS_SENT) {
                Log::warning('Account lifecycle email was not sent.', [
                    'user_id' => $user->id,
                    'school_id' => $school?->id,
                    'event_key' => $eventKey,
                    'communication_log_id' => $log->id,
                    'status' => $log->status,
                    'failure_reason' => $log->failure_reason,
                ]);
            }

            return [
                'sent' => $log->status === CommunicationLog::STATUS_SENT,
                'status' => (string) $log->status,
                'log' => $log,
                'error' => $log->failure_reason,
            ];
        } catch (Throwable $exception) {
            Log::warning('Account lifecycle email failed unexpectedly.', [
                'user_id' => $user->id,
                'school_id' => $school?->id,
                'event_key' => $eventKey,
                'message' => $exception->getMessage(),
            ]);

            return $this->failedResult($exception->getMessage());
        }
    }

    private function setupUrl(string $token, string $email): string
    {
        return $this->absoluteRoute('password.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    private function loginUrl(): string
    {
        return $this->absoluteRoute(Route::has('login') ? 'login' : 'dashboard');
    }

    private function absoluteRoute(string $name, array $parameters = []): string
    {
        $path = Route::has($name)
            ? route($name, $parameters, false)
            : '/login';

        return rtrim((string) config('app.url'), '/').$path;
    }

    /**
     * @return array{subject: string, headline: string}
     */
    private function setupCopy(string $eventKey, string $portalName): array
    {
        return [
            'subject' => $eventKey === self::ACCOUNT_SETUP_LINK_RESENT
                ? 'Your '.$portalName.' setup link'
                : 'Set up your '.$portalName.' account',
            'headline' => $eventKey === self::ACCOUNT_SETUP_LINK_RESENT
                ? 'Use this secure setup link'
                : 'Your portal account is ready',
        ];
    }

    private function setupBody(User $user, ?School $school, string $roleLabel): string
    {
        $schoolName = $school?->name ?: $this->portalName($school);
        $loginId = $user->staff_code ?: $user->email;
        $supportEmail = $this->supportEmail();

        return 'Hello '.$user->name.",\n\n"
            .'Your account for '.$schoolName." is ready.\n"
            .'Role: '.$roleLabel."\n"
            .'Login ID: '.$loginId."\n\n"
            .'Use the secure button to create your password. This link expires in '.$this->expiryMinutes().' minutes. '
            .'For your safety, no password is included in this email.'
            ."\n\n"
            .'If you did not expect this message, contact the school office'
            .($supportEmail ? ' or '.$supportEmail : '')
            .'.';
    }

    /**
     * @return array{subject: string, headline: string, body: string, action_url: ?string, action_label: ?string}
     */
    private function lifecycleCopy(string $eventKey, string $portalName): array
    {
        return match ($eventKey) {
            self::ACCOUNT_ENABLED => [
                'subject' => 'Your '.$portalName.' account was enabled',
                'headline' => 'Account access enabled',
                'body' => 'Your portal access has been enabled. You can sign in with your existing password or request a reset link if needed.',
                'action_url' => $this->loginUrl(),
                'action_label' => __('ui.open_login'),
            ],
            self::ACCOUNT_ARCHIVED => [
                'subject' => 'Your '.$portalName.' account was archived',
                'headline' => 'Account archived',
                'body' => 'Your portal account has been archived. Existing school records remain preserved. Contact the school office if you believe this change is incorrect.',
                'action_url' => null,
                'action_label' => null,
            ],
            self::ACCOUNT_RESTORED => [
                'subject' => 'Your '.$portalName.' account was restored',
                'headline' => 'Account restored',
                'body' => 'Your portal account has been restored. You can sign in again with your existing password or request a reset link if needed.',
                'action_url' => $this->loginUrl(),
                'action_label' => __('ui.open_login'),
            ],
            default => [
                'subject' => 'Your '.$portalName.' account was disabled',
                'headline' => 'Account access disabled',
                'body' => 'Your portal access has been disabled. Contact the school office if you believe this change is incorrect.',
                'action_url' => null,
                'action_label' => null,
            ],
        };
    }

    private function roleFor(User $user, ?School $school): string
    {
        if ($school) {
            $schoolRole = $user->schoolRoles()
                ->where('school_id', $school->id)
                ->latest()
                ->value('role_name');

            if ($schoolRole) {
                return $schoolRole;
            }
        }

        return $user->roles?->pluck('name')->first() ?: 'staff';
    }

    private function roleLabel(string $role): string
    {
        return str($role ?: 'staff')->replace('_', ' ')->title()->toString();
    }

    private function schoolFor(User $user): ?School
    {
        if ($user->school) {
            return $user->school;
        }

        return $user->schoolRoles()
            ->with('school')
            ->whereNotNull('school_id')
            ->latest()
            ->first()
            ?->school;
    }

    private function portalName(?School $school): string
    {
        return $school?->name
            ?: $this->platformSettings->get()->platform_name
            ?: config('app.name', 'Sanfaani Schools');
    }

    private function supportEmail(): ?string
    {
        return $this->platformSettings->get()->support_email
            ?: config('sanfaani.support_email');
    }

    private function expiryMinutes(): int
    {
        return (int) config('auth.passwords.users.expire', 60);
    }

    /**
     * @return array{sent: bool, status: string, log: ?CommunicationLog, error: ?string}
     */
    private function failedResult(?string $error): array
    {
        return [
            'sent' => false,
            'status' => CommunicationLog::STATUS_FAILED,
            'log' => null,
            'error' => $error,
        ];
    }
}
