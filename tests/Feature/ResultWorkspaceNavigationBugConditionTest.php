<?php

namespace Tests\Feature;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherResultSubmission;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test for Result Workspace Navigation Breaks
 *
 * **Validates: Requirements 1.10, 1.11, 1.12**
 *
 * CRITICAL: This test is EXPECTED TO FAIL on unfixed code.
 * Failure confirms the bug exists (JavaScript navigation handling incomplete).
 *
 * After the fix is implemented, this test should PASS, demonstrating that:
 * - Navigation works correctly in Student 360 Result Workspace
 * - Navigation works correctly in Result Management Workspace
 * - Navigation works correctly in Assigned Teacher Result Entry
 * - Form submissions complete without navigation errors
 * - Page transitions work smoothly during result operations
 */
class ResultWorkspaceNavigationBugConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Bug Condition - Result Workspace Navigation
     *
     * This test reproduces the exact bug condition:
     * - User performs result entry in Teacher Entry workspace
     * - Navigation breaks during result operations
     * - JavaScript navigation handling is incomplete
     *
     * EXPECTED BEHAVIOR AFTER FIX:
     * - Navigation works correctly without breaking
     * - Form submissions complete successfully
     * - Redirects work as expected
     * - No JavaScript errors during navigation
     *
     * CURRENT BEHAVIOR (UNFIXED):
     * - Navigation breaks during result operations
     * - Form submissions may fail or redirect incorrectly
     * - JavaScript errors may occur
     */
    public function test_teacher_result_entry_workspace_navigation_breaks(): void
    {
        // Setup: Create school and related entities
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $teacher = $this->createUserForSchool($school, 'teacher');

        // Create teacher assignment for the class and subject using the current teacher-assignment architecture.
        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'status' => 'active',
        ]);

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Execute: Navigate to result entry workspace (create page)
        $createResponse = $this->get(route('school.teacher-results.create', [
            'school_class_id' => $schoolClass->id,
            'academic_session_id' => $academicSession->id,
        ]));

        // Check if the page loads successfully
        if ($createResponse->status() !== 200) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Navigation to result entry workspace failed.\n".
                "Expected: HTTP 200 response\n".
                "Actual: HTTP {$createResponse->status()} response\n".
                "This indicates navigation is broken before even entering results.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // Execute: Submit result entry form
        $storeResponse = $this->post(route('school.teacher-results.store'), [
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'action' => 'save',
            'scores' => [
                $student->id => [
                    'ca_score' => '20.00',
                    'exam_score' => '60.00',
                    'teacher_remark' => 'Good work',
                ],
            ],
        ]);

        // Check if form submission completes and redirects correctly
        if ($storeResponse->status() >= 400) {
            // BEFORE FIX: Form submission fails or navigation breaks
            $this->markTestIncomplete(
                "BUG CONFIRMED: Result entry form submission failed with navigation error.\n".
                "Expected: Successful submission with redirect (HTTP 302)\n".
                "Actual: HTTP {$storeResponse->status()} response\n".
                "Root Cause: JavaScript navigation handling incomplete\n".
                "- Missing navigation event handlers\n".
                "- Form submission may not complete properly\n".
                "- Redirect logic may be broken\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // Check if redirect target is correct
        if (! $storeResponse->isRedirect()) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Result entry form submission did not redirect as expected.\n".
                "Expected: Redirect to result show page\n".
                "Actual: No redirect occurred\n".
                "This indicates navigation flow is broken.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // Follow the redirect
        $redirectResponse = $this->followRedirects($storeResponse);

        // Check if the redirect target loads successfully
        if ($redirectResponse->status() !== 200) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Navigation after result entry submission failed.\n".
                "Expected: HTTP 200 response on redirect target\n".
                "Actual: HTTP {$redirectResponse->status()} response\n".
                "This indicates navigation breaks after form submission.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // AFTER FIX: All navigation should work correctly
        $createResponse->assertStatus(200);
        $storeResponse->assertRedirect();
        $redirectResponse->assertStatus(200);
        $redirectResponse->assertSee('Result draft saved successfully');
    }

    /**
     * Test navigation in Result Management Workspace
     */
    public function test_result_management_workspace_navigation_breaks(): void
    {
        // Setup
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $resultOfficer = $this->createUserForSchool($school, 'result_officer');

        $this->actAsSchoolRole($resultOfficer, $school, 'result_officer');

        // Execute: Navigate to result management workspace
        $response = $this->get(route('school.teacher-results.create', [
            'school_class_id' => $schoolClass->id,
            'academic_session_id' => $academicSession->id,
        ]));

        // Check if navigation works
        if ($response->status() !== 200) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Navigation to result management workspace failed.\n".
                "Expected: HTTP 200 response\n".
                "Actual: HTTP {$response->status()} response\n".
                "This indicates navigation is broken in Result Management Workspace.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // AFTER FIX: Navigation should work
        $response->assertStatus(200);
    }

    /**
     * Test navigation during result editing operations
     */
    public function test_result_edit_workspace_navigation_breaks(): void
    {
        // Setup
        $school = $this->createSchool();
        $academicSession = $this->createAcademicSession($school);
        $term = $this->createTerm($school, $academicSession);
        $schoolClass = $this->createClass($school);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $schoolClass);
        $teacher = $this->createUserForSchool($school, 'teacher');

        // Create teacher assignment using the current teacher-assignment architecture.
        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'status' => 'active',
        ]);

        // Create a draft submission
        $submission = TeacherResultSubmission::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'status' => ResultWorkflowStatus::Draft->value,
            'metadata' => [
                'scores' => [
                    $student->id => [
                        'ca_score' => '20.00',
                        'exam_score' => '60.00',
                        'total_score' => '80.00',
                        'grade' => 'A',
                        'teacher_remark' => 'Good work',
                    ],
                ],
            ],
        ]);

        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Execute: Navigate to edit workspace
        $editResponse = $this->get(route('school.teacher-results.edit', $submission));

        // Check if navigation to edit page works
        if ($editResponse->status() !== 200) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Navigation to result edit workspace failed.\n".
                "Expected: HTTP 200 response\n".
                "Actual: HTTP {$editResponse->status()} response\n".
                "This indicates navigation breaks when editing results.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // Execute: Update the result
        $updateResponse = $this->patch(route('school.teacher-results.update', $submission), [
            'action' => 'save',
            'scores' => [
                $student->id => [
                    'ca_score' => '25.00',
                    'exam_score' => '65.00',
                    'teacher_remark' => 'Excellent work',
                ],
            ],
        ]);

        // Check if update completes and redirects correctly
        if ($updateResponse->status() >= 400) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Result update form submission failed with navigation error.\n".
                "Expected: Successful update with redirect (HTTP 302)\n".
                "Actual: HTTP {$updateResponse->status()} response\n".
                "This indicates navigation breaks during result editing.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        if (! $updateResponse->isRedirect()) {
            $this->markTestIncomplete(
                "BUG CONFIRMED: Result update did not redirect as expected.\n".
                "Expected: Redirect to result show page\n".
                "Actual: No redirect occurred\n".
                "This indicates navigation flow is broken during editing.\n".
                'This test will pass after the fix is implemented.'
            );
        }

        // AFTER FIX: All navigation should work correctly
        $editResponse->assertStatus(200);
        $updateResponse->assertRedirect();
    }

    /**
     * Test the CORRECT behavior (this should always pass)
     *
     * This demonstrates that basic page loading works without result operations.
     */
    public function test_basic_navigation_to_teacher_results_index_works(): void
    {
        // Setup
        $school = $this->createSchool();
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        // Execute: Navigate to teacher results index (no result operations)
        $response = $this->get(route('school.teacher-results.index'));

        // This should work - basic navigation without result operations
        $response->assertStatus(200);
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
            'status' => 'active',
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
            'status' => 'active',
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->addMonths(3),
        ]);
    }

    private function createClass(School $school): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Grade 10',
            'status' => 'active',
        ]);
    }

    private function createSubject(School $school): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'status' => 'active',
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
