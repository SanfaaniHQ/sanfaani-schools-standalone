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

    public function test_health_summary_appears_on_standalone_status_page_without_exposing_secrets(): void
    {
        config([
            'database.connections.sqlite.password' => 'database-secret-value',
            'mail.mailers.smtp.password' => 'smtp-secret-value',
            'standalone.sync.enabled' => true,
            'standalone.sync.endpoint' => 'https://sync.example.test/private-endpoint',
            'standalone.sync.token' => 'sync-token-secret-value',
        ]);

        $admin = $this->userWithRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.standalone.status'))
            ->assertOk()
            ->assertSee('System health summary')
            ->assertSee('Safe output rules')
            ->assertSee('Runtime and app')
            ->assertSee('PHP version')
            ->assertSee('Laravel version')
            ->assertSee('Database connection')
            ->assertSee('Storage, disk, and assets')
            ->assertSee('Disk free space')
            ->assertSee('Upload limit')
            ->assertSee('Post size limit')
            ->assertSee('Scheduler, queue, and mail')
            ->assertSee('Scheduler/cron heartbeat')
            ->assertSee('Queue')
            ->assertSee('Mail configuration')
            ->assertSee('Standalone readiness')
            ->assertSee('Installer status')
            ->assertSee('License status')
            ->assertSee('Backup status')
            ->assertSee('Update readiness')
            ->assertSee('Standalone sync/offline')
            ->assertSee('Safe health output')
            ->assertSee('Configured endpoint / Configured token')
            ->assertDontSee('database-secret-value')
            ->assertDontSee('smtp-secret-value')
            ->assertDontSee('sync-token-secret-value')
            ->assertDontSee('https://sync.example.test/private-endpoint');
    }

    public function test_unauthorized_user_cannot_access_standalone_status_page(): void
    {
        config([
            'standalone.sync.endpoint' => 'https://sync.example.test/private-endpoint',
            'standalone.sync.token' => 'sync-token-secret-value',
        ]);

        $schoolAdmin = $this->userWithRole('school_admin');

        $this->actingAs($schoolAdmin)
            ->get(route('admin.standalone.status'))
            ->assertForbidden()
            ->assertDontSee('sync-token-secret-value')
            ->assertDontSee('https://sync.example.test/private-endpoint');
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
