<?php

namespace Tests\Feature\Onboarding;

use App\Events\OnboardingChecklistCompleted;
use App\Events\OnboardingStepCompleted;
use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Models\UserOnboardingProgress;
use App\Services\Onboarding\OnboardingProgressService;
use Database\Seeders\OnboardingChecklistSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OnboardingProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RoleSeeder::class);
        $this->seed(OnboardingChecklistSeeder::class);
        $this->configureOnboarding();
    }

    public function test_user_can_complete_own_onboarding_step(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');
        $step = $this->firstSchoolAdminStep();

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->post(route('onboarding.steps.complete', $step))
            ->assertRedirect(route('onboarding.index'));

        $this->assertDatabaseHas('user_onboarding_progress', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'onboarding_step_id' => $step->id,
            'status' => UserOnboardingProgress::STATUS_COMPLETED,
        ]);
    }

    public function test_user_cannot_complete_another_users_onboarding_step(): void
    {
        [$school, $actor] = $this->schoolUser('school_admin');
        [, $target] = $this->schoolUser('school_admin');

        $this->expectException(AuthorizationException::class);

        app(OnboardingProgressService::class)->complete($actor, $this->firstSchoolAdminStep(), $school, $target);
    }

    public function test_school_a_user_cannot_update_school_b_onboarding_progress(): void
    {
        [, $actor] = $this->schoolUser('school_admin');
        [$schoolB] = $this->schoolUser('school_admin');

        $this->expectException(AuthorizationException::class);

        app(OnboardingProgressService::class)->complete($actor, $this->firstSchoolAdminStep(), $schoolB);
    }

    public function test_completed_step_creates_event_log_and_event(): void
    {
        Event::fake([OnboardingStepCompleted::class]);

        [$school, $user] = $this->schoolUser('school_admin');
        $step = $this->firstSchoolAdminStep();

        app(OnboardingProgressService::class)->complete($user, $step, $school);

        $this->assertDatabaseHas('onboarding_event_logs', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'event' => 'onboarding.step_completed',
        ]);
        Event::assertDispatched(OnboardingStepCompleted::class);
    }

    public function test_checklist_completion_logs_completion_event(): void
    {
        Event::fake([OnboardingChecklistCompleted::class]);

        [$school, $user] = $this->schoolUser('school_admin');
        $checklist = OnboardingChecklist::create([
            'key' => 'tiny_school_admin',
            'name' => 'Tiny school admin checklist',
            'role_name' => 'school_admin',
            'deployment_modes' => ['saas'],
            'license_modes' => ['subscription'],
            'is_active' => true,
        ]);
        $step = OnboardingStep::create([
            'onboarding_checklist_id' => $checklist->id,
            'key' => 'only_step',
            'title' => 'Only step',
            'required' => true,
            'sort_order' => 1,
        ])->load('checklist');

        app(OnboardingProgressService::class)->complete($user, $step, $school);

        $this->assertDatabaseHas('onboarding_event_logs', [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'event' => 'onboarding.checklist_completed',
        ]);
        Event::assertDispatched(OnboardingChecklistCompleted::class);
    }

    private function firstSchoolAdminStep(): OnboardingStep
    {
        return OnboardingChecklist::where('key', 'school_admin')
            ->firstOrFail()
            ->steps()
            ->firstOrFail()
            ->load('checklist');
    }

    private function configureOnboarding(): void
    {
        config([
            'onboarding.enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.guided_onboarding.enabled' => true,
        ]);
    }

    private function schoolUser(string $role): array
    {
        $school = School::create([
            'name' => 'Progress School '.School::count(),
            'slug' => 'progress-school-'.School::count(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return [$school, $user];
    }
}
