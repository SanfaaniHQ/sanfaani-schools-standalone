<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseEntitlementService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\System\DeploymentBehaviorService;
use App\Services\System\DeploymentModeService;
use App\Services\System\FeatureAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private DeploymentBehaviorService $behavior,
        private FeatureAccessService $features,
        private LicenseValidationService $licenses,
        private LicenseEntitlementService $entitlements,
        private BackupPreflightService $preflight,
        private BackupDatabaseService $database,
        private BackupFilesService $files,
        private BackupConfigService $config,
        private BackupVerificationService $verification,
        private BackupRetentionService $retention,
        private BackupLogService $logs,
        private BackupRestorePlanService $restorePlans,
    ) {}

    public function checkAccess(?User $user = null): array
    {
        $user ??= auth()->user();
        $school = $this->defaultSchool();

        if (! (bool) config('backups.enabled', true)) {
            return $this->deny('disabled', 'Backup manager is disabled by configuration.');
        }

        if (($this->licenses->requiresValidation() && $this->deployment->isDemo()) || $this->isDemoUser($user)) {
            return $this->deny('demo_blocked', 'Demo environments cannot access the backup manager.');
        }

        if ($this->licenses->requiresValidation()
            && $this->deployment->isTrial()
            && ! (bool) config('backups.trial_allowed', false)) {
            return $this->deny('trial_blocked', 'Trial licenses are not allowed to access backup manager.');
        }

        $feature = (string) config('backups.feature', 'backup_manager');
        if (! $this->features->enabled($feature, $school, $user)) {
            return $this->deny('feature_disabled', $this->features->reason($feature, $school, $user));
        }

        $routeGroup = $this->routeGroup();
        if ($routeGroup && ! $this->behavior->allowsRouteGroup($routeGroup, $school, $user)) {
            return $this->deny('deployment_blocked', 'Backup manager is not available for this deployment behavior.');
        }

        if ($this->licenses->requiresValidation()) {
            $licenseResult = $this->licenses->validate($school);

            if (! $licenseResult->valid()) {
                return $this->deny('license_invalid', $licenseResult->message);
            }

            if ((bool) config('backups.require_license_entitlement', true) && ! $this->hasBackupEntitlement($school)) {
                return $this->deny('entitlement_missing', 'The active license does not include backup manager entitlement.');
            }
        }

        return [
            'allowed' => true,
            'status' => 'allowed',
            'message' => 'Backup manager access is allowed.',
            'school_id' => $school?->id,
            'route_group' => $routeGroup,
            'label' => $this->label(),
        ];
    }

    public function createManualBackup(?User $actor = null, ?School $school = null, string $type = Backup::TYPE_MANUAL, string $trigger = 'manual'): Backup
    {
        $school ??= $this->defaultSchool();
        $preflight = $this->preflight->run($school, $actor);

        $backup = Backup::create([
            'school_id' => $school?->id,
            'type' => $type,
            'status' => Backup::STATUS_REQUESTED,
            'disk' => (string) config('backups.disk', 'local'),
            'path' => null,
            'filename' => null,
            'size_bytes' => null,
            'checksum' => null,
            'trigger' => $trigger,
            'created_by' => $actor?->id ?? auth()->id(),
            'started_at' => now(),
            'expires_at' => now()->addDays(max(1, (int) config('backups.retention_days', 14))),
            'metadata' => [
                'safe_foundation_only' => true,
                'preflight' => $preflight->toArray(),
                'archive_created' => false,
                'restore_performed' => false,
                'contains_env_secrets' => false,
            ],
        ]);

        $this->logs->log(
            'backup.requested',
            'Backup metadata request was created. No destructive operation was run.',
            $backup,
            severity: 'info',
            actor: $actor,
        );

        if (! $preflight->passed()) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'failed_at' => now(),
                'metadata' => array_merge($backup->metadata ?: [], ['blocked_by_preflight' => true]),
            ])->save();

            $this->logs->log(
                'backup.preflight_blocked',
                'Backup request was blocked by preflight checks.',
                $backup,
                severity: 'error',
                actor: $actor,
            );

            return $backup->fresh(['items', 'logs', 'latestVerification', 'restorePlan']);
        }

        $backup->forceFill(['status' => Backup::STATUS_RUNNING])->save();

        try {
            $this->database->capture($backup);
            $this->files->capture($backup);
            $this->config->capture($backup);
            $this->writeMetadataManifest($backup);

            $backup->load('items');
            $hasWarnings = $backup->items->contains(fn ($item): bool => in_array($item->status, ['warning', 'disabled', 'failed'], true));

            $backup->forceFill([
                'status' => $hasWarnings ? Backup::STATUS_WARNING : Backup::STATUS_COMPLETED,
                'completed_at' => now(),
                'metadata' => array_merge($backup->metadata ?: [], [
                    'completed_metadata_only' => true,
                    'items_count' => $backup->items->count(),
                    'manual_database_export_required' => $backup->items->contains(fn ($item): bool => (bool) data_get($item->metadata, 'manual_export_required')),
                ]),
            ])->save();

            $this->restorePlans->createForBackup($backup, $actor);

            if ((bool) config('backups.verify_after_create', true)) {
                $this->verification->verify($backup->fresh(), $actor);
            }

            $this->logs->log(
                $hasWarnings ? 'backup.completed_with_warnings' : 'backup.completed',
                $hasWarnings
                    ? 'Backup metadata was created with warnings that need manual review.'
                    : 'Backup metadata was created safely.',
                $backup,
                severity: $hasWarnings ? 'warning' : 'info',
                actor: $actor,
            );
        } catch (Throwable $exception) {
            $backup->forceFill([
                'status' => Backup::STATUS_FAILED,
                'failed_at' => now(),
                'metadata' => array_merge($backup->metadata ?: [], [
                    'failure' => 'metadata_write_failed',
                ]),
            ])->save();

            $this->logs->log(
                'backup.failed',
                'Backup metadata could not be completed: '.$exception->getMessage(),
                $backup,
                severity: 'error',
                actor: $actor,
            );
        }

        return $backup->fresh(['items', 'logs', 'latestVerification', 'restorePlan']);
    }

    public function hasRecentVerifiedBackup(?School $school = null): bool
    {
        return $this->verification->hasRecentVerifiedBackup($school);
    }

    public function preUpdateReadiness(?School $school = null): array
    {
        return $this->verification->readiness($school);
    }

    public function visibleBackups(?User $user = null): Builder
    {
        $user ??= auth()->user();
        return Backup::query()
            ->with(['school', 'creator', 'latestVerification'])
            ->when(! $user?->hasRole('super_admin'), function (Builder $query) use ($user): void {
                $query->where('school_id', $user?->school_id);
            });
    }

    public function defaultSchool(): ?School
    {
        if ($this->deployment->isSingleSchool() || $this->deployment->isManaged()) {
            return School::query()->orderBy('id')->first();
        }

        return null;
    }

    public function label(): string
    {
        return (string) config('backups.labels.'.$this->deployment->mode(), 'Backups');
    }

    public function routeGroup(): ?string
    {
        $group = config('backups.deployment_route_groups.'.$this->deployment->mode());

        return filled($group) ? (string) $group : null;
    }

    public function retentionPolicy(): array
    {
        return $this->retention->policy();
    }

    private function writeMetadataManifest(Backup $backup): void
    {
        $backup->load('items');

        $filename = 'backup-metadata-'.$backup->id.'-'.now()->format('Ymd-His').'.json';
        $path = trim((string) config('backups.metadata_directory', 'backups/metadata'), '/').'/'.$filename;

        $payload = [
            'backup_id' => $backup->id,
            'type' => $backup->type,
            'status' => 'metadata_recorded',
            'school_id' => $backup->school_id,
            'created_at' => now()->toIso8601String(),
            'safe_foundation_only' => true,
            'archive_created' => false,
            'restore_performed' => false,
            'env_exported' => false,
            'items' => $backup->items
                ->map(fn ($item): array => [
                    'type' => $item->item_type,
                    'label' => $item->source_label,
                    'path' => $item->path,
                    'status' => $item->status,
                ])
                ->values()
                ->all(),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (! Storage::disk($backup->disk)->put($path, $json)) {
            throw new \RuntimeException('Backup metadata file could not be stored.');
        }

        $backup->forceFill([
            'path' => $path,
            'filename' => $filename,
            'size_bytes' => strlen((string) $json),
            'checksum' => hash('sha256', (string) $json),
        ])->save();
    }

    private function hasBackupEntitlement(?School $school): bool
    {
        foreach ((array) config('backups.entitlement_keys', ['backup_manager']) as $key) {
            if ($this->entitlements->explicitAccess((string) $key, $school) === true) {
                return true;
            }
        }

        return false;
    }

    private function isDemoUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        try {
            return $user->demoCredentials()
                ->where('status', 'active')
                ->where(function ($query): void {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->whereHas('demoSession', function ($query): void {
                    $query->where('status', 'active')
                        ->where(function ($query): void {
                            $query->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                })
                ->exists();
        } catch (Throwable) {
            return false;
        }
    }

    private function deny(string $status, string $message): array
    {
        return [
            'allowed' => false,
            'status' => $status,
            'message' => $message,
            'school_id' => null,
            'route_group' => $this->routeGroup(),
            'label' => $this->label(),
        ];
    }
}
