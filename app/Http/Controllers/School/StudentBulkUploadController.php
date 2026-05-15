<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\CurrentSchoolService;
use App\Services\StudentCsvImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class StudentBulkUploadController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.students.upload.index', [
            'school' => $school,
            'classes' => $this->classesForSchool($school),
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
        ]);

        $schoolClass = $this->findSchoolClass($school, (int) $data['school_class_id']);

        $fileName = 'student-upload-template-'
            .str($schoolClass->name)->slug()
            .'-'
            .str($schoolClass->section ?: 'class')->slug()
            .'.csv';

        return response()->streamDownload(function () use ($schoolClass) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'class_name',
                'admission_number',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'date_of_birth',
                'guardian_name',
                'guardian_phone',
                'guardian_email',
                'address',
                'status',
            ]);

            fputcsv($handle, [
                trim($schoolClass->name.' '.$schoolClass->section),
                'SCH/2026/001',
                'Aisha',
                'Fatimah',
                'Bello',
                'female',
                '2015-09-12',
                'Mr Bello',
                '08012345678',
                'guardian@example.com',
                'Ilorin, Kwara State',
                'active',
            ]);

            fputcsv($handle, [
                trim($schoolClass->name.' '.$schoolClass->section),
                'SCH/2026/002',
                'Umar',
                '',
                'Abdullahi',
                'male',
                '2014-05-20',
                'Mrs Abdullahi',
                '08098765432',
                '',
                'Ilorin, Kwara State',
                'active',
            ]);

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
            'student_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $schoolClass = $this->findSchoolClass($school, (int) $data['school_class_id']);

        $import = new StudentCsvImportService($school, $schoolClass, auth()->id());

        try {
            $import->import($request->file('student_file')->getRealPath());
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('upload_error', 'Upload failed. Please check the CSV file format and try again.');
        }

        return back()
            ->with('success', "Upload completed. Created: {$import->createdCount}. Updated: {$import->updatedCount}. Skipped: {$import->skippedCount}.")
            ->with('import_errors', $import->errors);
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

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

    private function findSchoolClass(School $school, int $id): SchoolClass
    {
        return SchoolClass::where('school_id', $school->id)->findOrFail($id);
    }
}
