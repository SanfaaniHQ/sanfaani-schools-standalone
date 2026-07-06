<?php

namespace App\Services\Standalone;

use App\Models\Backup;
use App\Models\School;
use App\Models\UpdatePackage;
use App\Services\Backups\BackupService;
use App\Services\Installer\InstallerStateService;
use App\Services\Licensing\LicenseValidationService;
use App\Services\Security\SecretRedactionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class StandaloneSystemHealthService
{
    public function __construct(
        private StandaloneEditionService $edition,
        private StandaloneSyncService $sync,
        private StandaloneSchedulerHeartbeatService $scheduler,
        private BackupService $backups,
        private LicenseValidationService $licenses,
        private InstallerStateService $installer,
        private SecretRedactionService $redactor,
    ) {}

    public function summary(?School $school = null): array
    {
        $school ??= $this->defaultSchool();

        $sections = [
            'runtime' => [
                'label' => 'Runtime and app',
                'checks' => [
                    $this->phpVersionCheck(),
                    $this->laravelVersionCheck(),
                    $this->databaseCheck(),
                    $this->appUrlCheck(),
                    $this->environmentCheck(),
                    $this->debugModeCheck(),
                ],
            ],
            'storage' => [
                'label' => 'Storage, disk, and assets',
                'checks' => [
                    ...$this->storageWritableChecks(),
                    $this->cacheWorkingCheck(),
                    $this->diskFreeCheck(),
                    $this->uploadMaxFilesizeCheck(),
                    $this->postMaxSizeCheck(),
                    $this->publicBuildCheck(),
                ],
            ],
            'operations' => [
                'label' => 'Scheduler, queue, and mail',
                'checks' => [
                    $this->schedulerCheck(),
                    $this->queueCheck(),
                    $this->mailCheck(),
                ],
            ],
            'standalone' => [
                'label' => 'Standalone readiness',
                'checks' => [
                    $this->installerCheck(),
                    ...((bool) config('sanfaani.license_validation_enabled', false)
                        ? [$this->licenseCheck($school)]
                        : []),
                    $this->backupCheck($school),
                    $this->updateReadinessCheck($school),
                    $this->syncCheck(),
                    $this->offlineModeCheck(),
                    $this->safeOutputCheck(),
                ],
            ],
        ];

        $checks = collect($sections)
            ->flatMap(fn (array $section): array => $section['checks'])
            ->values();

        $failures = $checks->where('status', 'fail')->count();
        $warnings = $checks->where('status', 'warning')->count();

        $overallStatus = match (true) {
            $failures > 0 => 'fail',
            $warnings > 0 => 'warning',
            default => 'pass',
        };

        return [
            'generated_at' => now()->toIso8601String(),
            'overall' => [
                'status' => $overallStatus,
                'label' => $overallStatus === 'pass' ? 'Healthy' : ($overallStatus === 'fail' ? 'Action needed' : 'Review needed'),
                'message' => $overallStatus === 'pass'
                    ? 'No blocking standalone health issues were detected.'
                    : "{$failures} blocking issue(s), {$warnings} warning(s) detected.",
                'tone' => $this->tone($overallStatus),
            ],
            'summary' => [
                'pass' => $checks->where('status', 'pass')->count(),
                'warning' => $warnings,
                'fail' => $failures,
                'info' => $checks->where('status', 'info')->count(),
                'total' => $checks->count(),
            ],
            'warnings' => $checks
                ->filter(fn (array $check): bool => in_array($check['status'], ['warning', 'fail'], true))
                ->map(fn (array $check): string => $check['label'].': '.$check['message'])
                ->values()
                ->all(),
            'sections' => $sections,
            'cards' => $this->cards($sections),
        ];
    }

    private function phpVersionCheck(): array
    {
        $minimum = (string) config('installer.requirements.php_minimum', config('updates.php_minimum', '8.2.0'));
        $passes = version_compare(PHP_VERSION, $minimum, '>=');

        return $this->check(
            'php_version',
            'PHP version',
            $passes ? 'pass' : 'fail',
            $passes ? 'PHP '.PHP_VERSION." meets the {$minimum}+ requirement." : "PHP {$minimum} or newer is required.",
            ['current' => PHP_VERSION, 'minimum' => $minimum],
        );
    }

    private function laravelVersionCheck(): array
    {
        return $this->check(
            'laravel_version',
            'Laravel version',
            'info',
            'Laravel runtime version is available for support diagnostics.',
            ['version' => app()->version()],
        );
    }

    private function databaseCheck(): array
    {
        $connection = (string) config('database.default', 'unknown');

        try {
            $pdo = DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $pdo->query('select 1');

            return $this->check(
                'database_connection',
                'Database connection',
                'pass',
                'Laravel can connect to the configured database.',
                ['connection' => $connection, 'driver' => $driver],
            );
        } catch (Throwable) {
            return $this->check(
                'database_connection',
                'Database connection',
                'fail',
                'Laravel could not connect to the configured database.',
                ['connection' => $connection],
            );
        }
    }

    private function appUrlCheck(): array
    {
        $url = trim((string) config('app.url', ''));
        $scheme = $url !== '' ? parse_url($url, PHP_URL_SCHEME) : null;
        $host = $url !== '' ? parse_url($url, PHP_URL_HOST) : null;
        $production = app()->environment('production');

        if ($url === '' || ! $host) {
            return $this->check('app_url', 'App URL and SSL', 'fail', 'APP_URL is missing or invalid.', [
                'configured' => false,
            ]);
        }

        if ($production && $scheme !== 'https') {
            return $this->check('app_url', 'App URL and SSL', 'warning', 'Production APP_URL should use HTTPS.', [
                'scheme' => $scheme,
                'host' => $host,
            ]);
        }

        if ($production && in_array($host, ['localhost', '127.0.0.1'], true)) {
            return $this->check('app_url', 'App URL and SSL', 'warning', 'Production APP_URL should use the school portal domain, not localhost.', [
                'scheme' => $scheme,
                'host' => $host,
            ]);
        }

        return $this->check('app_url', 'App URL and SSL', $scheme === 'https' ? 'pass' : 'info', 'APP_URL is configured.', [
            'scheme' => $scheme,
            'host' => $host,
        ]);
    }

    private function environmentCheck(): array
    {
        $appKeyConfigured = filled(config('app.key'));

        return $this->check(
            'environment_safety',
            'Environment safety',
            $appKeyConfigured ? 'pass' : 'fail',
            $appKeyConfigured ? 'Application key is configured.' : 'APP_KEY is missing and must be generated before production use.',
            ['app_env' => app()->environment(), 'configured' => $appKeyConfigured],
        );
    }

    private function debugModeCheck(): array
    {
        $debug = (bool) config('app.debug', false);

        return $this->check(
            'debug_mode',
            'Support/debug mode',
            $debug ? 'warning' : 'pass',
            $debug ? 'APP_DEBUG is enabled; disable it before handing the portal to a school.' : 'APP_DEBUG is disabled.',
            ['debug_enabled' => $debug],
        );
    }

    private function storageWritableChecks(): array
    {
        return collect((array) config('standalone.health.writable_paths', []))
            ->map(fn (string $path): array => $this->writablePathCheck($path))
            ->values()
            ->all();
    }

    private function writablePathCheck(string $relativePath): array
    {
        $absolutePath = base_path($relativePath);
        $exists = File::isDirectory($absolutePath);
        $writable = $exists && File::isWritable($absolutePath);

        return $this->check(
            'writable_'.str($relativePath)->replace(['/', '\\'], '_')->toString(),
            str($relativePath)->replace(['/', '\\'], ' / ')->title()->toString().' writable',
            $writable ? 'pass' : 'fail',
            $writable ? "{$relativePath} is writable." : "{$relativePath} must be writable by PHP.",
            ['path' => $relativePath, 'exists' => $exists],
        );
    }

    private function cacheWorkingCheck(): array
    {
        $key = 'standalone_health_cache_probe';
        $value = 'ok-'.Str::random(8);

        try {
            Cache::put($key, $value, now()->addMinutes(5));
            $working = Cache::get($key) === $value;
            Cache::forget($key);
        } catch (Throwable) {
            $working = false;
        }

        return $this->check(
            'cache_working',
            'Cache writable',
            $working ? 'pass' : 'warning',
            $working ? 'Cache store accepted a short health probe.' : 'Cache store did not accept a short health probe.',
            ['store' => (string) config('cache.default', 'unknown')],
        );
    }

    private function diskFreeCheck(): array
    {
        $path = storage_path();
        $freeBytes = @disk_free_space($path);
        $totalBytes = @disk_total_space($path);
        $warningBytes = max(1, (int) config('standalone.health.disk_free_warning_mb', 1024)) * 1024 * 1024;

        if ($freeBytes === false) {
            return $this->check('disk_free_space', 'Disk free space', 'warning', 'Disk free space could not be measured.', [
                'path' => 'storage',
            ]);
        }

        $status = $freeBytes < $warningBytes ? 'warning' : 'pass';

        return $this->check(
            'disk_free_space',
            'Disk free space',
            $status,
            $status === 'pass' ? 'Disk free space is above the configured warning threshold.' : 'Disk free space is below the configured warning threshold.',
            [
                'path' => 'storage',
                'free' => $this->formatBytes((int) $freeBytes),
                'total' => $totalBytes === false ? 'Unknown' : $this->formatBytes((int) $totalBytes),
                'warning_threshold' => $this->formatBytes($warningBytes),
            ],
        );
    }

    private function uploadMaxFilesizeCheck(): array
    {
        $value = (string) ini_get('upload_max_filesize');
        $bytes = $this->iniBytes($value);
        $warningMb = (int) config('performance.shared_hosting_limits.upload_size_warning_mb', 64);
        $status = $bytes > 0 && $bytes < ($warningMb * 1024 * 1024) ? 'warning' : 'info';

        return $this->check(
            'upload_max_filesize',
            'Upload limit',
            $status,
            $status === 'warning' ? "upload_max_filesize is below {$warningMb} MB." : 'upload_max_filesize is recorded for package and document upload planning.',
            ['upload_max_filesize' => $value],
        );
    }

    private function postMaxSizeCheck(): array
    {
        $value = (string) ini_get('post_max_size');
        $bytes = $this->iniBytes($value);
        $warningMb = (int) config('performance.shared_hosting_limits.post_size_warning_mb', 64);
        $status = $bytes > 0 && $bytes < ($warningMb * 1024 * 1024) ? 'warning' : 'info';

        return $this->check(
            'post_max_size',
            'Post size limit',
            $status,
            $status === 'warning' ? "post_max_size is below {$warningMb} MB." : 'post_max_size is recorded for upload planning.',
            ['post_max_size' => $value],
        );
    }

    private function publicBuildCheck(): array
    {
        $buildExists = File::isDirectory(public_path('build'));
        $manifestExists = File::exists(public_path('build/manifest.json'));
        $buildZipExists = File::exists(public_path('build.zip'));

        return $this->check(
            'public_build_assets',
            'Public build assets',
            $buildExists ? 'pass' : 'warning',
            $buildExists ? 'public/build exists for Vite assets.' : 'public/build is missing; run the frontend build during deployment if assets are not already published.',
            [
                'path' => 'public/build',
                'manifest_present' => $manifestExists,
                'build_zip_present' => $buildZipExists,
            ],
        );
    }

    private function schedulerCheck(): array
    {
        $status = $this->scheduler->status();
        $checkStatus = match ($status['status']) {
            'healthy' => 'pass',
            'disabled' => 'info',
            default => 'warning',
        };

        return $this->check(
            'scheduler_heartbeat',
            'Scheduler/cron heartbeat',
            $checkStatus,
            $status['message'],
            [
                'heartbeat_status' => $status['status'],
                'last_heartbeat_at' => $status['last_heartbeat_at'] ? $this->ageLabel($status['last_heartbeat_at']) : 'Never',
                'stale_after_minutes' => $status['stale_after_minutes'],
            ],
        );
    }

    private function queueCheck(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $driver = (string) config("queue.connections.{$connection}.driver", $connection);
        $table = (string) config('queue.failed.table', 'failed_jobs');
        $jobsTable = (string) config("queue.connections.{$connection}.table", 'jobs');
        $failedJobsTableExists = $this->tableExists($table);
        $failedJobsCount = $failedJobsTableExists ? $this->failedJobsCount($table) : null;
        $production = app()->environment('production');

        if ($driver === 'sync' && $production) {
            $status = 'warning';
            $message = 'Sync queue is configured in production. This is acceptable only for very small shared-hosting installs; use database queue plus cron when jobs grow.';
        } elseif ($driver === 'database' && ! $this->tableExists($jobsTable)) {
            $status = 'fail';
            $message = 'Database queue is selected but the jobs table was not found.';
        } else {
            $status = 'pass';
            $message = "Queue connection [{$connection}] is configured.";
        }

        return $this->check(
            'queue_connection',
            'Queue',
            $status,
            $message,
            [
                'connection' => $connection,
                'driver' => $driver,
                'failed_jobs_table' => $failedJobsTableExists ? 'Present' : 'Missing',
                'failed_jobs_count' => $failedJobsCount ?? 'Unknown',
            ],
        );
    }

    private function mailCheck(): array
    {
        $mailer = (string) config('mail.default', 'log');
        $transport = (string) config("mail.mailers.{$mailer}.transport", $mailer);
        $fromConfigured = filled(config('mail.from.address'));
        $smtpHostConfigured = $mailer !== 'smtp' || filled(config('mail.mailers.smtp.host'));
        $production = app()->environment('production');

        if (! $fromConfigured || ! $smtpHostConfigured) {
            $status = 'warning';
            $message = 'Mail sender or SMTP host configuration is incomplete.';
        } elseif ($production && in_array($transport, ['log', 'array'], true)) {
            $status = 'warning';
            $message = 'Mail is using a non-delivery transport in production.';
        } else {
            $status = 'pass';
            $message = 'Mail configuration has the required safe fields.';
        }

        return $this->check('mail_configuration', 'Mail configuration', $status, $message, [
            'mailer' => $mailer,
            'transport' => $transport,
            'from_configured' => $fromConfigured,
        ]);
    }

    private function installerCheck(): array
    {
        $installed = $this->installer->isInstalled();
        $canAccessInstaller = $this->installer->canAccessInstaller();

        return $this->check(
            'installer_status',
            'Installer status',
            $installed ? 'pass' : 'warning',
            $installed ? 'Installer lock or installed configuration is present.' : 'Installation is not locked yet; complete the installer before handover.',
            [
                'installed' => $installed,
                'installer_access_open' => $canAccessInstaller,
            ],
        );
    }

    private function licenseCheck(?School $school): array
    {
        $status = $this->licenses->status($school);
        $license = $this->licenses->current($school);
        $ready = in_array($status, ['valid', 'offline_grace', 'validation_disabled', 'subscription_platform'], true);

        return $this->check(
            'license_status',
            'License status',
            $ready ? ($status === 'offline_grace' ? 'warning' : 'pass') : 'warning',
            $ready ? 'License status allows owner operations.' : 'License needs owner or Sanfaani support attention.',
            [
                'status' => $this->label($status),
                'license_type' => $license?->license_type ? $this->label($license->license_type) : 'None',
                'days_until_expiry' => $this->licenses->daysUntilExpiry($license) ?? 'Not applicable',
            ],
        );
    }

    private function backupCheck(?School $school): array
    {
        $latest = $this->latestBackup($school);
        $readiness = $this->backups->preUpdateReadiness($school);

        return $this->check(
            'backup_status',
            'Backup status',
            $readiness['ready'] ? 'pass' : 'warning',
            $readiness['message'],
            [
                'latest_status' => $latest ? $this->label($latest->status) : 'No backup recorded',
                'last_backup_age' => $latest?->completed_at ? $latest->completed_at->diffForHumans() : 'Unknown',
                'recent_verified_backup' => (bool) ($readiness['ready'] ?? false),
            ],
        );
    }

    private function updateReadinessCheck(?School $school): array
    {
        $latest = UpdatePackage::query()->latest('id')->first();
        $backupReadiness = $this->backups->preUpdateReadiness($school);
        $backupRequired = (bool) config('updates.backup_required', true);

        if (! (bool) config('updates.enabled', true)) {
            return $this->check('update_readiness', 'Update readiness', 'info', 'Guided updates are disabled by configuration.', [
                'updates_enabled' => false,
            ]);
        }

        if ($backupRequired && ! $backupReadiness['ready']) {
            $status = 'warning';
            $message = 'A recent verified backup is required before update readiness can pass.';
        } elseif ($latest && $latest->status === UpdatePackage::STATUS_PRECHECK_BLOCKED) {
            $status = 'warning';
            $message = 'Latest update package has blocked preflight checks.';
        } else {
            $status = 'pass';
            $message = 'Update readiness checks are safe for manual review.';
        }

        return $this->check('update_readiness', 'Update readiness', $status, $message, [
            'updates_enabled' => true,
            'update_finalization_available' => true,
            'guided_review_only' => true,
            'auto_apply_available' => false,
            'backup_required' => $backupRequired,
            'latest_package_status' => $latest ? $this->label($latest->status) : 'No package',
            'current_version' => (string) config('version.version', 'unknown'),
        ]);
    }

    private function syncCheck(): array
    {
        $status = $this->sync->status();

        if (($status['failed_count'] ?? 0) > 0) {
            $checkStatus = 'warning';
            $message = $status['failed_count'].' sync item(s) need review.';
        } elseif ($status['enabled'] && (! $status['endpoint_configured'] || ! $status['token_configured'])) {
            $checkStatus = 'warning';
            $message = 'Standalone sync is enabled but endpoint or token is missing.';
        } else {
            $checkStatus = 'pass';
            $message = $status['enabled'] ? 'Standalone sync settings are configured.' : 'Standalone sync is optional and currently disabled.';
        }

        return $this->check('standalone_sync', 'Standalone sync/offline', $checkStatus, $message, [
            'sync_enabled' => (bool) $status['enabled'],
            'endpoint_configured' => (bool) $status['endpoint_configured'],
            'token_configured' => (bool) $status['token_configured'],
            'tables_ready' => (bool) $status['tables_ready'],
            'pending_count' => $status['pending_count'] ?? 'Unknown',
            'failed_count' => $status['failed_count'] ?? 'Unknown',
        ]);
    }

    private function offlineModeCheck(): array
    {
        $localFirst = $this->edition->localFirstOfflineEnabled();

        return $this->check(
            'offline_mode',
            'Offline mode accuracy',
            $localFirst ? 'info' : 'warning',
            $localFirst
                ? 'Local-first server operation is enabled; full browser offline/PWA is not claimed as complete.'
                : 'Standalone offline mode is not local_first; review product wording before handover.',
            ['offline_mode' => $this->edition->offlineMode()],
        );
    }

    private function safeOutputCheck(): array
    {
        $redactionEnabled = (bool) config('security.secret_redaction_enabled', true);
        $probe = $this->redactor->redactArray([
            'DB_PASSWORD' => 'secret-password',
            'SANFAANI_STANDALONE_SYNC_TOKEN' => 'secret-token',
            'path' => base_path('storage/app/private/backup.zip'),
        ]);

        $probePassed = ($probe['DB_PASSWORD'] ?? null) === '[redacted]'
            && ($probe['SANFAANI_STANDALONE_SYNC_TOKEN'] ?? null) === '[redacted]'
            && ! str_contains((string) ($probe['path'] ?? ''), base_path());

        return $this->check(
            'safe_health_output',
            'Safe health output',
            $redactionEnabled && $probePassed ? 'pass' : 'warning',
            $redactionEnabled && $probePassed
                ? 'Health output is limited to safe summaries; secrets and private paths stay hidden.'
                : 'Secret redaction should be enabled before exposing diagnostics to support.',
            [
                'database_password' => 'Hidden',
                'sync_token' => 'Hidden',
                'api_keys' => 'Hidden',
                'private_backup_paths' => 'Hidden',
            ],
        );
    }

    private function latestBackup(?School $school): ?Backup
    {
        return Backup::query()
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->latest('id')
            ->first();
    }

    private function cards(array $sections): array
    {
        $checks = collect($sections)->flatMap(fn (array $section): array => $section['checks']);

        return [
            $this->card('Server', $checks->firstWhere('key', 'database_connection')),
            $this->card('Storage', $checks->firstWhere('key', 'disk_free_space')),
            $this->card('Scheduler', $checks->firstWhere('key', 'scheduler_heartbeat')),
            $this->card('Backups', $checks->firstWhere('key', 'backup_status')),
            $this->card('Updates', $checks->firstWhere('key', 'update_readiness')),
            ...((bool) config('sanfaani.license_validation_enabled', false)
                ? [$this->card('License', $checks->firstWhere('key', 'license_status'))]
                : []),
        ];
    }

    private function card(string $label, ?array $check): array
    {
        return [
            'label' => $label,
            'value' => $check ? $this->label($check['status']) : 'Unknown',
            'meta' => $check['message'] ?? 'No check returned.',
            'tone' => $this->tone($check['status'] ?? 'info'),
        ];
    }

    private function defaultSchool(): ?School
    {
        return School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first()
            ?? School::query()->orderBy('id')->first();
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function failedJobsCount(string $table): ?int
    {
        try {
            return DB::table($table)->count();
        } catch (Throwable) {
            return null;
        }
    }

    private function check(string $key, string $label, string $status, string $message, array $context = []): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'tone' => $this->tone($status),
            'message' => $this->redactor->redact($message) ?? '',
            'context' => $this->safeContext($context),
        ];
    }

    private function safeContext(array $context): array
    {
        $safe = [];

        foreach ($context as $key => $value) {
            $key = (string) $key;

            if (is_array($value)) {
                $safe[$key] = $this->safeContext($value);

                continue;
            }

            if (is_string($value)) {
                $safe[$key] = $this->redactor->isSensitiveKey($key)
                    ? '[redacted]'
                    : ($this->redactor->redact($value) ?? '');

                continue;
            }

            $safe[$key] = $value;
        }

        return $safe;
    }

    private function tone(string $status): string
    {
        return match ($status) {
            'pass' => 'success',
            'warning' => 'warning',
            'fail' => 'danger',
            default => 'info',
        };
    }

    private function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }

    private function ageLabel(string $isoDate): string
    {
        try {
            return Carbon::parse($isoDate)->diffForHumans();
        } catch (Throwable) {
            return 'Unknown';
        }
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') {
                return round($value, 1).' '.$unit;
            }

            $value /= 1024;
        }

        return $bytes.' B';
    }
}
