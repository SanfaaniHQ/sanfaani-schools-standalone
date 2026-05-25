<?php

namespace App\Services\Updates;

use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Backups\BackupVerificationService;
use App\Support\Updates\UpdatePreflightResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class UpdatePreflightService
{
    public function __construct(
        private UpdateManifestService $manifests,
        private UpdateEntitlementService $entitlements,
        private UpdateLogService $logs,
        private UpdateRollbackService $rollbacks,
        private SystemVersionService $versions,
        private BackupVerificationService $backups,
    ) {}

    public function run(UpdatePackage $package, ?User $actor = null): UpdatePreflightResult
    {
        $manifest = $package->manifest ?: [];
        $result = UpdatePreflightResult::make([
            'package_id' => $package->id,
            'from_version' => $this->versions->currentVersion(),
            'to_version' => $package->version,
        ]);

        $this->checkPackageStatus($package, $result);
        $this->checkManifest($manifest, $result);
        $this->checkIntegrity($package, $manifest, $result);
        $this->checkEntitlement($actor, $result);
        $this->checkPhp($manifest, $result);
        $this->checkLaravel($manifest, $result);
        $this->checkWritablePaths($result);
        $this->checkBackupRequirement($manifest, $result);
        $this->checkMigrationReview($manifest, $result);
        $this->addGuidance($result);

        $package->forceFill([
            'status' => $result->passed()
                ? UpdatePackage::STATUS_PRECHECK_READY
                : UpdatePackage::STATUS_PRECHECK_BLOCKED,
            'metadata' => array_merge($package->metadata ?: [], [
                'last_preflight_at' => now()->toIso8601String(),
                'preflight' => $result->toArray(),
                'application_performed' => false,
            ]),
        ])->save();

        $this->rollbacks->createForPackage($package->fresh(), $actor);
        $this->logs->log(
            'update.preflight_completed',
            $result->summary(),
            $package,
            severity: $result->passed() ? 'info' : 'warning',
            context: $result->toArray(),
            actor: $actor,
        );

        return $result;
    }

    private function checkPackageStatus(UpdatePackage $package, UpdatePreflightResult $result): void
    {
        if (! $package->hasKnownStatus()) {
            $result->add(
                'package_status',
                'Package status',
                'fail',
                'error',
                'Unknown update package status fails closed.',
                true,
                ['status' => $package->status],
            );

            return;
        }

        $result->add('package_status', 'Package status', 'pass', 'info', 'Package status is recognized.');
    }

    private function checkManifest(array $manifest, UpdatePreflightResult $result): void
    {
        $errors = $this->manifests->validate($manifest);

        if ($errors !== []) {
            $result->add('manifest', 'Manifest metadata', 'fail', 'error', implode(' ', $errors), true);

            return;
        }

        $result->add('manifest', 'Manifest metadata', 'pass', 'info', 'Manifest metadata includes the required update fields.');
    }

    private function checkIntegrity(UpdatePackage $package, array $manifest, UpdatePreflightResult $result): void
    {
        if (! filled($package->checksum)) {
            $result->add('integrity', 'Integrity metadata', 'fail', 'error', 'Package checksum metadata is missing.', true);

            return;
        }

        if (filled($manifest['checksum'] ?? null) && ! hash_equals(strtolower((string) $manifest['checksum']), strtolower((string) $package->checksum))) {
            $result->add('integrity', 'Integrity metadata', 'fail', 'error', 'Package checksum does not match the manifest.', true);

            return;
        }

        $result->add('integrity', 'Integrity metadata', 'pass', 'info', 'Package checksum metadata is present. Signature enforcement is planned.');
    }

    private function checkEntitlement(?User $actor, UpdatePreflightResult $result): void
    {
        $decision = $this->entitlements->check($actor);

        if (! $decision['allowed']) {
            $result->add('entitlement', 'License and feature entitlement', 'fail', 'error', $decision['message'], true, $decision);

            return;
        }

        $result->add('entitlement', 'License and feature entitlement', 'pass', 'info', $decision['message'], false, $decision);
    }

    private function checkPhp(array $manifest, UpdatePreflightResult $result): void
    {
        $minimum = (string) data_get($manifest, 'minimum_php', config('updates.php_minimum', '8.2.0'));

        if (version_compare(PHP_VERSION, $minimum, '<')) {
            $result->add('php_version', 'PHP version', 'fail', 'error', "PHP {$minimum} or newer is required.", true, [
                'current' => PHP_VERSION,
                'minimum' => $minimum,
            ]);

            return;
        }

        $result->add('php_version', 'PHP version', 'pass', 'info', "PHP ".PHP_VERSION.' satisfies the update requirement.');
    }

    private function checkLaravel(array $manifest, UpdatePreflightResult $result): void
    {
        $minimum = data_get($manifest, 'minimum_laravel', config('updates.laravel_minimum'));

        if (filled($minimum) && version_compare(app()->version(), (string) $minimum, '<')) {
            $result->add('laravel_version', 'Laravel version', 'fail', 'error', "Laravel {$minimum} or newer is required.", true, [
                'current' => app()->version(),
                'minimum' => $minimum,
            ]);

            return;
        }

        $result->add('laravel_version', 'Laravel version', 'pass', 'info', 'Laravel version satisfies the manifest requirement.');
    }

    private function checkWritablePaths(UpdatePreflightResult $result): void
    {
        $storageWritable = File::isWritable(storage_path());
        $cacheWritable = File::isWritable(base_path('bootstrap/cache'));

        $result->add(
            'storage_writable',
            'Storage writable',
            $storageWritable ? 'pass' : 'fail',
            $storageWritable ? 'info' : 'error',
            $storageWritable ? 'Storage is writable.' : 'Storage must be writable before update preparation.',
            ! $storageWritable,
        );

        $result->add(
            'cache_writable',
            'Bootstrap cache writable',
            $cacheWritable ? 'pass' : 'fail',
            $cacheWritable ? 'info' : 'error',
            $cacheWritable ? 'Bootstrap cache is writable.' : 'Bootstrap cache must be writable before update preparation.',
            ! $cacheWritable,
        );
    }

    private function checkBackupRequirement(array $manifest, UpdatePreflightResult $result): void
    {
        $backupRequired = (bool) config('updates.backup_required', true) || (bool) data_get($manifest, 'requires_backup', false);

        if (! $backupRequired) {
            $result->add('backup_requirement', 'Backup requirement', 'pass', 'info', 'Backup requirement is not enabled for this package.');

            return;
        }

        $school = $this->entitlements->defaultSchool();
        $hasRecentBackup = (bool) config('backups.enabled', true)
            && $this->backups->hasRecentVerifiedBackup($school);

        $result->add(
            'backup_requirement',
            'Backup requirement',
            $hasRecentBackup ? 'pass' : 'pending',
            $hasRecentBackup ? 'info' : 'pending',
            $hasRecentBackup
                ? 'A recent verified backup is available for this update preflight.'
                : 'A recent verified backup is required before update readiness can pass. Create and verify backup metadata in the backup manager first.',
            ! $hasRecentBackup,
            [
                'backup_manager_available' => (bool) config('backups.enabled', true),
                'recent_verified_backup' => $hasRecentBackup,
                'backup_manager_route' => Route::has('admin.backups.index') ? route('admin.backups.index') : null,
            ],
        );
    }

    private function checkMigrationReview(array $manifest, UpdatePreflightResult $result): void
    {
        $requiresMigration = (bool) data_get($manifest, 'requires_migration', false)
            || collect((array) data_get($manifest, 'database_changes', []))->isNotEmpty();

        if (! $requiresMigration) {
            $result->add('migration_review', 'Migration safety review', 'pass', 'info', 'No migration requirement is declared by the manifest.');

            return;
        }

        $result->add(
            'migration_review',
            'Migration safety review',
            'warning',
            'warning',
            'Manifest declares database changes. Review migration notes manually; the web wizard will not run migrations.',
            false,
            ['migration_notes' => data_get($manifest, 'migration_notes')],
        );
    }

    private function addGuidance(UpdatePreflightResult $result): void
    {
        $result->add(
            'maintenance_mode',
            'Maintenance-mode guidance',
            'info',
            'info',
            'Plan a maintenance window before copying files manually. Shared hosting users should use cPanel or Namecheap file manager guidance.',
        );

        $result->add(
            'no_web_update_execution',
            'Web execution safety',
            'info',
            'info',
            'This wizard never extracts packages, runs shell commands, or runs migrations from a web request.',
        );
    }
}
