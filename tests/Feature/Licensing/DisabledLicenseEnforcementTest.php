<?php

namespace Tests\Feature\Licensing;

use App\Events\LicenseExpiring;
use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Licensing\LicenseValidationService;
use App\Services\Standalone\StandaloneSystemHealthService;
use App\Services\System\FeatureAccessService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DisabledLicenseEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'student', 'parent'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.installed' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'sanfaani.license_validation_enabled' => false,
            'licensing.signing_key' => '',
            'features.features.license_activation.enabled' => true,
        ]);

        Route::middleware(['auth', 'license.valid'])
            ->get('/__licensing-disabled/authenticated', fn () => 'authenticated')
            ->name('__licensing-disabled.authenticated');

        Route::middleware(['auth', 'role:school_admin|teacher|student|parent', 'license.valid'])
            ->get('/__licensing-disabled/school-user', fn () => 'school user')
            ->name('__licensing-disabled.school-user');

        Route::middleware(['auth', 'role:school_admin', 'license.valid'])
            ->get('/__licensing-disabled/admin-only', fn () => 'admin only')
            ->name('__licensing-disabled.admin-only');
    }

    public function test_middleware_is_a_no_op_with_an_expired_existing_license(): void
    {
        $school = $this->school();
        $admin = $this->schoolUser($school, 'school_admin');
        $this->expiredLicense($school, ['status' => 'suspended', 'entitlements' => ['backup_manager' => false]]);

        $this->actingAs($admin)
            ->get('/__licensing-disabled/authenticated')
            ->assertOk()
            ->assertSee('authenticated');

        $result = app(LicenseValidationService::class)->validate($school);

        $this->assertTrue($result->valid());
        $this->assertSame('validation_disabled', $result->status);
        $this->assertTrue(app(FeatureAccessService::class)->enabled('backup_manager', $school, $admin));
    }

    public function test_authenticated_school_roles_are_not_blocked_but_role_checks_remain(): void
    {
        $school = $this->school();

        foreach (['school_admin', 'teacher', 'student', 'parent'] as $role) {
            $this->actingAs($this->schoolUser($school, $role))
                ->get('/__licensing-disabled/school-user')
                ->assertOk();
        }

        $teacher = $this->schoolUser($school, 'teacher');

        $this->actingAs($teacher)
            ->get('/__licensing-disabled/admin-only')
            ->assertForbidden();

        auth()->logout();

        $this->get('/__licensing-disabled/authenticated')
            ->assertRedirect(route('login'));
    }

    public function test_installation_and_school_dashboards_have_no_license_ui(): void
    {
        $school = $this->school();
        $installationAdmin = User::factory()->create();
        $installationAdmin->assignRole('super_admin');

        $this->actingAs($installationAdmin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('License Status')
            ->assertDontSee('Activate license')
            ->assertDontSee('Renewal reminders');

        foreach (['school_admin', 'teacher'] as $role) {
            $user = $this->schoolUser($school, $role);
            $this->actingAs($user);
            session(['active_school_id' => $school->id, 'active_role_context' => $role]);

            $this->get(route('school.dashboard'))
                ->assertOk()
                ->assertDontSee('License Status')
                ->assertDontSee('License expired');
        }
    }

    public function test_student_and_parent_dashboards_are_not_blocked(): void
    {
        $school = $this->school();

        foreach (['student' => 'student.dashboard', 'parent' => 'parent.dashboard'] as $role => $route) {
            $user = $this->schoolUser($school, $role);
            $this->actingAs($user);
            session(['active_school_id' => $school->id, 'active_role_context' => $role]);

            $this->get(route($route))->assertOk();
        }
    }

    public function test_activation_routes_and_navigation_are_unavailable(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.license.index'))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('License Status')
            ->assertDontSee('Enter license key');
    }

    public function test_health_scheduler_and_renewal_workflows_omit_licensing(): void
    {
        $school = $this->school();
        $license = $this->expiredLicense($school);
        $summary = app(StandaloneSystemHealthService::class)->summary($school);
        $checkKeys = collect($summary['sections'])
            ->flatMap(fn (array $section): array => $section['checks'])
            ->pluck('key');

        $this->assertNotContains('license_status', $checkKeys);
        $this->assertStringNotContainsString('license', strtolower(json_encode($summary, JSON_THROW_ON_ERROR)));

        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event): string => strtolower((string) ($event->command ?? '')))
            ->implode('\n');

        $this->assertStringNotContainsString('license', $commands);

        event(new LicenseExpiring($license));

        $this->assertDatabaseCount('sales_tasks', 0);
    }

    public function test_empty_signing_key_is_safe_and_reenabling_flag_restores_validation(): void
    {
        $school = $this->school();
        $this->expiredLicense($school);

        $this->assertTrue(app(LicenseValidationService::class)->isValid($school));

        config(['sanfaani.license_validation_enabled' => true]);

        $this->assertFalse(app(LicenseValidationService::class)->isValid($school));
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Unrestricted School',
            'slug' => 'unrestricted-school-'.School::count(),
            'email' => 'school'.School::count().'@example.test',
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

    private function expiredLicense(School $school, array $overrides = []): License
    {
        return License::create(array_merge([
            'school_id' => $school->id,
            'license_key_hash' => hash('sha256', 'dormant-license-'.$school->id),
            'license_type' => 'annual',
            'status' => 'expired',
            'domain' => 'wrong.example.test',
            'allowed_domains' => ['wrong.example.test'],
            'starts_at' => now()->subYear(),
            'expires_at' => now()->subMonth(),
            'features' => [],
            'entitlements' => [],
        ], $overrides));
    }
}
