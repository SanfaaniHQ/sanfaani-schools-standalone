<?php

namespace App\Listeners;

use App\Events\StaffTransactionalEmailRequested;
use App\Notifications\SystemDatabaseNotification;
use App\Services\AuditLogService;
use App\Services\CommunicationService;
use App\Services\NotificationPreferenceService;
use App\Support\MailSecurity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

            $channels = $event->respectPreferences
                ? $this->preferences->channelsFor($event->eventKey, $event->school, $event->staff, $event->role)
                : ['mail', 'database'];
            $emailEnabled = (bool) array_intersect($channels, ['mail', 'email'])
                && (! $event->respectPreferences || $this->emailEnabled($event));
            $databaseEnabled = (bool) array_intersect($channels, ['database', 'in_app']);

            if (! $emailEnabled && ! $databaseEnabled) {
                $this->auditSkipped($event, 'preference_disabled');

                return;
            }

            if ($databaseEnabled) {
                $this->notifyDatabase($event);
            }

            if ($emailEnabled) {
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
            }
        } catch (Throwable $exception) {
            $diagnostic = MailSecurity::diagnostic($exception);
            Log::warning('Staff transactional email listener failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'staff_id' => $event->staff->id,
                'exception' => $exception::class,
                'category' => $diagnostic['category'],
            ]);

            try {
                $this->auditLog->log('staff_transactional_email_failed', $event->staff, $event->school, metadata: [
                    'event_key' => $event->eventKey,
                    'recipient' => $event->recipient,
                    'error_category' => $diagnostic['category'],
                ]);
            } catch (Throwable $auditException) {
                Log::warning('Staff transactional email failure audit failed.', [
                    'event_key' => $event->eventKey,
                    'exception' => $auditException::class,
                ]);
            }
        }
    }

    private function notifyDatabase(StaffTransactionalEmailRequested $event): void
    {
        if (! $this->notificationsAreReady()) {
            return;
        }

        try {
            $event->staff->notify(new SystemDatabaseNotification([
                'title' => $event->subject,
                'body' => $event->headline."\n".$event->body,
                'category' => 'staff',
                'event' => $event->eventKey,
                'severity' => $event->metadata['severity'] ?? 'info',
                'action_url' => $event->metadata['action_url'] ?? null,
                'school_id' => $event->school->id,
                'role' => $event->role,
                'metadata' => array_merge($event->metadata, [
                    'staff_id' => $event->staff->id,
                    'role' => $event->role,
                ]),
            ]));

            $this->auditLog->log('staff_transactional_database_dispatched', $event->staff, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'staff_id' => $event->staff->id,
                'role' => $event->role,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Staff database notification failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'staff_id' => $event->staff->id,
                'exception' => $exception::class,
            ]);
        }
    }

    private function notificationsAreReady(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (Throwable) {
            return false;
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
                'exception' => $exception::class,
            ]);
        }
    }
}
