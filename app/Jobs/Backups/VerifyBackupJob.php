<?php

namespace App\Jobs\Backups;

use App\Models\Backup;
use App\Models\User;
use App\Services\Backups\BackupVerificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class VerifyBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $backupId,
        public ?int $actorId = null,
    ) {
        $this->onQueue('backups');
    }

    public function handle(BackupVerificationService $verification): void
    {
        $backup = Backup::find($this->backupId);

        if (! $backup) {
            return;
        }

        $verification->verify($backup, $this->actorId ? User::find($this->actorId) : null);
    }
}
