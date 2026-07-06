<?php

namespace Tests\Feature;

use App\Events\StudentTransactionalEmailRequested;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test for Scratch Card Result Check Crash
 *
 * **Validates: Requirements 1.1, 1.2, 1.3**
 *
 * CRITICAL: This test is EXPECTED TO FAIL on unfixed code.
 * Failure confirms the bug exists (incorrect event dispatch pattern causes crash).
 *
 * After the fix is implemented, this test should PASS, demonstrating that:
 * - The system dispatches StudentTransactionalEmailRequested event correctly
 * - The event can be constructed and dispatched without TypeError
 */
class ScratchCardResultCheckCrashBugConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Bug Condition - Scratch Card Email Dispatch
     *
     * This test reproduces the exact bug condition:
     * - Incorrect dispatch pattern: dispatch(constructedEvent) instead of constructedEvent->dispatch()
     * - This causes TypeError: Argument #1 ($school) must be of type App\Models\School
     *
     * EXPECTED BEHAVIOR AFTER FIX:
     * - Event dispatches correctly without crashing
     * - Event constructor receives correct School model instance
     *
     * CURRENT BEHAVIOR (UNFIXED):
     * - System crashes with: StudentTransactionalEmailRequested::__construct():
     *   Argument #1 ($school) must be of type App\Models\School
     */
    public function test_scratch_card_result_email_event_is_constructed_and_dispatched_safely(): void
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

        // Setup: Create a school class (required for student)
        $schoolClassId = \DB::table('school_classes')->insertGetId([
            'school_id' => $school->id,
            'name' => 'Grade 10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Setup: Create student with guardian email
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $schoolClassId,
            'admission_number' => 'TEST001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'guardian_email' => 'guardian@example.com',
            'status' => 'active',
        ]);

        // Load the school relationship (as done in the controller)
        $student->loadMissing('school');

        $event = StudentTransactionalEmailRequested::resultAvailable(
            $student,
            $academicSession,
            $term,
            [
                'result_type' => 'term_result',
                'scratch_card_id' => 1,
            ]
        );

        $this->assertSame($school->id, $event->school->id);
        $this->assertSame($student->id, $event->student->id);

        event($event);

        $this->assertTrue(true, 'Scratch card result email event dispatches without constructor type errors.');
    }

    /**
     * Test the CORRECT dispatch pattern (this should always pass)
     *
     * This demonstrates the expected behavior after the fix.
     */
    public function test_correct_event_dispatch_pattern_works(): void
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

        // Setup: Create a school class (required for student)
        $schoolClassId = \DB::table('school_classes')->insertGetId([
            'school_id' => $school->id,
            'name' => 'Grade 10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Setup: Create student with guardian email
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $schoolClassId,
            'admission_number' => 'TEST001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'guardian_email' => 'guardian@example.com',
            'status' => 'active',
        ]);

        // Load the school relationship
        $student->loadMissing('school');

        // Execute: Test the CORRECT dispatch pattern
        // This is how it should be after the fix:
        // Use event() helper or dispatch the event directly

        try {
            // CORRECT PATTERN: Use event() helper to dispatch the constructed event
            event(StudentTransactionalEmailRequested::resultAvailable(
                $student,
                $academicSession,
                $term,
                [
                    'result_type' => 'term_result',
                    'scratch_card_id' => 1,
                ]
            ));

            // This should succeed without errors
            $this->assertTrue(true, 'Correct dispatch pattern works without errors');

        } catch (\TypeError $e) {
            $this->fail(
                'Correct dispatch pattern should not cause TypeError. '.
                "Error: {$e->getMessage()}"
            );
        }
    }

    public function test_public_scratch_card_check_redirects_to_result_without_crashing(): void
    {
        $school = School::create([
            'name' => 'Published Result School',
            'slug' => 'published-result-school-'.uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        $academicSession = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'status' => 'active',
        ]);

        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $academicSession->id,
            'name' => 'First Term',
            'status' => 'active',
        ]);

        $classId = \DB::table('school_classes')->insertGetId([
            'school_id' => $school->id,
            'name' => 'Grade 10',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);

        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $classId,
            'admission_number' => 'PUB001',
            'first_name' => 'Amina',
            'last_name' => 'Bello',
            'guardian_email' => 'guardian@example.test',
            'status' => 'active',
        ]);

        StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $classId,
            'subject_id' => $subject->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $batch = ScratchCardBatch::create([
            'school_id' => $school->id,
            'school_class_id' => $classId,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'quantity' => 1,
            'amount' => 0,
            'status' => 'generated',
        ]);

        ScratchCard::create([
            'scratch_card_batch_id' => $batch->id,
            'school_id' => $school->id,
            'school_class_id' => $classId,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'serial_number' => 'SCR-PUB-001',
            'pin_code' => '123456',
            'pin_hash' => hash('sha256', '123456'),
            'max_uses' => 3,
            'used_count' => 0,
            'status' => 'unused',
        ]);

        $this->post(route('public.results.identify'), [
            'admission_number' => 'PUB001',
            'scratch_card_serial' => 'SCR-PUB-001',
            'scratch_card_pin' => '123456',
        ])->assertRedirect(route('public.results.index', ['lang' => 'en']));

        $response = $this->post(route('public.results.check'), [
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scratch_card_usages', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
        ]);
    }
}
