<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\School;
use App\Models\User;
use App\Services\Licensing\LicenseKeyHasher;
use App\Services\Licensing\LicenseValidationService;
use App\Services\Licensing\SignedLicenseKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LicenseGenerationTest extends TestCase
{
    use RefreshDatabase;

    private string $signingKey = 'test-signing-secret-never-print';

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'licensing.signing_key' => $this->signingKey,
            'sanfaani.license_validation_enabled' => true,
            'licensing.require_domain_match' => true,
        ]);
    }

    public function test_license_generator_command_exists(): void
    {
        $this->assertArrayHasKey('license:generate', Artisan::all());
    }

    public function test_annual_license_generation_includes_expiry(): void
    {
        $payload = $this->payload([
            'type' => 'annual',
            'expires' => now()->addYear()->toDateString(),
        ]);

        $this->assertSame('annual', $payload['type']);
        $this->assertNotNull($payload['expires_at']);
    }

    public function test_lifetime_license_generation_can_omit_expiry(): void
    {
        $payload = $this->payload([
            'type' => 'lifetime',
            'expires' => null,
        ]);

        $this->assertSame('lifetime', $payload['type']);
        $this->assertNull($payload['expires_at']);
    }

    public function test_generated_license_is_domain_bound(): void
    {
        $payload = $this->payload([
            'domain' => 'Portal.Demo-School.Test',
            'expires' => now()->addYear()->toDateString(),
        ]);

        $this->assertSame('portal.demo-school.test', $payload['domain']);
        $this->assertContains('portal.demo-school.test', $payload['allowed_domains']);
    }

    public function test_generated_license_includes_entitlements(): void
    {
        $payload = $this->payload([
            'entitlements' => 'standard,white_label,reports',
            'expires' => now()->addYear()->toDateString(),
        ]);

        $this->assertSame([
            'reports' => true,
            'standard' => true,
            'white_label' => true,
        ], $payload['entitlements']);
    }

    public function test_generator_output_does_not_print_signing_secret(): void
    {
        $exitCode = Artisan::call('license:generate', [
            '--type' => 'annual',
            '--school' => 'Demo School',
            '--domain' => 'portal.demo-school.test',
            '--starts' => now()->toDateString(),
            '--expires' => now()->addYear()->toDateString(),
            '--entitlements' => 'standard,white_label',
            '--issued-by' => 'Sanfaani',
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('License key:', $output);
        $this->assertStringNotContainsString($this->signingKey, $output);
    }

    public function test_activation_accepts_generated_signed_license_key(): void
    {
        $school = $this->school();
        $key = $this->licenseKey([
            'school' => $school->name,
            'domain' => 'portal.demo-school.test',
            'expires' => now()->addYear()->toDateString(),
            'entitlements' => 'standard,white_label',
        ]);

        $this->actingAs($this->superAdmin())
            ->withServerVariables(['HTTP_HOST' => 'portal.demo-school.test'])
            ->post(route('admin.license.store'), [
                'license_key' => $key,
                'license_type' => 'annual',
                'status' => 'active',
                'school_id' => $school->id,
            ])
            ->assertRedirect(route('admin.license.index'));

        $license = License::firstOrFail();

        $this->assertSame(app(LicenseKeyHasher::class)->hash($key), $license->license_key_hash);
        $this->assertSame('annual', $license->license_type);
        $this->assertSame('portal.demo-school.test', $license->domain);
        $this->assertTrue((bool) $license->entitlements['white_label']);
        $this->assertStringNotContainsString($key, json_encode($license->toArray()));
    }

    public function test_invalid_tampered_license_fails(): void
    {
        $key = $this->licenseKey(['expires' => now()->addYear()->toDateString()]);
        $tampered = substr($key, 0, -1).(str_ends_with($key, 'a') ? 'b' : 'a');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('signature is invalid');

        app(SignedLicenseKeyService::class)->verify($tampered);
    }

    public function test_expired_generated_license_fails_activation(): void
    {
        $school = $this->school();
        $key = $this->licenseKey([
            'school' => $school->name,
            'starts' => '2024-01-01',
            'expires' => '2024-12-31',
        ]);

        $this->actingAs($this->superAdmin())
            ->withServerVariables(['HTTP_HOST' => 'portal.demo-school.test'])
            ->from(route('admin.license.activate'))
            ->post(route('admin.license.store'), [
                'license_key' => $key,
                'license_type' => 'annual',
                'status' => 'active',
                'school_id' => $school->id,
            ])
            ->assertRedirect(route('admin.license.activate'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('licenses', 0);
    }

    public function test_domain_mismatch_fails_when_domain_matching_is_enabled(): void
    {
        $school = $this->school();
        $key = $this->licenseKey([
            'school' => $school->name,
            'domain' => 'portal.demo-school.test',
            'expires' => now()->addYear()->toDateString(),
        ]);

        $this->actingAs($this->superAdmin())
            ->withServerVariables(['HTTP_HOST' => 'other.test'])
            ->post(route('admin.license.store'), [
                'license_key' => $key,
                'license_type' => 'annual',
                'status' => 'active',
                'school_id' => $school->id,
            ])
            ->assertRedirect(route('admin.license.index'));

        $this->setHost('other.test');

        $this->assertSame('domain_mismatch', app(LicenseValidationService::class)->validate($school)->status);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return app(SignedLicenseKeyService::class)->verify($this->licenseKey($overrides));
    }

    private function licenseKey(array $overrides = []): string
    {
        return app(SignedLicenseKeyService::class)->generate(array_merge([
            'type' => 'annual',
            'school' => 'Demo School',
            'domain' => 'portal.demo-school.test',
            'starts' => now()->toDateString(),
            'expires' => now()->addYear()->toDateString(),
            'entitlements' => 'standard',
            'max_schools' => 1,
            'issued_by' => 'Sanfaani',
        ], $overrides))['license_key'];
    }

    private function school(): School
    {
        return School::create([
            'name' => 'Demo School '.School::count(),
            'slug' => 'demo-school-'.(School::count() + 1),
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

    private function setHost(string $host): void
    {
        request()->headers->set('host', $host);
        request()->server->set('HTTP_HOST', $host);
    }
}
