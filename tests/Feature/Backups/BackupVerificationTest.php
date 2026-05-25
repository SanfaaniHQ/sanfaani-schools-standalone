<?php

namespace Tests\Feature\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use App\Models\BackupLog;
use App\Models\BackupVerification;
use App\Services\Backups\BackupLogService;
use App\Services\Backups\BackupVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupVerificationTest extends TestCase
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

    public function test_backup_verification_creates_verification_record(): void
    {
        $backup = $this->backupWithMetadata();

        $verification = app(BackupVerificationService::class)->verify($backup, $this->superAdmin());

        $this->assertInstanceOf(BackupVerification::class, $verification);
        $this->assertDatabaseHas('backup_verifications', ['backup_id' => $backup->id]);
    }

    public function test_backup_log_is_created_for_verification(): void
    {
        $backup = $this->backupWithMetadata();

        app(BackupVerificationService::class)->verify($backup, $this->superAdmin());

        $this->assertDatabaseHas('backup_logs', [
            'backup_id' => $backup->id,
            'event' => 'backup.verification_completed',
        ]);
    }

    public function test_unknown_backup_status_fails_closed(): void
    {
        $backup = $this->backupWithMetadata('mystery');

        $verification = app(BackupVerificationService::class)->verify($backup);

        $this->assertSame(BackupVerification::STATUS_FAILED, $verification->status);
    }

    public function test_log_service_redacts_secrets(): void
    {
        $backup = $this->backupWithMetadata();

        app(BackupLogService::class)->log(
            'backup.secret_context',
            'DB_PASSWORD=secret-value',
            $backup,
            context: ['token' => 'abc', 'safe' => 'visible'],
        );

        $log = BackupLog::latest()->firstOrFail();

        $this->assertStringNotContainsString('secret-value', $log->message);
        $this->assertSame('[redacted]', $log->context['token']);
        $this->assertSame('visible', $log->context['safe']);
    }

    private function backupWithMetadata(string $status = Backup::STATUS_COMPLETED): Backup
    {
        $payload = json_encode(['backup' => true], JSON_THROW_ON_ERROR);
        $path = 'backups/metadata/manual.json';
        Storage::disk('local')->put($path, $payload);

        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => $status,
            'disk' => 'local',
            'path' => $path,
            'filename' => basename($path),
            'size_bytes' => strlen($payload),
            'checksum' => hash('sha256', $payload),
            'trigger' => 'test',
            'metadata' => [],
        ]);

        foreach ([BackupItem::TYPE_DATABASE, BackupItem::TYPE_FILES, BackupItem::TYPE_CONFIG] as $type) {
            BackupItem::create([
                'backup_id' => $backup->id,
                'item_type' => $type,
                'source_label' => "{$type} metadata",
                'status' => 'recorded',
                'metadata' => [],
            ]);
        }

        return $backup;
    }
}
