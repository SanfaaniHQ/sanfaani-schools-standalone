<?php

namespace Tests\Feature\Standalone;

use App\Models\StandaloneSyncOutbox;
use App\Services\Standalone\StandaloneSyncService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StandaloneSyncFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.offline_mode' => 'local_first',
            'standalone.sync.enabled' => false,
            'standalone.sync.endpoint' => '',
            'standalone.sync.token' => '',
            'standalone.sync.backup_enabled' => false,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);
    }

    public function test_sync_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('standalone_sync_devices'));
        $this->assertTrue(Schema::hasTable('standalone_sync_outbox'));
        $this->assertTrue(Schema::hasTable('standalone_sync_logs'));
    }

    public function test_sync_service_can_queue_and_mark_outbox_items_without_deleting_local_data(): void
    {
        $service = app(StandaloneSyncService::class);

        $item = $service->markPending('students', 10, 'upsert', ['name' => 'Local Student']);

        $this->assertSame(StandaloneSyncOutbox::STATUS_PENDING, $item->status);
        $this->assertSame(1, $service->status()['pending_count']);

        $failed = $service->markFailed($item, 'Endpoint unavailable');
        $this->assertSame(StandaloneSyncOutbox::STATUS_FAILED, $failed->status);
        $this->assertSame(1, $failed->attempts);

        $next = $service->markPending('students', 11, 'upsert', ['name' => 'Second Local Student']);
        $synced = $service->markSynced($next);

        $this->assertSame(StandaloneSyncOutbox::STATUS_SYNCED, $synced->status);
        $this->assertNotNull($synced->synced_at);
        $this->assertDatabaseHas('standalone_sync_outbox', [
            'entity_type' => 'students',
            'entity_id' => '10',
            'status' => StandaloneSyncOutbox::STATUS_FAILED,
        ]);
    }

    public function test_standalone_status_command_runs(): void
    {
        $this->artisan('standalone:status')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_standalone_sync_dry_run_runs_without_external_call(): void
    {
        app(StandaloneSyncService::class)->markPending('students', 12, 'upsert', ['name' => 'Dry Run Student']);

        $this->artisan('standalone:sync', ['--dry-run' => true])
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('standalone_sync_logs', [
            'direction' => 'push',
            'status' => 'dry_run',
        ]);
    }

    public function test_standalone_sync_refuses_real_sync_when_disabled(): void
    {
        config(['standalone.sync.enabled' => false]);

        $this->artisan('standalone:sync')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseHas('standalone_sync_logs', [
            'direction' => 'push',
            'status' => 'refused',
        ]);
    }

    public function test_standalone_sync_refuses_real_sync_when_endpoint_or_token_missing(): void
    {
        config([
            'standalone.sync.enabled' => true,
            'standalone.sync.endpoint' => '',
            'standalone.sync.token' => '',
        ]);

        $this->artisan('standalone:sync')
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseHas('standalone_sync_logs', [
            'direction' => 'push',
            'status' => 'refused',
        ]);
    }
}
