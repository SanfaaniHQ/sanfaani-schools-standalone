<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\TeacherReview;
use App\Services\CurrentSchoolService;
use App\Services\Portals\PortalCommunicationService;
use App\Services\Portals\StudentPortalLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherReviewController extends Controller
{
    public function __construct(
        private CurrentSchoolService $currentSchool,
        private PortalCommunicationService $communication,
        private StudentPortalLinkService $studentPortalLinks
    ) {}

    public function index(Request $request): View
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $children = $request->user()->hasRole('parent')
            ? $this->studentPortalLinks->childrenForParent($request->user(), $school)
            : collect();

        $student = $request->user()->hasRole('student')
            ? $this->studentPortalLinks->studentForUser($request->user(), $school)
            : null;

        $reviews = TeacherReview::query()
            ->where('school_id', $school->id)
            ->where('reviewer_user_id', $request->user()->id)
            ->with(['teacher', 'student'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('portal.teacher-reviews.index', [
            'school' => $school,
            'teachers' => $this->communication->teachersForReview($request->user(), $school),
            'children' => $children,
            'student' => $student,
            'reviews' => $reviews,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->currentSchool->get();

        abort_if(! $school, 404);

        $data = $request->validate([
            'teacher_user_id' => ['required', 'integer'],
            'student_id' => ['nullable', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:160'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->communication->submitTeacherReview($request->user(), $school, $data);

        return redirect()
            ->route('portal.teacher-reviews.index')
            ->with('success', 'Teacher review submitted. The school will review it before publishing.');
    }
}
