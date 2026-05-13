<?php

namespace App\Services;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\ReportCardCommentRule;
use App\Models\ReportCardSnapshot;
use App\Models\ResultPublication;
use App\Models\ResultVerification;
use App\Models\School;
use App\Models\SchoolResultAccessPolicy;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportCardSnapshotService
{
    public function __construct(
        private ReportCardService $reportCards,
        private ResultGradingService $grading
    ) {}

    public function captureForStudentContext(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        string $resultType = 'term_result',
        ?ResultPublication $publication = null,
        ?User $generatedBy = null,
        array $metadata = []
    ): ReportCardSnapshot {
        $results = StudentResult::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->where('status', ResultWorkflowStatus::Published->value)
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at')
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->orderBy('subject_id')
            ->get();

        if ($results->isEmpty()) {
            throw ValidationException::withMessages([
                'results' => 'A report card snapshot requires at least one currently published result.',
            ]);
        }

        $verification = ResultVerification::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->first();

        return $this->capture(
            $school,
            $student,
            $academicSession,
            $term,
            $results,
            $resultType,
            $publication,
            $verification,
            $generatedBy,
            $metadata
        );
    }

    public function captureForPublication(ResultPublication $publication, ?User $generatedBy = null): Collection
    {
        $publication->loadMissing(['school', 'academicSession', 'term']);

        if ($publication->status !== 'published' || ! $publication->school || ! $publication->academicSession || ! $publication->term) {
            return collect();
        }

        $studentIds = $this->publishedResultsForPublication($publication)
            ->pluck('student_id')
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return $studentIds
            ->map(function (int $studentId) use ($publication, $generatedBy) {
                $student = Student::withTrashed()
                    ->where('school_id', $publication->school_id)
                    ->find($studentId);

                if (! $student) {
                    return null;
                }

                return $this->captureForStudentContext(
                    $publication->school,
                    $student,
                    $publication->academicSession,
                    $publication->term,
                    $publication->result_type,
                    $publication,
                    $generatedBy,
                    [
                        'captured_from' => 'result_publication',
                        'publication_scope_type' => $publication->scope_type,
                        'publication_subject_id' => $publication->subject_id,
                        'publication_student_id' => $publication->student_id,
                    ]
                );
            })
            ->filter()
            ->values();
    }

    public function capture(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        Collection $publishedResults,
        string $resultType = 'term_result',
        ?ResultPublication $publication = null,
        ?ResultVerification $verification = null,
        ?User $generatedBy = null,
        array $metadata = []
    ): ReportCardSnapshot {
        $publishedResults = $publishedResults
            ->filter(fn (StudentResult $result) => (int) $result->school_id === (int) $school->id
                && (int) $result->student_id === (int) $student->id
                && (int) $result->academic_session_id === (int) $academicSession->id
                && (int) $result->term_id === (int) $term->id
                && $result->result_type === $resultType
                && $result->status === ResultWorkflowStatus::Published->value
                && filled($result->published_at)
                && blank($result->unpublished_at))
            ->values();

        if ($publishedResults->isEmpty()) {
            throw ValidationException::withMessages([
                'results' => 'A report card snapshot requires at least one currently published result.',
            ]);
        }

        $payload = $this->payload($school, $student, $academicSession, $term, $publishedResults, $resultType, $publication, $verification, $metadata);
        $snapshotHash = $this->hashPayload($payload);

        return DB::transaction(function () use ($school, $student, $academicSession, $term, $resultType, $publication, $verification, $generatedBy, $payload, $snapshotHash) {
            $existingSnapshot = ReportCardSnapshot::query()
                ->where('snapshot_hash', $snapshotHash)
                ->first();

            if ($existingSnapshot) {
                return $existingSnapshot;
            }

            $version = $this->nextVersion($school, $student, $academicSession, $term, $resultType);

            return ReportCardSnapshot::create([
                'snapshot_uuid' => (string) Str::uuid(),
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => data_get($payload, 'academic_snapshot.school_class_id'),
                'academic_session_id' => $academicSession->id,
                'term_id' => $term->id,
                'result_publication_id' => $publication?->id,
                'result_verification_id' => $verification?->id,
                'snapshot_version' => $version,
                'snapshot_type' => 'term_report',
                'payload_schema_version' => ReportCardSnapshot::PAYLOAD_SCHEMA_VERSION,
                'result_type' => $resultType,
                'source_status' => ResultWorkflowStatus::Published->value,
                'status' => 'active',
                'student_name' => data_get($payload, 'student_snapshot.full_name'),
                'admission_number' => data_get($payload, 'student_snapshot.admission_number'),
                'result_count' => count(data_get($payload, 'result_snapshot.subjects', [])),
                'total_score' => data_get($payload, 'result_snapshot.totals.total_score', 0),
                'average_score' => data_get($payload, 'result_snapshot.totals.average_score', 0),
                'student_snapshot' => $payload['student_snapshot'],
                'school_snapshot' => $payload['school_snapshot'],
                'academic_snapshot' => $payload['academic_snapshot'],
                'result_snapshot' => $payload['result_snapshot'],
                'grading_snapshot' => $payload['grading_snapshot'],
                'settings_snapshot' => $payload['settings_snapshot'],
                'comments_snapshot' => $payload['comments_snapshot'],
                'access_snapshot' => $payload['access_snapshot'],
                'snapshot_hash' => $snapshotHash,
                'verification_code' => $verification?->verification_code,
                'generated_by' => $generatedBy?->id,
                'generated_at' => now(),
                'metadata' => $payload['metadata'],
            ]);
        });
    }

    public function payload(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        Collection $publishedResults,
        string $resultType = 'term_result',
        ?ResultPublication $publication = null,
        ?ResultVerification $verification = null,
        array $metadata = []
    ): array {
        $publishedResults = $this->loadMissingForResults($publishedResults);

        $display = $this->reportCards->displayData($school, $student, $academicSession, $term, $publishedResults, publicOnly: true);
        $settings = $display['settings'];
        $template = $display['template'];
        $resultClass = $display['resultClass'];
        $average = (float) $display['averageScore'];

        return [
            'schema_version' => ReportCardSnapshot::PAYLOAD_SCHEMA_VERSION,
            'student_snapshot' => [
                'student_id' => $student->id,
                'full_name' => $student->fullName(),
                'admission_number' => $student->admission_number,
                'gender' => $student->gender,
                'status' => $student->status,
            ],
            'school_snapshot' => [
                'school_id' => $school->id,
                'name' => $school->name,
                'school_code' => $school->school_code,
                'email' => $school->email,
                'phone' => $school->phone,
                'address' => $school->address,
                'logo' => $school->logo,
                'logo_url' => $school->logoUrl(),
            ],
            'academic_snapshot' => [
                'academic_session_id' => $academicSession->id,
                'academic_session_name' => $academicSession->name,
                'term_id' => $term->id,
                'term_name' => $term->name,
                'school_class_id' => $resultClass?->id,
                'school_class_name' => $resultClass ? trim(($resultClass->name ?? '').' '.($resultClass->section ?? '')) : null,
                'result_type' => $resultType,
            ],
            'result_snapshot' => [
                'totals' => [
                    'total_score' => $display['totalScore'],
                    'average_score' => $display['averageScore'],
                    'result_count' => $display['results']->count(),
                ],
                'subjects' => $display['results']
                    ->map(fn (StudentResult $result) => [
                        'student_result_id' => $result->id,
                        'subject_id' => $result->subject_id,
                        'subject_name' => $result->subject?->name,
                        'subject_code' => $result->subject?->code,
                        'ca_score' => (float) $result->ca_score,
                        'exam_score' => (float) $result->exam_score,
                        'total_score' => (float) $result->total_score,
                        'grade' => $result->grade,
                        'remark' => $result->remark,
                        'teacher_remark' => $result->teacher_remark,
                        'officer_remark' => $result->officer_remark,
                        'admin_remark' => $result->admin_remark,
                        'published_at' => $result->published_at?->toDateTimeString(),
                        'published_by' => $result->published_by,
                    ])
                    ->values()
                    ->all(),
            ],
            'grading_snapshot' => [
                'scales' => $this->grading->activeScales($school)
                    ->map(fn ($scale) => [
                        'id' => $scale->id,
                        'name' => $scale->name,
                        'min_score' => (float) $scale->min_score,
                        'max_score' => (float) $scale->max_score,
                        'grade' => $scale->grade,
                        'remark' => $scale->remark,
                        'is_pass' => (bool) $scale->is_pass,
                        'sort_order' => (int) $scale->sort_order,
                    ])
                    ->values()
                    ->all(),
            ],
            'settings_snapshot' => [
                'report_card_setting_id' => $settings->id,
                'template' => [
                    'id' => $template?->id,
                    'name' => $template?->name,
                    'slug' => $template?->slug,
                ],
                'branding' => $settings->only([
                    'primary_color',
                    'accent_color',
                    'school_name_font',
                    'header_type',
                    'student_info_layout',
                    'result_table_style',
                    'show_logo',
                    'show_school_address',
                    'show_school_phone',
                    'show_school_email',
                    'show_student_photo',
                    'show_teacher_remark',
                    'show_class_teacher',
                    'show_head_teacher',
                    'class_teacher_title',
                    'head_teacher_title',
                    'class_teacher_name',
                    'head_teacher_name',
                    'class_teacher_signature_path',
                    'head_teacher_signature_path',
                    'enable_auto_class_teacher_comment',
                    'enable_auto_head_teacher_comment',
                ]),
            ],
            'comments_snapshot' => [
                'class_teacher_comment' => $display['classTeacherComment'],
                'head_teacher_comment' => $display['headTeacherComment'],
                'matching_rules' => $this->commentRulesFor($school, $average),
            ],
            'access_snapshot' => [
                'result_publication_id' => $publication?->id,
                'result_verification_id' => $verification?->id,
                'verification_code' => $verification?->verification_code,
                'access_policy' => $this->accessPolicySnapshot($school),
                'parent_access_ready' => true,
                'pdf_ready' => false,
            ],
            'metadata' => array_merge([
                'source' => 'report_card_snapshot_service',
                'pdf_generation' => 'not_generated',
                'immutable_payload' => true,
            ], $metadata),
        ];
    }

    private function nextVersion(School $school, Student $student, AcademicSession $academicSession, Term $term, string $resultType): int
    {
        return ((int) ReportCardSnapshot::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('academic_session_id', $academicSession->id)
            ->where('term_id', $term->id)
            ->where('result_type', $resultType)
            ->lockForUpdate()
            ->max('snapshot_version')) + 1;
    }

    private function publishedResultsForPublication(ResultPublication $publication)
    {
        return StudentResult::query()
            ->where('school_id', $publication->school_id)
            ->where('school_class_id', $publication->school_class_id)
            ->where('academic_session_id', $publication->academic_session_id)
            ->where('term_id', $publication->term_id)
            ->where('result_type', $publication->result_type)
            ->where('status', ResultWorkflowStatus::Published->value)
            ->whereNotNull('published_at')
            ->whereNull('unpublished_at')
            ->when($publication->scope_type === 'subject', fn ($query) => $query->where('subject_id', $publication->subject_id))
            ->when($publication->scope_type === 'student', fn ($query) => $query->where('student_id', $publication->student_id))
            ->with(['subject', 'schoolClass', 'academicSession', 'term'])
            ->orderBy('student_id')
            ->orderBy('subject_id');
    }

    private function loadMissingForResults(Collection $publishedResults): Collection
    {
        $relations = ['subject', 'schoolClass', 'academicSession', 'term'];

        if (method_exists($publishedResults, 'loadMissing')) {
            $publishedResults->loadMissing($relations);

            return $publishedResults;
        }

        return $publishedResults
            ->each(fn (StudentResult $result) => $result->loadMissing($relations));
    }

    private function hashPayload(array $payload): string
    {
        $payload = $this->hashablePayload($payload);

        return hash('sha256', json_encode(
            $this->canonicalize($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        ));
    }

    private function hashablePayload(array $payload): array
    {
        unset(
            $payload['metadata'],
            $payload['access_snapshot']['result_publication_id'],
            $payload['access_snapshot']['result_verification_id'],
            $payload['access_snapshot']['verification_code']
        );

        return $payload;
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        ksort($value);

        return array_map(fn ($item) => $this->canonicalize($item), $value);
    }

    private function commentRulesFor(School $school, float $average): array
    {
        return ReportCardCommentRule::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where('min_average', '<=', $average)
            ->where('max_average', '>=', $average)
            ->orderBy('comment_type')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ReportCardCommentRule $rule) => [
                'id' => $rule->id,
                'comment_type' => $rule->comment_type,
                'min_average' => (float) $rule->min_average,
                'max_average' => (float) $rule->max_average,
                'comment' => $rule->comment,
            ])
            ->values()
            ->all();
    }

    private function accessPolicySnapshot(School $school): ?array
    {
        $policy = SchoolResultAccessPolicy::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();

        if (! $policy) {
            return null;
        }

        return [
            'id' => $policy->id,
            'access_mode' => $policy->access_mode,
            'status' => $policy->status,
            'starts_at' => $policy->starts_at?->toDateTimeString(),
            'ends_at' => $policy->ends_at?->toDateTimeString(),
        ];
    }
}
