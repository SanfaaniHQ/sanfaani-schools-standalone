<?php

namespace App\Services;

use App\Enums\ResultWorkflowStatus;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtResultPublication;
use App\Models\ResultPublication;
use App\Models\School;
use App\Models\StudentResult;
use Illuminate\Support\Facades\DB;

class CbtResultIntegrationService
{
    public function __construct(
        private ResultGradingService $grading,
        private AuditLogService $auditLog
    ) {}

    public function syncAttemptToStudentResult(CbtAttempt $attempt): ?StudentResult
    {
        $attempt->loadMissing(['exam', 'student', 'school']);
        $exam = $attempt->exam;

        if (! $exam || ! $attempt->student_id || ! $exam->subject_id || ! $exam->academic_session_id || ! $exam->term_id) {
            return null;
        }

        $percentage = $this->percentage($attempt);
        $grading = $attempt->school ? $this->grading->calculate($attempt->school, $percentage) : ['grade' => null, 'remark' => null];
        $classId = $exam->school_class_id ?: $attempt->student?->school_class_id;

        return DB::transaction(function () use ($attempt, $exam, $percentage, $grading, $classId) {
            $result = StudentResult::updateOrCreate(
                [
                    'school_id' => $attempt->school_id,
                    'student_id' => $attempt->student_id,
                    'subject_id' => $exam->subject_id,
                    'academic_session_id' => $exam->academic_session_id,
                    'term_id' => $exam->term_id,
                    'result_type' => $exam->result_type ?: 'cbt_result',
                ],
                [
                    'school_class_id' => $classId,
                    'ca_score' => 0,
                    'exam_score' => $percentage,
                    'total_score' => $percentage,
                    'grade' => $grading['grade'] ?? null,
                    'remark' => $grading['remark'] ?? null,
                    'teacher_remark' => 'CBT attempt '.$attempt->attempt_no.' score: '.$attempt->total_score.'/'.$attempt->max_score,
                    'status' => ResultWorkflowStatus::Reviewed->value,
                    'recorded_by' => $attempt->user_id,
                    'updated_by' => $attempt->user_id,
                    'approved_by' => null,
                ]
            );

            $attempt->forceFill(['student_result_id' => $result->id])->save();

            return $result;
        });
    }

    public function publishExam(CbtExam $exam, School $school, int $publishedBy): CbtResultPublication
    {
        return DB::transaction(function () use ($exam, $school, $publishedBy) {
            $now = now();
            $attempts = $exam->attempts()
                ->where('school_id', $school->id)
                ->whereIn('status', ['graded'])
                ->get();

            $studentResultIds = $attempts->pluck('student_result_id')->filter()->unique()->values();

            if ($studentResultIds->isNotEmpty()) {
                StudentResult::where('school_id', $school->id)
                    ->whereIn('id', $studentResultIds)
                    ->whereIn('status', [ResultWorkflowStatus::Reviewed->value, ResultWorkflowStatus::Approved->value, ResultWorkflowStatus::Unpublished->value])
                    ->update([
                        'status' => ResultWorkflowStatus::Published->value,
                        'published_at' => $now,
                        'published_by' => $publishedBy,
                        'unpublished_at' => null,
                        'unpublished_by' => null,
                        'unpublish_reason' => null,
                        'updated_by' => $publishedBy,
                    ]);

                ResultPublication::create([
                    'school_id' => $school->id,
                    'school_class_id' => $exam->school_class_id,
                    'academic_session_id' => $exam->academic_session_id,
                    'term_id' => $exam->term_id,
                    'result_type' => $exam->result_type ?: 'cbt_result',
                    'scope_type' => 'cbt_exam',
                    'subject_id' => $exam->subject_id,
                    'student_id' => null,
                    'status' => 'published',
                    'published_at' => $now,
                    'published_by' => $publishedBy,
                    'created_by' => $publishedBy,
                ]);
            }

            $exam->attempts()
                ->where('school_id', $school->id)
                ->whereIn('id', $attempts->pluck('id'))
                ->update(['result_release_status' => 'published']);

            $publication = CbtResultPublication::create([
                'school_id' => $school->id,
                'cbt_exam_id' => $exam->id,
                'release_mode' => 'all_attempts',
                'status' => 'published',
                'published_at' => $now,
                'published_by' => $publishedBy,
                'metadata' => [
                    'attempts' => $attempts->count(),
                    'student_result_ids' => $studentResultIds->all(),
                ],
            ]);

            $this->auditLog->log('cbt_results_published', $exam, $school, metadata: [
                'cbt_exam_id' => $exam->id,
                'attempts' => $attempts->count(),
                'student_result_count' => $studentResultIds->count(),
            ]);

            return $publication;
        });
    }

    private function percentage(CbtAttempt $attempt): float
    {
        $maxScore = (float) $attempt->max_score;

        if ($maxScore <= 0) {
            return 0.0;
        }

        return round(((float) $attempt->total_score / $maxScore) * 100, 2);
    }
}
