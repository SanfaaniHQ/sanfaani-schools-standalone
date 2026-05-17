<?php

namespace Tests\Feature\School;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\SupportEscalationHistory;
use App\Models\SupportMessage;
use App\Models\SupportMessageAttachment;
use App\Models\SupportThread;
use App\Models\SupportThreadEvent;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SupportRoutingEscalationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'result_officer', 'teacher'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_teacher_ticket_routes_to_school_admin_and_keeps_staff_visibility_isolated(): void
    {
        $school = $this->school();
        $teacher = $this->schoolUser($school, 'teacher', 'teacher@example.test');
        $otherTeacher = $this->schoolUser($school, 'teacher', 'other.teacher@example.test');
        $schoolAdmin = $this->schoolUser($school, 'school_admin', 'school.admin@example.test');
        $superAdmin = $this->superAdmin();

        $this->actAsSchoolRole($teacher, $school, 'teacher');
        $response = $this->post(route('school.support.store'), [
            'subject' => 'Cannot submit results',
            'category' => 'result_issue',
            'priority' => 'high',
            'message' => 'The result submit button is not working.',
        ]);

        $thread = SupportThread::firstOrFail();

        $response->assertRedirect(route('school.support.show', $thread));
        $this->assertSame(SupportThread::ROUTE_SCHOOL_ADMIN, $thread->routed_to_role);
        $this->assertSame(SupportThread::STATUS_OPEN, $thread->status);
        $this->assertSame(0, $thread->escalation_level);
        $this->assertDatabaseHas('support_thread_events', [
            'support_thread_id' => $thread->id,
            'event_type' => 'created',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'support_thread_created',
            'auditable_id' => $thread->id,
        ]);

        $this->actAsSchoolRole($otherTeacher, $school, 'teacher');
        $this->get(route('school.support.show', $thread))->assertForbidden();

        $this->actAsSchoolRole($schoolAdmin, $school, 'school_admin');
        $this->get(route('school.support.index'))->assertOk()->assertSee('Cannot submit results');

        $this->actingAs($superAdmin);
        $this->get(route('admin.support-threads.index'))->assertOk()->assertDontSee('Cannot submit results');
    }

    public function test_school_admin_can_assign_internal_ticket_reply_privately_and_escalate_to_super_admin(): void
    {
        $school = $this->school();
        $teacher = $this->schoolUser($school, 'teacher', 'teacher@example.test');
        $schoolAdmin = $this->schoolUser($school, 'school_admin', 'school.admin@example.test');
        $assignee = $this->schoolUser($school, 'result_officer', 'officer@example.test');
        $superAdmin = $this->superAdmin();
        $thread = $this->internalThread($school, $teacher);

        $this->actAsSchoolRole($schoolAdmin, $school, 'school_admin');

        $this->patch(route('school.support.assign', $thread), [
            'assigned_to' => $assignee->id,
        ])->assertRedirect();

        $thread->refresh();
        $this->assertSame($assignee->id, $thread->assigned_to);
        $this->assertDatabaseHas('support_thread_events', [
            'support_thread_id' => $thread->id,
            'event_type' => 'assigned',
        ]);

        $this->post(route('school.support.reply', $thread), [
            'message' => 'Checking internally before escalation.',
            'is_internal_note' => '1',
        ])->assertRedirect();

        $this->assertSame(2, SupportMessage::where('support_thread_id', $thread->id)->count());

        $this->actAsSchoolRole($teacher, $school, 'teacher');
        $this->get(route('school.support.show', $thread))
            ->assertOk()
            ->assertDontSee('Checking internally before escalation.');

        $this->actAsSchoolRole($schoolAdmin, $school, 'school_admin');
        $this->post(route('school.support.escalate', $thread), [
            'reason' => 'Needs platform data repair.',
        ])->assertRedirect();

        $thread->refresh();
        $this->assertSame(SupportThread::ROUTE_SUPER_ADMIN, $thread->routed_to_role);
        $this->assertSame(SupportThread::STATUS_ESCALATED, $thread->status);
        $this->assertSame(1, $thread->escalation_level);
        $this->assertNotNull($thread->escalated_at);
        $this->assertSame(1, SupportEscalationHistory::where('support_thread_id', $thread->id)->count());
        $this->assertSame(1, SupportThreadEvent::where('support_thread_id', $thread->id)->where('event_type', 'escalated')->count());
        $this->assertSame(1, AuditLog::where('action', 'support_thread_escalated')->where('auditable_id', $thread->id)->count());

        $this->actingAs($superAdmin);
        $this->get(route('admin.support-threads.index'))->assertOk()->assertSee($thread->subject);
    }

    public function test_support_replies_persist_and_protect_attachments(): void
    {
        Storage::fake('local');

        $school = $this->school();
        $teacher = $this->schoolUser($school, 'teacher', 'teacher@example.test');
        $schoolAdmin = $this->schoolUser($school, 'school_admin', 'school.admin@example.test');
        $thread = $this->internalThread($school, $teacher);

        $this->actAsSchoolRole($teacher, $school, 'teacher');
        $this->post(route('school.support.reply', $thread), [
            'message' => 'Here is the screenshot.',
            'attachments' => [UploadedFile::fake()->create('support-proof.pdf', 64, 'application/pdf')],
        ])->assertRedirect();

        $attachment = SupportMessageAttachment::firstOrFail();

        Storage::disk($attachment->disk)->assertExists($attachment->path);

        $this->get(route('school.support-attachments.download', $attachment))
            ->assertOk();

        $this->actAsSchoolRole($schoolAdmin, $school, 'school_admin');
        $this->get(route('school.support.show', $thread))
            ->assertOk()
            ->assertSee('support-proof.pdf');
    }

    public function test_school_admin_created_support_request_preserves_platform_support_workflow(): void
    {
        $school = $this->school();
        $schoolAdmin = $this->schoolUser($school, 'school_admin', 'school.admin@example.test');
        $superAdmin = $this->superAdmin();

        $this->actAsSchoolRole($schoolAdmin, $school, 'school_admin');

        $this->post(route('school.support.store'), [
            'subject' => 'Billing question',
            'category' => 'subscription',
            'priority' => 'normal',
            'message' => 'Please review our subscription renewal.',
        ])->assertRedirect();

        $thread = SupportThread::firstOrFail();

        $this->assertSame(SupportThread::ROUTE_SUPER_ADMIN, $thread->routed_to_role);
        $this->assertSame(SupportThread::STATUS_ESCALATED, $thread->status);
        $this->assertSame(1, $thread->escalation_level);
        $this->assertSame(1, SupportEscalationHistory::where('support_thread_id', $thread->id)->count());

        $this->actingAs($superAdmin);
        $this->get(route('admin.support-threads.show', $thread))
            ->assertOk()
            ->assertSee('Billing question')
            ->assertSee('Escalation History');
    }

    public function test_legacy_platform_threads_without_routing_metadata_remain_visible_to_super_admin(): void
    {
        $school = $this->school();
        $schoolAdmin = $this->schoolUser($school, 'school_admin', 'school.admin@example.test');
        $superAdmin = $this->superAdmin();

        $thread = SupportThread::create([
            'school_id' => $school->id,
            'created_by' => $schoolAdmin->id,
            'subject' => 'Legacy platform ticket',
            'category' => 'general_support',
            'priority' => 'normal',
            'status' => 'awaiting_response',
            'visibility' => 'internal',
            'last_message_at' => now(),
        ]);

        $this->actingAs($superAdmin);

        $this->get(route('admin.support-threads.index'))
            ->assertOk()
            ->assertSee($thread->subject);
    }

    private function internalThread(School $school, User $creator): SupportThread
    {
        $thread = SupportThread::create([
            'school_id' => $school->id,
            'created_by' => $creator->id,
            'creator_role' => 'teacher',
            'routed_to_role' => SupportThread::ROUTE_SCHOOL_ADMIN,
            'subject' => 'Internal classroom support',
            'category' => 'technical_issue',
            'priority' => 'normal',
            'status' => SupportThread::STATUS_OPEN,
            'visibility' => SupportThread::VISIBILITY_INTERNAL,
            'escalation_level' => 0,
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_thread_id' => $thread->id,
            'school_id' => $school->id,
            'sender_id' => $creator->id,
            'sender_role' => 'teacher',
            'message' => 'Please help with this class issue.',
            'is_internal_note' => false,
        ]);

        return $thread;
    }

    private function school(): School
    {
        return School::create([
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function schoolUser(School $school, string $role, string $email): User
    {
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => $email,
        ]);
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
