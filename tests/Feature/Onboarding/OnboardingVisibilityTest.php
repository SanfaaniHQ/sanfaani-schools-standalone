<?php

namespace Tests\Feature\Onboarding;

use App\Models\OnboardingChecklist;
use App\Models\OnboardingStep;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Onboarding\OnboardingChecklistService;
use Database\Seeders\OnboardingChecklistSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OnboardingVisibilityTest extends TestCase
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

    public function test_steps_are_filtered_by_deployment_mode(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');
        $step = $this->step('single_school_only', deploymentModes: ['single_school']);

        $steps = app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key');

        $this->assertFalse($steps->contains('single_school_only'));

        config(['sanfaani.deployment.mode' => 'single_school']);

        $steps = app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key');

        $this->assertTrue($steps->contains('single_school_only'));
    }

    public function test_license_mode_step_filters_are_ignored_while_license_enforcement_is_disabled(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');
        $step = $this->step('annual_only', licenseModes: ['annual']);

        $this->assertTrue(app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key')
            ->contains('annual_only'));

        config(['sanfaani.deployment.license_mode' => 'annual']);

        $this->assertTrue(app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key')
            ->contains('annual_only'));
    }

    public function test_steps_are_filtered_by_feature_access(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');
        $step = $this->step('parent_portal_step', featureKey: 'parent_portal');

        $this->assertFalse(app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key')
            ->contains('parent_portal_step'));

        config(['features.features.parent_portal.enabled' => true]);

        $this->assertTrue(app(OnboardingChecklistService::class)
            ->visibleSteps($step->checklist, $user, $school)
            ->pluck('key')
            ->contains('parent_portal_step'));
    }

    private function step(
        string $key,
        array $deploymentModes = [],
        array $licenseModes = [],
        ?string $featureKey = null
    ): OnboardingStep {
        $checklist = OnboardingChecklist::where('key', 'school_admin')->firstOrFail();

        return OnboardingStep::create([
            'onboarding_checklist_id' => $checklist->id,
            'key' => $key,
            'title' => str($key)->replace('_', ' ')->title(),
            'deployment_modes' => $deploymentModes,
            'license_modes' => $licenseModes,
            'feature_key' => $featureKey,
            'required' => true,
            'sort_order' => 99,
        ])->load('checklist');
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
            'name' => 'Visibility School',
            'slug' => 'visibility-school-'.School::count(),
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
