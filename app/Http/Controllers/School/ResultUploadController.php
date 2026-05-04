<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use App\Services\StudentResultCsvImportService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ResultUploadController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.results.upload.index', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
            'academicSessions' => $this->academicSessionsForSchool($school),
            'terms' => $this->termsForSchool($school),
            'resultTypes' => $this->resultTypes(),
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validateContext($request, $school);

        $schoolClass = $this->findSchoolClass($school, (int) $data['school_class_id']);
        $academicSession = $this->findAcademicSession($school, (int) $data['academic_session_id']);
        $term = $this->findTerm($school, (int) $data['term_id'], $academicSession->id);

        $students = $school->students()
            ->where('school_class_id', $schoolClass->id)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $subjects = Subject::where('school_id', $school->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $fileName = 'result-template-'
            . str($schoolClass->name)->slug()
            . '-'
            . str($academicSession->name)->replace('/', '-')->slug()
            . '-'
            . str($term->name)->slug()
            . '.csv';

        return response()->streamDownload(function () use ($students, $subjects, $schoolClass, $academicSession, $term, $data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'class_name',
                'academic_session',
                'term',
                'result_type',
                'admission_number',
                'student_name',
                'subject_code',
                'subject_name',
                'ca_score',
                'exam_score',
                'status',
                'teacher_remark',
            ]);

            foreach ($students as $student) {
                foreach ($subjects as $subject) {
                    fputcsv($handle, [
                        trim($schoolClass->name . ' ' . $schoolClass->section),
                        $academicSession->name,
                        $term->name,
                        $data['result_type'],
                        $student->admission_number,
                        $student->fullName(),
                        $subject->code ?: $subject->name,
                        $subject->name,
                        '',
                        '',
                        'draft',
                        '',
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
            'result_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $schoolClass = $this->findSchoolClass($school, (int) $data['school_class_id']);
        $academicSession = $this->findAcademicSession($school, (int) $data['academic_session_id']);
        $term = $this->findTerm($school, (int) $data['term_id'], $academicSession->id);

        $import = new StudentResultCsvImportService(
            $school,
            $schoolClass,
            $academicSession,
            $term,
            auth()->user(),
            $data['result_type']
        );

        try {
            $import->import($request->file('result_file')->getRealPath());
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('upload_error', 'Upload failed. Please check the CSV file format and try again.');
        }

        app(AuditLogService::class)->log('result_csv_uploaded', null, $school, metadata: [
            'school_class_id' => $schoolClass->id,
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $data['result_type'],
            'created' => $import->createdCount,
            'updated' => $import->updatedCount,
            'skipped' => $import->skippedCount,
            'errors' => count($import->errors),
        ], request: $request);

        return back()
            ->with('success', "Upload completed. Created: {$import->createdCount}. Updated: {$import->updatedCount}. Skipped: {$import->skippedCount}.")
            ->with('import_errors', $import->errors);
    }

    private function validateContext(Request $request, School $school): array
    {
        return $request->validate([
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $school->id)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
        ]);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(\App\Services\CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function classesForSchool(School $school)
    {
        return $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('section')
            ->get();
    }

    private function academicSessionsForSchool(School $school)
    {
        return $school->academicSessions()
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function termsForSchool(School $school)
    {
        return $school->terms()
            ->with('academicSession')
            ->where('status', 'active')
            ->latest()
            ->get();
    }

    private function resultTypes(): array
    {
        return [
            'term_result' => 'Term Result',
            'assessment_result' => 'Assessment / Test Result - Coming later',
            'cbt_result' => 'CBT Result - Coming later',
        ];
    }

    private function findSchoolClass(School $school, int $id): SchoolClass
    {
        return SchoolClass::where('school_id', $school->id)->findOrFail($id);
    }

    private function findAcademicSession(School $school, int $id): AcademicSession
    {
        return AcademicSession::where('school_id', $school->id)->findOrFail($id);
    }

    private function findTerm(School $school, int $id, int $academicSessionId): Term
    {
        return Term::where('school_id', $school->id)
            ->where('academic_session_id', $academicSessionId)
            ->findOrFail($id);
    }
}
