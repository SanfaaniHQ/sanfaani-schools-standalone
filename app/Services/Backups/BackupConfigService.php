<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use App\Services\System\DeploymentModeService;

class BackupConfigService
{
    public function __construct(
        private DeploymentModeService $deployment,
        private BackupLogService $logs,
    ) {}

    public function capture(Backup $backup): ?BackupItem
    {
        if (! (bool) config('backups.config_enabled', true)) {
            return BackupItem::create([
                'backup_id' => $backup->id,
                'item_type' => BackupItem::TYPE_CONFIG,
                'source_label' => 'Sanitized configuration disabled',
                'path' => null,
                'size_bytes' => null,
                'checksum' => null,
                'status' => 'disabled',
                'metadata' => ['enabled' => false],
            ]);
        }

        $item = BackupItem::create([
            'backup_id' => $backup->id,
            'item_type' => BackupItem::TYPE_CONFIG,
            'source_label' => 'Sanitized configuration metadata',
            'path' => null,
            'size_bytes' => null,
            'checksum' => null,
            'status' => 'recorded',
            'metadata' => $this->sanitizedMetadata(),
        ]);

        $this->logs->log(
            'backup.config_metadata_recorded',
            'Sanitized configuration metadata was recorded. No .env contents or secret values were stored.',
            $backup,
            severity: 'info',
        );

        return $item;
    }

    public function sanitizedMetadata(): array
    {
        return [
            'app' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
                'debug' => (bool) config('app.debug'),
                'url_host' => parse_url((string) config('app.url'), PHP_URL_HOST),
            ],
            'deployment' => [
                'mode' => $this->deployment->mode(),
                'license_mode' => $this->deployment->licenseMode(),
                'brand_mode' => $this->deployment->brandMode(),
            ],
            'runtime' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ],
            'drivers' => [
                'database' => config('database.default'),
                'cache' => config('cache.default'),
                'queue' => config('queue.default'),
                'filesystem' => config('filesystems.default'),
                'session' => config('session.driver'),
            ],
            'features' => [
                'backup_manager' => (bool) config('features.features.backup_manager.enabled', false),
                'managed_backups' => (bool) config('features.features.managed_backups.enabled', false),
                'update_manager' => (bool) config('features.features.update_manager.enabled', false),
            ],
            'sanitized' => true,
            'env_exported' => false,
            'secret_values_exported' => false,
        ];
    }
}
