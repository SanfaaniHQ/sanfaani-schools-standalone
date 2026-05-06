<?php

namespace App\Services;

use App\Models\SchoolRoleFeatureSetting;
use Illuminate\Support\Facades\Cache;

class SchoolRoleFeatureService
{
    /**
     * Check if a feature is enabled for a role in a school.
     * Defaults to true if no setting exists.
     */
    public function enabled(int $schoolId, string $roleName, string $featureKey): bool
    {
        // Super Admin and School Admin bypass all feature checks
        if (in_array($roleName, ['super_admin', 'school_admin'], true)) {
            return true;
        }

        $cacheKey = "school_role_feature:{$schoolId}:{$roleName}:{$featureKey}";

        return Cache::remember($cacheKey, 3600, function () use ($schoolId, $roleName, $featureKey) {
            $setting = SchoolRoleFeatureSetting::where('school_id', $schoolId)
                ->where('role_name', $roleName)
                ->where('feature_key', $featureKey)
                ->first();

            // Default to true if no setting exists (safe default for V1.1 features)
            return $setting ? $setting->is_enabled : true;
        });
    }

    /**
     * Set a feature's enabled status for a role in a school.
     */
    public function setFeature(int $schoolId, string $roleName, string $featureKey, bool $enabled): void
    {
        SchoolRoleFeatureSetting::updateOrCreate(
            [
                'school_id' => $schoolId,
                'role_name' => $roleName,
                'feature_key' => $featureKey,
            ],
            [
                'is_enabled' => $enabled,
            ]
        );

        // Clear cache
        $cacheKey = "school_role_feature:{$schoolId}:{$roleName}:{$featureKey}";
        Cache::forget($cacheKey);
    }

    /**
     * Get all feature settings for a role in a school.
     */
    public function getFeatures(int $schoolId, string $roleName): array
    {
        $settings = SchoolRoleFeatureSetting::where('school_id', $schoolId)
            ->where('role_name', $roleName)
            ->get()
            ->keyBy('feature_key');

        $features = $this->getAvailableFeatures($roleName);
        $result = [];

        foreach ($features as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'enabled' => $settings->has($key) ? $settings[$key]->is_enabled : true,
            ];
        }

        return $result;
    }

    /**
     * Get available features for a role.
     */
    public function getAvailableFeatures(string $roleName): array
    {
        $features = [
            'result_officer' => [
                'students.view' => 'View Students',
                'results.manual_entry' => 'Manual Result Entry',
                'results.upload' => 'Result Upload',
                'results.review' => 'Result Review',
                'results.publish' => 'Result Publishing',
                'subjects.view' => 'View Subjects',
                'classes.view' => 'View Classes',
                'support.access' => 'Support Access',
            ],
            'teacher' => [
                'teacher.assignments.view' => 'View Assignments',
                'teacher.results.create' => 'Create Results',
                'teacher.results.submit' => 'Submit Results',
                'students.view_assigned' => 'View Assigned Students',
                'support.access' => 'Support Access',
            ],
        ];

        return $features[$roleName] ?? [];
    }
}
