<?php

namespace Tests\Feature\Branding;

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

class BrandingAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('public');
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'features.features.branding_manager.enabled' => true,
            'branding.enabled' => true,
        ]);
    }

    public function test_school_admin_cannot_edit_another_school_branding(): void
    {
        $schoolA = $this->school('School A');
        $schoolB = $this->school('School B');
        $admin = $this->schoolAdmin($schoolA);

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $schoolA->id, 'active_role_context' => 'school_admin'])
            ->patch(route('school.branding.update'), [
                'brand_name' => 'School A Brand',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('branding_settings', [
            'school_id' => $schoolA->id,
            'brand_name' => 'School A Brand',
        ]);
        $this->assertDatabaseMissing('branding_settings', [
            'school_id' => $schoolB->id,
            'brand_name' => 'School A Brand',
        ]);
    }

    public function test_super_admin_can_edit_platform_branding(): void
    {
        $this->actingAs($this->superAdmin())
            ->patch(route('admin.branding.update'), [
                'brand_name' => 'Platform Brand',
                'primary_color' => '#123456',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('branding_settings', [
            'scope' => 'platform',
            'brand_name' => 'Platform Brand',
            'primary_color' => '#123456',
        ]);
    }

    public function test_non_admin_cannot_access_branding_manager(): void
    {
        Role::findOrCreate('teacher');
        $user = User::factory()->create();
        $user->assignRole('teacher');

        $this->actingAs($user)
            ->get(route('admin.branding.edit'))
            ->assertForbidden();
    }

    public function test_demo_user_cannot_upload_branding_assets(): void
    {
        config(['sanfaani.deployment.license_mode' => 'demo']);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.branding.logo'), [
                'asset' => UploadedFile::fake()->image('logo.png')->size(32),
            ])
            ->assertNotFound();
    }

    public function test_dashboard_renders_with_branding_context(): void
    {
        BrandingSetting::create([
            'scope' => 'platform',
            'brand_name' => 'Dashboard Brand',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Brand')
            ->assertSee('Guided Branding', false);
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
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
