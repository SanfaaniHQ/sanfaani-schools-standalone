<?php

namespace Tests\Feature\UI;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BrandingUiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'branding.enabled' => true,
        ]);
    }

    public function test_branding_aware_ui_renders_with_default_branding(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('--color-brand-primary: #0f766e', false)
            ->assertSee('--tenant-primary: #0f766e', false);
    }

    public function test_branding_aware_ui_renders_with_school_branding(): void
    {
        $school = $this->school([
            'primary_color' => '#123456',
            'secondary_color' => '#654321',
        ]);
        $admin = $this->schoolAdmin($school);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('--color-brand-primary: #123456', false)
            ->assertSee('--color-brand-hover: #654321', false)
            ->assertSee('Branding Academy');
    }

    public function test_ui_does_not_expose_private_branding_paths(): void
    {
        $school = $this->school([
            'logo_path' => 'storage/app/private/branding/secret-logo.png',
            'favicon_path' => 'storage/app/private/branding/secret-favicon.ico',
        ]);
        $admin = $this->schoolAdmin($school);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('storage/app/private', false)
            ->assertDontSee('secret-logo', false)
            ->assertDontSee('secret-favicon', false);
    }

    public function test_invalid_school_brand_color_falls_back_to_default_ui_tokens(): void
    {
        $school = $this->school([
            'primary_color' => 'javascript:alert(1)',
            'secondary_color' => 'url(https://example.test/a)',
        ]);
        $admin = $this->schoolAdmin($school);

        $this->actingAs($admin);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('--color-brand-primary: #0f766e', false)
            ->assertDontSee('javascript:alert', false)
            ->assertDontSee('url(https://example.test/a)', false);
    }

    private function school(array $attributes = []): School
    {
        return School::create(array_merge([
            'name' => 'Branding Academy',
            'slug' => 'branding-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ], $attributes));
    }

    private function schoolAdmin(School $school): User
    {
        $admin = $this->userWithRole('school_admin', ['school_id' => $school->id]);

        UserSchoolRole::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $admin;
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
