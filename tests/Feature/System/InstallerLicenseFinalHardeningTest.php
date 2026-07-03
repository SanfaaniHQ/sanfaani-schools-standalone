<?php

namespace Tests\Feature\System;

use App\Models\AuditLog;
use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Installer\InstallerStateService;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InstallerLicenseFinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        File::delete(storage_path('app/installed.lock'));

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'standalone.product_edition' => 'standalone',
            'standalone.installer_enabled' => true,
            'standalone.installed' => false,
            'installer.enabled' => true,
            'features.features.standalone_installer.enabled' => true,
            'features.features.license_activation.enabled' => true,
            'sanfaani.license_validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/installed.lock'));

        parent::tearDown();
    }

    public function test_installer_diagnostics_hide_secrets_and_private_paths(): void
    {
        $this->enableInstaller();

        config([
            'app.key' => 'base64:MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=',
            'database.connections.sqlite.database' => base_path('database/database.sqlite'),
            'database.connections.sqlite.password' => 'stage21-db-secret',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.password' => 'stage21-mail-secret',
            'mail.from.address' => 'school@example.test',
            'licensing.license_key' => 'stage21-license-secret',
        ]);

        $this->get(route('installer.environment'))
            ->assertOk()
            ->assertSee('Mail configuration')
            ->assertSee('Session driver configured')
            ->assertSee('Backup metadata directory')
            ->assertSee('Update package directory')
            ->assertDontSee('MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI')
            ->assertDontSee('stage21-db-secret')
            ->assertDontSee('stage21-mail-secret')
            ->assertDontSee('stage21-license-secret')
            ->assertDontSee(base_path())
            ->assertDontSee(storage_path());

        $this->completeInstallerForms();

        $this->get(route('installer.review'))
            ->assertOk()
            ->assertSee('Support-safe diagnostics')
            ->assertSee('Secrets and private paths hidden')
            ->assertDontSee('MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI')
            ->assertDontSee('stage21-mail-secret')
            ->assertDontSee('mail-secret')
            ->assertDontSee(base_path())
            ->assertDontSee(storage_path());
    }

    public function test_installer_check_audit_uses_safe_metadata_when_database_is_available(): void
    {
        $this->enableInstaller();

        $this->get(route('installer.requirements'))->assertOk();

        $payload = AuditLog::query()
            ->where('action', 'installer_check_ran')
            ->latest('id')
            ->firstOrFail()
            ->toJson();

        $this->assertStringContainsString('requirements', $payload);
        $this->assertStringNotContainsString(base_path(), $payload);
        $this->assertStringNotContainsString(storage_path(), $payload);
        $this->assertStringNotContainsString('APP_KEY', $payload);
    }

    public function test_license_routes_are_admin_only(): void
    {
        $this->get(route('admin.license.index'))->assertRedirect(route('login'));

        Role::findOrCreate('teacher');
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->actingAs($teacher)
            ->get(route('admin.license.index'))
            ->assertForbidden();
    }

    public function test_license_activation_rejects_bad_key_and_does_not_audit_raw_key(): void
    {
        $school = $this->school();
        $rawKey = 'BAD KEY WITH SPACES AND stage21-secret-note';

        $this->actingAs($this->superAdmin())
            ->from(route('admin.license.activate'))
            ->post(route('admin.license.store'), $this->activationPayload($rawKey, $school))
            ->assertRedirect(route('admin.license.activate'))
            ->assertSessionHasErrors('license_key');

        $this->assertDatabaseCount('licenses', 0);

        $payload = AuditLog::query()
            ->whereIn('action', ['license_activation_attempted', 'license_activation_failed'])
            ->get()
            ->toJson();

        $this->assertStringContainsString('validation_failed', $payload);
        $this->assertStringNotContainsString($rawKey, $payload);
        $this->assertStringNotContainsString('stage21-secret-note', $payload);
    }

    public function test_license_status_redacts_key_and_shows_safe_module_diagnostics(): void
    {
        $school = $this->school();
        $admin = $this->superAdmin();
        $rawKey = 'SANFAANI-STAGE21-LICENSE-1234';

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->post(route('admin.license.store'), $this->activationPayload($rawKey, $school))
            ->assertRedirect(route('admin.license.index'));

        $this->assertDatabaseHas('licenses', [
            'license_key_hash' => app(LicenseKeyHasher::class)->hash($rawKey),
        ]);

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->get(route('admin.license.index'))
            ->assertOk()
            ->assertSee('Support-safe diagnostics')
            ->assertSee('Key storage')
            ->assertSee('Hashed and masked')
            ->assertSee('Module access')
            ->assertSee('Advanced Reports')
            ->assertSee('Disabled by license')
            ->assertSee('Remote license server')
            ->assertSee('Not enabled')
            ->assertDontSee($rawKey)
            ->assertDontSee(config('app.key'));

        $payload = AuditLog::query()
            ->whereIn('action', ['license_activation_attempted', 'license_activation_succeeded', 'license_status_viewed', 'license_entitlements_viewed'])
            ->get()
            ->toJson();

        $this->assertStringContainsString('license_activation_succeeded', $payload);
        $this->assertStringNotContainsString($rawKey, $payload);
    }

    public function test_local_testing_mode_is_not_hard_blocked_by_missing_license(): void
    {
        config(['app.env' => 'testing']);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.license.index'))
            ->assertOk()
            ->assertSee('No school license has been activated yet')
            ->assertSee('Local/test safety')
            ->assertSee('Not hard-blocked');
    }

    public function test_standalone_dashboard_marks_installer_hardening_available(): void
    {
        $this->school();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Installer hardening')
            ->assertSee('Support-safe installer diagnostics')
            ->assertSee('Installer final hardening');
    }

    private function completeInstallerForms(): void
    {
        $this->post(route('installer.admin.store'), [
            'name' => 'Local Owner',
            'email' => 'owner@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('installer.school'));

        $this->post(route('installer.school.store'), [
            'name' => 'Local School',
            'slug' => 'local-school',
            'email' => 'school@example.test',
            'phone' => '08000000000',
            'address' => 'Installer Road',
            'school_motto' => 'Learn well',
        ])->assertRedirect(route('installer.smtp'));

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

    private function activationPayload(string $rawKey, School $school): array
    {
        return [
            'license_key' => $rawKey,
            'license_type' => 'annual',
            'status' => 'active',
            'school_id' => $school->id,
            'issued_to_name' => $school->name,
            'issued_to_email' => $school->email,
            'domain' => 'licensed.test',
            'allowed_domains' => 'licensed.test',
            'features' => 'cbt,result_publication',
            'entitlements' => 'advanced_reports:false,white_label_branding',
            'starts_at' => now()->subDay()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
        ];
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

        File::delete(app(InstallerStateService::class)->lockPath());
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Stage 21 Academy',
            'slug' => 'stage-21-academy',
            'email' => 'school@example.test',
            'status' => 'active',
            'subscription_status' => 'active',
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
