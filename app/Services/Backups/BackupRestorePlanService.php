<?php

namespace App\Services\Backups;

use App\Models\Backup;
use App\Models\BackupRestorePlan;
use App\Models\User;

class BackupRestorePlanService
{
    public function __construct(
        private BackupLogService $logs,
    ) {}

    public function createForBackup(Backup $backup, ?User $actor = null): BackupRestorePlan
    {
        $plan = BackupRestorePlan::updateOrCreate(
            ['backup_id' => $backup->id],
            [
                'status' => BackupRestorePlan::STATUS_REVIEW_REQUIRED,
                'restore_scope' => $backup->type,
                'steps' => $this->steps(),
                'warnings' => $this->warnings(),
                'verified_at' => null,
                'metadata' => [
                    'restore_performed' => false,
                    'manual_only' => true,
                    'destructive_operations_run' => false,
                    'shared_hosting_safe' => true,
                ],
            ]
        );

        $this->logs->log(
            'backup.restore_plan_created',
            'Manual restore plan metadata was created. No restore was performed.',
            $backup,
            severity: 'info',
            actor: $actor,
        );

        return $plan;
    }

    private function steps(): array
    {
        return [
            [
                'label' => 'Confirm target environment',
                'status' => 'planned',
                'body' => 'Review PHP, Laravel, database, and storage settings before any manual restore.',
            ],
            [
                'label' => 'Download hosting backups manually',
                'status' => 'planned',
                'body' => 'Use cPanel Backup Wizard, Namecheap tools, or phpMyAdmin outside the application.',
            ],
            [
                'label' => 'Restore files outside the browser',
                'status' => 'planned',
                'body' => 'Restore uploaded files manually and avoid overwriting vendor, cache, sessions, logs, and environment secrets.',
            ],
            [
                'label' => 'Verify before reopening',
                'status' => 'planned',
                'body' => 'Run smoke checks and review logs before taking the school out of maintenance mode.',
            ],
        ];
    }

    private function warnings(): array
    {
        return [
            'This wizard does not execute restore operations.',
            'Never paste .env secrets or database passwords into the backup UI.',
            'Run migrations manually only after reviewing release notes and database changes.',
        ];
    }
}
