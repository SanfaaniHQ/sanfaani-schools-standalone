<?php

namespace Tests\Feature\Updates;

use App\Models\UpdateLog;
use App\Models\UpdatePackage;
use App\Models\UpdateRollbackPlan;
use App\Models\User;
use App\Services\Updates\UpdateManifestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdatePackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('updates');
        $this->configureSaasUpdates();
    }

    public function test_update_package_metadata_can_be_stored(): void
    {
        $file = UploadedFile::fake()->create('sanfaani-1.0.1.zip', 10, 'application/zip');
        $manifest = $this->manifest(hash_file('sha256', $file->getRealPath()));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($manifest),
            ])
            ->assertRedirect();

        $package = UpdatePackage::firstOrFail();

        $this->assertSame('1.0.1', $package->version);
        $this->assertSame(UpdatePackage::STATUS_VALIDATED, $package->status);
        $this->assertSame('sanfaani-1.0.1.zip', $package->filename);
        $this->assertTrue(Storage::disk('updates')->exists($package->path));
        $this->assertDatabaseHas('audit_logs', ['action' => 'update_package_uploaded']);
    }

    public function test_raw_package_is_not_extracted_or_applied(): void
    {
        $file = UploadedFile::fake()->create('safe-update.zip', 4, 'application/zip');
        $manifest = $this->manifest(hash_file('sha256', $file->getRealPath()));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($manifest),
            ])
            ->assertRedirect();

        $package = UpdatePackage::firstOrFail();

        $this->assertFalse((bool) data_get($package->metadata, 'extracted'));
        $this->assertFalse((bool) data_get($package->metadata, 'applied'));
        $this->assertFalse(File::exists(base_path('manifest.json')));
        $this->assertCount(1, Storage::disk('updates')->allFiles('packages'));
    }

    public function test_package_upload_validates_extension(): void
    {
        $file = UploadedFile::fake()->create('unsafe.exe', 1, 'application/octet-stream');

        $this->actingAs($this->superAdmin())
            ->from(route('admin.updates.upload'))
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($this->manifest(str_repeat('a', 64))),
            ])
            ->assertSessionHasErrors('package');
    }

    public function test_package_upload_rejects_disallowed_mime_even_with_zip_extension(): void
    {
        $file = UploadedFile::fake()->create('unsafe.zip', 1, 'application/x-msdownload');

        $this->actingAs($this->superAdmin())
            ->from(route('admin.updates.upload'))
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($this->manifest(str_repeat('a', 64))),
            ])
            ->assertSessionHasErrors('package');
    }

    public function test_package_upload_validates_max_size(): void
    {
        config(['updates.max_package_mb' => 1]);

        $file = UploadedFile::fake()->create('large.zip', 2048, 'application/zip');

        $this->actingAs($this->superAdmin())
            ->from(route('admin.updates.upload'))
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($this->manifest(str_repeat('a', 64))),
            ])
            ->assertSessionHasErrors('package');
    }

    public function test_manifest_parser_validates_required_fields(): void
    {
        $errors = app(UpdateManifestService::class)->validate([]);

        $this->assertContains('Manifest is missing version.', $errors);
        $this->assertContains('Manifest is missing channel.', $errors);
    }

    public function test_update_log_and_rollback_plan_are_created_for_package_upload(): void
    {
        $file = UploadedFile::fake()->create('logged.zip', 2, 'application/zip');
        $manifest = $this->manifest(hash_file('sha256', $file->getRealPath()));

        $this->actingAs($this->superAdmin())
            ->post(route('admin.updates.store'), [
                'package' => $file,
                'manifest_json' => json_encode($manifest),
            ]);

        $this->assertSame(2, UpdateLog::count());
        $this->assertDatabaseHas('update_logs', [
            'event' => 'update.package_uploaded',
        ]);
        $this->assertSame(1, UpdateRollbackPlan::count());
        $this->assertFalse((bool) data_get(UpdateRollbackPlan::first()->metadata, 'rollback_performed'));
    }

    private function manifest(string $checksum): array
    {
        return array_merge(app(UpdateManifestService::class)->sample(), [
            'version' => '1.0.1',
            'checksum' => $checksum,
            'minimum_laravel' => app()->version(),
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
