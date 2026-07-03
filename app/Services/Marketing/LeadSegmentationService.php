<?php

namespace App\Services\Marketing;

use App\Models\LeadRequest;
use App\Models\School;
use App\Services\System\DeploymentModeService;

class LeadSegmentationService
{
    public function __construct(private DeploymentModeService $deploymentMode) {}

    public function forLead(LeadRequest $lead): array
    {
        return array_filter([
            'source' => $lead->source ?: 'unknown',
            'interest' => $this->interest($lead),
            'deployment_mode' => data_get($lead->metadata, 'deployment_mode', $this->deploymentMode->mode()),
            ...((bool) config('sanfaani.license_validation_enabled', false)
                ? ['license_mode' => data_get($lead->metadata, 'license_mode', $this->deploymentMode->licenseMode())]
                : []),
            'lead_status' => $lead->status,
            'demo_status' => $lead->type === 'demo' ? 'requested' : null,
            'trial_status' => $lead->status === LeadRequest::STATUS_TRIAL_STARTED ? 'started' : null,
            'onboarding_progress' => $this->onboardingProgress($lead->convertedSchool),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function interest(LeadRequest $lead): string
    {
        $text = strtolower(implode(' ', array_filter([
            $lead->type,
            $lead->role,
            $lead->source,
            $lead->message,
            json_encode($lead->tags ?? []),
        ])));

        return match (true) {
            str_contains($text, 'white') => 'white_label',
            str_contains($text, 'managed') => 'managed',
            str_contains($text, 'marketplace') => 'marketplace',
            str_contains($text, 'trial') => 'trial',
            str_contains($text, 'demo') => 'demo',
            default => 'general',
        };
    }

    private function onboardingProgress(?School $school): ?string
    {
        if (! $school) {
            return null;
        }

        $completed = $school->userOnboardingProgress()
            ->where('status', 'completed')
            ->count();

        return $completed > 0 ? 'started' : 'not_started';
    }
}
