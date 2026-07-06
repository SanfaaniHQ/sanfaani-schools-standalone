<?php

namespace Tests\Feature\School;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\GradingScale;
use App\Models\PdfSnapshot;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\CbtAttemptService;
use App\Services\CbtResultIntegrationService;
use App\Services\PdfSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CbtEcosystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_objective_cbt_attempt_syncs_to_student_result_and_publishes(): void
    {
        $context = $this->createCbtContext();
        $admin = $this->createUserForSchool($context['school'], 'school_admin');
        $request = Request::create('/cbt-test', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Feature Test Browser',
        ]);

        $attempts = app(CbtAttemptService::class);
        $candidate = $attempts->candidateForStudent($context['exam'], $context['student']);
        $attempt = $attempts->start($context['exam'], $candidate, $request, $admin, 'internal');
        $answer = $attempt->answers()->firstOrFail();

        $attempts->saveAnswer($attempt, $answer->cbt_exam_question_id, [
            'selected_option_ids' => [$context['correctOption']->id],
        ], $request);

        $submitted = $attempts->submit($attempt->fresh(), $request);

        $this->assertSame('graded', $submitted->status);
        $this->assertEquals(1.0, (float) $submitted->total_score);
        $this->assertDatabaseHas('student_results', [
            'school_id' => $context['school']->id,
            'student_id' => $context['student']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'cbt_result',
            'total_score' => 100,
            'status' => ResultWorkflowStatus::Reviewed->value,
        ]);

        app(CbtResultIntegrationService::class)->publishExam($context['exam'], $context['school'], $admin->id);

        $this->assertDatabaseHas('student_results', [
            'school_id' => $context['school']->id,
            'student_id' => $context['student']->id,
            'result_type' => 'cbt_result',
            'status' => ResultWorkflowStatus::Published->value,
        ]);
        $this->assertSame('published', $submitted->fresh()->result_release_status);
    }

    public function test_public_candidate_flow_uses_attempt_uuid_and_school_isolated_candidate(): void
    {
        $context = $this->createCbtContext([
            'access_type' => 'public_candidate',
            'supports_public_candidates' => true,
            'show_result_immediately' => true,
        ]);

        $response = $this->post(route('public.cbt.access', [
            'school' => $context['school'],
            'exam' => $context['exam']->slug,
        ]), [
            'access_mode' => 'public_registration',
            'name' => 'External Candidate',
            'email' => 'candidate@example.test',
            'phone' => '+2348000000000',
        ]);

        $attempt = CbtAttempt::query()->sole();

        $response->assertRedirect(route('public.cbt.take', ['attempt' => $attempt->attempt_uuid]));
        $this->assertStringContainsString('/cbt/attempts/'.$attempt->attempt_uuid, $response->headers->get('Location'));
        $this->assertNotEquals((string) $attempt->id, $attempt->attempt_uuid);
        $this->assertDatabaseHas('cbt_candidates', [
            'school_id' => $context['school']->id,
            'cbt_exam_id' => $context['exam']->id,
            'student_id' => null,
            'source' => 'public',
            'email' => 'candidate@example.test',
        ]);

        $this->get(route('public.cbt.take', ['attempt' => $attempt->attempt_uuid]))
            ->assertOk()
            ->assertSee($context['exam']->title);
    }

    public function test_question_bank_routes_block_cross_school_access(): void
    {
        $school = $this->createSchool('Alpha CBT School', 'alpha-cbt-school');
        $otherSchool = $this->createSchool('Beta CBT School', 'beta-cbt-school');
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $foreignBank = CbtQuestionBank::create([
            'school_id' => $otherSchool->id,
            'title' => 'Other School Bank',
            'code' => 'OTHER-BANK',
            'difficulty' => 'mixed',
            'default_locale' => 'en',
            'status' => 'active',
            'is_reusable' => true,
        ]);

        $this->get(route('school.cbt.question-banks.show', $foreignBank))
            ->assertForbidden();
    }

    public function test_pdf_snapshot_generation_is_immutable_versioned_and_rtl_safe(): void
    {
        $context = $this->createCbtContext();
        $service = app(PdfSnapshotService::class);

        $snapshot = $service->captureAndGenerate(
            'cbt_result',
            'CBT Result Snapshot',
            [
                'candidate' => ['name' => 'Aisha Student'],
                'exam' => ['title' => $context['exam']->title],
                'score' => ['total_score' => 1, 'max_score' => 1],
            ],
            $context['school'],
            $context['exam'],
            $context['student'],
            referenceCode: 'CBT-SNAPSHOT-001',
            locale: 'ar'
        );

        $repeat = $service->capture(
            'cbt_result',
            'CBT Result Snapshot',
            [
                'candidate' => ['name' => 'Aisha Student'],
                'exam' => ['title' => $context['exam']->title],
                'score' => ['total_score' => 1, 'max_score' => 1],
            ],
            $context['school'],
            $context['exam'],
            $context['student'],
            referenceCode: 'CBT-SNAPSHOT-001',
            locale: 'ar'
        );

        $changed = $service->capture(
            'cbt_result',
            'CBT Result Snapshot',
            [
                'candidate' => ['name' => 'Aisha Student'],
                'exam' => ['title' => $context['exam']->title],
                'score' => ['total_score' => 0, 'max_score' => 1],
            ],
            $context['school'],
            $context['exam'],
            $context['student'],
            referenceCode: 'CBT-SNAPSHOT-001',
            locale: 'ar'
        );

        $this->assertTrue($snapshot->is($repeat));
        $this->assertSame(1, $snapshot->snapshot_version);
        $this->assertSame(2, $changed->snapshot_version);
        $this->assertSame('rtl', $snapshot->direction);
        $this->assertNotNull($snapshot->verification_code);
        $this->assertNotNull($snapshot->pdf_hash);
        $this->assertTrue(Storage::disk($snapshot->pdf_disk)->exists($snapshot->pdf_path));
        $this->assertSame(2, PdfSnapshot::count());

        Storage::disk($snapshot->pdf_disk)->delete($snapshot->pdf_path);
    }

    private function createCbtContext(array $examOverrides = []): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => 'A',
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'status' => 'active',
            'is_active' => true,
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'status' => 'active',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => 'ADM-CBT-001',
            'first_name' => 'Aisha',
            'last_name' => 'Student',
            'guardian_email' => 'guardian@example.test',
            'status' => 'active',
        ]);
        GradingScale::create([
            'school_id' => $school->id,
            'name' => 'Distinction',
            'min_score' => 80,
            'max_score' => 100,
            'grade' => 'A',
            'remark' => 'Excellent',
            'is_pass' => true,
            'sort_order' => 1,
            'status' => 'active',
        ]);
        $bank = CbtQuestionBank::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'Mathematics Objective Bank',
            'code' => 'MTH-OBJ',
            'difficulty' => 'medium',
            'default_locale' => 'en',
            'status' => 'active',
            'is_reusable' => true,
        ]);
        $question = CbtQuestion::create([
            'school_id' => $school->id,
            'cbt_question_bank_id' => $bank->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'question_type' => 'multiple_choice',
            'prompt' => 'What is 2 + 2?',
            'prompt_html' => '<p>What is 2 + 2?</p>',
            'default_locale' => 'en',
            'direction' => 'ltr',
            'difficulty' => 'easy',
            'topic' => 'Arithmetic',
            'default_marks' => 1,
            'status' => 'active',
        ]);
        $wrongOption = CbtQuestionOption::create([
            'school_id' => $school->id,
            'cbt_question_id' => $question->id,
            'option_key' => 'A',
            'body' => '3',
            'body_html' => '<p>3</p>',
            'is_correct' => false,
            'sort_order' => 1,
        ]);
        $correctOption = CbtQuestionOption::create([
            'school_id' => $school->id,
            'cbt_question_id' => $question->id,
            'option_key' => 'B',
            'body' => '4',
            'body_html' => '<p>4</p>',
            'is_correct' => true,
            'sort_order' => 2,
        ]);
        $exam = CbtExam::create(array_merge([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'Mathematics CBT',
            'slug' => 'mathematics-cbt',
            'exam_type' => 'objective',
            'access_type' => 'internal_student',
            'result_type' => 'cbt_result',
            'status' => 'open',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'question_count' => 1,
            'total_marks' => 1,
            'randomize_questions' => true,
            'randomize_options' => true,
            'allow_resume' => true,
            'auto_submit' => true,
            'show_result_immediately' => false,
            'supports_public_candidates' => false,
        ], $examOverrides));
        CbtExamQuestion::create([
            'school_id' => $school->id,
            'cbt_exam_id' => $exam->id,
            'cbt_question_id' => $question->id,
            'marks' => 1,
            'sort_order' => 1,
            'is_required' => true,
        ]);

        return compact(
            'school',
            'class',
            'session',
            'term',
            'subject',
            'student',
            'bank',
            'question',
            'wrongOption',
            'correctOption',
            'exam'
        );
    }

    private function createSchool(string $name = 'Sanfaani CBT School', string $slug = 'sanfaani-cbt-school'): School
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
