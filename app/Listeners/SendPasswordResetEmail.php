<?php

namespace App\Listeners;

use App\Events\PasswordResetEmailRequested;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CommunicationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPasswordResetEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'mail';

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        private CommunicationService $communications,
        private AuditLogService $auditLog
    ) {}

    public function handle(PasswordResetEmailRequested $event): void
    {
        $user = $event->user;
        $recipient = $user->getEmailForPasswordReset();
        $school = null;
        $role = 'staff';

        try {
            $school = $this->schoolFor($user);
            $role = $this->roleFor($user, $school);
            $roleLabel = $this->roleLabel($role);

            if (! filled($recipient)) {
                return;
            }

            $url = url(route('password.reset', [
                'token' => $event->token,
                'email' => $recipient,
            ], false));

            $log = $this->communications->sendTransactionalEmail(
                $school,
                $recipient,
                'Reset your password',
                'Password reset request',
                "A password reset was requested for your ".$roleLabel." account."
                    ."\nLogin ID: ".($user->staff_code ?: $recipient)
                    ."\nUse the secure link below to continue. If you did not request this, you can ignore this message.",
                'password_reset',
                [
                    'event_key' => 'password_reset',
                    'user_id' => $user->id,
                    'role' => $role,
                    'role_label' => $roleLabel,
                    'action_url' => $url,
                    'action_label' => 'Reset Password',
                ],
                CommunicationService::CATEGORY_STAFF_LIFECYCLE
            );

            $this->auditLog->log('password_reset_email_dispatched', $user, $school, metadata: [
                'recipient' => $recipient,
                'role' => $role,
                'communication_log_id' => $log->id,
                'communication_status' => $log->status,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Password reset email listener failed.', [
                'user_id' => $user->id,
                'school_id' => $school?->id,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->auditLog->log('password_reset_email_failed', $user, $school, metadata: [
                    'recipient' => $recipient,
                    'role' => $role,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $auditException) {
                Log::warning('Password reset email failure audit failed.', [
                    'user_id' => $user->id,
                    'message' => $auditException->getMessage(),
                ]);
            }
        }
    }

    private function schoolFor(User $user): ?School
    {
        if ($user->school) {
            return $user->school;
        }

        return $user->activeSchoolRoles()
            ->with('school')
            ->latest()
            ->first()
            ?->school;
    }

    private function roleFor(User $user, ?School $school): string
    {
        if ($school) {
            $schoolRole = $user->activeSchoolRoles()
                ->where('school_id', $school->id)
                ->latest()
                ->value('role_name');

            if ($schoolRole) {
                return $schoolRole;
            }
        }

        try {
            return $user->roles?->pluck('name')->first() ?: 'staff';
        } catch (Throwable) {
            return 'staff';
        }
    }

    private function roleLabel(string $role): string
    {
        return ucwords(str_replace('_', ' ', $role ?: 'staff'));
    }
}
