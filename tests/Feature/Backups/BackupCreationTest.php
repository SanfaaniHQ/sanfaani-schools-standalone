<?php

namespace Tests\Feature\Backups;

use App\Models\BackupItem;
use App\Models\BackupLog;
use App\Models\BackupRestorePlan;
use App\Models\AuditLog;
use App\Services\Backups\BackupConfigService;
use App\Services\Backups\BackupFilesService;
use App\Services\Backups\BackupPreflightService;
use App\Services\Backups\BackupRestorePlanService;
use App\Services\Backups\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupCreationTest extends TestCase
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

    public function test_backup_preflight_checks_storage_disk_and_config(): void
    {
        $result = app(BackupPreflightService::class)->run();
        $keys = collect($result->checks())->pluck('key')->all();

        $this->assertContains('storage_writable', $keys);
        $this->assertContains('backup_disk', $keys);
        $this->assertContains('database_enabled', $keys);
        $this->assertContains('files_enabled', $keys);
        $this->assertContains('config_enabled', $keys);
    }

    public function test_manual_backup_request_creates_backup_metadata(): void
    {
        $backup = app(BackupService::class)->createManualBackup($this->superAdmin());

        $this->assertDatabaseHas('backups', ['id' => $backup->id]);
        $this->assertGreaterThanOrEqual(3, BackupItem::where('backup_id', $backup->id)->count());
        $this->assertDatabaseHas('backup_logs', ['backup_id' => $backup->id, 'event' => 'backup.requested']);
        $this->assertNotEmpty($backup->fresh()->filename);
    }

    public function test_database_backup_warning_is_recorded_when_shell_dump_is_unavailable(): void
    {
        $backup = app(BackupService::class)->createManualBackup($this->superAdmin());

        $this->assertDatabaseHas('backup_items', [
            'backup_id' => $backup->id,
            'item_type' => BackupItem::TYPE_DATABASE,
            'status' => 'warning',
        ]);
        $this->assertDatabaseHas('backup_logs', [
            'backup_id' => $backup->id,
            'event' => 'backup.database_manual_export_required',
        ]);
    }

    public function test_files_backup_excludes_unsafe_paths(): void
    {
        config([
            'backups.safe_file_roots' => [
                'storage/app/public',
                'vendor',
                'node_modules',
                'public/build.zip',
            ],
        ]);

        $roots = app(BackupFilesService::class)->safeFileRoots();

        $this->assertContains('storage/app/public', $roots);
        $this->assertNotContains('vendor', $roots);
        $this->assertNotContains('node_modules', $roots);
        $this->assertNotContains('public/build.zip', $roots);
    }

    public function test_config_backup_does_not_store_env_secrets(): void
    {
        $metadata = app(BackupConfigService::class)->sanitizedMetadata();
        $json = json_encode($metadata, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString('APP_KEY', $json);
        $this->assertStringNotContainsString('DB_PASSWORD', $json);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $json);
        $this->assertTrue($metadata['sanitized']);
        $this->assertFalse($metadata['env_exported']);
    }

    public function test_backup_log_is_created(): void
    {
        app(BackupService::class)->createManualBackup($this->superAdmin());

        $this->assertGreaterThan(0, BackupLog::count());
    }

    public function test_restore_plan_is_generated_without_running_restore(): void
    {
        $backup = app(BackupService::class)->createManualBackup($this->superAdmin());
        $plan = app(BackupRestorePlanService::class)->createForBackup($backup);

        $this->assertInstanceOf(BackupRestorePlan::class, $plan);
        $this->assertFalse((bool) data_get($plan->metadata, 'restore_performed'));
        $this->assertTrue((bool) data_get($plan->metadata, 'manual_only'));
    }

    public function test_manual_backup_create_route_records_metadata(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('admin.backups.store'))
            ->assertRedirect();

        $this->assertDatabaseCount('backups', 1);
        $this->assertDatabaseHas('backup_items', ['item_type' => BackupItem::TYPE_CONFIG]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup_requested']);
    }

    public function test_restore_plan_view_is_audit_logged_without_running_restore(): void
    {
        $user = $this->superAdmin();
        $backup = app(BackupService::class)->createManualBackup($user);

        $this->actingAs($user)
            ->get(route('admin.backups.restore-plan', $backup))
            ->assertOk();

        $this->assertDatabaseHas('backup_logs', [
            'backup_id' => $backup->id,
            'event' => 'backup.restore_plan_viewed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'backup_restore_plan_viewed',
        ]);

        $this->assertFalse((bool) data_get(AuditLog::where('action', 'backup_restore_plan_viewed')->firstOrFail()->metadata, 'restore_performed'));
    }

    public function test_restore_plan_page_requires_authorization_and_shows_non_destructive_drill_guidance(): void
    {
        $user = $this->superAdmin();
        $backup = app(BackupService::class)->createManualBackup($user);

        $this->get(route('admin.backups.restore-plan', $backup))
            ->assertRedirect('/login');

        $this->actingAs($this->schoolAdmin())
            ->get(route('admin.backups.restore-plan', $backup))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.backups.restore-plan', $backup))
            ->assertOk()
            ->assertSee('Restore execution is not implemented')
            ->assertSee('No restore operation has been executed automatically')
            ->assertSee('Pre-restore checklist')
            ->assertSee('Restore drill guidance')
            ->assertSee('Contact Sanfaani support')
            ->assertSee('Never use the live school portal as the first restore test')
            ->assertDontSee('Restore executed successfully');
    }
}
