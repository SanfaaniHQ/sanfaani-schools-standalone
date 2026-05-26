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

class BrandingAssetTest extends TestCase
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
            'branding.uploads.max_logo_kb' => 512,
            'branding.uploads.max_favicon_kb' => 128,
        ]);
    }

    public function test_invalid_color_values_are_rejected(): void
    {
        $this->actingAs($this->superAdmin())
            ->patch(route('admin.branding.update'), ['primary_color' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('primary_color');
    }

    public function test_logo_upload_validates_image_type(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.branding.logo'), [
                'asset' => UploadedFile::fake()->create('logo.txt', 4, 'text/plain'),
            ])
            ->assertSessionHasErrors('asset');
    }

    public function test_logo_upload_validates_max_size(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.branding.logo'), [
                'asset' => UploadedFile::fake()->image('logo.png')->size(600),
            ])
            ->assertSessionHasErrors('asset');
    }

    public function test_favicon_upload_validates_image_type(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.branding.favicon'), [
                'asset' => UploadedFile::fake()->create('favicon.exe', 4, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('asset');
    }

    public function test_svg_and_executable_uploads_are_blocked(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('admin.branding.logo'), [
                'asset' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml'),
            ])
            ->assertSessionHasErrors('asset');

        $this->actingAs($admin)
            ->post(route('admin.branding.logo'), [
                'asset' => UploadedFile::fake()->create('logo.php', 4, 'application/x-php'),
            ])
            ->assertSessionHasErrors('asset');
    }

    public function test_asset_paths_are_tenant_scoped(): void
    {
        $school = $this->school('Scoped School');
        $admin = $this->schoolAdmin($school);

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->post(route('school.branding.logo'), [
                'asset' => UploadedFile::fake()->image('logo.png')->size(32),
            ])
            ->assertRedirect();

        $setting = BrandingSetting::where('school_id', $school->id)->firstOrFail();

        $this->assertStringStartsWith('branding/schools/'.$school->id.'/', $setting->logo_path);
        Storage::disk('public')->assertExists($setting->logo_path);
    }

    public function test_ui_does_not_expose_private_storage_paths(): void
    {
        $school = $this->school('Private Path School');
        $admin = $this->schoolAdmin($school);

        BrandingSetting::create([
            'school_id' => $school->id,
            'scope' => 'school',
            'brand_name' => 'Private Path School',
            'logo_path' => base_path('.env'),
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['active_school_id' => $school->id, 'active_role_context' => 'school_admin'])
            ->get(route('school.branding.edit'))
            ->assertOk()
            ->assertDontSee(base_path())
            ->assertDontSee('.env');
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
