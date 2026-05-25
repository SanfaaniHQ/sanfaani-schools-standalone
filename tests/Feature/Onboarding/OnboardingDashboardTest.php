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

class OnboardingDashboardTest extends TestCase
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

    public function test_dashboard_widget_renders_progress(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Guided onboarding')
            ->assertSee('School admin onboarding');
    }

    public function test_dashboard_widget_does_not_break_without_school_context(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Guided onboarding')
            ->assertSee('Platform operator onboarding');
    }

    public function test_admin_progress_page_is_blocked_for_non_admin(): void
    {
        [$school, $user] = $this->schoolUser('school_admin');

        $this->actingAs($user)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('admin.onboarding.progress'))
            ->assertForbidden();
    }

    public function test_demo_onboarding_does_not_expose_non_demo_school_data(): void
    {
        [$demoSchool, $user] = $this->schoolUser('school_admin', 'Dashboard Demo School');
        $realSchool = School::create([
            'name' => 'Private Non Demo School',
            'slug' => 'private-non-demo-school',
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
            'temporary_password_encrypted' => 'temporary',
            'expires_at' => now()->addDays(7),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->withSession(['active_school_id' => $demoSchool->id, 'active_role_context' => 'school_admin'])
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee($demoSchool->name)
            ->assertDontSee($realSchool->name);
    }

    private function configureOnboarding(): void
    {
        config([
            'onboarding.enabled' => true,
            'onboarding.progress_widget_enabled' => true,
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'features.features.guided_onboarding.enabled' => true,
        ]);
    }

    private function schoolUser(string $role, string $name = 'Dashboard School'): array
    {
        $school = School::create([
            'name' => $name.' '.School::count(),
            'slug' => str($name.' '.School::count())->slug()->toString(),
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
