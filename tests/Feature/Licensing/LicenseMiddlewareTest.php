<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\User;
use App\Services\Licensing\LicenseKeyHasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LicenseMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.license_validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);

        Route::middleware('license.valid')->get('/__licensed-route', fn () => 'licensed')->name('__licensed-route');
    }

    public function test_license_valid_middleware_blocks_invalid_license(): void
    {
        $this->withHeader('Host', 'licensed.test')
            ->get('/__licensed-route')
            ->assertForbidden();
    }

    public function test_license_valid_middleware_redirects_admin_to_license_status(): void
    {
        Role::findOrCreate('super_admin');

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->withHeader('Host', 'licensed.test')
            ->get('/__licensed-route')
            ->assertRedirect(route('admin.license.index'));
    }

    public function test_license_valid_middleware_allows_valid_license(): void
    {
        License::create([
            'license_key_hash' => app(LicenseKeyHasher::class)->hash('MIDDLEWARE-KEY'),
            'license_type' => 'annual',
            'status' => 'active',
            'domain' => null,
            'allowed_domains' => [],
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->withHeader('Host', 'licensed.test')
            ->get('/__licensed-route')
            ->assertOk()
            ->assertSee('licensed');
    }
}
