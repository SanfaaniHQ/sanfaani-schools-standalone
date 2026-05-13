<?php

namespace Tests\Feature\School;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\GradingScale;
use App\Models\ReportCardCommentRule;
use App\Models\ReportCardSnapshot;
use App\Models\ResultPublication;
use App\Models\ResultVerification;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolReportCardSetting;
use App\Models\SchoolResultAccessPolicy;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\ReportCardService;
use App\Services\ReportCardSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class ReportCardSnapshotArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_captures_immutable_report_payload_without_pdf_generation(): void
    {
        $context = $this->createPublishedReportContext();

        $snapshot = app(ReportCardSnapshotService::class)->captureForStudentContext(
            $context['school'],
            $context['student'],
            $context['session'],
            $context['term'],
            'term_result',
            $context['publication'],
            $context['admin'],
            ['trigger' => 'feature_test']
        );

        $this->assertDatabaseHas('report_card_snapshots', [
            'id' => $snapshot->id,
            'school_id' => $context['school']->id,
            'student_id' => $context['student']->id,
            'snapshot_version' => 1,
            'payload_schema_version' => ReportCardSnapshot::PAYLOAD_SCHEMA_VERSION,
            'source_status' => ResultWorkflowStatus::Published->value,
            'result_count' => 1,
        ]);

        $this->assertNotNull($snapshot->snapshot_uuid);
        $this->assertNull($snapshot->pdf_disk);
        $this->assertNull($snapshot->pdf_path);
        $this->assertNull($snapshot->pdf_hash);
        $this->assertNull($snapshot->pdf_generated_at);
        $this->assertSame('Aisha Nur Bello', $snapshot->student_snapshot['full_name']);
        $this->assertSame('ADM-SNAP-001', $snapshot->student_snapshot['admission_number']);
        $this->assertSame('Sanfaani Snapshot School', $snapshot->school_snapshot['name']);
        $this->assertSame('Basic 5 A', $snapshot->academic_snapshot['school_class_name']);
        $this->assertSame('Mathematics', $snapshot->result_snapshot['subjects'][0]['subject_name']);
        $this->assertEquals(85.0, $snapshot->result_snapshot['subjects'][0]['total_score']);
        $this->assertSame('A', $snapshot->grading_snapshot['scales'][0]['grade']);
        $this->assertSame('#047857', $snapshot->settings_snapshot['branding']['primary_color']);
        $this->assertSame('Excellent class progress.', $snapshot->comments_snapshot['class_teacher_comment']);
        $this->assertSame('Excellent leadership review.', $snapshot->comments_snapshot['head_teacher_comment']);
        $this->assertSame('scratch_card', $snapshot->access_snapshot['access_policy']['access_mode']);
        $this->assertTrue($snapshot->access_snapshot['parent_access_ready']);
        $this->assertFalse($snapshot->access_snapshot['pdf_ready']);
    }

    public function test_existing_snapshot_stays_unchanged_when_results_and_settings_later_change(): void
    {
        $context = $this->createPublishedReportContext();
        $service = app(ReportCardSnapshotService::class);

        $firstSnapshot = $service->captureForStudentContext(
            $context['school'],
            $context['student'],
            $context['session'],
            $context['term']
        );

        $context['result']->update([
            'ca_score' => 36,
            'exam_score' => 59,
            'total_score' => 95,
            'grade' => 'A+',
            'remark' => 'Outstanding',
        ]);
        SchoolReportCardSetting::where('school_id', $context['school']->id)->update([
            'primary_color' => '#111827',
        ]);

        $secondSnapshot = $service->captureForStudentContext(
            $context['school'],
            $context['student'],
            $context['session'],
            $context['term']
        );

        $this->assertNotSame($firstSnapshot->id, $secondSnapshot->id);
        $this->assertSame(2, $secondSnapshot->snapshot_version);
        $this->assertSame(2, ReportCardSnapshot::count());

        $firstSnapshot->refresh();
        $this->assertEquals(85.0, $firstSnapshot->result_snapshot['subjects'][0]['total_score']);
        $this->assertSame('A', $firstSnapshot->result_snapshot['subjects'][0]['grade']);
        $this->assertSame('#047857', $firstSnapshot->settings_snapshot['branding']['primary_color']);
        $this->assertEquals(95.0, $secondSnapshot->result_snapshot['subjects'][0]['total_score']);
        $this->assertSame('A+', $secondSnapshot->result_snapshot['subjects'][0]['grade']);
        $this->assertSame('#111827', $secondSnapshot->settings_snapshot['branding']['primary_color']);
        $this->assertSame('VERIFY-SNAP-001', $firstSnapshot->verification_code);
        $this->assertSame('VERIFY-SNAP-001', $secondSnapshot->verification_code);
    }

    public function test_identical_publication_capture_is_not_duplicated_and_core_payload_is_immutable(): void
    {
        $context = $this->createPublishedReportContext();
        $service = app(ReportCardSnapshotService::class);

        $firstSnapshot = $service->captureForPublication($context['publication'], $context['admin'])->sole();
        $repeatPublication = $this->createPublication($context, publishedAt: now()->addMinute());

        $secondSnapshot = $service->captureForPublication($repeatPublication, $context['admin'])->sole();

        $this->assertTrue($firstSnapshot->is($secondSnapshot));
        $this->assertSame(1, ReportCardSnapshot::count());

        $firstSnapshot->update([
            'pdf_disk' => 'private',
            'pdf_path' => 'future/reports/snapshot.pdf',
            'pdf_hash' => str_repeat('a', 64),
            'pdf_generated_at' => now(),
        ]);

        $this->assertSame('future/reports/snapshot.pdf', $firstSnapshot->refresh()->pdf_path);

        $this->expectException(LogicException::class);

        $firstSnapshot->update(['average_score' => 10]);
    }

    public function test_subject_publication_capture_preserves_the_full_rendered_report(): void
    {
        $context = $this->createPublishedReportContext();
        $secondSubject = Subject::create([
            'school_id' => $context['school']->id,
            'name' => 'English Language',
            'code' => 'ENG',
            'status' => 'active',
        ]);
        StudentResult::create([
            'school_id' => $context['school']->id,
            'student_id' => $context['student']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $secondSubject->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'ca_score' => 32,
            'exam_score' => 58,
            'total_score' => 90,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => now(),
            'published_by' => $context['admin']->id,
            'recorded_by' => $context['admin']->id,
        ]);
        $subjectPublication = ResultPublication::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'scope_type' => 'subject',
            'subject_id' => $context['subject']->id,
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $context['admin']->id,
            'created_by' => $context['admin']->id,
        ]);

        $snapshot = app(ReportCardSnapshotService::class)->captureForPublication($subjectPublication, $context['admin'])->sole();

        $this->assertSame(2, $snapshot->result_count);
        $this->assertSame(
            ['Mathematics', 'English Language'],
            collect($snapshot->result_snapshot['subjects'])->pluck('subject_name')->all()
        );
    }

    public function test_snapshot_requires_currently_published_results(): void
    {
        $context = $this->createPublishedReportContext();
        $context['result']->update([
            'status' => ResultWorkflowStatus::Approved->value,
            'published_at' => null,
            'published_by' => null,
        ]);

        $this->expectException(ValidationException::class);

        app(ReportCardSnapshotService::class)->captureForStudentContext(
            $context['school'],
            $context['student'],
            $context['session'],
            $context['term']
        );
    }

    private function createPublishedReportContext(): array
    {
        $school = School::create([
            'name' => 'Sanfaani Snapshot School',
            'slug' => 'sanfaani-snapshot-school',
            'school_code' => 'SSS',
            'email' => 'office@example.test',
            'phone' => '+2348000000000',
            'address' => '1 Record Lane',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Basic 5',
            'section' => 'A',
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'is_active' => true,
            'status' => 'active',
        ]);
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => 'ADM-SNAP-001',
            'first_name' => 'Aisha',
            'middle_name' => 'Nur',
            'last_name' => 'Bello',
            'gender' => 'female',
            'guardian_email' => 'guardian@example.test',
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);
        $admin = User::factory()->create(['school_id' => $school->id]);

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
        ReportCardCommentRule::create([
            'school_id' => $school->id,
            'comment_type' => 'class_teacher',
            'min_average' => 80,
            'max_average' => 100,
            'comment' => 'Excellent class progress.',
            'status' => 'active',
            'sort_order' => 1,
        ]);
        ReportCardCommentRule::create([
            'school_id' => $school->id,
            'comment_type' => 'head_teacher',
            'min_average' => 80,
            'max_average' => 100,
            'comment' => 'Excellent leadership review.',
            'status' => 'active',
            'sort_order' => 1,
        ]);
        app(ReportCardService::class)->settingsFor($school)->update([
            'enable_auto_class_teacher_comment' => true,
            'enable_auto_head_teacher_comment' => true,
        ]);
        SchoolResultAccessPolicy::create([
            'school_id' => $school->id,
            'name' => 'Scratch card access',
            'access_mode' => 'scratch_card',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $admin->id,
        ]);

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'grade' => 'A',
            'remark' => 'Excellent',
            'teacher_remark' => 'Strong performance.',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => now(),
            'published_by' => $admin->id,
            'recorded_by' => $admin->id,
        ]);
        $verification = ResultVerification::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'verification_code' => 'VERIFY-SNAP-001',
            'status' => 'active',
            'issued_at' => now(),
        ]);

        $context = compact('school', 'class', 'session', 'term', 'student', 'subject', 'admin', 'result', 'verification');
        $context['publication'] = $this->createPublication($context);

        return $context;
    }

    private function createPublication(array $context, mixed $publishedAt = null): ResultPublication
    {
        return ResultPublication::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'scope_type' => 'student',
            'student_id' => $context['student']->id,
            'status' => 'published',
            'published_at' => $publishedAt ?? now(),
            'published_by' => $context['admin']->id,
            'created_by' => $context['admin']->id,
        ]);
    }
}
