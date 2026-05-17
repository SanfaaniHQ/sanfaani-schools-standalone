<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\ReportCardCommentRule;
use App\Models\ReportCardTemplate;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolReportCardSetting;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ReportCardService
{
    public function settingsFor(School $school): SchoolReportCardSetting
    {
        $template = $this->defaultTemplate();

        return SchoolReportCardSetting::firstOrCreate(
            ['school_id' => $school->id],
            [
                'report_card_template_id' => $template->id,
                'primary_color' => '#047857',
                'accent_color' => '#0f172a',
                'school_name_font' => 'default',
                'header_type' => 'classic',
                'student_info_layout' => 'two_column',
                'result_table_style' => 'standard',
                'show_logo' => true,
                'show_school_address' => true,
                'show_school_phone' => true,
                'show_school_email' => true,
                'show_teacher_remark' => true,
                'show_class_teacher' => true,
                'show_head_teacher' => true,
                'class_teacher_title' => 'Class Teacher',
                'head_teacher_title' => 'Head Teacher',
            ]
        )->loadMissing('template');
    }

    public function defaultTemplate(): ReportCardTemplate
    {
        return ReportCardTemplate::firstOrCreate(
            ['slug' => 'classic'],
            [
                'name' => 'Classic',
                'description' => 'A clean print-friendly report card layout for production launch.',
                'is_default' => true,
                'status' => 'active',
            ]
        );
    }

    public function calculateTotalScore(Collection $results): float
    {
        return round($results->sum(fn ($result) => (float) $result->total_score), 2);
    }

    public function calculateAverageScore(Collection $results): float
    {
        if ($results->isEmpty()) {
            return 0.0;
        }

        return round($this->calculateTotalScore($results) / $results->count(), 2);
    }

    public function autoComment(School $school, string $commentType, float $average): ?string
    {
        return ReportCardCommentRule::where('school_id', $school->id)
            ->where('comment_type', $commentType)
            ->where('status', 'active')
            ->where('min_average', '<=', $average)
            ->where('max_average', '>=', $average)
            ->orderBy('sort_order')
            ->value('comment');
    }

    public function displayData(
        School $school,
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        Collection $results,
        bool $publicOnly = false
    ): array {
        $settings = $this->settingsFor($school);
        $safeResults = $publicOnly
            ? $results
                ->filter(fn ($result) => $result->status === 'published'
                    && filled($result->published_at)
                    && blank($result->unpublished_at ?? null))
                ->values()
            : $results;
        $total = $this->calculateTotalScore($safeResults);
        $average = $this->calculateAverageScore($safeResults);
        $resultClass = data_get($safeResults->first(), 'schoolClass');

        if (! $resultClass) {
            $resultClassId = app(StudentClassEnrollmentService::class)
                ->classIdForResultContext($school, $student, $academicSession, $term);
            $resultClass = $resultClassId
                ? SchoolClass::where('school_id', $school->id)->find($resultClassId)
                : $student->schoolClass;
        }

        return [
            'settings' => $settings,
            'template' => $settings->template,
            'student' => $student,
            'school' => $school,
            'resultClass' => $resultClass,
            'academicSession' => $academicSession,
            'term' => $term,
            'results' => $safeResults,
            'totalScore' => $total,
            'averageScore' => $average,
            'classTeacherComment' => $settings->enable_auto_class_teacher_comment
                ? $this->autoComment($school, 'class_teacher', $average)
                : null,
            'headTeacherComment' => $settings->enable_auto_head_teacher_comment
                ? $this->autoComment($school, 'head_teacher', $average)
                : null,
            'classTeacherSignatureUrl' => $this->assetUrl($settings->class_teacher_signature_path),
            'headTeacherSignatureUrl' => $this->assetUrl($settings->head_teacher_signature_path),
        ];
    }

    public function sampleDisplayData(School $school): array
    {
        $student = new Student([
            'first_name' => 'Amina',
            'middle_name' => null,
            'last_name' => 'Yusuf',
            'admission_number' => 'SS/2026/001',
            'status' => 'active',
        ]);
        $session = new AcademicSession(['name' => '2025/2026']);
        $term = new Term(['name' => 'First Term']);
        $resultClass = new SchoolClass(['name' => 'Basic 5', 'section' => 'A']);
        $student->setRelation('schoolClass', $resultClass);

        $results = collect([
            $this->sampleResult('Mathematics', 28, 61, 89, 'A', 'Excellent', 'Strong performance.', $resultClass),
            $this->sampleResult('English Language', 24, 58, 82, 'A', 'Excellent', 'Reads confidently.', $resultClass),
            $this->sampleResult('Basic Science', 25, 55, 80, 'A', 'Excellent', 'Good practical understanding.', $resultClass),
        ]);

        return $this->displayData($school, $student, $session, $term, $results, true);
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function sampleResult(
        string $subjectName,
        float $caScore,
        float $examScore,
        float $totalScore,
        string $grade,
        string $remark,
        string $teacherRemark,
        SchoolClass $schoolClass
    ): StudentResult {
        $result = new StudentResult([
            'ca_score' => $caScore,
            'exam_score' => $examScore,
            'total_score' => $totalScore,
            'grade' => $grade,
            'remark' => $remark,
            'teacher_remark' => $teacherRemark,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $result->setRelation('subject', new Subject(['name' => $subjectName]));
        $result->setRelation('schoolClass', $schoolClass);

        return $result;
    }
}
