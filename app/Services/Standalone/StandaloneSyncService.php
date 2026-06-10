<?php

namespace App\Services\Standalone;

use App\Models\StandaloneSyncLog;
use App\Models\StandaloneSyncOutbox;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Schema;

class StandaloneSyncService
{
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

        $this->log('push', 'skipped', 'Real cloud transport is not implemented in this stage; no local data was deleted or changed.', [
            'pending_count' => $items->count(),
            'endpoint_configured' => true,
        ]);

        return [
            'success' => true,
            'dry_run' => false,
            'code' => 'transport_not_implemented',
            'message' => 'Real cloud transport is not implemented in this stage; no local data was deleted or changed.',
            'pending_count' => $items->count(),
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

    private function tablesReady(): bool
    {
        return Schema::hasTable('standalone_sync_outbox')
            && Schema::hasTable('standalone_sync_logs');
    }
}
