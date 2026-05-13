<?php

namespace App\Services;

use App\Models\BulkCommunicationBatch;
use App\Models\BulkCommunicationRecipient;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class BulkCommunicationService
{
    private const RECIPIENT_RESOLUTION_CHUNK = 250;

    private const DEFAULT_SEND_CHUNK = 25;

    private const MAX_SEND_CHUNK = 100;

    private const DEFAULT_MAX_SYNC_RECIPIENTS = 150;

    private const DEFAULT_MAX_RUNTIME_SECONDS = 20;

    private const REQUEST_DEDUPE_MINUTES = 5;

    public function __construct(
        private BulkCommunicationRecipientResolver $recipients,
        private CommunicationService $communications,
        private AuditLogService $auditLog
    ) {}

    public function createAndProcess(School $school, User $sender, ?string $roleContext, array $data): BulkCommunicationBatch
    {
        $batch = $this->createBatch($school, $sender, $roleContext, $data);

        return $this->processPendingBatch($batch, $sender);
    }

    public function createBatch(School $school, User $sender, ?string $roleContext, array $data): BulkCommunicationBatch
    {
        $data = $this->normalizedData($data);
        $requestFingerprint = $this->requestFingerprint($school, $sender, $data);

        $existing = BulkCommunicationBatch::query()
            ->where('school_id', $school->id)
            ->where('sender_id', $sender->id)
            ->where('request_fingerprint', $requestFingerprint)
            ->where('created_at', '>=', now()->subMinutes(self::REQUEST_DEDUPE_MINUTES))
            ->latest('id')
            ->first();

        if ($existing) {
            $this->recordAudit('bulk_communication_duplicate_prevented', $existing, $school, [
                'request_fingerprint' => $requestFingerprint,
            ]);

            return $existing;
        }

        return DB::transaction(function () use ($school, $sender, $roleContext, $data, $requestFingerprint) {
            $batch = BulkCommunicationBatch::create([
                'batch_uuid' => (string) Str::uuid(),
                'school_id' => $school->id,
                'sender_id' => $sender->id,
                'audience' => $data['audience'],
                'channels' => $data['channels'],
                'type' => $data['type'],
                'subject' => $data['subject'],
                'body' => $data['message'],
                'status' => BulkCommunicationBatch::STATUS_PENDING,
                'chunk_size' => $data['chunk_size'],
                'request_fingerprint' => $requestFingerprint,
                'filters' => $this->filtersSnapshot($data),
                'metadata' => [
                    'queue_ready' => true,
                    'dispatch_mode' => 'sync_chunked',
                    'max_sync_recipients' => $data['max_sync_recipients'],
                    'max_runtime_seconds' => $data['max_runtime_seconds'],
                ],
            ]);

            $seen = [];
            $duplicateCount = 0;

            $this->recipients->chunkRecipients(
                $school,
                $sender,
                $roleContext,
                $data,
                self::RECIPIENT_RESOLUTION_CHUNK,
                function (Collection $rows) use ($batch, $school, &$seen, &$duplicateCount) {
                    $this->insertRecipientRows($batch, $school, $rows, $seen, $duplicateCount);
                }
            );

            $this->refreshCounts($batch, $duplicateCount);

            $this->recordAudit('bulk_communication_created', $batch, $school, [
                'audience' => $batch->audience,
                'channels' => $batch->channels,
                'total_recipients' => $batch->total_recipients,
                'duplicate_count' => $batch->duplicate_count,
            ]);

            return $batch->fresh() ?? $batch;
        });
    }

    public function processPendingBatch(BulkCommunicationBatch $batch, ?User $actor = null): BulkCommunicationBatch
    {
        $batch->loadMissing('school');

        if (! $batch->school || ! $batch->isProcessable()) {
            return $batch;
        }

        $startedAt = microtime(true);
        $processed = 0;
        $maxSyncRecipients = (int) data_get($batch->metadata, 'max_sync_recipients', self::DEFAULT_MAX_SYNC_RECIPIENTS);
        $maxRuntimeSeconds = (int) data_get($batch->metadata, 'max_runtime_seconds', self::DEFAULT_MAX_RUNTIME_SECONDS);

        $batch->forceFill([
            'status' => BulkCommunicationBatch::STATUS_PROCESSING,
            'started_at' => $batch->started_at ?: now(),
            'finished_at' => null,
        ])->save();

        while ($processed < $maxSyncRecipients && (microtime(true) - $startedAt) < $maxRuntimeSeconds) {
            $pendingRecipients = $batch->recipients()
                ->pending()
                ->orderBy('id')
                ->limit((int) $batch->chunk_size)
                ->get();

            if ($pendingRecipients->isEmpty()) {
                break;
            }

            foreach ($pendingRecipients as $recipient) {
                if ($processed >= $maxSyncRecipients || (microtime(true) - $startedAt) >= $maxRuntimeSeconds) {
                    break 2;
                }

                $this->sendRecipient($batch, $recipient, $actor);
                $processed++;
            }

            $this->refreshCounts($batch);
        }

        $this->refreshCounts($batch);
        $pendingCount = $batch->recipients()->pending()->count();
        $status = $this->completionStatus($batch, $pendingCount);

        $batch->forceFill([
            'status' => $status,
            'finished_at' => $pendingCount === 0 ? now() : null,
            'metadata' => array_merge($batch->metadata ?? [], [
                'last_processed_at' => now()->toDateTimeString(),
                'last_processed_count' => $processed,
                'pending_recipients' => $pendingCount,
            ]),
        ])->save();

        $this->recordAudit('bulk_communication_processed', $batch, $batch->school, [
            'status' => $batch->status,
            'processed' => $processed,
            'pending_recipients' => $pendingCount,
            'sent_count' => $batch->sent_count,
            'failed_count' => $batch->failed_count,
            'skipped_count' => $batch->skipped_count,
        ]);

        return $batch->fresh() ?? $batch;
    }

    public function retryFailed(BulkCommunicationBatch $batch, ?User $actor = null): BulkCommunicationBatch
    {
        $batch->recipients()
            ->failed()
            ->orderBy('id')
            ->chunkById(100, function (Collection $recipients) {
                foreach ($recipients as $recipient) {
                    $recipient->forceFill([
                        'status' => BulkCommunicationRecipient::STATUS_PENDING,
                        'failure_reason' => null,
                        'communication_log_id' => null,
                        'attempted_at' => null,
                        'sent_at' => null,
                        'metadata' => array_merge($recipient->metadata ?? [], [
                            'retry_count' => (int) data_get($recipient->metadata, 'retry_count', 0) + 1,
                            'previous_communication_log_id' => $recipient->communication_log_id,
                        ]),
                    ])->save();
                }
            });

        $batch->forceFill([
            'status' => BulkCommunicationBatch::STATUS_PENDING,
            'finished_at' => null,
        ])->save();

        $this->recordAudit('bulk_communication_retry_started', $batch, $batch->school, [
            'failed_count' => $batch->failed_count,
        ]);

        return $this->processPendingBatch($batch, $actor);
    }

    private function insertRecipientRows(
        BulkCommunicationBatch $batch,
        School $school,
        Collection $rows,
        array &$seen,
        int &$duplicateCount
    ): void {
        $now = now();
        $insertRows = [];

        foreach ($rows as $row) {
            $fingerprint = $this->recipientFingerprint($school, $row);
            $seenKey = $row['channel'].':'.$fingerprint;

            if (isset($seen[$seenKey])) {
                $duplicateCount++;

                continue;
            }

            $seen[$seenKey] = true;

            $insertRows[] = [
                'bulk_communication_batch_id' => $batch->id,
                'school_id' => $school->id,
                'channel' => $row['channel'],
                'recipient_type' => $row['recipient_type'],
                'recipient_id' => $row['recipient_id'],
                'recipient_name' => $row['recipient_name'],
                'recipient_address' => $row['recipient_address'],
                'status' => $row['status'],
                'failure_reason' => $row['failure_reason'],
                'fingerprint' => $fingerprint,
                'metadata' => json_encode($row['metadata'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($insertRows !== []) {
            BulkCommunicationRecipient::insert($insertRows);
        }
    }

    private function sendRecipient(BulkCommunicationBatch $batch, BulkCommunicationRecipient $recipient, ?User $actor): void
    {
        if ($recipient->channel !== 'email') {
            $this->markRecipientSkipped($recipient, 'Only email delivery is active. This channel is prepared for future dispatch.');

            return;
        }

        if (! filled($recipient->recipient_address)) {
            $this->markRecipientSkipped($recipient, 'No email address is available for this recipient.');

            return;
        }

        try {
            $log = $this->communications->sendSchoolEmail(
                $batch->school,
                $recipient->recipient_address,
                $batch->subject,
                $recipient->recipient_type === 'user' ? 'Bulk staff communication' : 'Bulk communication',
                $batch->body,
                $batch->type,
                array_merge($recipient->metadata ?? [], [
                    'bulk_communication_batch_id' => $batch->id,
                    'bulk_communication_batch_uuid' => $batch->batch_uuid,
                    'bulk_communication_recipient_id' => $recipient->id,
                    'channel' => $recipient->channel,
                    'filters' => $batch->filters,
                ]),
                $recipient->recipient_type === 'user'
                    ? CommunicationService::CATEGORY_STAFF_TRANSACTIONAL
                    : CommunicationService::CATEGORY_STUDENT_TRANSACTIONAL,
                $actor
            );

            $recipient->forceFill([
                'communication_log_id' => $log->id,
                'status' => $log->status === CommunicationLog::STATUS_SENT
                    ? BulkCommunicationRecipient::STATUS_SENT
                    : BulkCommunicationRecipient::STATUS_FAILED,
                'failure_reason' => $log->status === CommunicationLog::STATUS_SENT ? null : $log->failure_reason,
                'attempted_at' => now(),
                'sent_at' => $log->status === CommunicationLog::STATUS_SENT ? ($log->sent_at ?: now()) : null,
            ])->save();
        } catch (Throwable $exception) {
            $recipient->forceFill([
                'status' => BulkCommunicationRecipient::STATUS_FAILED,
                'failure_reason' => substr($exception->getMessage(), 0, 4000),
                'attempted_at' => now(),
            ])->save();

            Log::warning('Bulk communication recipient failed unexpectedly.', [
                'bulk_communication_batch_id' => $batch->id,
                'bulk_communication_recipient_id' => $recipient->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function markRecipientSkipped(BulkCommunicationRecipient $recipient, string $reason): void
    {
        $recipient->forceFill([
            'status' => BulkCommunicationRecipient::STATUS_SKIPPED,
            'failure_reason' => $reason,
            'attempted_at' => now(),
        ])->save();
    }

    private function refreshCounts(BulkCommunicationBatch $batch, ?int $duplicateCount = null): void
    {
        $counts = $batch->recipients()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $batch->forceFill([
            'total_recipients' => (int) $counts->sum(),
            'sent_count' => (int) ($counts[BulkCommunicationRecipient::STATUS_SENT] ?? 0),
            'failed_count' => (int) ($counts[BulkCommunicationRecipient::STATUS_FAILED] ?? 0),
            'skipped_count' => (int) ($counts[BulkCommunicationRecipient::STATUS_SKIPPED] ?? 0),
            'duplicate_count' => $duplicateCount ?? $batch->duplicate_count,
        ])->save();
    }

    private function completionStatus(BulkCommunicationBatch $batch, int $pendingCount): string
    {
        if ($pendingCount > 0) {
            return BulkCommunicationBatch::STATUS_PAUSED;
        }

        if ($batch->failed_count > 0) {
            return BulkCommunicationBatch::STATUS_COMPLETED_WITH_FAILURES;
        }

        return BulkCommunicationBatch::STATUS_COMPLETED;
    }

    private function normalizedData(array $data): array
    {
        $chunkSize = (int) ($data['chunk_size'] ?? self::DEFAULT_SEND_CHUNK);

        return array_merge($data, [
            'channels' => $this->recipients->channels($data),
            'result_type' => $data['result_type'] ?? 'term_result',
            'chunk_size' => max(1, min($chunkSize ?: self::DEFAULT_SEND_CHUNK, self::MAX_SEND_CHUNK)),
            'max_sync_recipients' => max(1, (int) ($data['max_sync_recipients'] ?? self::DEFAULT_MAX_SYNC_RECIPIENTS)),
            'max_runtime_seconds' => max(3, (int) ($data['max_runtime_seconds'] ?? self::DEFAULT_MAX_RUNTIME_SECONDS)),
        ]);
    }

    private function filtersSnapshot(array $data): array
    {
        return collect($data)
            ->only([
                'audience',
                'school_class_id',
                'arm_section',
                'academic_session_id',
                'term_id',
                'enrollment_status',
                'student_status',
                'published_result_status',
                'user_status',
                'result_type',
                'student_ids',
                'channels',
            ])
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== [])
            ->all();
    }

    private function requestFingerprint(School $school, User $sender, array $data): string
    {
        $fingerprintPayload = $this->filtersSnapshot($data);
        $fingerprintPayload['school_id'] = $school->id;
        $fingerprintPayload['sender_id'] = $sender->id;
        $fingerprintPayload['type'] = $data['type'];
        $fingerprintPayload['subject'] = $data['subject'];
        $fingerprintPayload['message'] = $data['message'];

        if (isset($fingerprintPayload['student_ids'])) {
            sort($fingerprintPayload['student_ids']);
        }

        return hash('sha256', json_encode(
            $this->canonicalize($fingerprintPayload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ));
    }

    private function recipientFingerprint(School $school, array $row): string
    {
        $address = Str::lower(trim((string) ($row['recipient_address'] ?? '')));
        $target = filled($address)
            ? $address
            : ($row['recipient_type'].':'.$row['recipient_id']);

        return hash('sha256', $school->id.'|'.$row['channel'].'|'.$target);
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        ksort($value);

        return array_map(fn ($item) => $this->canonicalize($item), $value);
    }

    private function recordAudit(string $action, BulkCommunicationBatch $batch, ?School $school, array $metadata): void
    {
        try {
            $this->auditLog->log($action, $batch, $school, metadata: $metadata);
        } catch (Throwable $exception) {
            Log::warning('Bulk communication audit log failed.', [
                'action' => $action,
                'bulk_communication_batch_id' => $batch->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
