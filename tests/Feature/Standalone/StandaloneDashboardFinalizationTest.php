<?php

namespace Tests\Feature\Standalone;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StandaloneDashboardFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.installed' => false,
            'standalone.offline_mode' => 'local_first',
            'standalone.sync.enabled' => false,
            'standalone.surface_gates.hide_saas_surfaces' => true,
            'standalone.surface_gates.hide_marketplace_surfaces' => true,
            'standalone.surface_gates.hide_demo_surfaces' => true,
            'standalone.surface_gates.hide_platform_marketing_surfaces' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.demo_enabled' => false,
            'demo.enabled' => false,
            'demo.marketplace.enabled' => false,
            'features.features.saas_billing.enabled' => true,
            'features.features.demo_system.enabled' => true,
            'features.features.marketing_automation.enabled' => true,
        ]);
    }

    public function test_owner_dashboard_shows_standalone_health_setup_operations_and_planned_boundaries(): void
    {
        config([
            'standalone.sync.enabled' => true,
            'standalone.sync.endpoint' => 'https://sync.example.test/dashboard-endpoint',
            'standalone.sync.token' => 'dashboard-sync-token-secret',
        ]);

        $school = $this->school();
        $owner = User::factory()->create();
        $owner->assignRole('super_admin');

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Standalone operating dashboard')
            ->assertSee('Installation')
            ->assertSee('License')
            ->assertSee('Backup readiness')
            ->assertSee('Guided updates')
            ->assertSee('Local-first sync')
            ->assertSee('System health')
            ->assertSee('Operational setup checklist')
            ->assertSee('School profile and contact details')
            ->assertSee($school->name)
            ->assertSee('Admissions')
            ->assertSee('Results')
            ->assertSee('CBT')
            ->assertSee('Attendance tracking')
            ->assertSee('LMS and learning content')
            ->assertSee('Live classes')
            ->assertSee('Full fees and accounting')
            ->assertSee('Full browser offline/PWA')
            ->assertSee('Planned')
            ->assertSee('Local-first means the school server and local database remain the source of truth.')
            ->assertDontSee('Database connection')
            ->assertDontSee('Scheduler/cron heartbeat')
            ->assertDontSee('Safe health output')
            ->assertDontSee('https://sync.example.test/dashboard-endpoint')
            ->assertDontSee('dashboard-sync-token-secret')
            ->assertDontSee('School Subscriptions')
            ->assertDontSee('Demo Sessions')
            ->assertDontSee('Marketing Pipeline')
            ->assertDontSee('Sales Tasks');
    }

    public function test_school_admin_dashboard_shows_readiness_and_existing_operations_without_saas_finance(): void
    {
        $school = $this->school();
        $admin = $this->schoolUser($school, 'school_admin');

        $this->actInSchool($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Standalone school readiness')
            ->assertSee('Setup checklist')
            ->assertSee('Branding and logo')
            ->assertSee('Active academic session')
            ->assertSee('Admissions cycle')
            ->assertSee('Result and report settings')
            ->assertSee('CBT setup')
            ->assertSee('Admissions')
            ->assertSee('Results')
            ->assertSee('CBT')
            ->assertSee('Full fees and accounting')
            ->assertSee('Current payments are limited to existing admissions and scratch-card workflows.')
            ->assertDontSee('Finance')
            ->assertDontSee('Subscription');
    }

    public function test_teacher_dashboard_surfaces_existing_cbt_workflows(): void
    {
        $school = $this->school();
        $teacher = $this->schoolUser($school, 'teacher');

        $this->actInSchool($teacher, $school, 'teacher');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Teacher Dashboard')
            ->assertSee('CBT Question Bank')
            ->assertSee('CBT Theory Marking');
    }

    public function test_result_officer_dashboard_surfaces_existing_cbt_result_workflow(): void
    {
        $school = $this->school();
        $officer = $this->schoolUser($school, 'result_officer');

        $this->actInSchool($officer, $school, 'result_officer');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Result Officer Dashboard')
            ->assertSee('CBT Results')
            ->assertSee('Review Queue')
            ->assertSee('Publishing');
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Standalone Finalization Academy',
            'slug' => 'standalone-finalization-academy',
            'email' => 'school@example.test',
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function schoolUser(School $school, string $role): User
    {
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actInSchool(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
