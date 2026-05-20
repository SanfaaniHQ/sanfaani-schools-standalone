<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtAttempt;
use App\Models\CbtAttemptAnswer;
use App\Models\School;
use App\Services\CbtAttemptService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;

class CbtMarkingController extends Controller
{
    public function index()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.cbt.marking.index', [
            'school' => $school,
            'attempts' => CbtAttempt::query()
                ->where('school_id', $school->id)
                ->whereHas('answers', fn ($query) => $query->where('status', 'needs_marking'))
                ->with(['exam', 'candidate', 'student'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function show(CbtAttempt $attempt)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($attempt, $school);

        return view('school.cbt.marking.show', [
            'school' => $school,
            'attempt' => $attempt->load(['exam', 'candidate', 'student', 'answers.question.options']),
        ]);
    }

    public function update(Request $request, CbtAttemptAnswer $answer, CbtAttemptService $attempts)
    {
        $school = $this->currentSchoolOrFail();

        if ((int) $answer->school_id !== (int) $school->id) {
            abort(403);
        }

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:1000'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $attempts->markAnswer($answer, (float) $data['score'], $data['comment'] ?? null, $request->user(), $request);

        return back()->with('success', __('cbt.answer_marked'));
    }

    private function authorizeSchoolScope(CbtAttempt $attempt, School $school): void
    {
        if ((int) $attempt->school_id !== (int) $school->id) {
            abort(403);
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
}
