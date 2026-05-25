<?php

namespace App\Services\Onboarding;

use App\Models\OnboardingChecklist;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class OnboardingChecklistService
{
    public function __construct(private OnboardingVisibilityService $visibility) {}

    public function checklistFor(User $user, ?School $school = null): ?OnboardingChecklist
    {
        $role = $this->visibility->roleFor($user);

        if (! $role) {
            return null;
        }

        $keys = $this->visibility->isDemoUser($user)
            ? ["demo_{$role}", $role]
            : [$role];

        return OnboardingChecklist::query()
            ->with('steps')
            ->whereIn('key', $keys)
            ->where('role_name', $role)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->sortBy(fn (OnboardingChecklist $checklist): int => array_search($checklist->key, $keys, true) ?: 0)
            ->first(fn (OnboardingChecklist $checklist): bool => $this->visibility->checklistVisible($checklist, $user, $school));
    }

    public function visibleSteps(OnboardingChecklist $checklist, User $user, ?School $school = null): EloquentCollection
    {
        $checklist->loadMissing('steps.checklist');

        return $checklist->steps
            ->filter(fn ($step): bool => $this->visibility->stepVisible($step, $user, $school))
            ->values();
    }

    public function summaryFor(User $user, ?School $school = null): array
    {
        $checklist = $this->checklistFor($user, $school);

        if (! $checklist) {
            return [
                'available' => false,
                'checklist' => null,
                'steps' => collect(),
                'progress' => [
                    'total' => 0,
                    'completed' => 0,
                    'skipped' => 0,
                    'pending' => 0,
                    'percent' => 0,
                    'complete' => false,
                ],
            ];
        }

        $steps = $this->visibleSteps($checklist, $user, $school);
        $progress = app(OnboardingProgressService::class)->progressFor($user, $checklist, $steps, $school);

        return compact('checklist', 'steps', 'progress') + ['available' => true];
    }
}
