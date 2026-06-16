<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\ResultAccessRequest;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\CurrentSchoolService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StageDResultAccessGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_submit_manual_result_access_request(): void
    {
        [$school, $parent, $student, $session, $term] = $this->publishedResultSetupForParent();

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($parent)
            ->post(route('portal.results.requests.store'), [
                'student_id' => $student->id,
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
                'result_type' => 'term_result',
                'access_method' => ResultAccessRequest::METHOD_MANUAL_APPROVAL,
                'request_note' => 'Please approve result access.',
            ])
            ->assertRedirect(route('portal.results.index'));

        $this->assertDatabaseHas('result_access_requests', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $parent->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'access_method' => ResultAccessRequest::METHOD_MANUAL_APPROVAL,
            'status' => ResultAccessRequest::STATUS_PENDING,
        ]);
    }

    public function test_school_admin_can_approve_result_access_request(): void
    {
        [$school, $parent, $student, $session, $term] = $this->publishedResultSetupForParent();
        $admin = $this->portalUser($school, 'school_admin', 'school.admin@example.com');

        $accessRequest = ResultAccessRequest::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $parent->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'access_method' => ResultAccessRequest::METHOD_MANUAL_APPROVAL,
            'status' => ResultAccessRequest::STATUS_PENDING,
        ]);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'school_admin');

        $this->actingAs($admin)
            ->post(route('school.result-access-requests.approve', ['resultAccessRequest' => $accessRequest->id]), [
                'expires_in_days' => 30,
                'decision_note' => 'Approved.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('result_access_requests', [
            'id' => $accessRequest->id,
            'status' => ResultAccessRequest::STATUS_APPROVED,
            'approved_by' => $admin->id,
        ]);

        $this->assertNotNull($accessRequest->fresh()->approved_at);
    }

    public function test_approved_parent_can_view_published_result(): void
    {
        [$school, $parent, $student, $session, $term] = $this->publishedResultSetupForParent();

        $accessRequest = ResultAccessRequest::query()->create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $parent->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'access_method' => ResultAccessRequest::METHOD_MANUAL_APPROVAL,
            'status' => ResultAccessRequest::STATUS_APPROVED,
            'approved_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'parent');

        $this->actingAs($parent)
            ->get(route('portal.results.show', ['resultAccessRequest' => $accessRequest->id]))
            ->assertOk()
            ->assertSee($student->fullName())
            ->assertSee('Mathematics');
    }

    public function test_student_can_unlock_result_with_scratch_card(): void
    {
        [$school, $studentUser, $student, $session, $term] = $this->publishedResultSetupForStudent();
        $card = $this->scratchCard($school, $student, $session, $term, 'CARD-STUDENT-1', '123456');

        $this->withoutMiddleware();
        $this->mockSchoolContext($school, 'student');

        $this->actingAs($studentUser)
            ->post(route('portal.results.requests.store'), [
                'student_id' => $student->id,
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
                'result_type' => 'term_result',
                'access_method' => ResultAccessRequest::METHOD_SCRATCH_CARD,
                'scratch_card_serial' => 'CARD-STUDENT-1',
                'scratch_card_pin' => '123456',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('result_access_requests', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'requester_user_id' => $studentUser->id,
            'scratch_card_id' => $card->id,
            'status' => ResultAccessRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('scratch_card_usages', [
            'scratch_card_id' => $card->id,
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
        ]);
    }

    private function publishedResultSetupForParent(): array
    {
        $school = $this->school();
        $parent = $this->portalUser($school, 'parent', 'stage.d.parent@example.com');
        $student = $this->student($school);

        app(StudentPortalLinkService::class)->attachParentToStudent($parent, $student, 'guardian', true);

        [$session, $term] = $this->sessionAndTerm($school);

        $this->publishedResult($school, $student, $session, $term);

        return [$school, $parent, $student, $session, $term];
    }

    private function publishedResultSetupForStudent(): array
    {
        $school = $this->school();
        $studentUser = $this->portalUser($school, 'student', 'stage.d.student@example.com');
        $student = $this->student($school, [
            'student_user_id' => $studentUser->id,
        ]);

        [$session, $term] = $this->sessionAndTerm($school);

        $this->publishedResult($school, $student, $session, $term);

        return [$school, $studentUser, $student, $session, $term];
    }

    private function school(array $overrides = []): School
    {
        $columns = Schema::getColumnListing('schools');

        $defaults = [
            'name' => 'Stage D School '.uniqid(),
            'slug' => 'stage-d-school-'.uniqid(),
            'code' => 'SD'.uniqid(),
            'school_code' => 'SD'.uniqid(),
            'short_name' => 'Stage D',
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

    private function student(School $school, array $overrides = []): Student
    {
        $columns = Schema::getColumnListing('students');

        $defaults = [
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
        ];

        $data = array_intersect_key(array_merge($defaults, $overrides), array_flip($columns));

        return Student::unguarded(fn () => Student::query()->create($data));
    }

    private function sessionAndTerm(School $school): array
    {
        $sessionColumns = Schema::getColumnListing('academic_sessions');

        $sessionData = array_intersect_key([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->addMonths(10)->toDateString(),
            'status' => 'active',
            'is_active' => true,
        ], array_flip($sessionColumns));

        $session = AcademicSession::unguarded(fn () => AcademicSession::query()->create($sessionData));

        $termColumns = Schema::getColumnListing('terms');

        $termData = array_intersect_key([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->addMonths(2)->toDateString(),
            'status' => 'active',
            'is_active' => true,
        ], array_flip($termColumns));

        $term = Term::unguarded(fn () => Term::query()->create($termData));

        return [$session, $term];
    }

    private function publishedResult(School $school, Student $student, AcademicSession $session, Term $term): StudentResult
    {
        $columns = Schema::getColumnListing('student_results');
        $subjectId = $this->tableRecordId('subjects', [
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'status' => 'active',
        ]);

        $data = array_intersect_key([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $student->school_class_id,
            'subject_id' => $subjectId,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 60,
            'total_score' => 90,
            'grade' => 'A',
            'teacher_remark' => 'Excellent',
            'status' => 'published',
            'published_at' => now(),
            'unpublished_at' => null,
        ], array_flip($columns));

        return StudentResult::unguarded(fn () => StudentResult::query()->create($data));
    }

    private function scratchCard(
        School $school,
        Student $student,
        AcademicSession $session,
        Term $term,
        string $serial,
        string $pin
    ): ScratchCard {
        $columns = Schema::getColumnListing('scratch_cards');

        $batchId = $this->tableRecordId('scratch_card_batches', [
            'school_id' => $school->id,
            'batch_code' => 'BATCH-'.uniqid(),
            'quantity' => 1,
            'status' => 'active',
            'generated_by' => null,
        ]);

        $data = array_intersect_key([
            'scratch_card_batch_id' => $batchId,
            'school_id' => $school->id,
            'school_class_id' => $student->school_class_id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'serial_number' => $serial,
            'pin_code' => $pin,
            'pin_hash' => hash('sha256', $pin),
            'max_uses' => 3,
            'used_count' => 0,
            'status' => 'active',
            'expires_at' => now()->addMonth(),
            'generated_by' => null,
        ], array_flip($columns));

        return ScratchCard::unguarded(fn () => ScratchCard::query()->create($data));
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
