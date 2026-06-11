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
        $this->assertSame(BackupVerification::STATUS_VERIFIED, $verification->status);
        $this->assertTrue((bool) data_get($verification->context, 'manifest_consistent'));
        $this->assertGreaterThan(0, data_get($verification->context, 'metadata_file_size_bytes'));
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

    public function test_empty_metadata_file_reports_safe_warning_status(): void
    {
        $backup = $this->backupWithMetadata();
        Storage::disk('local')->put($backup->path, '');
        $backup->forceFill([
            'size_bytes' => 0,
            'checksum' => hash('sha256', ''),
        ])->save();

        $verification = app(BackupVerificationService::class)->verify($backup);

        $this->assertSame(BackupVerification::STATUS_WARNING, $verification->status);
        $this->assertFalse((bool) data_get($verification->context, 'metadata_file_readable'));
        $this->assertSame(0, data_get($verification->context, 'metadata_file_size_bytes'));
    }

    public function test_inconsistent_manifest_reports_safe_warning_status(): void
    {
        $backup = $this->backupWithMetadata();
        $payload = json_encode([
            'backup_id' => $backup->id + 1000,
            'type' => Backup::TYPE_PRE_UPDATE,
            'safe_foundation_only' => false,
            'restore_performed' => true,
            'env_exported' => true,
        ], JSON_THROW_ON_ERROR);
        Storage::disk('local')->put($backup->path, $payload);
        $backup->forceFill([
            'size_bytes' => strlen($payload),
            'checksum' => hash('sha256', $payload),
        ])->save();

        $verification = app(BackupVerificationService::class)->verify($backup);

        $this->assertSame(BackupVerification::STATUS_WARNING, $verification->status);
        $this->assertFalse((bool) data_get($verification->context, 'manifest_consistent'));
        $this->assertFalse((bool) data_get($verification->context, 'manifest_checks.backup_id_matches'));
        $this->assertFalse((bool) data_get($verification->context, 'manifest_checks.restore_not_performed'));
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
        $path = 'backups/metadata/manual.json';

        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => $status,
            'disk' => 'local',
            'path' => $path,
            'filename' => basename($path),
            'size_bytes' => null,
            'checksum' => null,
            'trigger' => 'test',
            'metadata' => [],
        ]);

        $payload = json_encode([
            'backup_id' => $backup->id,
            'type' => $backup->type,
            'status' => 'metadata_recorded',
            'safe_foundation_only' => true,
            'restore_performed' => false,
            'env_exported' => false,
        ], JSON_THROW_ON_ERROR);
        Storage::disk('local')->put($path, $payload);
        $backup->forceFill([
            'size_bytes' => strlen($payload),
            'checksum' => hash('sha256', $payload),
        ])->save();

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
