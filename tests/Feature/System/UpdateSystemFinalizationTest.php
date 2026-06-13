<?php

namespace Tests\Feature\System;

use App\Models\AuditLog;
use App\Models\UpdatePackage;
use App\Models\User;
use App\Services\Standalone\StandaloneSystemHealthService;
use App\Services\Updates\UpdateManifestService;
use App\Services\Updates\UpdatePreflightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdateSystemFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('updates');
        $this->configureSaasUpdates();
    }

    public function test_update_routes_require_admin_access(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertOk()
            ->assertSee('Platform Updates');

        $this->assertDatabaseHas('audit_logs', ['action' => 'update_center_viewed']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'update_history_viewed']);
    }

    public function test_public_cannot_access_update_pages(): void
    {
        $package = $this->package();

        $this->get(route('admin.updates.index'))->assertRedirect(route('login'));
        $this->get(route('admin.updates.upload'))->assertRedirect(route('login'));
        $this->get(route('admin.updates.show', $package))->assertRedirect(route('login'));
        $this->post(route('admin.updates.preflight', $package))->assertRedirect(route('login'));
    }

    public function test_school_roles_cannot_manage_updates(): void
    {
        foreach (['school_admin', 'teacher', 'accountant', 'result_officer'] as $role) {
            $this->actingAs($this->roleUser($role))
                ->get(route('admin.updates.index'))
                ->assertForbidden();
        }
    }

    public function test_update_center_displays_safe_current_version_status(): void
    {
        $appKey = $this->validAppKey('stage22-app-key-secret');

        config([
            'version.version' => '2.4.6',
            'app.key' => $appKey,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.updates.index'))
            ->assertOk()
            ->assertSee('2.4.6')
            ->assertSee('Entitlement status')
            ->assertDontSee($appKey)
            ->assertDontSee('stage22-app-key-secret')
            ->assertDontSee(base_path())
            ->assertDontSee(storage_path());
    }

    public function test_package_validation_rejects_unsafe_extension_or_type(): void
    {
        $this->actingAs($this->superAdmin())
            ->from(route('admin.updates.upload'))
            ->post(route('admin.updates.store'), [
                'package' => UploadedFile::fake()->create('stage22.exe', 1, 'application/octet-stream'),
                'manifest_json' => json_encode($this->manifest(str_repeat('a', 64))),
            ])
            ->assertRedirect(route('admin.updates.upload'))
            ->assertSessionHasErrors('package');
    }

    public function test_package_validation_rejects_invalid_or_missing_manifest(): void
    {
        $file = $this->zipUpload('missing-manifest.zip');

        $this->actingAs($this->superAdmin())
            ->from(route('admin.updates.upload'))
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => '{}',
            ])
            ->assertRedirect(route('admin.updates.upload'))
            ->assertSessionHasErrors('package');
    }

    public function test_manifest_validation_detects_incompatible_edition_version_and_protected_paths(): void
    {
        $manifest = $this->manifest(str_repeat('b', 64), [
            'target_product' => 'Different Product',
            'target_edition' => 'cloud_only',
            'minimum_current_version' => '99.0.0',
            'files_changed' => [
                '../storage/logs/laravel.log',
                '.env',
                'public/build.zip',
                'database/migrations/2026_05_01_173857_create_result_publications_table.php',
            ],
        ]);

        $errors = app(UpdateManifestService::class)->validate($manifest);

        $this->assertContains('Manifest target product does not match this installation.', $errors);
        $this->assertContains('Manifest target edition does not match this installation.', $errors);
        $this->assertContains('Current application version is below the manifest minimum version.', $errors);
        $this->assertNotEmpty(array_filter($errors, fn (string $error): bool => str_contains($error, 'contains traversal segments')));
        $this->assertNotEmpty(array_filter($errors, fn (string $error): bool => str_contains($error, 'targets environment configuration')));
        $this->assertNotEmpty(array_filter($errors, fn (string $error): bool => str_contains($error, 'targets a protected file')));
    }

    public function test_diagnostics_redact_secrets_and_private_paths(): void
    {
        $appKey = $this->validAppKey('stage22-diagnostic-app-key');

        config([
            'app.key' => $appKey,
            'database.connections.sqlite.password' => 'stage22-db-password',
            'mail.mailers.smtp.password' => 'stage22-mail-password',
            'licensing.license_key' => 'stage22-license-key',
        ]);

        $json = json_encode(app(StandaloneSystemHealthService::class)->summary(), JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString($appKey, $json);
        $this->assertStringNotContainsString('stage22-diagnostic-app-key', $json);
        $this->assertStringNotContainsString('stage22-db-password', $json);
        $this->assertStringNotContainsString('stage22-mail-password', $json);
        $this->assertStringNotContainsString('stage22-license-key', $json);
        $this->assertStringNotContainsString(base_path(), $json);
        $this->assertStringNotContainsString(storage_path(), $json);
    }

    public function test_preflight_warns_when_backup_is_missing(): void
    {
        $result = app(UpdatePreflightService::class)->run($this->package());
        $backup = collect($result->checks())->firstWhere('key', 'backup_requirement');

        $this->assertSame('pending', $backup['status']);
        $this->assertTrue($backup['blocks']);
        $this->assertStringContainsString('verified backup', $backup['message']);
    }

    public function test_preflight_reports_readiness_status_safely(): void
    {
        $appKey = $this->validAppKey('stage22-preflight-app-key');

        config(['app.key' => $appKey]);

        $result = app(UpdatePreflightService::class)->run($this->package());
        $keys = collect($result->checks())->pluck('key')->all();
        $json = json_encode($result->toArray(), JSON_THROW_ON_ERROR);

        $this->assertContains('current_version', $keys);
        $this->assertContains('compatibility', $keys);
        $this->assertContains('database_status', $keys);
        $this->assertContains('license_status', $keys);
        $this->assertContains('installer_status', $keys);
        $this->assertContains('storage_writable', $keys);
        $this->assertContains('update_package_directory', $keys);
        $this->assertStringNotContainsString($appKey, $json);
        $this->assertStringNotContainsString('stage22-preflight-app-key', $json);
        $this->assertStringNotContainsString(base_path(), $json);
        $this->assertStringNotContainsString(storage_path(), $json);
    }

    public function test_update_review_and_audit_logs_do_not_expose_raw_package_payloads(): void
    {
        $file = $this->zipUpload('stage22-review.zip', ['payload.txt' => 'raw-stage22-package-payload-secret']);
        $manifest = $this->manifest(hash_file('sha256', $file->getRealPath()));
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($manifest),
            ])
            ->assertRedirect();

        $package = UpdatePackage::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.updates.show', $package))
            ->assertOk()
            ->assertSee('Manual review plan')
            ->assertDontSee('raw-stage22-package-payload-secret')
            ->assertDontSee((string) $package->path);

        $payload = AuditLog::query()->get()->toJson();

        $this->assertDatabaseHas('audit_logs', ['action' => 'update_package_validated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'update_package_reviewed']);
        $this->assertStringNotContainsString('raw-stage22-package-payload-secret', $payload);
        $this->assertStringNotContainsString((string) $package->path, $payload);
    }

    public function test_protected_files_are_not_touched_by_rejected_manifest(): void
    {
        $protected = [
            public_path('build.zip'),
            base_path('database/migrations/2026_05_01_173857_create_result_publications_table.php'),
        ];
        $before = collect($protected)
            ->mapWithKeys(fn (string $path): array => [$path => File::exists($path) ? hash_file('sha256', $path) : null])
            ->all();

        $errors = app(UpdateManifestService::class)->validate($this->manifest(str_repeat('c', 64), [
            'files_changed' => [
                '.env.local',
                'public/build.zip',
                'database/migrations/2026_05_01_173857_create_result_publications_table.php',
            ],
        ]));

        $after = collect($protected)
            ->mapWithKeys(fn (string $path): array => [$path => File::exists($path) ? hash_file('sha256', $path) : null])
            ->all();

        $this->assertNotEmpty($errors);
        $this->assertSame($before, $after);
    }

    public function test_standalone_dashboard_marks_update_finalization_available(): void
    {
        config([
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
            'sanfaani.deployment.installed' => true,
            'standalone.product_edition' => 'standalone',
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
            'licensing.validation_enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Update system finalization')
            ->assertSee('Guided package compatibility')
            ->assertSee('manual review planning');
    }

    private function configureSaasUpdates(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.updates_enabled' => true,
            'sanfaani.deployment.installed' => true,
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
            'updates.backup_required' => true,
            'updates.require_license_entitlement' => true,
            'licensing.validation_enabled' => true,
        ]);
    }

    private function package(array $manifestOverrides = [], string $status = UpdatePackage::STATUS_VALIDATED): UpdatePackage
    {
        $checksum = str_repeat('d', 64);
        $manifest = $this->manifest($checksum, $manifestOverrides);
        Storage::disk('updates')->put('packages/stage22-preflight.zip', 'private update package fixture');

        return UpdatePackage::create([
            'version' => $manifest['version'],
            'channel' => $manifest['channel'],
            'source' => 'upload',
            'filename' => 'stage22-preflight.zip',
            'path' => 'packages/stage22-preflight.zip',
            'checksum' => $checksum,
            'signature' => $manifest['signature'],
            'size_bytes' => 1024,
            'status' => $status,
            'manifest' => $manifest,
            'validated_at' => now(),
            'metadata' => ['extracted' => false, 'applied' => false, 'migrations_run' => false],
        ]);
    }

    private function manifest(string $checksum, array $overrides = []): array
    {
        return array_merge(app(UpdateManifestService::class)->sample(), [
            'version' => '1.0.9',
            'target_version' => '1.0.9',
            'checksum' => $checksum,
            'minimum_laravel' => app()->version(),
        ], $overrides);
    }

    private function zipUpload(string $name, array $entries = ['README.txt' => 'Safe update package fixture.']): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'stage22-update-package-');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);

        foreach ($entries as $entry => $contents) {
            $zip->addFromString($entry, $contents);
        }

        $zip->close();

        return new UploadedFile($path, $name, 'application/zip', null, true);
    }

    private function validAppKey(string $secret): string
    {
        return 'base64:'.base64_encode(str_pad($secret, 32, '-'));
    }

    private function superAdmin(): User
    {
        return $this->roleUser('super_admin');
    }

    private function roleUser(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
