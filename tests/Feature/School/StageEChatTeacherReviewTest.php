<?php

namespace Tests\Feature\School;

use App\Models\PortalConversation;
use App\Models\PortalMessage;
use App\Models\School;
use App\Models\Student;
use App\Models\TeacherReview;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StageEChatTeacherReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_start_conversation_and_school_admin_can_reply(): void
    {
        [$school, $parent, $student] = $this->parentSetup();
        $admin = $this->portalUser($school, 'school_admin', 'stage.e.admin@example.com');

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($parent)
            ->post(route('portal.conversations.store'), [
                'subject' => 'Result question',
                'conversation_type' => 'result',
                'recipient_user_ids' => [$admin->id],
                'body' => 'Please I need help with my child result.',
            ])
            ->assertRedirect();

        $conversation = PortalConversation::query()->first();

        $this->assertNotNull($conversation);
        $this->assertDatabaseHas('portal_conversation_participants', [
            'portal_conversation_id' => $conversation->id,
            'user_id' => $parent->id,
            'school_id' => $school->id,
        ]);
        $this->assertDatabaseHas('portal_conversation_participants', [
            'portal_conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'school_id' => $school->id,
        ]);

        $this->mockSchoolContext($school, 'school_admin');

        $this->actingAs($admin)
            ->post(route('portal.conversations.messages.store', ['conversationId' => $conversation->id]), [
                'body' => 'We will check and respond shortly.',
            ])
            ->assertRedirect(route('portal.conversations.show', ['conversationId' => $conversation->id]));

        $this->assertDatabaseHas('portal_messages', [
            'portal_conversation_id' => $conversation->id,
            'school_id' => $school->id,
            'sender_user_id' => $admin->id,
        ]);

        $this->assertSame(2, PortalMessage::query()->where('portal_conversation_id', $conversation->id)->count());
    }

    public function test_unrelated_user_cannot_view_another_conversation(): void
    {
        [$school, $parent] = $this->parentSetup();
        $admin = $this->portalUser($school, 'school_admin', 'stage.e.admin2@example.com');
        $otherParent = $this->portalUser($school, 'parent', 'stage.e.other.parent@example.com');

        $conversation = PortalConversation::query()->create([
            'school_id' => $school->id,
            'created_by' => $parent->id,
            'subject' => 'Private message',
            'conversation_type' => 'general',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $conversation->participants()->create([
            'school_id' => $school->id,
            'user_id' => $parent->id,
            'participant_role' => 'parent',
        ]);

        $conversation->participants()->create([
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'participant_role' => 'school_admin',
        ]);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($otherParent)
            ->get(route('portal.conversations.show', ['conversationId' => $conversation->id]))
            ->assertForbidden();
    }

    public function test_parent_can_submit_teacher_review(): void
    {
        [$school, $parent, $student] = $this->parentSetup();
        $teacher = $this->portalUser($school, 'teacher', 'stage.e.teacher@example.com');
        $admin = $this->portalUser($school, 'school_admin', 'stage.e.admin3@example.com');

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($parent)
            ->post(route('portal.teacher-reviews.store'), [
                'teacher_user_id' => $teacher->id,
                'student_id' => $student->id,
                'rating' => 5,
                'title' => 'Excellent support',
                'comment' => 'The teacher has been very supportive.',
            ])
            ->assertRedirect(route('portal.teacher-reviews.index'));

        $this->assertDatabaseHas('teacher_reviews', [
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'reviewer_user_id' => $parent->id,
            'student_id' => $student->id,
            'rating' => 5,
            'status' => TeacherReview::STATUS_PENDING,
        ]);
    }

    public function test_school_admin_can_approve_teacher_review(): void
    {
        [$school, $parent, $student] = $this->parentSetup();
        $teacher = $this->portalUser($school, 'teacher', 'stage.e.teacher2@example.com');
        $admin = $this->portalUser($school, 'school_admin', 'stage.e.admin4@example.com');

        $review = TeacherReview::query()->create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'reviewer_user_id' => $parent->id,
            'student_id' => $student->id,
            'rating' => 4,
            'title' => 'Good work',
            'comment' => 'Good teacher.',
            'status' => TeacherReview::STATUS_PENDING,
        ]);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'school_admin');

        $this->actingAs($admin)
            ->post(route('school.teacher-reviews.approve', ['teacherReview' => $review->id]), [
                'moderation_note' => 'Approved.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('teacher_reviews', [
            'id' => $review->id,
            'status' => TeacherReview::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertNotNull($review->fresh()->reviewed_at);
    }

    private function parentSetup(): array
    {
        $school = $this->school();
        $parent = $this->portalUser($school, 'parent', 'stage.e.parent@example.com');
        $student = $this->student($school);

        app(StudentPortalLinkService::class)->attachParentToStudent($parent, $student, 'guardian', true);

        return [$school, $parent, $student];
    }

    private function school(array $overrides = []): School
    {
        $columns = Schema::getColumnListing('schools');

        $defaults = [
            'name' => 'Stage E School '.uniqid(),
            'slug' => 'stage-e-school-'.uniqid(),
            'code' => 'SE'.uniqid(),
            'school_code' => 'SE'.uniqid(),
            'short_name' => 'Stage E',
            'email' => 'school.'.uniqid().'@example.com',
            'contact_email' => 'school.'.uniqid().'@example.com',
            'phone' => '08000000000',
            'contact_phone' => '08000000000',
            'address' => 'Ilorin',
            'city' => 'Ilorin',
            'state' => 'Kwara',
            'country' => 'Nigeria',
            'status' => 'active',
            'subscription_status' => 'active',
            'is_active' => true,
        ];

        $data = array_intersect_key(array_merge($defaults, $overrides), array_flip($columns));

        return School::unguarded(fn () => School::query()->create($data));
    }

    private function portalUser(School $school, string $role, string $email): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => $email,
        ]);

        $user->assignRole($role);

        UserSchoolRole::query()->updateOrCreate([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
        ], [
            'status' => 'active',
            'assigned_by' => null,
        ]);

        return $user;
    }

    private function student(School $school): Student
    {
        $columns = Schema::getColumnListing('students');

        $data = array_intersect_key([
            'school_id' => $school->id,
            'school_class_id' => $this->tableRecordId('school_classes', [
                'school_id' => $school->id,
                'name' => 'JSS 1',
                'code' => 'JSS1',
                'status' => 'active',
            ]),
            'admission_number' => 'ADM-'.uniqid(),
            'first_name' => 'Stage',
            'middle_name' => null,
            'last_name' => 'Student',
            'gender' => 'female',
            'date_of_birth' => now()->subYears(10)->toDateString(),
            'guardian_name' => 'Guardian User',
            'guardian_phone' => '08000000000',
            'guardian_email' => 'guardian.'.uniqid().'@example.com',
            'address' => 'Ilorin',
            'status' => 'active',
        ], array_flip($columns));

        return Student::unguarded(fn () => Student::query()->create($data));
    }

    private function tableRecordId(string $table, array $data): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $columns = Schema::getColumnListing($table);
        $payload = array_intersect_key($data, array_flip($columns));

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = now();
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = now();
        }

        return (int) DB::table($table)->insertGetId($payload);
    }

    private function mockSchoolContext(School $school, string $roleContext): void
    {
        $this->mock(CurrentSchoolService::class, function ($mock) use ($school, $roleContext) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn($school);
            $mock->shouldReceive('roleContext')->withAnyArgs()->andReturn($roleContext);
        });
    }
}
