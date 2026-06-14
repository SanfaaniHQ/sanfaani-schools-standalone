<?php

namespace Tests\Feature\Installer;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Installer\InstallerDatabaseService;
use App\Services\Installer\InstallerRequirementsService;
use App\Services\Installer\InstallerStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InstallerFlowTest extends TestCase
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

    public function test_requirements_page_renders(): void
    {
        $this->get(route('installer.requirements'))
            ->assertOk()
            ->assertSee('Requirements Check')
            ->assertSee('PHP version');
    }

    public function test_permission_checks_classify_required_and_warning_checks(): void
    {
        $checks = collect(app(InstallerRequirementsService::class)->permissions());

        $this->assertTrue($checks->firstWhere('label', 'Storage folder writable')['required']);
        $this->assertTrue($checks->firstWhere('label', 'Bootstrap cache writable')['required']);
        $this->assertFalse($checks->firstWhere('label', 'Public storage link')['required']);
        $this->assertContains($checks->firstWhere('label', 'Public storage link')['status'], ['pass', 'warning']);
    }

    public function test_database_check_does_not_expose_secrets(): void
    {
        config([
            'database.connections.sqlite.username' => 'installer-secret-user',
            'database.connections.sqlite.password' => 'installer-secret-password',
        ]);

        $payload = json_encode(app(InstallerDatabaseService::class)->status());

        $this->assertStringNotContainsString('installer-secret-user', $payload);
        $this->assertStringNotContainsString('installer-secret-password', $payload);
    }

    public function test_admin_setup_validates_required_fields(): void
    {
        $this->post(route('installer.admin.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_school_setup_validates_required_fields(): void
    {
        $this->post(route('installer.school.store'), [])
            ->assertSessionHasErrors(['name']);
    }

    public function test_installation_finalization_creates_installed_lock(): void
    {
        $this->completeInstallerForms();

        $this->post(route('installer.complete'))
            ->assertOk()
            ->assertSee('School portal is ready');

        $this->assertFileExists(app(InstallerStateService::class)->lockPath());
        $this->assertDatabaseHas('schools', ['slug' => 'local-school']);
        $this->assertDatabaseHas('users', ['email' => 'owner@example.test']);
        $this->assertTrue(User::where('email', 'owner@example.test')->first()->hasRole('super_admin'));
    }

    public function test_finalization_prevents_reinstall(): void
    {
        $this->completeInstallerForms();
        $this->post(route('installer.complete'))->assertOk();

        $this->get(route('installer.admin'))->assertNotFound();
        $this->post(route('installer.complete'))->assertNotFound();
    }

    public function test_single_school_setup_creates_only_one_school(): void
    {
        School::create([
            'name' => 'Existing Local School',
            'slug' => 'existing-local-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $this->completeInstallerForms([
            'name' => 'Updated Local School',
            'slug' => 'updated-local-school',
        ]);

        $this->post(route('installer.complete'))->assertOk();

        $this->assertSame(1, School::count());
        $this->assertDatabaseHas('schools', ['slug' => 'updated-local-school']);
        $this->assertDatabaseCount('user_school_roles', 1);
        $this->assertSame('school_admin', UserSchoolRole::first()->role_name);
    }

    private function completeInstallerForms(array $schoolOverrides = []): void
    {
        $this->post(route('installer.admin.store'), [
            'name' => 'Local Owner',
            'email' => 'owner@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('installer.school'));

        $this->post(route('installer.school.store'), array_merge([
            'name' => 'Local School',
            'slug' => 'local-school',
            'email' => 'school@example.test',
            'phone' => '08000000000',
            'address' => 'Installer Road',
            'school_motto' => 'Learn well',
        ], $schoolOverrides))->assertRedirect(route('installer.smtp'));

        $this->post(route('installer.smtp.store'), [
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'username' => 'mailer@example.test',
            'password' => 'mail-secret',
            'encryption' => 'tls',
            'from_address' => 'mailer@example.test',
            'from_name' => 'Local School',
        ])->assertRedirect(route('installer.review'));
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
