<?php

namespace Tests\Feature\Updates;

use App\Models\UpdateLog;
use App\Models\UpdatePackage;
use App\Models\UpdateRollbackPlan;
use App\Services\Updates\UpdateManifestService;
use App\Services\Updates\UpdatePreflightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UpdatePreflightTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'sanfaani.deployment.updates_enabled' => true,
            'features.features.update_manager.enabled' => true,
            'updates.enabled' => true,
            'updates.backup_required' => true,
        ]);
    }

    public function test_preflight_checks_php_version(): void
    {
        $package = $this->package(['minimum_php' => '99.0.0']);

        $result = app(UpdatePreflightService::class)->run($package);

        $phpCheck = collect($result->checks())->firstWhere('key', 'php_version');
        $this->assertSame('fail', $phpCheck['status']);
        $this->assertTrue($phpCheck['blocks']);
    }

    public function test_preflight_checks_writable_storage_and_cache(): void
    {
        $result = app(UpdatePreflightService::class)->run($this->package());
        $keys = collect($result->checks())->pluck('key')->all();

        $this->assertContains('storage_writable', $keys);
        $this->assertContains('cache_writable', $keys);
    }

    public function test_preflight_flags_backup_requirement_as_pending_when_backup_manager_is_not_implemented(): void
    {
        $result = app(UpdatePreflightService::class)->run($this->package());

        $backup = collect($result->checks())->firstWhere('key', 'backup_requirement');
        $this->assertSame('pending', $backup['status']);
        $this->assertTrue($backup['blocks']);
        $this->assertFalse($backup['context']['backup_manager_available']);
    }

    public function test_preflight_flags_migrations_as_warning_not_auto_run(): void
    {
        $result = app(UpdatePreflightService::class)->run($this->package([
            'requires_migration' => true,
            'database_changes' => ['add update audit index'],
        ]));

        $migration = collect($result->checks())->firstWhere('key', 'migration_review');
        $this->assertSame('warning', $migration['status']);
        $this->assertFalse($migration['blocks']);
    }

    public function test_update_log_is_created_for_preflight(): void
    {
        app(UpdatePreflightService::class)->run($this->package());

        $this->assertDatabaseHas('update_logs', [
            'event' => 'update.preflight_completed',
        ]);
        $this->assertGreaterThan(0, UpdateLog::count());
    }

    public function test_rollback_plan_metadata_is_created_for_preflight(): void
    {
        $package = $this->package();

        app(UpdatePreflightService::class)->run($package);

        $plan = UpdateRollbackPlan::firstOrFail();
        $this->assertSame($package->id, $plan->update_package_id);
        $this->assertFalse((bool) data_get($plan->metadata, 'rollback_performed'));
        $this->assertTrue((bool) data_get($plan->metadata, 'manual_only'));
    }

    public function test_unknown_update_package_status_fails_closed(): void
    {
        $package = $this->package(status: 'mystery');

        $result = app(UpdatePreflightService::class)->run($package);

        $status = collect($result->checks())->firstWhere('key', 'package_status');
        $this->assertSame('fail', $status['status']);
        $this->assertTrue($status['blocks']);
    }

    private function package(array $manifestOverrides = [], string $status = UpdatePackage::STATUS_VALIDATED): UpdatePackage
    {
        $checksum = str_repeat('b', 64);
        $manifest = array_merge(app(UpdateManifestService::class)->sample(), [
            'version' => '1.0.2',
            'checksum' => $checksum,
            'minimum_laravel' => app()->version(),
        ], $manifestOverrides);

        return UpdatePackage::create([
            'version' => $manifest['version'],
            'channel' => $manifest['channel'],
            'source' => 'upload',
            'filename' => 'preflight.zip',
            'path' => 'packages/preflight.zip',
            'checksum' => $manifest['checksum'],
            'signature' => $manifest['signature'],
            'size_bytes' => 1024,
            'status' => $status,
            'manifest' => $manifest,
            'validated_at' => now(),
            'metadata' => ['extracted' => false, 'applied' => false],
        ]);
    }
}
