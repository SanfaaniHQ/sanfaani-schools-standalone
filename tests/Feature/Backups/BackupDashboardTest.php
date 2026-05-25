<?php

namespace Tests\Feature\Backups;

use App\Models\Backup;
use App\Models\BackupItem;
use App\Services\Backups\BackupLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackupDashboardTest extends TestCase
{
    use BackupTestSupport;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureSaasBackups();
    }

    public function test_backup_dashboard_renders_when_backup_manager_is_enabled(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Platform Backups')
            ->assertSee('Foundation mode');
    }

    public function test_backup_dashboard_is_blocked_when_backup_manager_is_disabled(): void
    {
        config([
            'features.features.backup_manager.enabled' => false,
            'backups.enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_access_backup_manager(): void
    {
        $this->actingAs($this->schoolAdmin())
            ->get(route('admin.backups.index'))
            ->assertForbidden();
    }

    public function test_demo_mode_cannot_access_backup_manager(): void
    {
        config(['sanfaani.deployment.license_mode' => 'demo']);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertNotFound();
    }

    public function test_expired_license_blocks_backup_manager_when_validation_is_required(): void
    {
        $school = $this->configureSingleSchoolBackups();
        $this->license($school, ['expires_at' => now()->subDays(2), 'offline_grace_until' => null]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertRedirect(route('admin.license.index'));
    }

    public function test_suspended_license_blocks_backup_manager_when_validation_is_required(): void
    {
        $school = $this->configureSingleSchoolBackups();
        $this->license($school, ['status' => 'suspended', 'suspended_at' => now()]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertRedirect(route('admin.license.index'));
    }

    public function test_valid_license_with_backup_entitlement_allows_access(): void
    {
        $school = $this->configureSingleSchoolBackups();
        $this->license($school, ['entitlements' => ['backup_manager' => true]]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Backups');
    }

    public function test_missing_backup_entitlement_blocks_access_when_required(): void
    {
        $school = $this->configureSingleSchoolBackups();
        $this->license($school, ['entitlements' => []]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.index'))
            ->assertForbidden();
    }

    public function test_backup_ui_does_not_expose_env_secrets(): void
    {
        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'filename' => 'safe.json',
            'trigger' => 'test',
            'metadata' => [],
        ]);

        app(BackupLogService::class)->log(
            'backup.secret_test',
            'APP_KEY=base64:secret-value should be hidden',
            $backup,
            context: ['APP_KEY' => 'base64:secret-value', 'password' => 'database-password', 'note' => 'safe'],
        );

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.show', $backup))
            ->assertOk()
            ->assertDontSee('base64:secret-value')
            ->assertDontSee('database-password')
            ->assertSee('[redacted]');
    }

    public function test_backup_ui_sanitizes_sensitive_paths(): void
    {
        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'path' => base_path('.env'),
            'filename' => 'safe.json',
            'trigger' => 'test',
            'metadata' => [],
        ]);

        BackupItem::create([
            'backup_id' => $backup->id,
            'item_type' => BackupItem::TYPE_CONFIG,
            'source_label' => 'Sanitized config metadata',
            'path' => base_path('.env'),
            'status' => 'recorded',
            'metadata' => [],
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.show', $backup))
            ->assertOk()
            ->assertDontSee(base_path('.env'))
            ->assertSee('[app]');
    }

    public function test_school_admin_cannot_view_another_school_backup(): void
    {
        $schoolA = $this->school('School A');
        $schoolB = $this->school('School B');
        $backup = Backup::create([
            'school_id' => $schoolB->id,
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'filename' => 'school-b.json',
            'trigger' => 'test',
            'metadata' => [],
        ]);

        $this->actingAs($this->schoolAdmin($schoolA))
            ->get(route('admin.backups.show', $backup))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_platform_backups_where_intended(): void
    {
        $backup = Backup::create([
            'type' => Backup::TYPE_MANUAL,
            'status' => Backup::STATUS_COMPLETED,
            'disk' => 'local',
            'filename' => 'platform.json',
            'trigger' => 'test',
            'metadata' => [],
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.backups.show', $backup))
            ->assertOk()
            ->assertSee('platform.json');
    }
}
