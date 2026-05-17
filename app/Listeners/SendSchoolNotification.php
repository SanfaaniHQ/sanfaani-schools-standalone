<?php

namespace App\Listeners;

use App\Events\SchoolNotificationRequested;
use App\Notifications\SystemDatabaseNotification;
use App\Services\AuditLogService;
use App\Services\CommunicationService;
use App\Services\NotificationPreferenceService;
use App\Services\SchoolNotificationRecipientResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SendSchoolNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'mail';

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        private SchoolNotificationRecipientResolver $recipients,
        private CommunicationService $communications,
        private NotificationPreferenceService $preferences,
        private AuditLogService $auditLog
    ) {}

    public function handle(SchoolNotificationRequested $event): void
    {
        try {
            $recipients = $this->recipients->recipientsFor(
                $event->school,
                $event->targetRoles,
                $event->includeSchoolContact
            );

            if ($recipients->isEmpty()) {
                $this->auditSkipped($event, 'missing_recipient');

                return;
            }

            foreach ($recipients as $recipient) {
                $channels = $event->respectPreferences
                    ? $this->preferences->channelsFor($event->eventKey, $event->school, $recipient['user'], $recipient['role'])
                    : ['mail', 'database'];
                $emailEnabled = (bool) array_intersect($channels, ['mail', 'email']);
                $databaseEnabled = (bool) array_intersect($channels, ['database', 'in_app']);

                if (! $emailEnabled && ! $databaseEnabled) {
                    $this->auditSkipped($event, 'preference_disabled', $recipient);

                    continue;
                }

                if ($databaseEnabled) {
                    $this->notifyDatabase($event, $recipient);
                }

                if ($emailEnabled) {
                    $log = $this->communications->sendTransactionalEmail(
                        $event->school,
                        $recipient['email'],
                        $event->subject,
                        $event->headline,
                        $event->body,
                        $event->type,
                        array_merge($event->metadata, [
                            'event_key' => $event->eventKey,
                            'target_role' => $recipient['role'],
                            'recipient_source' => $recipient['source'],
                            'recipient_user_id' => $recipient['user']?->id,
                        ]),
                        CommunicationService::CATEGORY_SCHOOL_NOTIFICATION
                    );

                    $this->auditLog->log('school_notification_email_dispatched', null, $event->school, metadata: [
                        'event_key' => $event->eventKey,
                        'recipient' => $recipient['email'],
                        'target_role' => $recipient['role'],
                        'communication_log_id' => $log->id,
                        'communication_status' => $log->status,
                    ]);
                }
            }
        } catch (Throwable $exception) {
            Log::warning('School notification listener failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->auditLog->log('school_notification_email_failed', null, $event->school, metadata: [
                    'event_key' => $event->eventKey,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $auditException) {
                Log::warning('School notification failure audit failed.', [
                    'event_key' => $event->eventKey,
                    'message' => $auditException->getMessage(),
                ]);
            }
        }
    }

    private function notifyDatabase(SchoolNotificationRequested $event, array $recipient): void
    {
        if (! $recipient['user'] || ! $this->notificationsAreReady()) {
            return;
        }

        try {
            $recipient['user']->notify(new SystemDatabaseNotification([
                'title' => $event->subject,
                'body' => $event->headline."\n".$event->body,
                'category' => 'school',
                'event' => $event->eventKey,
                'severity' => $event->metadata['severity'] ?? 'info',
                'action_url' => $event->metadata['action_url'] ?? null,
                'school_id' => $event->school->id,
                'role' => $recipient['role'],
                'metadata' => array_merge($event->metadata, [
                    'recipient_source' => $recipient['source'],
                    'recipient_user_id' => $recipient['user']?->id,
                ]),
            ]));

            $this->auditLog->log('school_notification_database_dispatched', null, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'recipient_user_id' => $recipient['user']->id,
                'target_role' => $recipient['role'],
            ]);
        } catch (Throwable $exception) {
            Log::warning('School notification database dispatch failed.', [
                'event_key' => $event->eventKey,
                'school_id' => $event->school->id,
                'recipient_user_id' => $recipient['user']?->id,
                'message' => $exception->getMessage(),
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

    private function auditSkipped(SchoolNotificationRequested $event, string $reason, ?array $recipient = null): void
    {
        try {
            $this->auditLog->log('school_notification_email_skipped', null, $event->school, metadata: [
                'event_key' => $event->eventKey,
                'reason' => $reason,
                'recipient' => $recipient['email'] ?? null,
                'target_role' => $recipient['role'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::warning('School notification skip audit failed.', [
                'event_key' => $event->eventKey,
                'reason' => $reason,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
