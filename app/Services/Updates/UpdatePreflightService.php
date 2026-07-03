<?php

namespace App\Services\Updates;

use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Installer\InstallerStateService;
use App\Services\Backups\BackupVerificationService;
use App\Services\Licensing\LicenseValidationService;
use App\Support\Updates\UpdatePreflightResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdatePreflightService
{
    public function __construct(
        private UpdateManifestService $manifests,
        private UpdateEntitlementService $entitlements,
        private UpdateLogService $logs,
        private UpdateRollbackService $rollbacks,
        private SystemVersionService $versions,
        private BackupVerificationService $backups,
        private InstallerStateService $installer,
        private LicenseValidationService $licenses,
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
        $this->checkCurrentVersion($manifest, $result);
        $this->checkCompatibility($manifest, $result);
        $this->checkIntegrity($package, $manifest, $result);
        $this->checkEntitlement($actor, $result);
        if ($this->licenses->requiresValidation()) {
            $this->checkLicenseStatus($result);
        }
        $this->checkPhp($manifest, $result);
        $this->checkLaravel($manifest, $result);
        $this->checkRequiredExtensions($manifest, $result);
        $this->checkDatabase($result);
        $this->checkWritablePaths($result);
        $this->checkUpdatePackageDirectory($package, $result);
        $this->checkInstallerStatus($result);
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

    private function checkCurrentVersion(array $manifest, UpdatePreflightResult $result): void
    {
        $current = $this->versions->currentVersion();
        $target = (string) (data_get($manifest, 'target_version') ?: data_get($manifest, 'to_version') ?: data_get($manifest, 'version', 'unknown'));

        $result->add(
            'current_version',
            'Current application version',
            'info',
            'info',
            'Current and target versions are recorded for manual update planning.',
            false,
            [
                'current_version' => $current,
                'target_version' => $target,
            ],
        );
    }

    private function checkCompatibility(array $manifest, UpdatePreflightResult $result): void
    {
        $compatibility = $this->manifests->compatibility($manifest, $this->versions->currentVersion());
        $errors = (array) ($compatibility['errors'] ?? []);
        $warnings = (array) ($compatibility['warnings'] ?? []);

        if ($errors !== []) {
            $result->add(
                'compatibility',
                'Compatibility review',
                'fail',
                'error',
                implode(' ', $errors),
                true,
                $this->compatibilityContext($compatibility),
            );

            return;
        }

        if ($warnings !== []) {
            $result->add(
                'compatibility',
                'Compatibility review',
                'warning',
                'warning',
                implode(' ', $warnings),
                false,
                $this->compatibilityContext($compatibility),
            );

            return;
        }

        $result->add(
            'compatibility',
            'Compatibility review',
            'pass',
            'info',
            'Package compatibility metadata matches this installation.',
            false,
            $this->compatibilityContext($compatibility),
        );
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
            $result->add('entitlement', 'Update feature access', 'fail', 'error', $decision['message'], true, $decision);

            return;
        }

        $result->add('entitlement', 'Update feature access', 'pass', 'info', $decision['message'], false, $decision);
    }

    private function checkLicenseStatus(UpdatePreflightResult $result): void
    {
        $school = $this->entitlements->defaultSchool();
        $status = $this->licenses->status($school);
        $ready = in_array($status, ['valid', 'offline_grace', 'validation_disabled', 'subscription_platform'], true);

        $result->add(
            'license_status',
            'License status',
            $ready ? ($status === 'offline_grace' ? 'warning' : 'pass') : 'fail',
            $ready ? ($status === 'offline_grace' ? 'warning' : 'info') : 'error',
            $ready ? 'License status allows guided update review.' : 'License status must be resolved before update readiness can pass.',
            ! $ready,
            [
                'status' => str($status)->replace('_', ' ')->title()->toString(),
                'requires_validation' => $this->licenses->requiresValidation(),
            ],
        );
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

    private function checkRequiredExtensions(array $manifest, UpdatePreflightResult $result): void
    {
        $required = collect((array) data_get($manifest, 'required_extensions', []))
            ->map(fn (mixed $extension): string => str((string) $extension)->trim()->lower()->replace(['ext-', 'php-', ' '], ['', '', '_'])->toString())
            ->filter()
            ->unique()
            ->values();

        if ($required->isEmpty()) {
            $result->add('required_extensions', 'Required PHP extensions', 'info', 'info', 'No additional PHP extensions are declared by the manifest.');

            return;
        }

        $missing = $required
            ->reject(fn (string $extension): bool => extension_loaded($extension))
            ->values();

        $result->add(
            'required_extensions',
            'Required PHP extensions',
            $missing->isEmpty() ? 'pass' : 'fail',
            $missing->isEmpty() ? 'info' : 'error',
            $missing->isEmpty()
                ? 'Required PHP extensions are loaded.'
                : 'Required PHP extensions are missing: '.$missing->implode(', ').'.',
            $missing->isNotEmpty(),
            [
                'required_extensions' => $required->all(),
                'missing_extensions' => $missing->all(),
            ],
        );
    }

    private function checkDatabase(UpdatePreflightResult $result): void
    {
        $connection = (string) config('database.default', 'unknown');

        try {
            DB::connection()->getPdo()->query('select 1');
            $migrationsTableReady = Schema::hasTable('migrations');

            $result->add(
                'database_status',
                'Database and migration repository',
                $migrationsTableReady ? 'pass' : 'warning',
                $migrationsTableReady ? 'info' : 'warning',
                $migrationsTableReady
                    ? 'Database connection and migration repository are available.'
                    : 'Database is reachable, but the migration repository table was not found.',
                false,
                [
                    'connection' => $connection,
                    'migrations_table' => $migrationsTableReady ? 'Present' : 'Missing',
                ],
            );
        } catch (Throwable) {
            $result->add(
                'database_status',
                'Database and migration repository',
                'fail',
                'error',
                'Database connection failed; resolve database readiness before update planning.',
                true,
                ['connection' => $connection],
            );
        }
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

    private function checkUpdatePackageDirectory(UpdatePackage $package, UpdatePreflightResult $result): void
    {
        $disk = (string) config('updates.package_disk', 'updates');
        $directory = trim((string) config('updates.package_directory', 'packages'), '/');
        $packageRecorded = filled($package->path);

        try {
            $storage = Storage::disk($disk);
            $packagePresent = $packageRecorded && $storage->exists((string) $package->path);
            $directoryReady = method_exists($storage, 'directoryExists')
                ? $storage->directoryExists($directory)
                : true;

            $status = $packageRecorded ? ($packagePresent ? 'pass' : 'warning') : 'warning';
            $message = $packageRecorded
                ? ($packagePresent
                    ? 'Private update package metadata points to an available stored package.'
                    : 'Private update package metadata is recorded, but the package file was not found on the configured disk.')
                : 'Update package storage path metadata is missing.';

            $result->add(
                'update_package_directory',
                'Update package directory',
                $status,
                $status === 'pass' ? 'info' : 'warning',
                $message,
                false,
                [
                    'disk' => $disk,
                    'directory' => $directory,
                    'directory_ready' => $directoryReady,
                    'package_path_recorded' => $packageRecorded,
                    'package_file_present' => $packagePresent,
                    'private_package_path_hidden' => true,
                ],
            );
        } catch (Throwable) {
            $result->add(
                'update_package_directory',
                'Update package directory',
                'fail',
                'error',
                'Update package storage disk is not available.',
                true,
                [
                    'disk' => $disk,
                    'directory' => $directory,
                ],
            );
        }
    }

    private function checkInstallerStatus(UpdatePreflightResult $result): void
    {
        $installed = $this->installer->isInstalled();

        $result->add(
            'installer_status',
            'Installer status',
            $installed ? 'pass' : 'warning',
            $installed ? 'info' : 'warning',
            $installed
                ? 'Installer lock or installed configuration is present.'
                : 'Complete and lock installation before update readiness can pass.',
            ! $installed,
            [
                'installed' => $installed,
                'installer_access_open' => $this->installer->canAccessInstaller(),
            ],
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
                : 'A recent verified backup is required before update readiness can pass. Create and verify a backup before update planning continues.',
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
            [
                'database_change_count' => count((array) data_get($manifest, 'database_changes', [])),
                'manual_review_required' => true,
                'migration_notes_available' => filled(data_get($manifest, 'migration_notes')),
            ],
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

    private function compatibilityContext(array $compatibility): array
    {
        return [
            'status' => $compatibility['status'] ?? 'review',
            'current_version' => $compatibility['current_version'] ?? null,
            'target_version' => $compatibility['target_version'] ?? null,
            'target_product' => $compatibility['target_product'] ?? null,
            'target_edition' => $compatibility['target_edition'] ?? null,
            'deployment_modes' => $compatibility['deployment_modes'] ?? [],
            'requirements' => $compatibility['requirements'] ?? [],
        ];
    }
}
