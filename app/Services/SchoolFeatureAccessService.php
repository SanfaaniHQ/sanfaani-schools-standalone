<?php

namespace App\Services;

use App\Models\PlanFeature;
use App\Models\School;

class SchoolFeatureAccessService
{
    public function canAccess(School $school, string $featureKey): bool
    {
        $override = $this->activeOverride($school, $featureKey);

        if ($override) {
            return (bool) $override->is_enabled;
        }

        $planFeature = $this->activePlanFeature($school, $featureKey);

        if ($planFeature) {
            return (bool) $planFeature->is_enabled;
        }

        if ($featureKey === 'public_result_checker' && $school->status === 'active') {
            return true;
        }

        return false;
    }

    public function getLimit(School $school, string $featureKey): ?int
    {
        $override = $this->activeOverride($school, $featureKey);

        if ($override) {
            return $override->limit_value !== null ? (int) $override->limit_value : null;
        }

        $planFeature = $this->activePlanFeature($school, $featureKey);

        if ($planFeature) {
            return $planFeature->limit_value !== null ? (int) $planFeature->limit_value : null;
        }

        return null;
    }

    private function activeOverride(School $school, string $featureKey)
    {
        return $school->featureOverrides()
            ->where('feature_key', $featureKey)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    private function activePlanFeature(School $school, string $featureKey): ?PlanFeature
    {
        $subscription = $school->subscriptions()
            ->whereIn('status', ['active', 'trial', 'grace'])
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();

        if (! $subscription) {
            return null;
        }

        return PlanFeature::where('subscription_plan_id', $subscription->subscription_plan_id)
            ->where('feature_key', $featureKey)
            ->first();
    }
}
