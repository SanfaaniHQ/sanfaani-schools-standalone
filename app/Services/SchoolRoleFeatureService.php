<?php

namespace App\Services;

use App\Models\SchoolRoleFeatureSetting;
use Illuminate\Support\Facades\Cache;

class SchoolRoleFeatureService
{
    private const FEATURE_ALIASES = [
        'support.manage' => ['support.access'],
        'support.access' => ['support.manage'],
    ];

    private const DEFAULT_DISABLED_FEATURES = [
        'support.direct_escalation',
    ];

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
            $settings = $this->settingsForRole($schoolId, $roleName);
            $candidateKeys = $this->candidateFeatureKeys($featureKey);

            foreach ($candidateKeys as $key) {
                if (array_key_exists($key, $settings)) {
                    return (bool) $settings[$key];
                }
            }

            return ! in_array($featureKey, self::DEFAULT_DISABLED_FEATURES, true);
        });
    }

    public function roleSupports(string $roleName, string $featureKey): bool
    {
        $availableKeys = array_keys($this->getAvailableFeatures($roleName));

        return collect($this->candidateFeatureKeys($featureKey))
            ->intersect($availableKeys)
            ->isNotEmpty();
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
        Cache::forget($this->roleSettingsCacheKey($schoolId, $roleName));
    }

    /**
     * Get all feature settings for a role in a school.
     */
    public function getFeatures(int $schoolId, string $roleName): array
    {
        $settings = $this->settingsForRole($schoolId, $roleName);

        $features = $this->getAvailableFeatures($roleName);
        $result = [];

        foreach ($features as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'enabled' => array_key_exists($key, $settings) ? (bool) $settings[$key] : true,
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
                'cbt.mark_theory' => 'CBT Theory Marking',
                'cbt.publish_results' => 'CBT Result Publishing',
                'student.promote' => 'Student Promotion',
                'student.transfer' => 'Student Transfer / Withdrawal',
                'subjects.view' => 'View Subjects',
                'classes.view' => 'View Classes',
                'support.manage' => 'Support Access',
                'support.direct_escalation' => 'Direct Support Escalation',
            ],
            'teacher' => [
                'teacher.assignments.view' => 'View Assignments',
                'teacher.results.create' => 'Create Results',
                'teacher.results.submit' => 'Submit Results',
                'attendance.view' => 'View Attendance',
                'attendance.manage' => 'Mark Class Attendance',
                'lms.view' => 'View LMS',
                'lms.materials.manage' => 'Manage Assigned LMS Materials',
                'lms.assignments.post' => 'Post Assignment Materials',
                'cbt.question_bank' => 'Manage CBT Question Bank',
                'cbt.mark_theory' => 'Mark CBT Theory Questions',
                'students.view_assigned' => 'View Assigned Students',
                'support.manage' => 'Support Access',
                'support.direct_escalation' => 'Direct Support Escalation',
            ],
            'accountant' => [
                'finance.view' => 'View School Finance',
                'finance.manage' => 'Manage School Finance',
                'students.view' => 'View Students',
                'support.manage' => 'Support Access',
            ],
        ];

        return $features[$roleName] ?? [];
    }

    private function settingsForRole(int $schoolId, string $roleName): array
    {
        return Cache::remember($this->roleSettingsCacheKey($schoolId, $roleName), 3600, fn () => SchoolRoleFeatureSetting::where('school_id', $schoolId)
            ->where('role_name', $roleName)
            ->get(['feature_key', 'is_enabled'])
            ->mapWithKeys(fn (SchoolRoleFeatureSetting $setting) => [
                (string) $setting->feature_key => (bool) $setting->is_enabled,
            ])
            ->all());
    }

    private function candidateFeatureKeys(string $featureKey): array
    {
        return array_values(array_unique([
            $featureKey,
            ...(self::FEATURE_ALIASES[$featureKey] ?? []),
        ]));
    }

    private function roleSettingsCacheKey(int $schoolId, string $roleName): string
    {
        return "school_role_features:v2:{$schoolId}:{$roleName}";
    }
}
