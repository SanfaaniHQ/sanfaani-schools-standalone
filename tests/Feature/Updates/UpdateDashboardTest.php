<?php

namespace Tests\Feature\Updates;

use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Updates\UpdateLogService;
use App\Services\Updates\UpdateManifestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasUpdates();
    }

    public function test_update_manager_dashboard_renders_when_update_manager_is_enabled(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertOk()
            ->assertSee('Platform Updates')
            ->assertSee('Application of updates is planned and not implemented');
    }

    public function test_update_manager_dashboard_is_blocked_when_update_manager_is_disabled(): void
    {
        config([
            'features.features.update_manager.enabled' => false,
            'updates.enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_access_update_manager(): void
    {
        Role::findOrCreate('school_admin');

        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.updates.index'))
            ->assertForbidden();
    }

    public function test_update_pages_require_authenticated_authorized_access(): void
    {
        $package = $this->package();

        $this->get(route('admin.updates.index'))->assertRedirect('/login');
        $this->get(route('admin.updates.upload'))->assertRedirect('/login');
        $this->get(route('admin.updates.show', $package))->assertRedirect('/login');
        $this->post(route('admin.updates.store'))->assertRedirect('/login');
        $this->post(route('admin.updates.preflight', $package))->assertRedirect('/login');
        $this->post(route('admin.updates.mark-ready', $package))->assertRedirect('/login');

        Role::findOrCreate('school_admin');
        $user = User::factory()->create();
        $user->assignRole('school_admin');

        $this->actingAs($user)
            ->get(route('admin.updates.upload'))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('admin.updates.store'))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('admin.updates.preflight', $package))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('admin.updates.mark-ready', $package))
            ->assertForbidden();
    }

    public function test_update_ui_does_not_expose_env_secrets(): void
    {
        $package = $this->package();

        app(UpdateLogService::class)->log(
            'update.secret_test',
            'APP_KEY=base64:secret-value should be hidden',
            $package,
            context: [
                'APP_KEY' => 'base64:secret-value',
                'password' => 'database-password',
                'note' => 'safe context',
            ],
        );

        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.show', $package))
            ->assertOk()
            ->assertDontSee('base64:secret-value')
            ->assertDontSee('database-password')
            ->assertSee('[redacted]');
    }

    public function test_shared_hosting_guidance_is_visible(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertOk()
            ->assertSee('cPanel')
            ->assertSee('Namecheap')
            ->assertSee('maintenance mode');
    }

    public function test_command_check_is_safe_and_does_not_require_network(): void
    {
        $this->artisan('updates:check')
            ->expectsOutput('Update check completed safely.')
            ->expectsOutput('Channel: stable')
            ->expectsOutput('Server configured: no')
            ->expectsOutput('No external network request was made.')
            ->assertExitCode(0);
    }

    private function package(): UpdatePackage
    {
        $checksum = str_repeat('c', 64);
        $manifest = array_merge(app(UpdateManifestService::class)->sample(), [
            'version' => '1.0.3',
            'checksum' => $checksum,
            'minimum_laravel' => app()->version(),
        ]);

        return UpdatePackage::create([
            'version' => $manifest['version'],
            'channel' => $manifest['channel'],
            'source' => 'upload',
            'filename' => 'safe.zip',
            'path' => 'packages/safe.zip',
            'checksum' => $checksum,
            'signature' => $manifest['signature'],
            'size_bytes' => 1024,
            'status' => UpdatePackage::STATUS_VALIDATED,
            'manifest' => $manifest,
            'validated_at' => now(),
            'metadata' => ['extracted' => false, 'applied' => false],
        ]);
    }

    private function configureSaasUpdates(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.updates_enabled' => true,
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
            'updates.require_license_entitlement' => true,
        ]);
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
