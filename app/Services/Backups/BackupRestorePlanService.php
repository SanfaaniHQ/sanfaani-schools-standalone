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
                    'restore_drill_recommended' => true,
                    'production_restore_requires_support_review' => true,
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
                'label' => 'Create a fresh pre-restore backup',
                'status' => 'required',
                'body' => 'Before touching production, export the current database and uploaded files so the present state can be recovered if the restore is wrong.',
            ],
            [
                'label' => 'Download hosting backups manually',
                'status' => 'planned',
                'body' => 'Use cPanel Backup Wizard, Namecheap tools, or phpMyAdmin outside the application.',
            ],
            [
                'label' => 'Test restore in staging or a local copy first',
                'status' => 'required',
                'body' => 'Never test a restore directly on production first. Use a staging copy, local server, or support-controlled clone.',
            ],
            [
                'label' => 'Contact Sanfaani support for production restore review',
                'status' => 'required',
                'body' => 'Confirm the target database, uploaded-file path, app version, license state, and maintenance window with support before production restore work starts.',
            ],
            [
                'label' => 'Restore files outside the browser',
                'status' => 'planned',
                'body' => 'Restore uploaded files manually and avoid overwriting vendor, cache, sessions, logs, and environment secrets.',
            ],
            [
                'label' => 'Verify application workflows before reopening',
                'status' => 'planned',
                'body' => 'Check login, students, staff, classes, sessions, admissions, results, CBT, branding, report cards, scheduler, queue, mail, and logs before taking the school out of maintenance mode.',
            ],
        ];
    }

    private function warnings(): array
    {
        return [
            'This wizard does not execute restore operations.',
            'No restore operation has been executed automatically.',
            'Do not test restore directly on production first.',
            'Never paste .env secrets or database passwords into the backup UI.',
            'Run migrations manually only after reviewing release notes and database changes.',
            'Contact Sanfaani support before production restore work when data loss is possible.',
        ];
    }
}
