<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\GradingScale;
use App\Models\School;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GradingScaleController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        $gradingScales = $school->gradingScales()
            ->orderByDesc('min_score')
            ->orderBy('sort_order')
            ->get();

        return view('school.grading-scales.index', [
            'school' => $school,
            'gradingScales' => $gradingScales,
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.grading-scales.create', [
            'school' => $school,
        ]);
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        $data = $this->validateGradingScale($request);

        $this->ensureNoOverlap($school, $data);

        $school->gradingScales()->create($data);

        return redirect()
            ->route('school.grading-scales.index')
            ->with('success', 'Grading rule created successfully.');
    }

    public function edit(GradingScale $gradingScale)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeGradingScale($gradingScale, $school);

        return view('school.grading-scales.edit', [
            'school' => $school,
            'gradingScale' => $gradingScale,
        ]);
    }

    public function update(Request $request, GradingScale $gradingScale)
    {
        $school = $this->currentSchoolOrFail();

        $this->authorizeGradingScale($gradingScale, $school);

        $data = $this->validateGradingScale($request);

        $this->ensureNoOverlap($school, $data, $gradingScale->id);

        $gradingScale->update($data);

        return redirect()
            ->route('school.grading-scales.index')
            ->with('success', 'Grading rule updated successfully.');
    }

    private function validateGradingScale(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_score' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_score'],
            'grade' => ['required', 'string', 'max:20'],
            'remark' => ['required', 'string', 'max:100'],
            'is_pass' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function ensureNoOverlap(School $school, array $data, ?int $ignoreId = null): void
    {
        if ($data['status'] !== 'active') {
            return;
        }

        $query = $school->gradingScales()
            ->where('status', 'active')
            ->where('min_score', '<=', $data['max_score'])
            ->where('max_score', '>=', $data['min_score']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'min_score' => 'This score range overlaps with an existing active grading rule.',
                'max_score' => 'This score range overlaps with an existing active grading rule.',
            ]);
        }
    }

    private function currentSchoolOrFail(): School
    {
        $school = app(CurrentSchoolService::class)->get();

        if (! $school) {
            abort(403, 'Your account is not assigned to a school.');
        }

        return $school;
    }

    private function authorizeGradingScale(GradingScale $gradingScale, School $school): void
    {
        if ($gradingScale->school_id !== $school->id) {
            abort(403, 'You cannot access this grading rule.');
        }
    }
}
