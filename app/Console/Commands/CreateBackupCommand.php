<?php

namespace App\Console\Commands;

use App\Models\Backup;
use App\Models\School;
use App\Services\Backups\BackupService;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'backups:create {--school= : School ID for tenant-scoped backup metadata} {--pre-update : Mark this backup as a pre-update request}';

    protected $description = 'Create safe backup metadata and shared-hosting guidance without running destructive restore operations.';

    public function handle(BackupService $backups): int
    {
        $school = $this->option('school') ? School::find((int) $this->option('school')) : null;

        if ($this->option('school') && ! $school) {
            $this->error('School was not found.');

            return self::FAILURE;
        }

        $backup = $backups->createManualBackup(
            actor: null,
            school: $school,
            type: $this->option('pre-update') ? Backup::TYPE_PRE_UPDATE : Backup::TYPE_MANUAL,
            trigger: $this->option('pre-update') ? 'pre_update' : 'manual_cli',
        );

        $this->info('Backup metadata request completed safely.');
        $this->line('Status: '.$backup->status);
        $this->line('Reference: '.$backup->displayName());

        if ($backup->items()->where('item_type', 'database')->where('status', 'warning')->exists()) {
            $this->warn('Database shell dump was not required. Use cPanel Backup Wizard or phpMyAdmin export when shell access is unavailable.');
        }

        $this->line('No restore operation, full-app archive, or destructive command was executed.');

        return $backup->status === Backup::STATUS_FAILED ? self::FAILURE : self::SUCCESS;
    }
}
