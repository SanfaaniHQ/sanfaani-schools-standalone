<?php

namespace Tests\Feature\Installer;

use App\Services\Installer\InstallerStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InstallerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->deleteInstallerLock();
        $this->enableInstaller();
    }

    protected function tearDown(): void
    {
        $this->deleteInstallerLock();

        parent::tearDown();
    }

    public function test_installer_blocked_in_saas_mode(): void
    {
        config(['sanfaani.deployment.mode' => 'saas']);

        $this->get(route('installer.welcome'))->assertNotFound();
    }

    public function test_installer_allowed_in_single_school_mode_when_feature_enabled_and_not_installed(): void
    {
        $this->get(route('installer.welcome'))
            ->assertOk()
            ->assertSee('Welcome')
            ->assertSee('Standalone installer');
    }

    public function test_fresh_single_school_uninstalled_can_access_required_installer_steps(): void
    {
        $this->get(route('installer.welcome'))->assertOk();
        $this->get(route('installer.requirements'))->assertOk();
        $this->get(route('installer.school'))->assertOk();
    }

    public function test_installer_does_not_require_feature_gate_rows_before_installation(): void
    {
        config(['features.features.standalone_installer.enabled' => false]);

        $this->get(route('installer.welcome'))->assertOk();
    }

    public function test_login_redirects_to_installer_before_installation(): void
    {
        $this->get(route('login'))
            ->assertRedirect(route('installer.welcome'));
    }

    public function test_installer_blocked_when_installed_lock_exists(): void
    {
        File::put(app(InstallerStateService::class)->lockPath(), json_encode(['installed_at' => now()->toIso8601String()]));

        $this->get(route('installer.welcome'))->assertNotFound();
    }

    public function test_installer_fails_closed_when_deployment_mode_is_unknown(): void
    {
        config(['sanfaani.deployment.mode' => 'client_specific_mode']);

        $this->get(route('installer.welcome'))->assertNotFound();
    }

    private function enableInstaller(): void
    {
        config([
            'installer.enabled' => true,
            'installer.allow_managed' => false,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => false,
            'features.features.standalone_installer.enabled' => true,
        ]);
    }

    private function deleteInstallerLock(): void
    {
        File::delete(storage_path('app/installed.lock'));
    }
}
