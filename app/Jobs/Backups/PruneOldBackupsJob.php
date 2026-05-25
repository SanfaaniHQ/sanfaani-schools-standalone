<?php

namespace App\Jobs\Backups;

use App\Models\User;
use App\Services\Backups\BackupRetentionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PruneOldBackupsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public ?int $actorId = null,
    ) {
        $this->onQueue('backups');
    }

    public function handle(BackupRetentionService $retention): void
    {
        $retention->pruneExpired($this->actorId ? User::find($this->actorId) : null);
    }
}
