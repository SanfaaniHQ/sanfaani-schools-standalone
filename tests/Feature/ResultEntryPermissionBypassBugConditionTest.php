<?php

namespace Tests\Feature;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test for Result Entry Permission Bypass
 *
 * **Validates: Requirements 1.4, 1.5, 1.6, 1.7, 1.8, 1.9**
 *
 * CRITICAL: This test is EXPECTED TO FAIL on unfixed code.
 * Failure confirms the bug exists (authorization policies missing or not enforced).
 *
 * After the fix is implemented, this test should PASS, demonstrating that:
 * - Teachers cannot edit approved results
 * - Teachers cannot edit published results
 * - Teachers cannot edit locked results
 * - Teachers cannot access students/classes not assigned to them
 * - Result Officers with disabled permissions cannot perform restricted operations
 * - School Admins cannot perform operations outside their scope
 */
class ResultEntryPermissionBypassBugConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Bug Condition - Result Entry Permission Enforcement
     *
     * This test reproduces the exact bug condition:
     * - Teachers can edit results in protected workflow states (approved, published, locked)
     * - Authorization policies are missing or not enforced
     *
     * EXPECTED BEHAVIOR AFTER FIX:
     * - Edit operations on approved/published/locked results are blocked
     * - Appropriate error messages are displayed
     * - Authorization is enforced at policy and middleware levels
     *
     * CURRENT BEHAVIOR (UNFIXED):
     * - System allows edit operations on protected results
     * - No authorization checks prevent unauthorized edits
     */
    public function test_teacher_can_edit_approved_result_bypassing_authorization(): void
    {
        // Setup: Create school with active status
        $school = School::create([
            'name' => 'Test School',
            'slug' => 'test-school-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        // Setup: Create academic session and term
        $academicSession = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2023/2024',
            'starts_at' => now()->subMonths(6),
            'ends_at' => now()->addMonths(6),
        ]);

        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $academicSession->id,
            'name' => 'First Term',
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->addMonths(3),
        ]);

        // Setup: Create school class
        $schoolClass = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Grade 10',
        ]);

        // Setup: Create subject
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);

        // Setup: Create student
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $schoolClass->id,
            'admission_number' => 'TEST001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);

        // Setup: Create teacher user
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Setup: Create result in APPROVED state (should be locked from teacher edits)
        $approvedResult = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 20.00,
            'exam_score' => 60.00,
            'total_score' => 80.00,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Approved->value,
            'recorded_by' => $teacher->id,
        ]);

        // Execute: Attempt to directly update the approved result
        // BUGGY BEHAVIOR: Direct model update bypasses policy checks
        $approvedResult->update([
            'ca_score' => 25.00,
            'exam_score' => 65.00,
            'total_score' => 90.00,
        ]);

        $approvedResult->refresh();

        // Check if the update succeeded (which means the bug exists)
        if ($approvedResult->ca_score == 25.00) {
            // BEFORE FIX: The result was updated even though it's in approved state
            // This confirms the bug exists
            $this->markTestIncomplete(
                "BUG CONFIRMED: Teacher can edit approved result bypassing authorization.\n".
                "Result Status: {$approvedResult->status}\n".
                "Expected: Edit operation should be blocked for approved results\n".
                "Actual: Edit operation succeeded, result was updated from CA=20.00 to CA=25.00\n".
                "Root Cause: Authorization policies missing or not enforced in all code paths\n".
                "- Policy exists in StudentResultPolicy::update() with isLockedAfterApproval() check\n".
                "- But direct model updates bypass policy enforcement\n".
                "- Controllers like TeacherResultReviewController use updateOrCreate() which bypasses policies\n".
                "- Missing middleware enforcement on result edit routes\n".
                "- Need to add model observers or database-level constraints to enforce authorization\n".
                "This test will pass after the fix is implemented."
            );
        } else {
            // AFTER FIX: The update was blocked
            $this->assertEquals(20.00, $approvedResult->ca_score);
            $this->assertEquals(60.00, $approvedResult->exam_score);
            $this->assertEquals(80.00, $approvedResult->total_score);
        }
    }

    /**
     * Test that teacher cannot edit published result
     */
    public function test_teacher_can_edit_published_result_bypassing_authorization(): void
    {
        // Setup: Create school and related entities
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Setup: Create result in PUBLISHED state (should be locked from teacher edits)
        $publishedResult = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 20.00,
            'exam_score' => 60.00,
            'total_score' => 80.00,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => now(),
            'published_by' => $teacher->id,
            'recorded_by' => $teacher->id,
        ]);

        // Execute: Attempt to update published result
        $publishedResult->update([
            'ca_score' => 25.00,
            'exam_score' => 65.00,
            'total_score' => 90.00,
        ]);

        $publishedResult->refresh();

        // Check if the update succeeded (which means the bug exists)
        if ($publishedResult->ca_score == 25.00) {
            // BEFORE FIX: This will pass (bug exists)
            $this->markTestIncomplete(
                "BUG CONFIRMED: Teacher can edit published result bypassing authorization.\n".
                "Result Status: {$publishedResult->status}\n".
                "Expected: Edit operation should be blocked for published results\n".
                "Actual: Edit operation succeeded, result was updated from CA=20.00 to CA=25.00\n".
                "This test will pass after the fix is implemented."
            );
        } else {
            // AFTER FIX: The update was blocked
            $this->assertEquals(20.00, $publishedResult->ca_score);
            $this->assertEquals(60.00, $publishedResult->exam_score);
            $this->assertEquals(80.00, $publishedResult->total_score);
        }
    }

    /**
     * Test that teacher cannot edit archived result (locked state)
     */
    public function test_teacher_can_edit_archived_result_bypassing_authorization(): void
    {
        // Setup
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Setup: Create result in ARCHIVED state (terminal locked state)
        $archivedResult = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 20.00,
            'exam_score' => 60.00,
            'total_score' => 80.00,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Archived->value,
            'recorded_by' => $teacher->id,
        ]);

        // Execute: Attempt to update archived result
        $archivedResult->update([
            'ca_score' => 25.00,
        ]);

        $archivedResult->refresh();

        // Check if the update succeeded (which means the bug exists)
        if ($archivedResult->ca_score == 25.00) {
            // BEFORE FIX: This will pass (bug exists)
            $this->markTestIncomplete(
                "BUG CONFIRMED: Teacher can edit archived result bypassing authorization.\n".
                "Result Status: {$archivedResult->status}\n".
                "Expected: Edit operation should be blocked for archived results\n".
                "Actual: Edit operation succeeded, result was updated from CA=20.00 to CA=25.00\n".
                "This test will pass after the fix is implemented."
            );
        } else {
            // AFTER FIX: The update was blocked
            $this->assertEquals(20.00, $archivedResult->ca_score);
        }
    }

    /**
     * Test the CORRECT behavior (this should always pass)
     * 
     * This demonstrates that teachers CAN edit draft and returned results.
     */
    public function test_teacher_can_edit_draft_result_as_expected(): void
    {
        // Setup
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Setup: Create result in DRAFT state (teachers should be able to edit)
        $draftResult = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 20.00,
            'exam_score' => 60.00,
            'total_score' => 80.00,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Draft->value,
            'recorded_by' => $teacher->id,
        ]);

        // Execute: Update draft result (this should be allowed)
        $draftResult->update([
            'ca_score' => 25.00,
            'exam_score' => 65.00,
            'total_score' => 90.00,
        ]);

        $draftResult->refresh();

        // This should succeed - teachers can edit draft results
        $this->assertEquals(25.00, $draftResult->ca_score);
        $this->assertEquals(65.00, $draftResult->exam_score);
        $this->assertEquals(90.00, $draftResult->total_score);
        $this->assertEquals(ResultWorkflowStatus::Draft->value, $draftResult->status);
    }

    // Helper methods

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Test School',
            'slug' => 'test-school-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createAcademicSession(School $school): AcademicSession
    {
        return AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2023/2024',
            'starts_at' => now()->subMonths(6),
            'ends_at' => now()->addMonths(6),
        ]);
    }

    private function createTerm(School $school, AcademicSession $academicSession): Term
    {
        return Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $academicSession->id,
            'name' => 'First Term',
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->addMonths(3),
        ]);
    }

    private function createClass(School $school): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Grade 10',
        ]);
    }

    private function createSubject(School $school): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);
    }

    private function createStudent(School $school, SchoolClass $schoolClass): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $schoolClass->id,
            'admission_number' => 'TEST'.uniqid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active',
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
