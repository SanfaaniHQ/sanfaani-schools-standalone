<?php

namespace App\Services\Updates;

use App\Models\UpdatePackage;
use App\Models\UpdateRollbackPlan;
use App\Models\User;

class UpdateRollbackService
{
    public function __construct(
        private SystemVersionService $versions,
        private UpdateLogService $logs,
    ) {}

    public function createForPackage(UpdatePackage $package, ?User $actor = null): UpdateRollbackPlan
    {
        $manifest = $package->manifest ?: [];
        $fromVersion = $this->versions->currentVersion();
        $toVersion = (string) ($package->version ?: data_get($manifest, 'version'));

        $plan = UpdateRollbackPlan::updateOrCreate(
            ['update_package_id' => $package->id],
            [
                'from_version' => $fromVersion,
                'to_version' => $toVersion,
                'status' => UpdateRollbackPlan::STATUS_REVIEW_REQUIRED,
                'backup_reference' => null,
                'steps' => $this->steps((bool) data_get($manifest, 'rollback_supported', false)),
                'verified_at' => null,
                'metadata' => [
                    'rollback_performed' => false,
                    'manual_only' => true,
                    'backup_manager_available' => false,
                    'rollback_supported_by_manifest' => (bool) data_get($manifest, 'rollback_supported', false),
                ],
            ]
        );

        $this->logs->log(
            'update.rollback_plan_created',
            'Rollback plan metadata was created for manual review. No rollback was performed.',
            $package,
            severity: 'info',
            context: ['from_version' => $fromVersion, 'to_version' => $toVersion],
            actor: $actor,
        );

        return $plan;
    }

    private function steps(bool $manifestSupportsRollback): array
    {
        return [
            [
                'label' => 'Confirm backup',
                'status' => 'planned',
                'body' => 'Verify a recent database and file backup outside the public web root before update work starts.',
            ],
            [
                'label' => 'Review changed files',
                'status' => 'planned',
                'body' => 'Compare package metadata with the release notes before copying any files manually.',
            ],
            [
                'label' => 'Prepare maintenance window',
                'status' => 'planned',
                'body' => 'Use Laravel maintenance mode or hosting panel controls during any manual update window.',
            ],
            [
                'label' => 'Manual restore only',
                'status' => $manifestSupportsRollback ? 'planned' : 'review_required',
                'body' => 'Rollback means restoring a verified backup manually; this wizard does not perform rollback actions.',
            ],
        ];
    }
}
