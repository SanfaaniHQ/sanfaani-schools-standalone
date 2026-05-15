<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectAssignment;
use App\Models\LanguagePreference;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionBatch;
use App\Models\StudentPromotionItem;
use App\Models\StudentResult;
use App\Services\AuditLogService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $classes = $school->schoolClasses()
            ->when($request->boolean('include_archived'), fn ($query) => $query->withTrashed())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('school.classes.index', [
            'school' => $school,
            'classes' => $classes,
            'filters' => $request->only(['search', 'status', 'include_archived']),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.classes.create', [
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
                'max:100',
                Rule::unique('school_classes', 'name')
                    ->where('school_id', $school->id)
                    ->where('section', $request->input('section')),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_classes', 'code')->where('school_id', $school->id),
            ],
            'section' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $data['school_id'] = $school->id;
        $data['code'] = filled($data['code'] ?? null) ? strtoupper(trim($data['code'])) : null;

        SchoolClass::create($data);

        return redirect()
            ->route('school.classes.index')
            ->with('success', 'Class created successfully.')
            ->with('next_actions', [
                ['label' => 'Add another class', 'href' => route('school.classes.create')],
                ['label' => 'Assign subjects to class', 'href' => route('school.subject-assignments.create')],
                ['label' => 'Upload classes', 'href' => route('school.classes.upload.index')],
                ['label' => 'Add students', 'href' => route('school.students.create')],
                ['label' => 'Back to dashboard', 'href' => route('school.dashboard')],
            ]);
    }

    public function edit(SchoolClass $class)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSchoolClass($class, $school);

        return view('school.classes.edit', [
            'school' => $school,
            'class' => $class,
            'languagePreference' => LanguagePreference::where('school_id', $school->id)
                ->where('scope_type', 'class')
                ->where('scope_id', $class->id)
                ->first(),
        ]);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeSchoolClass($class, $school);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('school_classes', 'name')
                    ->where('school_id', $school->id)
                    ->where('section', $request->input('section'))
                    ->ignore($class->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_classes', 'code')
                    ->where('school_id', $school->id)
                    ->ignore($class->id),
            ],
            'section' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'language_code' => ['nullable', Rule::in(['en', 'fr', 'ar'])],
        ]);

        $languageCode = $data['language_code'] ?? null;
        unset($data['language_code']);

        $data['code'] = filled($data['code'] ?? null) ? strtoupper(trim($data['code'])) : null;

        $class->update($data);

        $this->saveLanguagePreference($school, $class->id, $languageCode);

        return redirect()
            ->route('school.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(Request $request, SchoolClass $class, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolClass($class, $school);

        if ($this->hasLinkedData($class)) {
            $class->update(['status' => 'inactive']);
            $class->delete();

            $auditLog->log('class_archived', $class, $school, metadata: [
                'name' => $class->name,
                'reason' => 'linked_data',
            ], request: $request);

            return back()->with('success', 'This record is linked to existing data. It has been archived instead of permanently deleted.');
        }

        $auditLog->log('class_deleted', $class, $school, metadata: [
            'name' => $class->name,
        ], request: $request);

        $class->forceDelete();

        return back()->with('success', 'Class deleted permanently because no linked data was found.');
    }

    public function restore(Request $request, int $class, AuditLogService $auditLog)
    {
        $school = $this->currentSchoolOrFail();
        $class = SchoolClass::onlyTrashed()
            ->where('school_id', $school->id)
            ->findOrFail($class);

        $class->restore();
        $class->update(['status' => 'active']);

        $auditLog->log('class_restored', $class, $school, metadata: [
            'name' => $class->name,
        ], request: $request);

        return back()->with('success', 'Class restored successfully.');
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeSchoolClass(SchoolClass $class, School $school): void
    {
        if ($class->school_id !== $school->id) {
            abort(403, 'You cannot access this class.');
        }
    }

    private function hasLinkedData(SchoolClass $class): bool
    {
        return $class->students()->withTrashed()->exists()
            || StudentResult::withTrashed()->where('school_class_id', $class->id)->exists()
            || ClassSubjectAssignment::withTrashed()->where('school_class_id', $class->id)->exists()
            || StudentClassEnrollment::where('school_class_id', $class->id)->exists()
            || StudentPromotionBatch::where('from_school_class_id', $class->id)->orWhere('to_school_class_id', $class->id)->exists()
            || StudentPromotionItem::where('from_school_class_id', $class->id)->orWhere('to_school_class_id', $class->id)->exists();
    }

    private function saveLanguagePreference(School $school, int $classId, ?string $languageCode): void
    {
        if (! filled($languageCode)) {
            return;
        }

        LanguagePreference::updateOrCreate([
            'school_id' => $school->id,
            'scope_type' => 'class',
            'scope_id' => $classId,
        ], [
            'language_code' => $languageCode,
            'is_default' => false,
            'status' => 'active',
        ]);
    }
}
