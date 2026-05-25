<?php

namespace App\Services\Onboarding;

use App\Events\OnboardingChecklistCompleted;
use App\Events\OnboardingStepCompleted;
use App\Models\DemoSession;
use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Models\UserOnboardingProgress;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class OnboardingProgressService
{
    public function __construct(
        private OnboardingVisibilityService $visibility,
        private OnboardingEventService $events,
    ) {}

    public function complete(User $actor, OnboardingStep $step, ?School $school = null, ?User $target = null): UserOnboardingProgress
    {
        return $this->record($actor, $step, UserOnboardingProgress::STATUS_COMPLETED, $school, $target);
    }

    public function skip(User $actor, OnboardingStep $step, ?School $school = null, ?User $target = null): UserOnboardingProgress
    {
        return $this->record($actor, $step, UserOnboardingProgress::STATUS_SKIPPED, $school, $target);
    }

    public function progressFor(User $user, OnboardingChecklist $checklist, Collection $steps, ?School $school = null): array
    {
        $records = UserOnboardingProgress::query()
            ->where('user_id', $user->id)
            ->where('onboarding_checklist_id', $checklist->id)
            ->whereIn('onboarding_step_id', $steps->pluck('id'))
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->get()
            ->keyBy('onboarding_step_id');

        $total = $steps->count();
        $completed = $records->where('status', UserOnboardingProgress::STATUS_COMPLETED)->count();
        $skipped = $records->where('status', UserOnboardingProgress::STATUS_SKIPPED)->count();
        $pending = max(0, $total - $completed - $skipped);
        $requiredStepIds = $steps->where('required', true)->pluck('id');
        $requiredCompleted = $records
            ->where('status', UserOnboardingProgress::STATUS_COMPLETED)
            ->whereIn('onboarding_step_id', $requiredStepIds)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'skipped' => $skipped,
            'pending' => $pending,
            'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'complete' => $total > 0 && $requiredCompleted === $requiredStepIds->count(),
            'records' => $records,
        ];
    }

    private function record(
        User $actor,
        OnboardingStep $step,
        string $status,
        ?School $school,
        ?User $target
    ): UserOnboardingProgress {
        $target ??= $actor;
        $step->loadMissing('checklist.steps');

        $this->authorize($actor, $target, $step, $school);

        $attributes = [
            'user_id' => $target->id,
            'school_id' => $school?->id,
            'onboarding_step_id' => $step->id,
        ];

        $values = [
            'onboarding_checklist_id' => $step->onboarding_checklist_id,
            'status' => $status,
            'completed_at' => $status === UserOnboardingProgress::STATUS_COMPLETED ? now() : null,
            'skipped_at' => $status === UserOnboardingProgress::STATUS_SKIPPED ? now() : null,
            'metadata' => [
                'role_name' => $this->visibility->roleFor($target),
            ],
        ];

        $progress = UserOnboardingProgress::updateOrCreate($attributes, $values);
        $demoSession = $this->demoSessionFor($target);

        $event = $status === UserOnboardingProgress::STATUS_COMPLETED
            ? 'onboarding.step_completed'
            : 'onboarding.step_skipped';

        $this->events->log($event, $step->title, $target, $school, $demoSession, [
            'onboarding_step_id' => $step->id,
            'onboarding_checklist_id' => $step->onboarding_checklist_id,
            'step_key' => $step->key,
        ]);

        if ($status === UserOnboardingProgress::STATUS_COMPLETED) {
            OnboardingStepCompleted::dispatch($target, $step, $progress, $school);
            $this->logChecklistCompletionIfDone($target, $step->checklist, $school, $demoSession);
        }

        return $progress;
    }

    private function authorize(User $actor, User $target, OnboardingStep $step, ?School $school): void
    {
        if ((int) $actor->id !== (int) $target->id) {
            throw new AuthorizationException('Users can only update their own onboarding progress.');
        }

        if (! $this->visibility->stepVisible($step, $target, $school)) {
            throw new AuthorizationException('This onboarding step is not available in the current context.');
        }

        if ($school && ! $target->hasRole('super_admin') && (int) $target->school_id !== (int) $school->id) {
            $hasRoleForSchool = $target->activeSchoolRoles()
                ->where('school_id', $school->id)
                ->exists();

            if (! $hasRoleForSchool) {
                throw new AuthorizationException('This onboarding school context is not available to the user.');
            }
        }
    }

    private function logChecklistCompletionIfDone(
        User $user,
        OnboardingChecklist $checklist,
        ?School $school,
        ?DemoSession $demoSession
    ): void {
        $steps = app(OnboardingChecklistService::class)->visibleSteps($checklist, $user, $school);
        $progress = $this->progressFor($user, $checklist, $steps, $school);

        if (! $progress['complete']) {
            return;
        }

        $alreadyLogged = $user->onboardingEventLogs()
            ->where('event', 'onboarding.checklist_completed')
            ->where('context->onboarding_checklist_id', $checklist->id)
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->exists();

        if ($alreadyLogged) {
            return;
        }

        $this->events->log('onboarding.checklist_completed', $checklist->name, $user, $school, $demoSession, [
            'onboarding_checklist_id' => $checklist->id,
            'checklist_key' => $checklist->key,
        ]);

        OnboardingChecklistCompleted::dispatch($user, $checklist, $school);
    }

    private function demoSessionFor(User $user): ?DemoSession
    {
        return DemoSession::query()
            ->where('status', DemoSession::STATUS_ACTIVE)
            ->whereHas('credentials', fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->first();
    }
}
