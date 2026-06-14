<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\CbtQuestionOption;
use App\Models\ClassSubjectAssignment;
use App\Models\LmsCbtActivity;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LmsCbtIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_lms_cbt_routes_require_authentication(): void
    {
        $this->post(route('school.lms.classrooms.cbt.store', 1), ['cbt_exam_id' => 1])
            ->assertRedirect(route('login'));

        $this->post(route('school.lms.materials.cbt.store', 1), ['cbt_exam_id' => 1])
            ->assertRedirect(route('login'));

        $this->delete(route('school.lms.cbt-links.destroy', 1))
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_link_cbt_item_to_lms_classroom_and_material(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
            'title' => 'Week 1 Quiz',
        ])->assertRedirect();

        $this->post(route('school.lms.materials.cbt.store', $context['material']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('lms_cbt_activities', [
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => null,
            'cbt_exam_id' => $context['exam']->id,
            'target_type' => LmsCbtActivity::TARGET_CLASSROOM,
            'target_id' => $context['classroom']->id,
            'title' => 'Week 1 Quiz',
            'status' => LmsCbtActivity::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('lms_cbt_activities', [
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $context['material']->id,
            'cbt_exam_id' => $context['exam']->id,
            'target_type' => LmsCbtActivity::TARGET_MATERIAL,
            'target_id' => $context['material']->id,
            'status' => LmsCbtActivity::STATUS_ACTIVE,
        ]);
    }

    public function test_teacher_can_link_cbt_item_only_for_assigned_class_subject_scope(): void
    {
        $context = $this->lmsCbtContext('teacher');
        $this->assignTeacherToSubject($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->post(route('school.lms.materials.cbt.store', $context['material']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('lms_cbt_activities', [
            'school_id' => $context['school']->id,
            'target_type' => LmsCbtActivity::TARGET_CLASSROOM,
            'target_id' => $context['classroom']->id,
            'cbt_exam_id' => $context['exam']->id,
            'created_by' => $context['user']->id,
        ]);

        $this->assertDatabaseHas('lms_cbt_activities', [
            'school_id' => $context['school']->id,
            'target_type' => LmsCbtActivity::TARGET_MATERIAL,
            'target_id' => $context['material']->id,
            'cbt_exam_id' => $context['exam']->id,
            'created_by' => $context['user']->id,
        ]);
    }

    public function test_teacher_cannot_link_unassigned_cbt_item(): void
    {
        $context = $this->lmsCbtContext('teacher');
        $this->assignTeacherToSubject($context);
        $otherClass = SchoolClass::create([
            'school_id' => $context['school']->id,
            'name' => 'JSS 2',
            'section' => 'B',
            'status' => 'active',
        ]);
        $otherExam = $this->createExam($context, [
            'title' => 'Unassigned Class CBT',
            'slug' => 'unassigned-class-cbt-'.fake()->unique()->numberBetween(1000, 9999),
            'school_class_id' => $otherClass->id,
        ]);

        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->from(route('school.lms.classrooms.show', $context['classroom']))
            ->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
                'cbt_exam_id' => $otherExam->id,
            ])
            ->assertRedirect(route('school.lms.classrooms.show', $context['classroom']))
            ->assertSessionHasErrors('cbt_exam_id');

        $this->assertDatabaseCount('lms_cbt_activities', 0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_cbt_activity_link_failed']);
    }

    public function test_teacher_cannot_unlink_admin_linked_cbt_item_outside_teacher_cbt_scope(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $schoolWideExam = $this->createExam($context, [
            'title' => 'School Wide CBT',
            'slug' => 'school-wide-cbt-'.fake()->unique()->numberBetween(1000, 9999),
            'school_class_id' => null,
            'subject_id' => null,
        ]);

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $schoolWideExam->id,
        ])->assertRedirect();

        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $teacherContext = array_merge($context, ['user' => $teacher]);
        $this->assignTeacherToSubject($teacherContext);
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');

        $activity = LmsCbtActivity::query()->sole();

        $this->delete(route('school.lms.cbt-links.destroy', $activity))
            ->assertForbidden();

        $this->assertSame(LmsCbtActivity::STATUS_ACTIVE, $activity->fresh()->status);
    }

    public function test_cross_school_cbt_item_cannot_be_linked(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $other = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.lms.classrooms.show', $context['classroom']))
            ->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
                'cbt_exam_id' => $other['exam']->id,
            ])
            ->assertRedirect(route('school.lms.classrooms.show', $context['classroom']))
            ->assertSessionHasErrors('cbt_exam_id');

        $this->assertDatabaseCount('lms_cbt_activities', 0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_cbt_activity_link_failed']);
    }

    public function test_cross_school_lms_classroom_and_material_cannot_be_used(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $other = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.classrooms.cbt.store', $other['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertForbidden();

        $this->post(route('school.lms.materials.cbt.store', $other['material']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertForbidden();

        $this->assertDatabaseCount('lms_cbt_activities', 0);
    }

    public function test_result_officer_and_accountant_cannot_manage_lms_cbt_links(): void
    {
        $context = $this->lmsCbtContext('school_admin');

        foreach (['result_officer', 'accountant'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
                'cbt_exam_id' => $context['exam']->id,
            ])->assertForbidden();

            $this->post(route('school.lms.materials.cbt.store', $context['material']), [
                'cbt_exam_id' => $context['exam']->id,
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('lms_cbt_activities', 0);
    }

    public function test_duplicate_lms_cbt_link_is_rejected_without_creating_duplicate(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->from(route('school.lms.classrooms.show', $context['classroom']))
            ->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
                'cbt_exam_id' => $context['exam']->id,
            ])
            ->assertRedirect(route('school.lms.classrooms.show', $context['classroom']))
            ->assertSessionHasErrors('cbt_exam_id');

        $this->assertDatabaseCount('lms_cbt_activities', 1);
    }

    public function test_linked_cbt_activity_appears_on_lms_classroom_and_material_pages(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->post(route('school.lms.materials.cbt.store', $context['material']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->get(route('school.lms.classrooms.show', $context['classroom']))
            ->assertOk()
            ->assertSee('CBT Activities')
            ->assertSee($context['exam']->title)
            ->assertSee('CBT attempt and result rules remain unchanged');

        $this->get(route('school.lms.materials.show', $context['material']))
            ->assertOk()
            ->assertSee('CBT Activities')
            ->assertSee($context['exam']->title);
    }

    public function test_unpublished_lms_material_does_not_expose_cbt_link_to_unauthorized_viewer(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        LmsCbtActivity::create($this->activityPayload($context, LmsCbtActivity::TARGET_MATERIAL));
        $unassignedTeacher = $this->createUserForSchool($context['school'], 'teacher');

        $this->actAsSchoolRole($unassignedTeacher, $context['school'], 'teacher');

        $this->get(route('school.lms.materials.show', $context['material']))
            ->assertForbidden();
    }

    public function test_linking_does_not_modify_existing_cbt_attempt_or_result_logic(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $before = $context['exam']->only(['status', 'question_count', 'total_marks', 'result_type', 'max_attempts']);

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $context['exam']->refresh();
        $this->assertSame($before['status'], $context['exam']->status);
        $this->assertSame((int) $before['question_count'], (int) $context['exam']->question_count);
        $this->assertEquals((float) $before['total_marks'], (float) $context['exam']->total_marks);
        $this->assertSame($before['result_type'], $context['exam']->result_type);
        $this->assertSame((int) $before['max_attempts'], (int) $context['exam']->max_attempts);
        $this->assertDatabaseCount('cbt_attempts', 0);
        $this->assertDatabaseHas('cbt_exam_questions', [
            'school_id' => $context['school']->id,
            'cbt_exam_id' => $context['exam']->id,
            'cbt_question_id' => $context['question']->id,
        ]);
    }

    public function test_link_and_unlink_actions_are_audit_logged(): void
    {
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.classrooms.cbt.store', $context['classroom']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $activity = LmsCbtActivity::firstOrFail();

        $this->delete(route('school.lms.cbt-links.destroy', $activity))
            ->assertRedirect();

        $this->assertSame(LmsCbtActivity::STATUS_ARCHIVED, $activity->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_cbt_activity_linked']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_cbt_activity_unlinked']);
    }

    public function test_lms_link_views_do_not_expose_raw_cbt_payloads(): void
    {
        $context = $this->lmsCbtContext('school_admin', [
            'question_prompt' => 'RAW-CBT-PROMPT-DO-NOT-SHOW',
            'correct_option' => 'SECRET-CORRECT-OPTION',
        ]);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.materials.cbt.store', $context['material']), [
            'cbt_exam_id' => $context['exam']->id,
        ])->assertRedirect();

        $this->get(route('school.lms.materials.show', $context['material']))
            ->assertOk()
            ->assertSee($context['exam']->title)
            ->assertDontSee('RAW-CBT-PROMPT-DO-NOT-SHOW')
            ->assertDontSee('SECRET-CORRECT-OPTION')
            ->assertDontSee('candidate_code')
            ->assertDontSee('answer_payload')
            ->assertDontSee('total_score');
    }

    public function test_lms_dashboard_sidebar_and_teacher_dashboard_surface_integration_safely(): void
    {
        $context = $this->lmsCbtContext('teacher');
        $this->assignTeacherToSubject($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Learning Materials')
            ->assertSee('eligible CBT activity links');

        $this->get(route('school.lms.index'))
            ->assertOk()
            ->assertSee('CBT Activities')
            ->assertSee('Existing CBT items can be linked');
    }

    public function test_standalone_dashboard_marks_lms_cbt_available_and_live_offline_lms_deferred(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);
        $context = $this->lmsCbtContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('LMS and CBT activity links')
            ->assertSee('CBT remains the assessment engine')
            ->assertSee('Live classes')
            ->assertSee('Full browser offline/PWA');
    }

    private function lmsCbtContext(string $role, array $overrides = []): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('A??'),
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('MTH???'),
            'status' => 'active',
            'assignment_type' => 'core',
            'is_elective' => false,
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->numerify('2026/2027 ###'),
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term '.fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
            'status' => 'active',
        ]);
        ClassSubjectAssignment::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'assignment_type' => 'core',
            'is_elective' => false,
            'is_required' => true,
            'status' => 'active',
        ]);

        $user = $this->createUserForSchool($school, $role);
        $classroom = LmsClassroom::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'JSS 1 Mathematics LMS',
            'status' => LmsClassroom::STATUS_ACTIVE,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $material = LmsMaterial::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'teacher_user_id' => $user->id,
            'title' => 'Draft Lesson',
            'body' => 'Draft material body.',
            'type' => LmsMaterial::TYPE_LESSON,
            'status' => LmsMaterial::STATUS_DRAFT,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $bank = CbtQuestionBank::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'Mathematics Bank '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('CBT???'),
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
            'prompt' => $overrides['question_prompt'] ?? 'What is 2 + 2?',
            'prompt_html' => '<p>'.e($overrides['question_prompt'] ?? 'What is 2 + 2?').'</p>',
            'default_locale' => 'en',
            'direction' => 'ltr',
            'difficulty' => 'easy',
            'topic' => 'Arithmetic',
            'default_marks' => 1,
            'status' => 'active',
        ]);
        CbtQuestionOption::create([
            'school_id' => $school->id,
            'cbt_question_id' => $question->id,
            'option_key' => 'A',
            'body' => 'Wrong answer',
            'is_correct' => false,
            'sort_order' => 1,
        ]);
        $correctOption = CbtQuestionOption::create([
            'school_id' => $school->id,
            'cbt_question_id' => $question->id,
            'option_key' => 'B',
            'body' => $overrides['correct_option'] ?? '4',
            'is_correct' => true,
            'sort_order' => 2,
        ]);
        $exam = $this->createExam(compact('school', 'class', 'subject', 'session', 'term'));
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
            'subject',
            'session',
            'term',
            'user',
            'classroom',
            'material',
            'bank',
            'question',
            'correctOption',
            'exam'
        );
    }

    private function createExam(array $context, array $overrides = []): CbtExam
    {
        return CbtExam::create(array_merge([
            'school_id' => $context['school']->id,
            'subject_id' => $context['subject']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'title' => 'Mathematics CBT '.fake()->unique()->numberBetween(1, 9999),
            'slug' => 'mathematics-cbt-'.fake()->unique()->numberBetween(1, 999999),
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
        ], $overrides));
    }

    private function activityPayload(array $context, string $targetType): array
    {
        $material = $targetType === LmsCbtActivity::TARGET_MATERIAL ? $context['material'] : null;
        $targetId = $material?->id ?? $context['classroom']->id;

        return [
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $material?->id,
            'cbt_exam_id' => $context['exam']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'status' => LmsCbtActivity::STATUS_ACTIVE,
            'created_by' => $context['user']->id,
            'updated_by' => $context['user']->id,
        ];
    }

    private function createSchool(): School
    {
        $id = fake()->unique()->numberBetween(1, 999999);

        return School::create([
            'name' => 'Sanfaani LMS CBT Academy '.$id,
            'slug' => 'sanfaani-lms-cbt-academy-'.$id,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
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

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }

    private function assignTeacherToSubject(array $context): void
    {
        TeacherSubjectAssignment::create([
            'school_id' => $context['school']->id,
            'teacher_user_id' => $context['user']->id,
            'subject_id' => $context['subject']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ]);
    }
}
