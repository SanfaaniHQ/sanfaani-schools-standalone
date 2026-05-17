<?php

namespace Tests\Feature\School;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SchoolAuditLogIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_school_admin_only_sees_own_school_audit_logs(): void
    {
        $school = $this->createSchool('Visible Audit School', 'visible-audit-school');
        $otherSchool = $this->createSchool('Hidden Audit School', 'hidden-audit-school');
        $admin = $this->createUserForSchool($school, 'school_admin');

        AuditLog::create([
            'user_id' => $admin->id,
            'school_id' => $school->id,
            'action' => 'result_published',
            'action_tag' => 'result',
            'category' => 'result',
            'event' => 'result_published',
            'severity' => 'info',
        ]);

        AuditLog::create([
            'school_id' => $otherSchool->id,
            'action' => 'scratch_card_generated',
            'action_tag' => 'scratch_card',
            'category' => 'scratch_card',
            'event' => 'scratch_card_generated',
            'severity' => 'info',
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.audit-logs.index'))
            ->assertOk()
            ->assertSee('result_published')
            ->assertDontSee('scratch_card_generated')
            ->assertDontSee('Hidden Audit School');

        $content = $this->get(route('school.audit-logs.export'))->streamedContent();

        $this->assertStringContainsString('result_published', $content);
        $this->assertStringNotContainsString('scratch_card_generated', $content);
    }

    public function test_teacher_cannot_open_school_audit_logs_directly(): void
    {
        $school = $this->createSchool('Teacher Audit School', 'teacher-audit-school');
        $teacher = $this->createUserForSchool($school, 'teacher');

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->get(route('school.audit-logs.index'))->assertForbidden();
        $this->get(route('school.audit-logs.export'))->assertForbidden();
    }

    private function createSchool(string $name, string $slug): School
    {
        return School::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);

        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
