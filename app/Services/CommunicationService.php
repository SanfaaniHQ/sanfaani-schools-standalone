<?php

namespace App\Services;

use App\Mail\CommunicationMail;
use App\Mail\Transactional\AnnouncementMail;
use App\Mail\Transactional\PlatformTransactionalMail;
use App\Mail\Transactional\SchoolNotificationMail;
use App\Mail\Transactional\StaffLifecycleMail;
use App\Mail\Transactional\StaffTransactionalMail;
use App\Mail\Transactional\StudentLifecycleMail;
use App\Mail\Transactional\StudentTransactionalMail;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Support\MailSecurity;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CommunicationService
{
    public const CATEGORY_MANUAL = 'manual';

    public const CATEGORY_STUDENT_TRANSACTIONAL = 'student_transactional';

    public const CATEGORY_STUDENT_LIFECYCLE = 'student_lifecycle';

    public const CATEGORY_STAFF_TRANSACTIONAL = 'staff_transactional';

    public const CATEGORY_STAFF_LIFECYCLE = 'staff_lifecycle';

    public const CATEGORY_SCHOOL_NOTIFICATION = 'school_notification';

    public const CATEGORY_PLATFORM_TRANSACTIONAL = 'platform_transactional';

    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    public function __construct(
        private MailSettingService $mailSettings,
        private AuditLogService $auditLog,
        private SystemNotificationService $notifications
    ) {}

    public function sendSchoolEmail(
        School $school,
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        array $metadata = [],
        string $category = 'student_transactional',
        ?Authenticatable $sender = null
    ): CommunicationLog {
        return $this->sendTransactionalEmail($school, $recipient, $subject, $headline, $body, $type, $metadata, $category, $sender);
    }

    public function sendPlatformEmail(
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        array $metadata = [],
        string $category = 'platform_transactional',
        ?Authenticatable $sender = null
    ): CommunicationLog {
        return $this->sendTransactionalEmail(null, $recipient, $subject, $headline, $body, $type, $metadata, $category, $sender);
    }

    public function sendManualEmail(
        ?School $school,
        string $recipient,
        string $subject,
        string $message,
        array $metadata = [],
        string $type = 'manual_email',
        string $headline = 'Manual communication',
        ?Authenticatable $sender = null
    ): CommunicationLog {
        return $this->dispatch($school, $recipient, $subject, $headline, $message, $type, self::CATEGORY_MANUAL, $metadata, $sender);
    }

    public function sendTransactionalEmail(
        ?School $school,
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        array $metadata = [],
        string $category = self::CATEGORY_STUDENT_TRANSACTIONAL,
        ?Authenticatable $sender = null
    ): CommunicationLog {
        return $this->dispatch($school, $recipient, $subject, $headline, $body, $type, $category, $metadata, $sender);
    }

    public function resend(CommunicationLog $log, ?School $school = null, ?string $headline = null): CommunicationLog
    {
        $school ??= $log->school;
        $metadata = array_merge($log->metadata ?? [], ['resend_of' => $log->id]);

        return $this->dispatch(
            $school,
            $log->recipient,
            $log->subject,
            $headline ?? 'Resent communication',
            (string) data_get($log->metadata, 'original_message', 'This email was resent from communication history.'),
            $log->type,
            (string) data_get($log->metadata, 'category', $school ? self::CATEGORY_STUDENT_TRANSACTIONAL : self::CATEGORY_PLATFORM_TRANSACTIONAL),
            $metadata
        );
    }

    private function dispatch(
        ?School $school,
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        string $category,
        array $metadata = [],
        ?Authenticatable $sender = null
    ): CommunicationLog {
        $sender ??= auth()->user();
        $log = $this->createLog($this->logAttributes($school, $sender, $recipient, $subject, $body, $type, $category, $metadata));

        try {
            $delivery = $this->mailSettings->deliverForSchool($school, function () use ($recipient, $subject, $headline, $body, $school, $metadata, $category) {
                Mail::to($recipient)->send($this->attachFiles(
                    $this->mailableForCategory($category, $subject, $headline, $body, $school, $metadata),
                    $metadata
                ));
            });

            $this->markSent($log, $delivery);

            $this->recordAudit('communication_email_sent', $log, $school, [
                'type' => $type,
                'recipient' => $recipient,
                'fallback_used' => $delivery['fallback_used'],
            ]);
        } catch (Throwable $exception) {
            $this->markFailed($log, $exception);

            $this->recordAudit('communication_email_failed', $log, $school, [
                'type' => $type,
                'recipient' => $recipient,
                'error' => MailSecurity::sanitizeError($exception),
            ]);

            Log::warning('Communication email failed.', [
                'school_id' => $school?->id,
                'recipient' => $recipient,
                'type' => $type,
                'exception' => $exception::class,
                'category' => MailSecurity::diagnostic($exception)['category'],
            ]);

            $this->notifyFailure($log, $school, $recipient, $type, $exception);
        }

        return $log->exists ? ($log->fresh() ?? $log) : $log;
    }

    private function logAttributes(
        ?School $school,
        ?Authenticatable $sender,
        string $recipient,
        string $subject,
        string $body,
        string $type,
        string $category,
        array $metadata
    ): array {
        return [
            'school_id' => $school?->id,
            'sender_id' => $sender?->getAuthIdentifier(),
            'sender_type' => $sender ? 'user' : 'system',
            'sender_role' => $this->senderRole($sender),
            'recipient' => $recipient,
            'subject' => $subject,
            'type' => $type,
            'status' => CommunicationLog::STATUS_PENDING,
            'metadata' => array_merge($metadata, [
                'category' => $category,
                'original_message' => $body,
                'queue_ready' => true,
            ]),
        ];
    }

    private function createLog(array $attributes): CommunicationLog
    {
        if (! $this->communicationLogsAreReady()) {
            return new CommunicationLog($attributes);
        }

        try {
            return CommunicationLog::create($attributes);
        } catch (Throwable $exception) {
            Log::warning('Communication log creation failed.', [
                'recipient' => $attributes['recipient'] ?? null,
                'type' => $attributes['type'] ?? null,
                'exception' => $exception::class,
            ]);

            return new CommunicationLog($attributes);
        }
    }

    private function senderRole(?Authenticatable $sender): ?string
    {
        if (! $sender || ! method_exists($sender, 'roles')) {
            return null;
        }

        try {
            return $sender->roles?->pluck('name')->first();
        } catch (Throwable) {
            return null;
        }
    }

    private function communicationLogsAreReady(): bool
    {
        try {
            return Schema::hasTable('communication_logs');
        } catch (Throwable) {
            return false;
        }
    }

    private function markSent(CommunicationLog $log, array $delivery): void
    {
        $log->status = CommunicationLog::STATUS_SENT;
        $log->sent_at = now();
        $log->failure_reason = null;
        $log->metadata = array_merge($log->metadata ?? [], [
            'delivery' => [
                'fallback_used' => $delivery['fallback_used'],
                'primary_error' => $delivery['primary_error'],
                'transport' => $delivery['transport'] ?? null,
            ],
        ]);

        $this->saveLog($log);
    }

    private function markFailed(CommunicationLog $log, Throwable $exception): void
    {
        $log->status = CommunicationLog::STATUS_FAILED;
        $log->failure_reason = MailSecurity::diagnostic($exception)['message'];

        $this->saveLog($log);
    }

    private function saveLog(CommunicationLog $log): void
    {
        if (! $log->exists) {
            return;
        }

        try {
            $log->save();
        } catch (Throwable $exception) {
            Log::warning('Communication log update failed.', [
                'communication_log_id' => $log->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function recordAudit(string $action, CommunicationLog $log, ?School $school, array $metadata): void
    {
        try {
            $this->auditLog->log($action, $log->exists ? $log : null, $school, metadata: $metadata);
        } catch (Throwable $exception) {
            Log::warning('Communication audit log failed.', [
                'action' => $action,
                'communication_log_id' => $log->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function notifyFailure(CommunicationLog $log, ?School $school, string $recipient, string $type, Throwable $exception): void
    {
        try {
            $payload = [
                'title' => $school ? 'School mail delivery failed' : 'Platform mail delivery failed',
                'body' => 'Mail to '.$recipient.' failed for '.$type.'.',
                'category' => 'mail',
                'event' => $school ? 'school.mail.failed' : 'platform.mail.failed',
                'severity' => 'warning',
                'action_url' => $school
                    ? (Route::has('school.communications.bulk') ? route('school.communications.bulk') : null)
                    : (Route::has('admin.communications.index') ? route('admin.communications.index', ['status' => 'failed']) : null),
                'school_id' => $school?->id,
                'metadata' => [
                    'communication_log_id' => $log->id,
                    'recipient' => $recipient,
                    'type' => $type,
                    'error_category' => MailSecurity::diagnostic($exception)['category'],
                ],
            ];

            if ($school) {
                $this->notifications->notifySchoolRoles($school, ['school_admin'], $payload);
            } else {
                $this->notifications->notifySuperAdmins($payload);
            }
        } catch (Throwable $notificationException) {
            Log::warning('Communication failure notification failed.', [
                'communication_log_id' => $log->id,
                'exception' => $notificationException::class,
            ]);
        }
    }

    private function mailableForCategory(
        string $category,
        string $subject,
        string $headline,
        string $body,
        ?School $school,
        array $metadata
    ): Mailable {
        return match ($category) {
            self::CATEGORY_STUDENT_TRANSACTIONAL => new StudentTransactionalMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_STUDENT_LIFECYCLE => new StudentLifecycleMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_STAFF_TRANSACTIONAL => new StaffTransactionalMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_STAFF_LIFECYCLE => new StaffLifecycleMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_SCHOOL_NOTIFICATION => new SchoolNotificationMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_PLATFORM_TRANSACTIONAL => new PlatformTransactionalMail($subject, $headline, $body, $school, $metadata),
            self::CATEGORY_ANNOUNCEMENT => new AnnouncementMail($subject, $headline, $body, $school, $metadata),
            default => new CommunicationMail($subject, $headline, $body, $school, $metadata),
        };
    }

    private function attachFiles(Mailable $mailable, array $metadata): Mailable
    {
        foreach ((array) data_get($metadata, 'attachments', []) as $attachment) {
            $path = data_get($attachment, 'path');

            if (! filled($path)) {
                continue;
            }

            $mailable->attachFromStorageDisk(
                data_get($attachment, 'disk', 'local'),
                $path,
                data_get($attachment, 'name')
            );
        }

        return $mailable;
    }
}
