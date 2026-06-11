<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupVerification;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupVerificationService
{
    public function __construct(
        private BackupLogService $logs,
    ) {}

    public function verify(Backup $backup, ?User $actor = null): BackupVerification
    {
        $backup->loadMissing('items');

        if (! $backup->hasKnownStatus()) {
            return $this->record($backup, BackupVerification::STATUS_FAILED, false, false, false, 'Unknown backup status fails closed.', [
                'backup_status' => $backup->status,
            ], $actor);
        }

        $metadataExists = filled($backup->path) && Storage::disk($backup->disk)->exists($backup->path);
        $metadataSize = $this->metadataSize($backup, $metadataExists);
        $metadataReadable = $metadataExists && $metadataSize !== null && $metadataSize > 0;
        $checksumValid = $this->checksumValid($backup, $metadataReadable);
        $manifest = $this->manifestConsistency($backup, $metadataReadable);
        $requiredItemsPresent = $this->requiredItemsPresent($backup);
        $hasWarnings = $backup->items->contains(fn ($item): bool => in_array($item->status, ['warning', 'disabled', 'failed'], true));

        $status = $metadataReadable
            && $requiredItemsPresent
            && $checksumValid === true
            && $manifest['consistent']
            && ! $hasWarnings
            ? BackupVerification::STATUS_VERIFIED
            : BackupVerification::STATUS_WARNING;

        $message = $status === BackupVerification::STATUS_VERIFIED
            ? 'Backup metadata verification passed.'
            : 'Backup metadata verification needs manual review before it can satisfy pre-update requirements.';

        return $this->record($backup, $status, $checksumValid, $metadataExists, $requiredItemsPresent, $message, [
            'metadata_file_present' => $metadataExists,
            'metadata_file_readable' => $metadataReadable,
            'metadata_file_size_bytes' => $metadataSize ?? 0,
            'required_items_present' => $requiredItemsPresent,
            'checksum_available' => filled($backup->checksum),
            'manifest_consistent' => $manifest['consistent'],
            'manifest_checks' => $manifest['checks'],
            'items_with_warnings' => $backup->items->whereIn('status', ['warning', 'disabled', 'failed'])->pluck('source_label')->values()->all(),
        ], $actor);
    }

    public function hasRecentVerifiedBackup(?School $school = null): bool
    {
        return BackupVerification::query()
            ->where('status', BackupVerification::STATUS_VERIFIED)
            ->where('checked_at', '>=', now()->subDays(max(1, (int) config('backups.recent_verified_days', 14))))
            ->whereHas('backup', function ($query) use ($school): void {
                $query->whereNotIn('status', [
                    Backup::STATUS_FAILED,
                    Backup::STATUS_EXPIRED,
                    Backup::STATUS_PRUNED,
                ])->where(function ($query): void {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });

                if ($school) {
                    $query->where('school_id', $school->id);
                } else {
                    $query->whereNull('school_id');
                }
            })
            ->exists();
    }

    public function readiness(?School $school = null): array
    {
        $ready = $this->hasRecentVerifiedBackup($school);

        return [
            'ready' => $ready,
            'status' => $ready ? 'ready' : 'missing',
            'message' => $ready
                ? 'A recent verified backup is available for pre-update checks.'
                : 'A recent verified backup is required before update readiness can pass.',
            'recent_verified_days' => max(1, (int) config('backups.recent_verified_days', 14)),
        ];
    }

    private function record(
        Backup $backup,
        string $status,
        ?bool $checksumValid,
        ?bool $archiveReadable,
        ?bool $requiredItemsPresent,
        string $message,
        array $context,
        ?User $actor,
    ): BackupVerification {
        $verification = BackupVerification::create([
            'backup_id' => $backup->id,
            'status' => $status,
            'checked_at' => now(),
            'checksum_valid' => $checksumValid,
            'archive_readable' => $archiveReadable,
            'required_items_present' => $requiredItemsPresent,
            'message' => $message,
            'context' => $context,
        ]);

        if ($status === BackupVerification::STATUS_VERIFIED) {
            $backup->forceFill(['status' => Backup::STATUS_VERIFIED])->save();
        } elseif ($status === BackupVerification::STATUS_WARNING && ! in_array($backup->status, [Backup::STATUS_FAILED, Backup::STATUS_PRUNED], true)) {
            $backup->forceFill(['status' => Backup::STATUS_WARNING])->save();
        }

        $this->logs->log(
            'backup.verification_completed',
            $message,
            $backup,
            severity: $status === BackupVerification::STATUS_VERIFIED ? 'info' : 'warning',
            context: $context + ['verification_status' => $status],
            actor: $actor,
        );

        return $verification;
    }

    private function metadataSize(Backup $backup, bool $metadataExists): ?int
    {
        if (! $metadataExists) {
            return null;
        }

        try {
            return Storage::disk($backup->disk)->size($backup->path);
        } catch (Throwable) {
            return null;
        }
    }

    private function checksumValid(Backup $backup, bool $metadataReadable): ?bool
    {
        if (! $metadataReadable || ! filled($backup->checksum)) {
            return null;
        }

        try {
            $contents = Storage::disk($backup->disk)->get($backup->path);

            return hash_equals((string) $backup->checksum, hash('sha256', (string) $contents));
        } catch (Throwable) {
            return false;
        }
    }

    private function manifestConsistency(Backup $backup, bool $metadataReadable): array
    {
        $checks = [
            'json_readable' => false,
            'backup_id_matches' => false,
            'type_matches' => false,
            'safe_foundation_only' => false,
            'restore_not_performed' => false,
            'env_not_exported' => false,
        ];

        if (! $metadataReadable) {
            return ['consistent' => false, 'checks' => $checks];
        }

        try {
            $manifest = json_decode((string) Storage::disk($backup->disk)->get($backup->path), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ['consistent' => false, 'checks' => $checks];
        }

        if (! is_array($manifest)) {
            return ['consistent' => false, 'checks' => $checks];
        }

        $checks['json_readable'] = true;
        $checks['backup_id_matches'] = (string) ($manifest['backup_id'] ?? '') === (string) $backup->id;
        $checks['type_matches'] = (string) ($manifest['type'] ?? '') === (string) $backup->type;
        $checks['safe_foundation_only'] = (bool) ($manifest['safe_foundation_only'] ?? false);
        $checks['restore_not_performed'] = (bool) data_get($manifest, 'restore_performed') === false;
        $checks['env_not_exported'] = (bool) data_get($manifest, 'env_exported') === false;

        return [
            'consistent' => collect($checks)->every(fn (bool $passed): bool => $passed),
            'checks' => $checks,
        ];
    }

    private function requiredItemsPresent(Backup $backup): bool
    {
        $required = collect([
            'database' => (bool) config('backups.database_enabled', true),
            'files' => (bool) config('backups.files_enabled', true),
            'config' => (bool) config('backups.config_enabled', true),
        ])->filter()->keys();

        $present = $backup->items->pluck('item_type')->unique();

        return $required->every(fn (string $type): bool => $present->contains($type));
    }
}
