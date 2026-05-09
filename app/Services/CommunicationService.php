<?php

namespace App\Services;

use App\Mail\CommunicationMail;
use App\Mail\Transactional\AnnouncementMail;
use App\Mail\Transactional\PlatformTransactionalMail;
use App\Mail\Transactional\StaffTransactionalMail;
use App\Mail\Transactional\StudentTransactionalMail;
use App\Models\CommunicationLog;
use App\Models\School;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CommunicationService
{
    public function __construct(
        private MailSettingService $mailSettings,
        private AuditLogService $auditLog
    ) {}

    public function sendSchoolEmail(
        School $school,
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        array $metadata = [],
        string $category = 'student_transactional'
    ): CommunicationLog {
        return $this->dispatch($school, $recipient, $subject, $headline, $body, $type, $category, $metadata);
    }

    public function sendPlatformEmail(
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        array $metadata = [],
        string $category = 'platform_transactional'
    ): CommunicationLog {
        return $this->dispatch(null, $recipient, $subject, $headline, $body, $type, $category, $metadata);
    }

    private function dispatch(
        ?School $school,
        string $recipient,
        string $subject,
        string $headline,
        string $body,
        string $type,
        string $category,
        array $metadata = []
    ): CommunicationLog {
        $user = auth()->user();
        $tableReady = Schema::hasTable('communication_logs');

        $attributes = [
            'school_id' => $school?->id,
            'sender_id' => $user?->id,
            'sender_type' => $user ? 'user' : 'system',
            'sender_role' => $user?->roles?->pluck('name')->first(),
            'recipient' => $recipient,
            'subject' => $subject,
            'type' => $type,
            'status' => 'pending',
            'metadata' => array_merge($metadata, ['category' => $category, 'original_message' => $body]),
        ];

        $log = $tableReady
            ? CommunicationLog::create($attributes)
            : new CommunicationLog($attributes);

        try {
            $this->mailSettings->withSchoolMailContext($school, function () use ($recipient, $subject, $headline, $body, $school, $metadata, $category) {
                Mail::to($recipient)->send($this->mailableForCategory($category, $subject, $headline, $body, $school, $metadata));
            });

            $log->status = 'sent';
            $log->sent_at = now();
            $log->failure_reason = null;
            if ($tableReady) {
                $log->save();
            }

            $this->auditLog->log('communication_email_sent', $log, $school, metadata: [
                'type' => $type,
                'recipient' => $recipient,
            ]);
        } catch (Throwable $exception) {
            $log->status = 'failed';
            $log->failure_reason = $exception->getMessage();
            if ($tableReady) {
                $log->save();
            }

            $this->auditLog->log('communication_email_failed', $log, $school, metadata: [
                'type' => $type,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            Log::warning('Communication email failed.', [
                'school_id' => $school?->id,
                'recipient' => $recipient,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }

        return $tableReady ? $log->fresh() : $log;
    }

    private function mailableForCategory(
        string $category,
        string $subject,
        string $headline,
        string $body,
        ?School $school,
        array $metadata
    ): CommunicationMail|StudentTransactionalMail|StaffTransactionalMail|PlatformTransactionalMail|AnnouncementMail {
        return match ($category) {
            'student_transactional' => new StudentTransactionalMail($subject, $headline, $body, $school, $metadata),
            'staff_transactional' => new StaffTransactionalMail($subject, $headline, $body, $school, $metadata),
            'platform_transactional' => new PlatformTransactionalMail($subject, $headline, $body, $school, $metadata),
            'announcement' => new AnnouncementMail($subject, $headline, $body, $school, $metadata),
            default => new CommunicationMail($subject, $headline, $body, $school, $metadata),
        };
    }
}
