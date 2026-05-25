<?php

namespace Tests\Feature\Updates;

use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateEntitlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_valid_license_with_update_entitlement_allows_access(): void
    {
        $school = $this->configureLicensedInstall();
        $this->license($school, ['entitlements' => ['update_manager' => true]]);

        $this->actingAs($this->superAdmin())
            ->withHeader('Host', 'licensed.test')
            ->get(route('admin.updates.index'))
            ->assertOk()
            ->assertSee('Guided Updates');
    }

    public function test_missing_update_entitlement_blocks_access_when_required(): void
    {
        $school = $this->configureLicensedInstall();
        $this->license($school, ['entitlements' => []]);

        $this->actingAs($this->superAdmin())
            ->withHeader('Host', 'licensed.test')
            ->get(route('admin.updates.index'))
            ->assertForbidden();
    }

    public function test_expired_license_blocks_update_manager_when_validation_is_required(): void
    {
        $school = $this->configureLicensedInstall();
        $this->license($school, [
            'entitlements' => ['update_manager' => true],
            'expires_at' => now()->subDays(2),
            'offline_grace_until' => null,
        ]);

        $this->actingAs($this->superAdmin())
            ->withHeader('Host', 'licensed.test')
            ->get(route('admin.updates.index'))
            ->assertRedirect(route('admin.license.index'));
    }

    public function test_suspended_license_blocks_update_manager_when_validation_is_required(): void
    {
        $school = $this->configureLicensedInstall();
        $this->license($school, [
            'status' => 'suspended',
            'suspended_at' => now(),
            'entitlements' => ['update_manager' => true],
        ]);

        $this->actingAs($this->superAdmin())
            ->withHeader('Host', 'licensed.test')
            ->get(route('admin.updates.index'))
            ->assertRedirect(route('admin.license.index'));
    }

    public function test_demo_mode_cannot_access_update_manager(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'demo',
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertNotFound();
    }

    private function configureLicensedInstall(): School
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.updates_enabled' => true,
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
            'updates.require_license_entitlement' => true,
            'licensing.validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);

        return School::create([
            'name' => 'Licensed Update School',
            'slug' => 'licensed-update-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function license(School $school, array $overrides = []): License
    {
        return License::create(array_merge([
            'school_id' => $school->id,
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('UPDATE-LICENSE-'.$school->id),
            'license_type' => 'annual',
            'status' => 'active',
            'domain' => null,
            'allowed_domains' => [],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'features' => [],
            'entitlements' => ['update_manager' => true],
        ], $overrides));
    }

    private function superAdmin(): User
    {
        Role::findOrCreate('super_admin');

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
