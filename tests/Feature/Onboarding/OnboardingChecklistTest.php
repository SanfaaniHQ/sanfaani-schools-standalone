<?php

namespace Tests\Feature\Onboarding;

use App\Models\DemoCredential;
use App\Models\DemoSession;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Database\Seeders\OnboardingChecklistSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OnboardingChecklistTest extends TestCase
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

    public function test_onboarding_page_renders_when_guided_onboarding_is_enabled(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Guided Onboarding')
            ->assertSee('School admin onboarding');
    }

    public function test_onboarding_page_is_blocked_when_guided_onboarding_is_disabled(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');
        config(['features.features.guided_onboarding.enabled' => false]);

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('onboarding.index'))
            ->assertNotFound();
    }

    public function test_school_admin_receives_school_admin_checklist(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Complete school profile')
            ->assertSee('Configure academic sessions');
    }

    public function test_teacher_receives_teacher_checklist(): void
    {
        [$school, $user] = $this->schoolUser('teacher');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'teacher'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Teacher onboarding')
            ->assertSee('Review assigned classes');
    }

    public function test_result_officer_receives_result_officer_checklist(): void
    {
        [$school, $user] = $this->schoolUser('result_officer');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'result_officer'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Result officer onboarding')
            ->assertSee('Configure grading scales');
    }

    public function test_accountant_receives_accountant_checklist_if_role_exists(): void
    {
        [$school, $user] = $this->schoolUser('accountant');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'accountant'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Accountant onboarding')
            ->assertSee('Review payment settings');
    }

    public function test_demo_user_receives_demo_safe_checklist_when_linked_to_demo_session(): void
    {
        [$demoSchool, $user] = $this->schoolUser('school_admin', 'Demo Tenant');
        $realSchool = School::create([
            'name' => 'Real Production School',
            'slug' => 'real-production-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $session = DemoSession::create([
            'school_id' => $demoSchool->id,
            'status' => DemoSession::STATUS_ACTIVE,
            'starts_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        DemoCredential::create([
            'demo_session_id' => $session->id,
            'user_id' => $user->id,
            'role_name' => 'school_admin',
            'label' => 'School Admin demo',
            'email' => $user->email,
            'temporary_password_encrypted' => 'one-time-secret',
            'expires_at' => now()->addDays(7),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withSession(['active_school_id' => $demoSchool->id, 'active_role_context' => 'school_admin'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Demo School admin onboarding')
            ->assertSee('Explore the demo dashboard')
            ->assertSee($demoSchool->name)
            ->assertDontSee($realSchool->name);
    }

    private function configureOnboarding(): void
    {
        config([
            'onboarding.enabled' => true,
            'onboarding.demo_enabled' => true,
            'onboarding.trial_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.guided_onboarding.enabled' => true,
        ]);
    }

    private function schoolUser(string $role, string $schoolName = 'Onboarding School'): array
    {
        $school = School::create([
            'name' => $schoolName.' '.School::count(),
            'slug' => str($schoolName.' '.School::count())->slug()->toString(),
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
