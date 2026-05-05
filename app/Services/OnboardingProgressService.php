<?php

namespace App\Services;

use App\Models\OnboardingProgress;
use App\Models\School;
use App\Models\User;

class OnboardingProgressService
{
    public function platformSteps(): array
    {
        return [
            'platform_details' => 'Platform details',
            'logo_upload' => 'Logo upload',
            'mail_settings' => 'Mail settings',
            'payment_mode' => 'Payment mode',
            'first_school' => 'Create first school',
            'first_school_admin' => 'Create school admin',
            'finish' => 'Finish',
        ];
    }

    public function schoolSteps(): array
    {
        return [
            'school_profile' => 'School profile',
            'classes' => 'Classes',
            'subjects' => 'Subjects',
            'sessions_terms' => 'Sessions and terms',
            'students' => 'Students',
            'grading_scales' => 'Grading scales',
            'result_settings' => 'Result settings',
            'access_policy' => 'Scratch card and access policy',
        ];
    }

    public function completedKeys(string $context, ?School $school = null, ?User $user = null): array
    {
        return OnboardingProgress::query()
            ->where('context', $context)
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->whereNotNull('completed_at')
            ->pluck('step_key')
            ->all();
    }

    public function progress(array $steps, array $completed): array
    {
        $total = count($steps);
        $done = count(array_intersect(array_keys($steps), $completed));

        return [
            'total' => $total,
            'done' => $done,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }
}
