<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\TeacherReview;
use App\Services\CurrentSchoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherReviewModerationController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool
    ) {}

    public function index(Request $request): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $query = TeacherReview::query()
            ->where('school_id', $school->id)
            ->with(['teacher', 'reviewer', 'student', 'moderator'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('school.teacher-reviews.index', [
            'school' => $school,
            'reviews' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function approve(Request $request, int|string $teacherReview): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $review = TeacherReview::query()->whereKey($teacherReview)->firstOrFail();

        abort_if((int) $review->school_id !== (int) $school->id, 403);

        $data = $request->validate([
            'moderation_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->forceFill([
            'status' => TeacherReview::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'moderation_note' => $data['moderation_note'] ?? null,
        ])->save();

        return back()->with('success', 'Teacher review approved.');
    }

    public function reject(Request $request, int|string $teacherReview): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $review = TeacherReview::query()->whereKey($teacherReview)->firstOrFail();

        abort_if((int) $review->school_id !== (int) $school->id, 403);

        $data = $request->validate([
            'moderation_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->forceFill([
            'status' => TeacherReview::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'moderation_note' => $data['moderation_note'] ?? null,
        ])->save();

        return back()->with('success', 'Teacher review rejected.');
    }
}
