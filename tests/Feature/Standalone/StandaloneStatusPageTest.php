<?php

namespace Tests\Feature\Standalone;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StandaloneStatusPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        File::delete(storage_path('app/installed.lock'));

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.installer_enabled' => true,
            'standalone.installed' => false,
            'standalone.offline_mode' => 'local_first',
            'standalone.sync.enabled' => false,
            'standalone.sync.endpoint' => '',
            'standalone.sync.token' => '',
            'standalone.sync.backup_enabled' => false,
            'installer.enabled' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.demo_enabled' => false,
            'demo.enabled' => false,
            'demo.marketplace.enabled' => false,
        ]);
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/installed.lock'));

        parent::tearDown();
    }

    public function test_admin_standalone_status_page_renders_for_authorized_admin(): void
    {
        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.standalone.status'))
            ->assertOk()
            ->assertSee('Standalone Status')
            ->assertSee('Standalone School')
            ->assertSee('single_school')
            ->assertSee('local_first')
            ->assertSee('Sync')
            ->assertSee('Missing')
            ->assertSee('No sync has run yet')
            ->assertSee('Browser offline/PWA is not complete');
    }

    public function test_unauthorized_user_cannot_access_standalone_status_page(): void
    {
        $schoolAdmin = $this->userWithRole('school_admin');

        $this->actingAs($schoolAdmin)
            ->get(route('admin.standalone.status'))
            ->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
