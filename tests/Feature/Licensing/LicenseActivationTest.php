<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\Licensing\LicenseValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LicenseActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'sanfaani.license_validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);
    }

    public function test_license_key_is_hashed_and_raw_key_is_not_stored(): void
    {
        $school = $this->school();
        $admin = $this->superAdmin();
        $rawKey = 'SANFAANI-RAW-SECRET-1234';

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->post(route('admin.license.store'), $this->activationPayload($rawKey, $school))
            ->assertRedirect(route('admin.license.index'));

        $license = License::firstOrFail();

        $this->assertSame(app(LicenseKeyHasher::class)->hash($rawKey), $license->license_key_hash);
        $this->assertStringNotContainsString($rawKey, json_encode($license->toArray()));
        $this->assertDatabaseMissing('licenses', ['license_key_hash' => $rawKey]);
    }

    public function test_license_status_page_does_not_show_raw_key(): void
    {
        $school = $this->school();
        $admin = $this->superAdmin();
        $rawKey = 'SANFAANI-STATUS-SECRET-5678';

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->post(route('admin.license.store'), $this->activationPayload($rawKey, $school));

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->get(route('admin.license.index'))
            ->assertOk()
            ->assertSee('Stored key')
            ->assertDontSee($rawKey);
    }

    public function test_license_activation_page_uses_customer_ready_copy(): void
    {
        $school = $this->school();
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->get(route('admin.license.activate'))
            ->assertOk()
            ->assertSee('Activate your school license')
            ->assertSee('Seller-only signing keys are not entered on this customer portal.')
            ->assertDontSee('hashed before storage')
            ->assertDontSee('foundation');
    }

    public function test_activation_creates_audit_log(): void
    {
        $school = $this->school();

        $this->actingAs($this->superAdmin())
            ->withServerVariables(['HTTP_HOST' => 'licensed.test'])
            ->post(route('admin.license.store'), $this->activationPayload('SANFAANI-AUDIT-1234', $school))
            ->assertRedirect(route('admin.license.index'));

        $this->assertDatabaseHas('license_audit_logs', ['event' => 'license.created']);
        $this->assertDatabaseHas('license_audit_logs', ['event' => 'license.activated']);
    }

    public function test_validation_failure_creates_audit_log(): void
    {
        $school = $this->school();

        License::create([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('EXPIRED-AUDIT-KEY'),
            'license_type' => 'annual',
            'status' => 'active',
            'domain' => 'licensed.test',
            'allowed_domains' => ['licensed.test'],
            'expires_at' => now()->subDays(10),
        ]);

        $this->setHost('licensed.test');

        $result = app(LicenseValidationService::class)->validate($school);

        $this->assertFalse($result->valid());
        $this->assertDatabaseHas('license_audit_logs', ['event' => 'license.expired']);
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
            'entitlements' => 'advanced_reports',
            'starts_at' => now()->subDay()->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
        ];
    }

    private function setHost(string $host): void
    {
        request()->headers->set('host', $host);
        request()->server->set('HTTP_HOST', $host);
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Licensed School',
            'slug' => 'licensed-school',
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
