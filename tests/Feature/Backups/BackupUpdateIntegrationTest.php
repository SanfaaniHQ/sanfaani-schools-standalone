<?php

namespace Tests\Feature\Backups;

use App\Services\Updates\UpdatePreflightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupUpdateIntegrationTest extends TestCase
{
    use BackupTestSupport;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasBackups();
        Storage::fake('local');
    }

    public function test_recent_verified_backup_satisfies_update_preflight_backup_requirement(): void
    {
        $this->verifiedBackup();

        $result = app(UpdatePreflightService::class)->run($this->updatePackage(), $this->superAdmin());
        $backup = collect($result->checks())->firstWhere('key', 'backup_requirement');

        $this->assertSame('pass', $backup['status']);
        $this->assertFalse($backup['blocks']);
        $this->assertTrue($backup['context']['recent_verified_backup']);
    }

    public function test_missing_recent_verified_backup_blocks_update_preflight_backup_requirement(): void
    {
        $result = app(UpdatePreflightService::class)->run($this->updatePackage(), $this->superAdmin());
        $backup = collect($result->checks())->firstWhere('key', 'backup_requirement');

        $this->assertSame('pending', $backup['status']);
        $this->assertTrue($backup['blocks']);
        $this->assertFalse($backup['context']['recent_verified_backup']);
        $this->assertTrue($backup['context']['backup_manager_available']);
    }
}
