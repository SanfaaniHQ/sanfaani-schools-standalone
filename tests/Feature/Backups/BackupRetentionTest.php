<?php

namespace Tests\Feature\Backups;

use App\Models\Backup;
use App\Services\Backups\BackupRetentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupRetentionTest extends TestCase
{
    use BackupTestSupport;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasBackups();
    }

    public function test_retention_marks_and_prunes_expired_backups_safely(): void
    {
        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'filename' => 'expired.json',
            'trigger' => 'test',
            'expires_at' => now()->subDay(),
            'metadata' => [],
        ]);

        $count = app(BackupRetentionService::class)->pruneExpired($this->superAdmin());

        $this->assertSame(1, $count);
        $this->assertSame(Backup::STATUS_PRUNED, $backup->fresh()->status);
        $this->assertFalse((bool) data_get($backup->fresh()->metadata, 'restore_performed'));
        $this->assertDatabaseHas('backup_logs', [
            'backup_id' => $backup->id,
            'event' => 'backup.retention_pruned',
        ]);
    }

    public function test_prune_command_is_safe(): void
    {
        Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'filename' => 'expired.json',
            'trigger' => 'test',
            'expires_at' => now()->subDay(),
            'metadata' => [],
        ]);

        $this->artisan('backups:prune')
            ->expectsOutput('Expired backup metadata pruned: 1')
            ->expectsOutput('No restore operation was run and no public download route was created.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('backups', ['status' => Backup::STATUS_PRUNED]);
    }
}
