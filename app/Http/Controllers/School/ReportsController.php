<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use App\Services\Reports\SchoolReportsOverviewService;
use App\Services\SchoolAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportsController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private SchoolAuthorizationService $authorization,
        private SchoolReportsOverviewService $reports,
        private AuditLogService $auditLog,
    ) {}

    public function index(Request $request)
    {
        $school = $this->schoolOrFail();
        $this->authorization->authorize($request->user(), $school, 'reports.view');

        $filters = $this->validatedFilters($request, $school);
        $report = $this->reports->overview($school, $filters);

        $this->auditLog->log('school_reports_viewed', $school, $school, metadata: [
            'school_id' => $school->id,
            'actor_id' => $request->user()->id,
            'filters_used' => $filters !== [],
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'school_class_id' => isset($filters['school_class_id']) ? (int) $filters['school_class_id'] : null,
            'academic_session_id' => isset($filters['academic_session_id']) ? (int) $filters['academic_session_id'] : null,
            'term_id' => isset($filters['term_id']) ? (int) $filters['term_id'] : null,
            'status' => $filters['status'] ?? null,
        ], request: $request);

        return view('school.reports.index', [
            'school' => $school,
            'filters' => $filters,
            'filterOptions' => $this->reports->filterOptions($school),
            'report' => $report,
        ]);
    }

    private function validatedFilters(Request $request, School $school): array
    {
        return collect($request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'school_class_id' => [
                'nullable',
                'integer',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'academic_session_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where('school_id', $school->id),
            ],
            'status' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]))->filter(fn ($value): bool => filled($value))->all();
    }

    private function schoolOrFail(): School
    {
        $school = $this->currentSchool->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }
}
