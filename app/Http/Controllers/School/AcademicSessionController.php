<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Services\CurrentSchoolService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $sessions = $school->academicSessions()
            ->withCount([
                'terms',
                'studentClassEnrollments',
                'studentResults',
                'resultPublications',
                'reportCardSnapshots',
                'teacherResultSubmissions',
            ])
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(10);

        return view('school.sessions.index', [
            'school' => $school,
            'sessions' => $sessions,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.sessions.create', [
            'school' => $school,
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_sessions', 'name')
                    ->where('school_id', $school->id),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            $school->academicSessions()
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        AcademicSession::create($data);

        return redirect()
            ->route('school.sessions.index')
            ->with('success', 'Academic session created successfully.');
    }

    public function edit(AcademicSession $academicSession)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeAcademicSession($academicSession, $school);

        return view('school.sessions.edit', [
            'school' => $school,
            'academicSession' => $academicSession,
        ]);
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeAcademicSession($academicSession, $school);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_sessions', 'name')
                    ->where('school_id', $school->id)
                    ->ignore($academicSession->id),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            $school->academicSessions()
                ->where('id', '!=', $academicSession->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $academicSession->update($data);

        return redirect()
            ->route('school.sessions.index')
            ->with('success', 'Academic session updated successfully.');
    }

    public function activate(Request $request, AcademicSession $academicSession, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeAcademicSession($academicSession, $school);

        $oldValues = $academicSession->only(['status', 'is_active']);

        $school->academicSessions()
            ->whereKeyNot($academicSession->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $academicSession->update([
            'status' => 'active',
            'is_active' => true,
        ]);

        $auditLog->log('academic_session_activated', $academicSession, $school, $oldValues, $academicSession->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Academic session activated successfully.');
    }

    public function archive(Request $request, AcademicSession $academicSession, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeAcademicSession($academicSession, $school);

        $oldValues = $academicSession->only(['status', 'is_active']);

        $academicSession->update([
            'status' => 'archived',
            'is_active' => false,
        ]);

        $auditLog->log('academic_session_archived', $academicSession, $school, $oldValues, $academicSession->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Academic session archived. Historical results and enrollments remain intact.');
    }

    public function restore(Request $request, AcademicSession $academicSession, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeAcademicSession($academicSession, $school);

        $oldValues = $academicSession->only(['status', 'is_active']);

        $academicSession->update([
            'status' => 'inactive',
            'is_active' => false,
        ]);

        $auditLog->log('academic_session_restored', $academicSession, $school, $oldValues, $academicSession->only([
            'status',
            'is_active',
        ]), request: $request);

        return back()->with('success', 'Academic session restored as inactive.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeAcademicSession(AcademicSession $academicSession, School $school): void
    {
        if ($academicSession->school_id !== $school->id) {
            abort(403, 'You cannot access this academic session.');
        }
    }
}
