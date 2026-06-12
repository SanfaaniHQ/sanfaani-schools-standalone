<?php

namespace App\Services\Communications;

use App\Models\Admissions\AdmissionApplication;
use App\Models\LiveClass;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Security\SecretRedactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class SchoolNotificationService
{
    public function __construct(
        private AuditLogService $auditLog,
        private NotificationRecipientResolver $recipients,
        private SecretRedactionService $redaction,
    ) {}

    public function createTemplate(School $school, User $actor, array $data): SchoolNotificationTemplate
    {
        $template = SchoolNotificationTemplate::create([
            'school_id' => $school->id,
            'template_key' => $this->normalizeTemplateKey($data['template_key']),
            'title' => trim((string) $data['title']),
            'subject' => filled($data['subject'] ?? null) ? trim((string) $data['subject']) : null,
            'body' => trim((string) $data['body']),
            'channel' => $data['channel'] ?? SchoolNotificationTemplate::CHANNEL_DATABASE,
            'audience_type' => $data['audience_type'] ?? SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'metadata' => $this->sanitizeMetadata($data['metadata'] ?? []),
        ]);

        $this->audit('communication_template_created', $template, $school, [
            'school_id' => $school->id,
            'template_id' => $template->id,
            'template_key' => $template->template_key,
            'channel' => $template->channel,
            'audience_type' => $template->audience_type,
            'actor_id' => $actor->id,
        ]);

        return $template;
    }

    public function updateTemplate(School $school, User $actor, SchoolNotificationTemplate $template, array $data): SchoolNotificationTemplate
    {
        $this->assertTemplateSchool($school, $template);

        $old = $template->only(['template_key', 'title', 'subject', 'body', 'channel', 'audience_type', 'is_active']);
        $template->update([
            'template_key' => $this->normalizeTemplateKey($data['template_key']),
            'title' => trim((string) $data['title']),
            'subject' => filled($data['subject'] ?? null) ? trim((string) $data['subject']) : null,
            'body' => trim((string) $data['body']),
            'channel' => $data['channel'] ?? $template->channel,
            'audience_type' => $data['audience_type'] ?? $template->audience_type,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'updated_by' => $actor->id,
            'metadata' => $this->sanitizeMetadata($data['metadata'] ?? $template->metadata ?? []),
        ]);

        $this->auditLog->log(
            'communication_template_updated',
            $template,
            $school,
            $old,
            $template->only(['template_key', 'title', 'subject', 'body', 'channel', 'audience_type', 'is_active']),
            [
                'school_id' => $school->id,
                'template_id' => $template->id,
                'template_key' => $template->template_key,
                'channel' => $template->channel,
                'audience_type' => $template->audience_type,
                'actor_id' => $actor->id,
            ]
        );

        return $template->fresh() ?? $template;
    }

    public function logOperationalNotification(School $school, array $data, ?User $actor = null): ?SchoolNotificationLog
    {
        if (! $this->logsAreReady()) {
            return null;
        }

        try {
            $actor ??= auth()->user();
            $template = $this->resolveTemplate($school, $data);
            $variables = $this->sanitizeMetadata($data['variables'] ?? []);
            $channel = $this->channel($data['channel'] ?? $template?->channel ?? SchoolNotificationLog::CHANNEL_DATABASE);
            $recipientType = $data['recipient_type'] ?? $template?->audience_type ?? NotificationRecipientResolver::TYPE_SCHOOL_OPERATIONS;
            $recipient = $this->recipients->resolve(
                $school,
                $recipientType,
                filled($data['recipient_id'] ?? null) ? (int) $data['recipient_id'] : null,
                $data['recipient_context'] ?? []
            );
            $subject = $this->renderText($data['subject'] ?? $template?->subject ?? $template?->title ?? null, $variables);
            $message = $this->renderText($data['message'] ?? $template?->body ?? null, $variables);
            $summary = $this->summary($data['message_summary'] ?? $message ?? $subject);
            $metadata = $this->metadata($data['metadata'] ?? [], $channel);
            $related = $data['related'] ?? null;
            $status = $data['status'] ?? $this->defaultStatus($channel);

            $log = SchoolNotificationLog::create([
                'school_id' => $school->id,
                'template_id' => $template?->id,
                'event_type' => $this->eventType($data['event_type'] ?? null),
                'channel' => $channel,
                ...$recipient,
                'subject' => $this->summary($subject, 191),
                'message_summary' => $summary,
                'status' => $status,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'sent_at' => $status === SchoolNotificationLog::STATUS_SENT ? now() : null,
                'failed_at' => $status === SchoolNotificationLog::STATUS_FAILED ? now() : null,
                'failure_reason' => filled($data['failure_reason'] ?? null) ? $this->summary($data['failure_reason'], 1000) : null,
                'related_model_type' => $related instanceof Model ? $related::class : null,
                'related_model_id' => $related instanceof Model ? $related->getKey() : null,
                'created_by' => $actor?->id,
                'metadata' => $metadata,
            ]);

            $this->audit('communication_notification_logged', $log, $school, [
                'school_id' => $school->id,
                'template_id' => $template?->id,
                'notification_log_id' => $log->id,
                'event_type' => $log->event_type,
                'channel' => $log->channel,
                'recipient_type' => $log->recipient_type,
                'status' => $log->status,
                'actor_id' => $actor?->id,
                'related_model_type' => $log->related_model_type,
                'related_model_id' => $log->related_model_id,
            ]);

            return $log;
        } catch (Throwable $exception) {
            Log::warning('School notification log creation failed.', [
                'school_id' => $school->id,
                'event_type' => $data['event_type'] ?? null,
                'message' => $this->redaction->redact($exception),
            ]);

            return null;
        }
    }

    public function logLiveClassScheduled(School $school, User $actor, LiveClass $liveClass): ?SchoolNotificationLog
    {
        return $this->logLiveClassEvent($school, $actor, $liveClass, 'live_class.scheduled', 'scheduled');
    }

    public function logLiveClassUpdated(School $school, User $actor, LiveClass $liveClass): ?SchoolNotificationLog
    {
        return $this->logLiveClassEvent($school, $actor, $liveClass, 'live_class.updated', 'updated');
    }

    public function logLiveClassCancelled(School $school, User $actor, LiveClass $liveClass): ?SchoolNotificationLog
    {
        return $this->logLiveClassEvent($school, $actor, $liveClass, 'live_class.cancelled', 'cancelled');
    }

    public function logLmsMaterialPublished(School $school, User $actor, LmsMaterial $material): ?SchoolNotificationLog
    {
        $material->loadMissing('classroom.schoolClass', 'classroom.subject');
        $classroom = $material->classroom;

        return $this->logOperationalNotification($school, [
            'event_type' => 'lms.material.published',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => NotificationRecipientResolver::TYPE_CLASS,
            'recipient_id' => $classroom?->school_class_id,
            'subject' => 'Learning material published',
            'message_summary' => trim('Published '.$material->title.' for '.($classroom?->schoolClass?->name ?? 'class').' / '.($classroom?->subject?->name ?? 'subject').'.'),
            'related' => $material,
            'metadata' => [
                'school_id' => $school->id,
                'material_id' => $material->id,
                'classroom_id' => $material->lms_classroom_id,
                'class_id' => $classroom?->school_class_id,
                'subject_id' => $classroom?->subject_id,
                'status' => $material->status,
                'actor_id' => $actor->id,
            ],
        ], $actor);
    }

    public function logFinanceInvoiceGenerated(School $school, User $actor, StudentFeeInvoice $invoice): ?SchoolNotificationLog
    {
        $invoice->loadMissing('student', 'schoolClass', 'academicSession', 'term');

        return $this->logOperationalNotification($school, [
            'event_type' => 'finance.invoice.generated',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => NotificationRecipientResolver::TYPE_STUDENT,
            'recipient_id' => $invoice->student_id,
            'subject' => 'Student invoice generated',
            'message_summary' => 'Invoice '.$invoice->invoice_number.' was generated for '.$invoice->student?->fullName().'. Balance: NGN '.number_format((float) $invoice->balance_amount, 2).'.',
            'related' => $invoice,
            'metadata' => [
                'school_id' => $school->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student_id' => $invoice->student_id,
                'class_id' => $invoice->school_class_id,
                'status' => $invoice->status,
                'balance_amount' => (float) $invoice->balance_amount,
                'actor_id' => $actor->id,
            ],
        ], $actor);
    }

    public function logFinancePaymentRecorded(School $school, User $actor, StudentFeePayment $payment, StudentFeeInvoice $invoice): ?SchoolNotificationLog
    {
        $invoice->loadMissing('student');

        return $this->logOperationalNotification($school, [
            'event_type' => 'finance.payment.recorded',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => NotificationRecipientResolver::TYPE_STUDENT,
            'recipient_id' => $payment->student_id,
            'subject' => 'Student payment recorded',
            'message_summary' => 'Payment of NGN '.number_format((float) $payment->amount, 2).' was recorded for invoice '.$invoice->invoice_number.'.',
            'related' => $payment,
            'metadata' => [
                'school_id' => $school->id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'student_id' => $payment->student_id,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'has_reference' => filled($payment->reference),
                'actor_id' => $actor->id,
            ],
        ], $actor);
    }

    public function logAdmissionStatusChanged(AdmissionApplication $application, ?User $actor = null): ?SchoolNotificationLog
    {
        $application->loadMissing('school', 'statusLogs');
        $school = $application->school;

        if (! $school) {
            return null;
        }

        $latestStatusLog = $application->statusLogs()->latest('id')->first();

        return $this->logOperationalNotification($school, [
            'event_type' => 'admissions.status.changed',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => NotificationRecipientResolver::TYPE_SCHOOL_ADMIN,
            'subject' => 'Admission status updated',
            'message_summary' => 'Application '.$application->application_number.' moved from '.($latestStatusLog?->from_status ?? 'unknown').' to '.$application->status.'.',
            'related' => $application,
            'metadata' => [
                'school_id' => $school->id,
                'admission_application_id' => $application->id,
                'application_number' => $application->application_number,
                'from_status' => $latestStatusLog?->from_status,
                'to_status' => $application->status,
                'actor_id' => $actor?->id,
            ],
        ], $actor);
    }

    public function statusCounts(School $school): array
    {
        if (! $this->logsAreReady()) {
            return [];
        }

        return SchoolNotificationLog::query()
            ->forSchool($school)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function logLiveClassEvent(School $school, User $actor, LiveClass $liveClass, string $eventType, string $verb): ?SchoolNotificationLog
    {
        $liveClass->loadMissing('schoolClass', 'subject', 'teacher');

        $classLabel = trim(($liveClass->schoolClass?->name ?? '').' '.($liveClass->schoolClass?->section ?? ''));
        $startsAt = $liveClass->starts_at?->timezone($liveClass->timezone ?: config('app.timezone'))->format('d M Y H:i');
        $providerLabel = data_get($liveClass->metadata, 'provider_label') ?: str($liveClass->provider)->replace('_', ' ')->title()->toString();

        return $this->logOperationalNotification($school, [
            'event_type' => $eventType,
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => NotificationRecipientResolver::TYPE_LIVE_CLASS_AUDIENCE,
            'recipient_context' => ['live_class' => $liveClass],
            'subject' => 'Live class '.str($verb)->replace('_', ' ')->toString().': '.$liveClass->title,
            'message_summary' => trim('Live class '.$liveClass->title.' for '.($classLabel ?: 'selected class').' / '.($liveClass->subject?->name ?? 'subject').' was '.$verb.'. Starts '.$startsAt.' via '.$providerLabel.'.'),
            'related' => $liveClass,
            'metadata' => [
                'school_id' => $school->id,
                'live_class_id' => $liveClass->id,
                'class_id' => $liveClass->school_class_id,
                'subject_id' => $liveClass->subject_id,
                'teacher_user_id' => $liveClass->teacher_user_id,
                'starts_at' => $liveClass->starts_at?->toIso8601String(),
                'status' => $liveClass->status,
                'provider' => $liveClass->provider,
                'provider_label' => $providerLabel,
                'provider_delivery_deferred' => true,
                'actor_id' => $actor->id,
            ],
        ], $actor);
    }

    private function resolveTemplate(School $school, array $data): ?SchoolNotificationTemplate
    {
        $template = $data['template'] ?? null;

        if ($template instanceof SchoolNotificationTemplate) {
            $this->assertTemplateSchool($school, $template);

            return $template;
        }

        if (! filled($data['template_key'] ?? null)) {
            return null;
        }

        return SchoolNotificationTemplate::query()
            ->forSchool($school)
            ->active()
            ->where('template_key', $this->normalizeTemplateKey($data['template_key']))
            ->first();
    }

    private function metadata(array $metadata, string $channel): array
    {
        return $this->sanitizeMetadata([
            ...$metadata,
            'external_provider_active' => false,
            'external_provider_delivery' => in_array($channel, [
                SchoolNotificationLog::CHANNEL_SMS,
                SchoolNotificationLog::CHANNEL_WHATSAPP,
                SchoolNotificationLog::CHANNEL_EMAIL,
            ], true) ? 'deferred' : 'not_required',
        ]);
    }

    private function sanitizeMetadata(array $metadata): array
    {
        return $this->redaction->redactArray($this->dropSensitiveKeys($metadata));
    }

    private function dropSensitiveKeys(array $metadata): array
    {
        $clean = [];

        foreach ($metadata as $key => $value) {
            $key = (string) $key;

            if ($this->redaction->isSensitiveKey($key) || preg_match('/meeting[_-]?password|provider[_-]?payload/i', $key)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->dropSensitiveKeys($value) : $value;
        }

        return $clean;
    }

    private function renderText(?string $text, array $variables): ?string
    {
        if (! filled($text)) {
            return null;
        }

        $rendered = (string) $text;

        foreach ($variables as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $rendered = str_replace('{{ '.$key.' }}', (string) $value, $rendered);
                $rendered = str_replace('{{'.$key.'}}', (string) $value, $rendered);
            }
        }

        return $this->redaction->redact($rendered, 5000);
    }

    private function summary(?string $value, int $limit = 500): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? (string) $value);

        return Str::limit($this->redaction->redact($text, $limit) ?? '', $limit, '');
    }

    private function channel(string $channel): string
    {
        $channel = Str::lower(trim($channel));

        return in_array($channel, SchoolNotificationLog::CHANNELS, true)
            ? $channel
            : SchoolNotificationLog::CHANNEL_LOG;
    }

    private function eventType(?string $eventType): string
    {
        $eventType = Str::lower(trim((string) $eventType));

        return $eventType !== '' ? Str::limit($eventType, 120, '') : 'school.notification.logged';
    }

    private function defaultStatus(string $channel): string
    {
        return in_array($channel, [
            SchoolNotificationLog::CHANNEL_EMAIL,
            SchoolNotificationLog::CHANNEL_SMS,
            SchoolNotificationLog::CHANNEL_WHATSAPP,
        ], true)
            ? SchoolNotificationLog::STATUS_DEFERRED
            : SchoolNotificationLog::STATUS_LOGGED;
    }

    private function normalizeTemplateKey(string $key): string
    {
        return Str::of($key)
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]+/', '.')
            ->trim('.')
            ->limit(120, '')
            ->toString();
    }

    private function assertTemplateSchool(School $school, SchoolNotificationTemplate $template): void
    {
        abort_unless((int) $template->school_id === (int) $school->id, 403);
    }

    private function audit(string $action, Model $model, School $school, array $metadata): void
    {
        try {
            $this->auditLog->log($action, $model, $school, metadata: $this->sanitizeMetadata($metadata));
        } catch (Throwable $exception) {
            Log::warning('School notification audit failed.', [
                'action' => $action,
                'message' => $this->redaction->redact($exception),
            ]);
        }
    }

    private function logsAreReady(): bool
    {
        try {
            return Schema::hasTable('school_notification_logs');
        } catch (Throwable) {
            return false;
        }
    }
}
