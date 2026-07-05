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
use Spatie\Permission\Models\Role;
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

    public function test_installation_finalization_requires_no_license_key(): void
    {
        $this->completeInstallerForms();

        $this->post(route('installer.complete'))
            ->assertOk()
            ->assertSee('School portal is ready')
            ->assertDontSee('License key')
            ->assertDontSee('Activate license');

        $this->assertFileExists(app(InstallerStateService::class)->lockPath());
        $this->assertDatabaseHas('schools', ['slug' => 'local-school']);
        $this->assertDatabaseHas('users', ['email' => 'owner@example.test']);
        $this->assertDatabaseCount('licenses', 0);
        $installationMetadata = app(InstallerStateService::class)->installationMetadata();
        $this->assertArrayNotHasKey('license_mode', $installationMetadata);
        $this->assertArrayNotHasKey('password', data_get($installationMetadata, 'smtp_placeholder', []));
        $this->assertStringNotContainsString('mail-secret', json_encode($installationMetadata));
        $owner = User::where('email', 'owner@example.test')->first();
        $this->assertTrue($owner->hasRole('super_admin'));
        $this->assertTrue($owner->hasRole('school_admin'));
        $this->assertTrue(Role::findByName('school_admin')->hasPermissionTo('school.mail.manage'));
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $owner->id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->post('/admin/login', [
            'email' => 'owner@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', [
            'email' => 'owner@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_finalization_prevents_reinstall(): void
    {
        $this->completeInstallerForms();
        $this->post(route('installer.complete'))->assertOk();

        $this->get(route('installer.admin'))->assertNotFound();
        $this->post(route('installer.complete'))->assertNotFound();
    }

    public function test_separate_installation_and_school_admin_emails_create_scoped_accounts(): void
    {
        $this->post(route('installer.admin.store'), [
            'name' => 'Installation Owner',
            'email' => 'installation@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'separate_school_admin' => '1',
            'school_admin_name' => 'School Owner',
            'school_admin_email' => 'school-admin@example.test',
            'school_admin_password' => 'password456',
            'school_admin_password_confirmation' => 'password456',
        ])->assertRedirect(route('installer.school'));

        $this->post(route('installer.school.store'), [
            'name' => 'Separate Accounts School',
            'slug' => 'separate-accounts-school',
            'email' => 'school@example.test',
        ])->assertRedirect(route('installer.smtp'));

        $this->post(route('installer.smtp.store'), ['mailer' => 'log'])
            ->assertRedirect(route('installer.review'));
        $this->post(route('installer.complete'))->assertOk();

        $installationAdmin = User::where('email', 'installation@example.test')->firstOrFail();
        $schoolAdmin = User::where('email', 'school-admin@example.test')->firstOrFail();

        $this->assertTrue($installationAdmin->hasRole('super_admin'));
        $this->assertFalse($installationAdmin->hasRole('school_admin'));
        $this->assertNull($installationAdmin->school_id);
        $this->assertTrue($schoolAdmin->hasRole('school_admin'));
        $this->assertFalse($schoolAdmin->hasRole('super_admin'));
        $this->assertNotNull($schoolAdmin->school_id);
        $this->assertDatabaseHas('user_school_roles', [
            'user_id' => $schoolAdmin->id,
            'school_id' => $schoolAdmin->school_id,
            'role_name' => 'school_admin',
            'status' => 'active',
        ]);

        $this->post('/admin/login', [
            'email' => 'installation@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->post('/logout')->assertRedirect('/');
        $this->post('/login', [
            'email' => 'school-admin@example.test',
            'password' => 'password456',
        ])->assertRedirect(route('dashboard', absolute: false));
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
            'sanfaani.license_validation_enabled' => false,
            'licensing.signing_key' => '',
            'sanfaani.deployment.installed' => false,
            'features.features.standalone_installer.enabled' => true,
        ]);
    }

    private function deleteInstallerLock(): void
    {
        File::delete(storage_path('app/installed.lock'));
    }
}
