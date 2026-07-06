<?php

namespace App\Services\Marketing;

use App\Models\DemoRequest;
use App\Models\LeadRequest;
use App\Models\License;
use App\Models\SalesTask;
use App\Models\User;

class SalesTaskService
{
    public function create(array $data): SalesTask
    {
        return SalesTask::create(array_merge([
            'status' => SalesTask::STATUS_OPEN,
            'priority' => 'normal',
            'due_at' => now()->addDay(),
            'metadata' => [],
        ], $data));
    }

    public function createForDemoRequest(DemoRequest $demoRequest, ?LeadRequest $lead = null): ?SalesTask
    {
        if (! (bool) config('marketing.sales_tasks_enabled', true)) {
            return null;
        }

        return $this->create([
            'lead_request_id' => $lead?->id,
            'demo_request_id' => $demoRequest->id,
            'title' => 'Follow up demo request',
            'description' => trim(($demoRequest->school_name ?: $demoRequest->name).' requested a demo.'),
            'priority' => 'high',
            'due_at' => now()->addHours(4),
            'metadata' => [
                'source' => $demoRequest->source,
                'role_interest' => $demoRequest->role_interest,
            ],
        ]);
    }

    public function createRenewalReminder(License $license): ?SalesTask
    {
        if (! (bool) config('sanfaani.license_validation_enabled', false)
            || ! (bool) config('marketing.sales_tasks_enabled', true)) {
            return null;
        }

        return $this->create([
            'school_id' => $license->school_id,
            'title' => 'License renewal reminder',
            'description' => 'Review renewal for '.($license->issued_to_name ?: 'licensed school'),
            'priority' => 'high',
            'due_at' => $license->expires_at?->copy()->subDays(7) ?? now()->addDay(),
            'metadata' => [
                'source_event' => 'license.expiring',
                'license_id' => $license->id,
                'license_type' => $license->license_type,
                'expires_at' => $license->expires_at?->toDateString(),
            ],
        ]);
    }

    public function complete(SalesTask $task, User $actor): SalesTask
    {
        $task->forceFill([
            'status' => SalesTask::STATUS_COMPLETED,
            'completed_at' => now(),
            'metadata' => array_merge($task->metadata ?? [], [
                'completed_by' => $actor->id,
            ]),
        ])->save();

        return $task;
    }
}
