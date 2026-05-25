<?php

namespace App\Console\Commands;

use App\Services\Backups\BackupRetentionService;
use Illuminate\Console\Command;

class PruneBackupsCommand extends Command
{
    protected $signature = 'backups:prune';

    protected $description = 'Safely mark and prune expired backup metadata according to retention policy.';

    public function handle(BackupRetentionService $retention): int
    {
        $count = $retention->pruneExpired();

        $this->info("Expired backup metadata pruned: {$count}");
        $this->line('No restore operation was run and no public download route was created.');

        return self::SUCCESS;
    }
}
