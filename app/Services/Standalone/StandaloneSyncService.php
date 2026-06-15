<?php

namespace App\Services\Standalone;

use App\Models\AttendanceOfflineSyncReceipt;
use App\Models\School;
use App\Models\StandaloneSyncLog;
use App\Models\StandaloneSyncOutbox;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StandaloneSyncService
{
    public const OFFLINE_ATTENDANCE_RESULT_STATUSES = [
        'synced',
        'skipped_duplicate',
        'conflict',
        'failed_validation',
        'failed_permission',
        'processing',
    ];

    public function __construct(
        private StandaloneEditionService $edition,
    ) {}

    public function status(): array
    {
        $tablesReady = $this->tablesReady();
        $lastLog = $tablesReady
            ? StandaloneSyncLog::query()->latest('id')->first()
            : null;

        return [
            'enabled' => $this->edition->cloudSyncEnabled(),
            'endpoint_configured' => $this->edition->syncEndpoint() !== null,
            'token_configured' => $this->edition->syncTokenConfigured(),
            'backup_sync_enabled' => $this->edition->backupSyncEnabled(),
            'tables_ready' => $tablesReady,
            'offline_attendance_capture_enabled' => $this->edition->offlineAttendanceCaptureEnabled(),
            'offline_attendance_sync_enabled' => $this->edition->offlineAttendanceSyncEnabled(),
            'offline_allowed_modules' => $this->edition->pwaOfflineAllowedModules(),
            'offline_attendance_sync_health' => $this->offlineAttendanceSyncHealth(),
            'pending_count' => $tablesReady ? StandaloneSyncOutbox::query()->where('status', StandaloneSyncOutbox::STATUS_PENDING)->count() : null,
            'failed_count' => $tablesReady ? StandaloneSyncOutbox::query()->where('status', StandaloneSyncOutbox::STATUS_FAILED)->count() : null,
            'last_sync' => $lastLog ? [
                'direction' => $lastLog->direction,
                'status' => $lastLog->status,
                'message' => $lastLog->message,
                'started_at' => $lastLog->started_at,
                'finished_at' => $lastLog->finished_at,
            ] : null,
        ];
    }

    public function pendingOutboxItems(int $limit = 50): EloquentCollection
    {
        if (! $this->tablesReady()) {
            return new EloquentCollection;
        }

        return StandaloneSyncOutbox::query()
            ->where('status', StandaloneSyncOutbox::STATUS_PENDING)
            ->where(function ($query): void {
                $query->whereNull('available_at')
                    ->orWhere('available_at', '<=', now());
            })
            ->orderByRaw('available_at is null desc')
            ->orderBy('available_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function markPending(string $entityType, int|string|null $entityId, string $action, array $payload = [], mixed $availableAt = null): StandaloneSyncOutbox
    {
        return StandaloneSyncOutbox::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'payload' => $payload,
            'payload_hash' => $this->payloadHash($payload),
            'status' => StandaloneSyncOutbox::STATUS_PENDING,
            'attempts' => 0,
            'available_at' => $availableAt,
        ]);
    }

    public function markSynced(StandaloneSyncOutbox|int|string $item): StandaloneSyncOutbox
    {
        $outbox = $this->resolveOutbox($item);

        $outbox->forceFill([
            'status' => StandaloneSyncOutbox::STATUS_SYNCED,
            'last_error' => null,
            'synced_at' => now(),
        ])->save();

        return $outbox;
    }

    public function markFailed(StandaloneSyncOutbox|int|string $item, string $error): StandaloneSyncOutbox
    {
        $outbox = $this->resolveOutbox($item);

        $outbox->forceFill([
            'status' => StandaloneSyncOutbox::STATUS_FAILED,
            'attempts' => (int) $outbox->attempts + 1,
            'last_error' => str($error)->limit(1000)->toString(),
        ])->save();

        return $outbox;
    }

    public function markSkipped(StandaloneSyncOutbox|int|string $item, string $reason): StandaloneSyncOutbox
    {
        $outbox = $this->resolveOutbox($item);

        $outbox->forceFill([
            'status' => StandaloneSyncOutbox::STATUS_SKIPPED,
            'last_error' => str($reason)->limit(1000)->toString(),
        ])->save();

        return $outbox;
    }

    public function dryRunSync(): array
    {
        $items = $this->pendingOutboxItems();

        $this->log('push', 'dry_run', 'Dry-run completed. No external sync was attempted.', [
            'pending_count' => $items->count(),
        ]);

        return [
            'success' => true,
            'dry_run' => true,
            'message' => 'Dry-run completed. No external sync was attempted.',
            'would_sync_count' => $items->count(),
            'items' => $items->map(fn (StandaloneSyncOutbox $item): array => [
                'uuid' => $item->uuid,
                'entity_type' => $item->entity_type,
                'entity_id' => $item->entity_id,
                'action' => $item->action,
                'status' => $item->status,
            ])->values()->all(),
        ];
    }

    public function runSync(bool $dryRun = false): array
    {
        if ($dryRun) {
            return $this->dryRunSync();
        }

        if (! $this->edition->cloudSyncEnabled()) {
            return $this->refuse('sync_disabled', 'Standalone sync is disabled. Enable SANFAANI_STANDALONE_SYNC_ENABLED before running real sync.');
        }

        if (! $this->edition->syncEndpoint() || ! $this->edition->syncTokenConfigured()) {
            return $this->refuse('sync_not_configured', 'Standalone sync endpoint and token must be configured before running real sync.');
        }

        $items = $this->pendingOutboxItems();

        $this->log('push', 'skipped', 'Cloud transport is not active for this installation; no local data was deleted or changed.', [
            'pending_count' => $items->count(),
            'endpoint_configured' => true,
        ]);

        return [
            'success' => true,
            'dry_run' => false,
            'code' => 'transport_not_implemented',
            'message' => 'Cloud transport is not active for this installation; no local data was deleted or changed.',
            'pending_count' => $items->count(),
        ];
    }

    public function logBrowserOfflineAttendanceSync(?int $schoolId, ?int $userId, array $summary): ?StandaloneSyncLog
    {
        return $this->log('browser_push', 'processed', 'Browser offline attendance sync processed by Laravel validation.', [
            'module' => 'attendance',
            'school_id' => $schoolId,
            'user_id' => $userId,
            'summary' => $summary,
        ]);
    }

    public function offlineAttendanceSyncHealth(School|int|null $school = null, array $filters = []): array
    {
        $schoolId = $school instanceof School ? $school->id : $school;
        $defaults = $this->emptyOfflineAttendanceSyncHealth();

        if (! $this->offlineAttendanceReceiptTableReady()) {
            return $defaults;
        }

        $receiptQuery = $this->offlineAttendanceReceiptQuery($schoolId, $filters);
        $receiptStatusCounts = $this->receiptStatusCounts($receiptQuery);
        $attemptSummary = $this->offlineAttendanceAttemptSummary($schoolId, $filters);

        $statusCounts = collect(self::OFFLINE_ATTENDANCE_RESULT_STATUSES)
            ->mapWithKeys(fn (string $status): array => [
                $status => max(
                    (int) ($receiptStatusCounts[$status] ?? 0),
                    (int) ($attemptSummary['status_counts'][$status] ?? 0)
                ),
            ])
            ->all();

        $latestSuccessfulReceipt = (clone $receiptQuery)
            ->where('result_status', 'synced')
            ->whereNotNull('processed_at')
            ->latest('processed_at')
            ->first();
        $latestFailureReceipt = (clone $receiptQuery)
            ->whereIn('result_status', ['conflict', 'failed_validation', 'failed_permission'])
            ->whereNotNull('processed_at')
            ->latest('processed_at')
            ->first();
        $latestReceipt = (clone $receiptQuery)
            ->orderByDesc('processed_at')
            ->orderByDesc('created_at')
            ->first();

        $latestFailureAttemptAt = $attemptSummary['latest_failure_or_conflict_at'];
        $latestFailureReceiptAt = $latestFailureReceipt?->processed_at;

        return [
            ...$defaults,
            'tables_ready' => true,
            'receipt_total' => (clone $receiptQuery)->count(),
            'attempt_total' => $attemptSummary['total'],
            'status_counts' => $statusCounts,
            'receipt_status_counts' => $receiptStatusCounts,
            'attempt_status_counts' => $attemptSummary['status_counts'],
            'synced_count' => $statusCounts['synced'],
            'skipped_duplicate_count' => $statusCounts['skipped_duplicate'],
            'conflict_count' => $statusCounts['conflict'],
            'failed_validation_count' => $statusCounts['failed_validation'],
            'failed_permission_count' => $statusCounts['failed_permission'],
            'processing_count' => $statusCounts['processing'],
            'latest_sync_attempt_at' => $attemptSummary['latest_attempt_at'],
            'latest_successful_sync_at' => $latestSuccessfulReceipt?->processed_at,
            'latest_failure_or_conflict_at' => $this->latestCarbon($latestFailureReceiptAt, $latestFailureAttemptAt),
            'latest_receipt_at' => $latestReceipt?->processed_at ?? $latestReceipt?->created_at,
        ];
    }

    private function refuse(string $code, string $message): array
    {
        $this->log('push', 'refused', $message, ['code' => $code]);

        return [
            'success' => false,
            'dry_run' => false,
            'code' => $code,
            'message' => $message,
        ];
    }

    private function log(string $direction, string $status, string $message, array $meta = []): ?StandaloneSyncLog
    {
        if (! $this->tablesReady()) {
            return null;
        }

        return StandaloneSyncLog::create([
            'direction' => $direction,
            'status' => $status,
            'message' => $message,
            'started_at' => now(),
            'finished_at' => now(),
            'meta' => $meta,
        ]);
    }

    private function resolveOutbox(StandaloneSyncOutbox|int|string $item): StandaloneSyncOutbox
    {
        if ($item instanceof StandaloneSyncOutbox) {
            return $item;
        }

        return StandaloneSyncOutbox::query()
            ->where('id', $item)
            ->orWhere('uuid', (string) $item)
            ->firstOrFail();
    }

    private function payloadHash(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        return hash('sha256', $json === false ? '' : $json);
    }

    private function emptyOfflineAttendanceSyncHealth(): array
    {
        $statusCounts = collect(self::OFFLINE_ATTENDANCE_RESULT_STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => 0])
            ->all();

        return [
            'tables_ready' => false,
            'receipt_total' => 0,
            'attempt_total' => 0,
            'status_counts' => $statusCounts,
            'receipt_status_counts' => $statusCounts,
            'attempt_status_counts' => $statusCounts,
            'synced_count' => 0,
            'skipped_duplicate_count' => 0,
            'conflict_count' => 0,
            'failed_validation_count' => 0,
            'failed_permission_count' => 0,
            'processing_count' => 0,
            'latest_sync_attempt_at' => null,
            'latest_successful_sync_at' => null,
            'latest_failure_or_conflict_at' => null,
            'latest_receipt_at' => null,
        ];
    }

    private function offlineAttendanceReceiptQuery(?int $schoolId, array $filters)
    {
        return AttendanceOfflineSyncReceipt::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when(filled($filters['processed_by'] ?? null), fn ($query) => $query->where('processed_by', (int) $filters['processed_by']))
            ->when(filled($filters['status'] ?? null), function ($query) use ($filters): void {
                $status = (string) $filters['status'];

                if ($status === 'skipped_duplicate') {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('result_status', $status);
            })
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $this->whereReceiptDate($query, '>=', (string) $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $this->whereReceiptDate($query, '<=', (string) $filters['date_to']));
    }

    private function receiptStatusCounts($receiptQuery): array
    {
        $counts = (clone $receiptQuery)
            ->selectRaw('result_status, count(*) as aggregate')
            ->groupBy('result_status')
            ->pluck('aggregate', 'result_status')
            ->map(fn ($count): int => (int) $count)
            ->all();

        return collect(self::OFFLINE_ATTENDANCE_RESULT_STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function offlineAttendanceAttemptSummary(?int $schoolId, array $filters): array
    {
        $statusCounts = collect(self::OFFLINE_ATTENDANCE_RESULT_STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => 0])
            ->all();
        $selectedStatus = filled($filters['status'] ?? null) ? (string) $filters['status'] : null;
        $selectedUser = filled($filters['processed_by'] ?? null) ? (int) $filters['processed_by'] : null;
        $latestAttemptAt = null;
        $latestFailureOrConflictAt = null;

        if (! $this->tablesReady()) {
            return [
                'total' => 0,
                'status_counts' => $statusCounts,
                'latest_attempt_at' => null,
                'latest_failure_or_conflict_at' => null,
            ];
        }

        $logs = StandaloneSyncLog::query()
            ->where('direction', 'browser_push')
            ->where('status', 'processed')
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('started_at', '>=', (string) $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('started_at', '<=', (string) $filters['date_to']))
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get(['started_at', 'created_at', 'meta']);

        foreach ($logs as $log) {
            $meta = (array) $log->meta;

            if ($schoolId && (int) ($meta['school_id'] ?? 0) !== (int) $schoolId) {
                continue;
            }

            if ($selectedUser && (int) ($meta['user_id'] ?? 0) !== $selectedUser) {
                continue;
            }

            $attemptAt = $log->started_at ?? $log->created_at;
            $latestAttemptAt = $this->latestCarbon($latestAttemptAt, $attemptAt);
            $summary = (array) ($meta['summary'] ?? []);

            foreach (self::OFFLINE_ATTENDANCE_RESULT_STATUSES as $status) {
                if ($selectedStatus && $selectedStatus !== $status) {
                    continue;
                }

                $statusCounts[$status] += (int) ($summary[$status] ?? 0);
            }

            if (((int) ($summary['conflict'] ?? 0) + (int) ($summary['failed_validation'] ?? 0) + (int) ($summary['failed_permission'] ?? 0)) > 0) {
                $latestFailureOrConflictAt = $this->latestCarbon($latestFailureOrConflictAt, $attemptAt);
            }
        }

        return [
            'total' => array_sum($statusCounts),
            'status_counts' => $statusCounts,
            'latest_attempt_at' => $latestAttemptAt,
            'latest_failure_or_conflict_at' => $latestFailureOrConflictAt,
        ];
    }

    private function whereReceiptDate($query, string $operator, string $date): void
    {
        $query->where(function ($query) use ($operator, $date): void {
            $query->whereDate('processed_at', $operator, $date)
                ->orWhere(function ($query) use ($operator, $date): void {
                    $query->whereNull('processed_at')
                        ->whereDate('created_at', $operator, $date);
                });
        });
    }

    private function latestCarbon(?CarbonInterface $first, ?CarbonInterface $second): ?CarbonInterface
    {
        if (! $first) {
            return $second;
        }

        if (! $second) {
            return $first;
        }

        return $first->greaterThan($second) ? $first : $second;
    }

    private function offlineAttendanceReceiptTableReady(): bool
    {
        try {
            return Schema::hasTable('attendance_offline_sync_receipts');
        } catch (Throwable) {
            return false;
        }
    }

    private function tablesReady(): bool
    {
        try {
            return Schema::hasTable('standalone_sync_outbox')
                && Schema::hasTable('standalone_sync_logs');
        } catch (Throwable) {
            return false;
        }
    }
}
