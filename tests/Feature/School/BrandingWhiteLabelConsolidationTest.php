<?php

namespace Tests\Feature\School;

use App\Models\AuditLog;
use App\Models\BrandingSetting;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BrandingWhiteLabelConsolidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'accountant', 'result_officer'] as $role) {
            Role::findOrCreate($role);
        }

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'features.features.branding_manager.enabled' => true,
            'branding.enabled' => true,
        ]);
    }

    public function test_branding_routes_require_authentication(): void
    {
        $this->get(route('school.branding.edit'))->assertRedirect(route('login'));
        $this->patch(route('school.branding.update'), [])->assertRedirect(route('login'));
        $this->post(route('school.branding.logo'), [])->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_and_update_school_branding(): void
    {
        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.branding.edit'))
            ->assertOk()
            ->assertSee('School Branding')
            ->assertSee('White-label boundary')
            ->assertSee('Powered by Sanfaani boundary');

        $this->patch(route('school.branding.update'), [
            'brand_name' => 'Cedar Grove Portal',
            'login_heading' => 'Welcome to Cedar Grove',
            'login_subheading' => 'Sign in to your private school workspace.',
            'dashboard_heading' => 'Cedar Grove Operations',
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
            'accent_color' => '#abcdef',
            'email_footer_text' => 'Powered by Sanfaani for Cedar Grove.',
            'report_footer_text' => 'Generated for Cedar Grove.',
            'white_label_enabled' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('branding_settings', [
            'school_id' => $context['school']->id,
            'scope' => 'school',
            'brand_name' => 'Cedar Grove Portal',
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'school_branding_updated',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'school_branding_colors_updated',
        ]);
    }

    public function test_non_admin_school_roles_cannot_manage_branding(): void
    {
        $school = $this->createSchool();

        foreach (['teacher', 'accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->get(route('school.branding.edit'))->assertForbidden();
            $this->patch(route('school.branding.update'), ['brand_name' => 'Blocked'])->assertForbidden();
            $this->post(route('school.branding.logo'), [
                'asset' => UploadedFile::fake()->image('logo.png')->size(16),
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('branding_settings', 0);
    }

    public function test_invalid_color_values_are_rejected(): void
    {
        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.branding.edit'))
            ->patch(route('school.branding.update'), [
                'brand_name' => 'Cedar Grove Portal',
                'primary_color' => 'javascript:alert(1)',
            ])
            ->assertRedirect(route('school.branding.edit'))
            ->assertSessionHasErrors('primary_color');
    }

    public function test_logo_upload_accepts_safe_images_and_rejects_unsafe_files(): void
    {
        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.branding.logo'), [
            'asset' => UploadedFile::fake()->image('logo.png')->size(24),
        ])->assertRedirect();

        $setting = BrandingSetting::where('school_id', $context['school']->id)->firstOrFail();
        $this->assertStringStartsWith('branding/schools/'.$context['school']->id.'/', $setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);

        $this->from(route('school.branding.edit'))
            ->post(route('school.branding.logo'), [
                'asset' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml'),
            ])
            ->assertRedirect(route('school.branding.edit'))
            ->assertSessionHasErrors('asset');
    }

    public function test_cross_school_branding_is_isolated_and_audit_metadata_is_safe(): void
    {
        $context = $this->schoolContext('school_admin');
        $otherSchool = $this->createSchool('Hidden Maple School');
        BrandingSetting::create([
            'school_id' => $otherSchool->id,
            'scope' => 'school',
            'brand_name' => 'Hidden Maple Portal',
            'primary_color' => '#111111',
            'is_active' => true,
        ]);

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.branding.logo'), [
            'asset' => UploadedFile::fake()->image('logo.png')->size(24),
        ])->assertRedirect();

        $this->patch(route('school.branding.update'), [
            'brand_name' => 'Cedar Grove Portal',
            'primary_color' => '#224466',
            'secondary_color' => '#113355',
            'white_label_enabled' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('branding_settings', [
            'school_id' => $otherSchool->id,
            'brand_name' => 'Hidden Maple Portal',
            'primary_color' => '#111111',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'school_branding_logo_updated',
        ]);

        $auditJson = json_encode(AuditLog::where('school_id', $context['school']->id)->get()->toArray());
        $this->assertStringNotContainsString('branding/schools/'.$context['school']->id, $auditJson);
        $this->assertStringNotContainsString('Hidden Maple Portal', $this->get(route('school.branding.edit'))->getContent());
    }

    public function test_dashboard_navigation_and_school_facing_views_use_resolved_branding(): void
    {
        config(['standalone.product_edition' => 'standalone']);
        $context = $this->schoolContext('school_admin');
        BrandingSetting::create([
            'school_id' => $context['school']->id,
            'scope' => 'school',
            'brand_name' => 'Cedar Grove Portal',
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
            'dashboard_heading' => 'Cedar Grove Operations',
            'is_active' => true,
        ]);

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Cedar Grove Portal')
            ->assertSee('Branding / White Label')
            ->assertSee('Branding and white-label consolidation')
            ->assertSee('--color-brand-primary: #123456', false);

        $this->get(route('school.communications.index'))
            ->assertOk()
            ->assertSee('Cedar Grove Portal');
    }

    public function test_branding_management_link_is_hidden_from_non_admin_dashboards(): void
    {
        $school = $this->createSchool();
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('Branding / White Label')
            ->assertDontSee('School Branding');
    }

    private function schoolContext(string $role): array
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school, $role);

        return compact('school', 'user');
    }

    private function createSchool(string $name = 'Cedar Grove School'): School
    {
        $suffix = fake()->unique()->numberBetween(1000, 999999);

        return School::create([
            'name' => $name.' '.$suffix,
            'slug' => str($name)->slug().'-'.$suffix,
            'email' => 'office'.$suffix.'@example.test',
            'phone' => '0800000'.$suffix,
            'address' => '1 School Road',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
