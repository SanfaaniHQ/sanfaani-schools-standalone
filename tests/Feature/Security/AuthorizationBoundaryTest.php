<?php

namespace Tests\Feature\Security;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SupportMessage;
use App\Models\SupportMessageAttachment;
use App\Models\SupportThread;
use App\Models\TeacherClassAssignment;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'parent', 'student', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_invalid_school_context_fails_closed_for_school_user(): void
    {
        $schoolA = $this->school('Context Alpha', 'context-alpha');
        $schoolB = $this->school('Context Beta', 'context-beta');
        $adminA = $this->schoolUser($schoolA, 'school_admin', 'context-admin@example.test');

        $this->actingAs($adminA);
        session([
            'active_school_id' => $schoolB->id,
            'active_role_context' => 'school_admin',
        ]);

        $this->get(route('school.dashboard'))
            ->assertRedirect(route('workspace.create'));
    }

    public function test_parent_student_and_accountant_roles_cannot_access_school_staff_routes(): void
    {
        $school = $this->school('Boundary School', 'boundary-school');
        $class = $this->schoolClass($school);
        $studentRecord = $this->student($school, $class, 'BOUND-001');

        foreach (['parent', 'student', 'accountant'] as $role) {
            $user = $this->schoolUser($school, $role, $role.'@example.test');
            $this->actAsSchoolRole($user, $school, $role);

            $this->get(route('school.students.show', $studentRecord))->assertForbidden();
        }
    }

    public function test_teacher_cannot_view_unassigned_student_in_same_school(): void
    {
        $school = $this->school('Teacher Boundary', 'teacher-boundary');
        $assignedClass = $this->schoolClass($school, 'Assigned');
        $otherClass = $this->schoolClass($school, 'Unassigned');
        $assignedStudent = $this->student($school, $assignedClass, 'ASSIGNED-001');
        $unassignedStudent = $this->student($school, $otherClass, 'UNASSIGNED-001');
        $teacher = $this->schoolUser($school, 'teacher', 'boundary-teacher@example.test');

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $assignedClass->id,
            'status' => 'active',
        ]);

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->get(route('school.students.show', $assignedStudent))->assertOk();
        $this->get(route('school.students.show', $unassignedStudent))->assertForbidden();
    }

    public function test_support_attachment_downloads_cannot_cross_school_boundaries(): void
    {
        Storage::fake('local');

        $schoolA = $this->school('Attachment Alpha', 'attachment-alpha');
        $schoolB = $this->school('Attachment Beta', 'attachment-beta');
        $adminA = $this->schoolUser($schoolA, 'school_admin', 'attachment-a@example.test');
        $adminB = $this->schoolUser($schoolB, 'school_admin', 'attachment-b@example.test');
        $threadB = $this->supportThread($schoolB, $adminB);
        $messageB = SupportMessage::create([
            'support_thread_id' => $threadB->id,
            'school_id' => $schoolB->id,
            'sender_id' => $adminB->id,
            'sender_role' => 'school_admin',
            'message' => 'Private file',
            'is_internal_note' => false,
        ]);

        Storage::disk('local')->put('support-attachments/schools/'.$schoolB->id.'/private.txt', 'private');
        $attachment = SupportMessageAttachment::create([
            'support_message_id' => $messageB->id,
            'school_id' => $schoolB->id,
            'uploaded_by' => $adminB->id,
            'disk' => 'local',
            'path' => 'support-attachments/schools/'.$schoolB->id.'/private.txt',
            'original_name' => 'private.txt',
            'mime_type' => 'text/plain',
            'size' => 7,
        ]);

        $this->actAsSchoolRole($adminA, $schoolA, 'school_admin');
        $this->get(route('school.support-attachments.download', $attachment))->assertForbidden();

        $this->actAsSchoolRole($adminB, $schoolB, 'school_admin');
        $this->get(route('school.support-attachments.download', $attachment))->assertOk();
    }

    public function test_new_support_uploads_are_stored_under_school_prefixed_paths(): void
    {
        Storage::fake('local');

        $school = $this->school('Upload Tenant', 'upload-tenant');
        $teacher = $this->schoolUser($school, 'teacher', 'upload-teacher@example.test');

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $this->post(route('school.support.store'), [
            'subject' => 'Upload proof',
            'category' => 'technical_issue',
            'priority' => 'normal',
            'message' => 'Please see attached proof.',
            'attachments' => [UploadedFile::fake()->create('proof.txt', 4, 'text/plain')],
        ])->assertRedirect();

        $attachment = SupportMessageAttachment::firstOrFail();

        $this->assertStringStartsWith('support-attachments/schools/'.$school->id.'/', $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);
    }

    private function school(string $name, string $slug): School
    {
        return School::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function schoolClass(School $school, string $name = 'Basic 1'): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => $name,
            'section' => 'A',
            'status' => 'active',
        ]);
    }

    private function student(School $school, SchoolClass $class, string $admissionNumber): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Boundary',
            'last_name' => 'Student',
            'guardian_email' => strtolower($admissionNumber).'@example.test',
            'status' => 'active',
        ]);
    }

    private function supportThread(School $school, User $creator): SupportThread
    {
        return SupportThread::create([
            'school_id' => $school->id,
            'created_by' => $creator->id,
            'creator_role' => 'school_admin',
            'routed_to_role' => SupportThread::ROUTE_SUPER_ADMIN,
            'subject' => 'Attachment boundary',
            'category' => 'technical_issue',
            'priority' => 'normal',
            'status' => SupportThread::STATUS_ESCALATED,
            'visibility' => SupportThread::VISIBILITY_ESCALATED,
            'escalation_level' => 1,
            'last_message_at' => now(),
        ]);
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
