<?php

namespace Tests\Feature\Branding;

use App\Models\BrandingSetting;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BrandingEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'features.features.branding_manager.enabled' => true,
            'branding.enabled' => true,
        ]);
    }

    public function test_email_footer_uses_resolved_branding_safely(): void
    {
        BrandingSetting::create([
            'scope' => 'platform',
            'brand_name' => 'Safe Brand',
            'email_footer_text' => '<script>alert(1)</script>Safe footer',
            'is_active' => true,
        ]);

        $html = (string) view('emails.partials.brand-footer')->render();

        $this->assertStringContainsString('Safe footer', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_login_auth_layout_can_receive_branding_variables(): void
    {
        BrandingSetting::create([
            'scope' => 'platform',
            'brand_name' => 'Login Brand',
            'login_heading' => 'Login to Login Brand',
            'login_subheading' => 'A safe branded login page.',
            'is_active' => true,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Login Brand')
            ->assertSee('Login to Login Brand');
    }

    public function test_result_report_branding_hook_is_present(): void
    {
        $this->assertFileExists(resource_path('views/public/results/view.blade.php'));
        $this->assertStringContainsString(
            'report_footer_text',
            file_get_contents(resource_path('views/public/results/view.blade.php'))
        );
    }

    public function test_school_dashboard_uses_branding_context(): void
    {
        $school = $this->school('Dashboard School');
        $admin = $this->schoolAdmin($school);

        BrandingSetting::create([
            'school_id' => $school->id,
            'scope' => 'school',
            'brand_name' => 'Dashboard School',
            'dashboard_heading' => 'Branded Command Center',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Branded Command Center');
    }

    private function schoolAdmin(School $school): User
    {
        Role::findOrCreate('school_admin');
        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole('school_admin');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        return $user;
    }

    private function school(string $name): School
    {
        return School::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }
}
