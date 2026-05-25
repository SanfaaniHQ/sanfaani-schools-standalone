<?php

namespace App\Services\Demo;

use App\Models\DemoRequest;
use App\Models\LeadRequest;
use App\Services\LeadCrmService;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DemoRequestService
{
    public function create(array $data): DemoRequest
    {
        $request = DemoRequest::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'role_interest' => $data['role_interest'] ?? null,
            'source' => $data['source'] ?? 'public_demo',
            'status' => DemoRequest::STATUS_REQUESTED,
            'message' => $data['message'] ?? null,
            'metadata' => array_filter($data['metadata'] ?? []),
        ]);

        $this->syncLead($request);

        return $request;
    }

    public function syncLead(DemoRequest $demoRequest): ?LeadRequest
    {
        if (! Schema::hasTable('lead_requests')) {
            return null;
        }

        $lead = LeadRequest::updateOrCreate(
            [
                'type' => 'demo',
                'email' => $demoRequest->email,
            ],
            [
                'name' => $demoRequest->name,
                'school_name' => $demoRequest->school_name,
                'phone' => $demoRequest->phone,
                'role' => $demoRequest->role_interest,
                'message' => $demoRequest->message,
                'source' => $demoRequest->source ?: 'demo_automation',
                'status' => LeadRequest::STATUS_DEMO_SCHEDULED,
                'last_activity_at' => now(),
                'metadata' => array_filter([
                    'demo_request_id' => $demoRequest->id,
                    'demo_foundation' => true,
                ]),
            ]
        );

        try {
            app(LeadCrmService::class)->recordSystemEvent(
                $lead,
                'demo.requested',
                'Demo requested',
                $demoRequest->school_name ?: $demoRequest->name,
                ['demo_request_id' => $demoRequest->id]
            );
        } catch (Throwable) {
            //
        }

        return $lead;
    }
}
