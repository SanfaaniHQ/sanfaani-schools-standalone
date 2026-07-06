<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admissions\AdmissionApplicationService;
use App\Services\Admissions\AdmissionWebsiteIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicAdmissionController extends Controller
{
    public function __construct(
        private readonly AdmissionApplicationService $applications,
        private readonly AdmissionWebsiteIntegrationService $integration
    ) {}

    public function config(Request $request): JsonResponse
    {
        $this->ensureEnabled();
        $apiKey = $this->integration->authenticateApiRequest($request);
        $cycle = $this->integration->currentCycle($apiKey->school);

        return response()->json($this->integration->publicConfig($apiKey->school, $cycle));
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureEnabled();
        $apiKey = $this->integration->authenticateApiRequest($request);
        $validated = $request->validate($this->applications->validationRules($apiKey->school));
        $validated['source_channel'] = $apiKey->channel?->name ?: 'api';
        $result = $this->applications->submit($apiKey->school, $validated, 'api');

        return response()->json([
            'message' => 'Admission application submitted.',
            'application_number' => $result['application']->application_number,
            'tracking_token' => $result['tracking_token'],
            'next_step' => 'Keep the application number and tracking token for status checks.',
            'payment_required' => false,
        ], 201);
    }

    private function ensureEnabled(): void
    {
        abort_unless(config('admissions.enabled') && config('admissions.api_enabled'), 404);
    }
}
