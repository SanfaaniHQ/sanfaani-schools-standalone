<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class GenerateDatabaseBackup extends Command
{
    protected $signature = 'backup:database {--keep= : Number of newest backups to retain}';

    protected $description = 'Generate a chunked SQL database backup and apply retention cleanup.';

    public function handle(DatabaseBackupService $backups): int
    {
        $keep = $this->option('keep');
        $backup = $backups->create($keep !== null ? (int) $keep : null);

        $this->info('Backup created: '.$backup['file_name'].' ('.$backup['size_for_humans'].')');

        return self::SUCCESS;
    }
}
