<?php

namespace App\Jobs\Backups;

use App\Models\Backup;
use App\Models\School;
use App\Models\User;
use App\Services\Backups\BackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public ?int $actorId = null,
        public ?int $schoolId = null,
        public string $type = Backup::TYPE_MANUAL,
        public string $trigger = 'manual',
    ) {
        $this->onQueue('backups');
    }

    public function handle(BackupService $backups): void
    {
        $backups->createManualBackup(
            $this->actorId ? User::find($this->actorId) : null,
            $this->schoolId ? School::find($this->schoolId) : null,
            $this->type,
            $this->trigger,
        );
    }
}
