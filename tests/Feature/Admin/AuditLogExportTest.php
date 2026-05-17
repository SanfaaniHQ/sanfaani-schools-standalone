<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditLogExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_can_export_filtered_audit_logs(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $school = School::create([
            'name' => 'Audit Export Academy',
            'slug' => 'audit-export-academy',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'action' => 'result_published',
            'action_tag' => 'result',
            'category' => 'result',
            'event' => 'result_published',
            'severity' => 'info',
            'metadata' => ['student_id' => 10],
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'mail_settings_updated',
            'action_tag' => 'mail',
            'category' => 'mail',
            'event' => 'mail_settings_updated',
            'severity' => 'info',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-logs.export', ['action' => 'result']));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('result_published', $content);
        $this->assertStringContainsString('Audit Export Academy', $content);
        $this->assertStringNotContainsString('mail_settings_updated', $content);
    }
}
