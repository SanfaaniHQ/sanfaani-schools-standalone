<?php

namespace Tests\Feature\Mail;

use App\Models\School;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\RolePermissionService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolMailSettingsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'parent', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        app(RolePermissionService::class)->ensureDefaultRolePermissions();
    }

    public function test_school_admin_can_view_update_and_test_school_mail_settings(): void
    {
        Mail::fake();
        $school = $this->school('Mail Academy');
        $admin = $this->schoolUser($school, 'school_admin');
        $this->actInSchool($admin, $school, 'school_admin');

        $this->get(route('school.mail-settings.edit'))
            ->assertOk()
            ->assertSee('School Mail Settings');

        $this->patch(route('school.mail-settings.update'), $this->settings())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mail_settings', [
            'school_id' => $school->id,
            'host' => 'smtp.example.test',
        ]);

        $this->post(route('school.mail-settings.test'), array_merge($this->settings(), [
            'test_email' => 'recipient@example.test',
        ]))->assertSessionHas('success');

        $this->assertDatabaseHas('mail_delivery_attempts', [
            'school_id' => $school->id,
            'recipient' => 'recipient@example.test',
            'status' => 'accepted_by_smtp',
            'configuration' => 'saved',
            'fallback_used' => false,
        ]);
    }

    public function test_non_admin_school_roles_cannot_access_school_mail_settings(): void
    {
        $school = $this->school('Denied Mail Academy');

        foreach (['teacher', 'result_officer', 'parent', 'student'] as $role) {
            $user = $this->schoolUser($school, $role);
            $this->actInSchool($user, $school, $role);
            $this->get(route('school.mail-settings.edit'))->assertForbidden();
        }
    }

    public function test_school_admin_is_confined_to_assigned_school_mail_settings(): void
    {
        $school = $this->school('Assigned Academy');
        $other = $this->school('Other Academy');
        $admin = $this->schoolUser($school, 'school_admin');

        $this->actingAs($admin)->withSession([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$other->id}:school_admin",
            'active_school_id' => $other->id,
            'active_role_context' => 'school_admin',
        ])->get(route('school.mail-settings.edit'))
            ->assertRedirect(route('workspace.create'));

        $this->assertNull(session('active_school_id'));
    }

    public function test_dual_role_school_context_can_access_mail_but_installation_context_must_switch(): void
    {
        $school = $this->school('Dual Mail Academy');
        $owner = $this->schoolUser($school, 'school_admin');
        $owner->assignRole('super_admin');

        $this->actInSchool($owner, $school, 'school_admin');
        $this->get(route('school.mail-settings.edit'))->assertOk();

        $this->withSession([
            'workspace.type' => TenantContext::WORKSPACE_INSTALLATION_ADMIN,
            'workspace.key' => 'global:super_admin',
            'active_school_id' => null,
            'active_role_context' => 'super_admin',
        ])->get(route('school.mail-settings.edit'))
            ->assertRedirect(route('workspace.create'));
    }

    private function settings(): array
    {
        return [
            'is_enabled' => '1',
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'username' => 'mailer@example.test',
            'password' => 'mail-secret',
            'encryption' => 'tls',
            'from_address' => 'mailer@example.test',
            'from_name' => 'Mail Academy',
            'reply_to_email' => 'reply@example.test',
            'timeout' => 10,
        ];
    }

    private function school(string $name): School
    {
        return School::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function schoolUser(School $school, string $role): User
    {
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

    private function actInSchool(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'workspace.type' => TenantContext::WORKSPACE_SCHOOL,
            'workspace.key' => "school:{$school->id}:{$role}",
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
