<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use Illuminate\Support\Facades\DB;

class BackupDatabaseService
{
    public function __construct(
        private BackupLogService $logs,
    ) {}

    public function capture(Backup $backup): ?BackupItem
    {
        if (! (bool) config('backups.database_enabled', true)) {
            return $this->item($backup, 'Database export disabled', 'disabled', [
                'enabled' => false,
            ]);
        }

        $driver = DB::connection()->getDriverName();

        $item = $this->item($backup, 'Database export metadata', 'warning', [
            'driver' => $driver,
            'manual_export_required' => true,
            'shell_dump_attempted' => false,
            'guidance' => 'Use cPanel Backup Wizard or phpMyAdmin export when shell access or mysqldump is unavailable.',
        ]);

        $this->logs->log(
            'backup.database_manual_export_required',
            'Database backup metadata was recorded. Manual cPanel or phpMyAdmin export is required on shared hosting.',
            $backup,
            severity: 'warning',
            context: ['driver' => $driver, 'shell_dump_attempted' => false],
        );

        return $item;
    }

    private function item(Backup $backup, string $label, string $status, array $metadata): BackupItem
    {
        return BackupItem::create([
            'backup_id' => $backup->id,
            'item_type' => BackupItem::TYPE_DATABASE,
            'source_label' => $label,
            'path' => null,
            'size_bytes' => null,
            'checksum' => null,
            'status' => $status,
            'metadata' => $metadata + [
                'contains_secret' => false,
                'raw_dump_created_by_web' => false,
            ],
        ]);
    }
}
