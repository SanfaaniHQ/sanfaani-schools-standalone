<?php

namespace App\Listeners;

use App\Events\StudentTransactionalEmailRequested;
use App\Services\AuditLogService;
use App\Services\CommunicationService;
use App\Services\NotificationPreferenceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendStudentTransactionalEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'mail';

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        private CommunicationService $communications,
        private NotificationPreferenceService $preferences,
        private AuditLogService $auditLog
    ) {}

    public function handle(StudentTransactionalEmailRequested $event): void
    {
        try {
            if (! filled($event->recipient)) {
                $this->auditSkipped($event, 'missing_recipient');

                return;
            }

            if (
                $event->respectPreferences
                && ! $this->preferences->emailEnabled($event->eventKey, $event->school)
            ) {
                $this->auditSkipped($event, 'preference_disabled');

                return;
            }

            $log = $this->communications->sendTransactionalEmail(
                $event->school,
                $event->recipient,
                $event->subject,
                $event->headline,
                $event->body,
                $event->type,
                array_merge($event->metadata, [
                    'event_key' => $event->eventKey,
                    'student_id' => $event->student->id,
                ]),
                CommunicationService::CATEGORY_STUDENT_LIFECYCLE
            );

            $this->auditLog->log('student_transactional_email_dispatched', $event->student, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'recipient' => $event->recipient,
                'communication_log_id' => $log->id,
                'communication_status' => $log->status,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Student transactional email listener failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'student_id' => $event->student->id,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->auditLog->log('student_transactional_email_failed', $event->student, $event->school, metadata: [
                    'event_key' => $event->eventKey,
                    'recipient' => $event->recipient,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $auditException) {
                Log::warning('Student transactional email failure audit failed.', [
                    'event_key' => $event->eventKey,
                    'message' => $auditException->getMessage(),
                ]);
            }
        }
    }

    private function auditSkipped(StudentTransactionalEmailRequested $event, string $reason): void
    {
        try {
            $this->auditLog->log('student_transactional_email_skipped', $event->student, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'reason' => $reason,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Student transactional email skip audit failed.', [
                'event_key' => $event->eventKey,
                'reason' => $reason,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
