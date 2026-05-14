<?php

namespace App\Listeners;

use App\Events\StaffTransactionalEmailRequested;
use App\Services\AuditLogService;
use App\Services\CommunicationService;
use App\Services\NotificationPreferenceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendStaffTransactionalEmail implements ShouldQueue
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

    public function handle(StaffTransactionalEmailRequested $event): void
    {
        try {
            if (! filled($event->recipient)) {
                $this->auditSkipped($event, 'missing_recipient');

                return;
            }

            if (
                $event->respectPreferences
                && ! $this->emailEnabled($event)
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
                    'staff_id' => $event->staff->id,
                    'role' => $event->role,
                ]),
                CommunicationService::CATEGORY_STAFF_LIFECYCLE
            );

            $this->auditLog->log('staff_transactional_email_dispatched', $event->staff, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'recipient' => $event->recipient,
                'communication_log_id' => $log->id,
                'communication_status' => $log->status,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Staff transactional email listener failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'staff_id' => $event->staff->id,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->auditLog->log('staff_transactional_email_failed', $event->staff, $event->school, metadata: [
                    'event_key' => $event->eventKey,
                    'recipient' => $event->recipient,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $auditException) {
                Log::warning('Staff transactional email failure audit failed.', [
                    'event_key' => $event->eventKey,
                    'message' => $auditException->getMessage(),
                ]);
            }
        }
    }

    private function emailEnabled(StaffTransactionalEmailRequested $event): bool
    {
        if (! $this->preferences->emailEnabled($event->eventKey, $event->school, $event->staff, $event->role)) {
            return false;
        }

        if (! in_array($event->eventKey, ['teacher_account_created', 'result_officer_account_created'], true)) {
            return true;
        }

        return $this->preferences->emailEnabled('user_account_created', $event->school, $event->staff, $event->role);
    }

    private function auditSkipped(StaffTransactionalEmailRequested $event, string $reason): void
    {
        try {
            $this->auditLog->log('staff_transactional_email_skipped', $event->staff, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'reason' => $reason,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Staff transactional email skip audit failed.', [
                'event_key' => $event->eventKey,
                'reason' => $reason,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
