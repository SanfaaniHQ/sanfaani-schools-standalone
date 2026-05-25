<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\User;

class BackupRetentionService
{
    public function __construct(
        private BackupLogService $logs,
    ) {}

    public function policy(): array
    {
        return [
            'retention_days' => max(1, (int) config('backups.retention_days', 14)),
            'max_archive_mb' => max(1, (int) config('backups.max_archive_mb', 250)),
            'safe_prune_only' => true,
        ];
    }

    public function markExpired(?User $actor = null): int
    {
        $count = 0;

        Backup::query()
            ->where('expires_at', '<', now())
            ->whereNotIn('status', [Backup::STATUS_PRUNED, Backup::STATUS_FAILED])
            ->each(function (Backup $backup) use (&$count, $actor): void {
                $backup->forceFill(['status' => Backup::STATUS_EXPIRED])->save();
                $count++;

                $this->logs->log(
                    'backup.retention_marked_expired',
                    'Backup metadata was marked expired by retention policy. No restore operation was run.',
                    $backup,
                    severity: 'info',
                    actor: $actor,
                );
            });

        return $count;
    }

    public function pruneExpired(?User $actor = null): int
    {
        $this->markExpired($actor);
        $count = 0;

        Backup::query()
            ->where('status', Backup::STATUS_EXPIRED)
            ->each(function (Backup $backup) use (&$count, $actor): void {
                $backup->forceFill([
                    'status' => Backup::STATUS_PRUNED,
                    'metadata' => array_merge($backup->metadata ?: [], [
                        'pruned_at' => now()->toIso8601String(),
                        'metadata_file_deleted' => false,
                        'restore_performed' => false,
                    ]),
                ])->save();
                $count++;

                $this->logs->log(
                    'backup.retention_pruned',
                    'Backup metadata was pruned safely. Backup contents were not restored or exposed.',
                    $backup,
                    severity: 'info',
                    context: ['metadata_file_deleted' => false],
                    actor: $actor,
                );
            });

        return $count;
    }
}
