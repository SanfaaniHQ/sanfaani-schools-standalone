<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtAccessCode;
use App\Models\CbtExam;
use App\Models\CbtQuestion;
use App\Models\School;
use App\Services\CbtResultIntegrationService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CbtExamController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        return view('school.cbt.exams.index', [
            'school' => $school,
            'exams' => $school->cbtExams()
                ->with(['subject', 'schoolClass', 'academicSession', 'term'])
                ->withCount(['examQuestions', 'candidates', 'attempts'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.cbt.exams.create', $this->formData($school, new CbtExam));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validateExam($request, $school);
        $data['school_id'] = $school->id;
        $data['slug'] = $this->uniqueSlug($school, $data['title']);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['language_settings'] = [
            'locales' => $request->input('locales', ['en']),
            'supports_rtl' => (bool) $request->boolean('supports_rtl'),
        ];
        $data['anti_cheat_settings'] = [
            'track_tab_switches' => true,
            'track_refreshes' => true,
            'duplicate_login_detection' => true,
        ];

        $exam = CbtExam::create($data);

        return redirect()
            ->route('school.cbt.exams.show', $exam)
            ->with('success', __('cbt.exam_created'));
    }

    public function show(CbtExam $exam)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($exam, $school);

        return view('school.cbt.exams.show', [
            'school' => $school,
            'exam' => $exam->load(['subject', 'schoolClass', 'academicSession', 'term', 'examQuestions.question.options']),
            'banks' => $school->cbtQuestionBanks()->where('status', 'active')->orderBy('title')->get(),
            'questions' => CbtQuestion::where('school_id', $school->id)->where('status', 'active')->latest()->limit(100)->get(),
            'accessCodes' => $exam->accessCodes()->latest()->limit(30)->get(),
            'attempts' => $exam->attempts()->with(['candidate', 'studentResult'])->latest()->paginate(10),
            'publicUrl' => route('public.cbt.entry', ['school' => $school->slug, 'exam' => $exam->slug]),
        ]);
    }

    public function attachQuestions(Request $request, CbtExam $exam)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($exam, $school);

        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => [Rule::exists('cbt_questions', 'id')->where('school_id', $school->id)],
            'marks' => ['nullable', 'numeric', 'min:0', 'max:1000'],
        ]);

        $nextOrder = ((int) $exam->examQuestions()->max('sort_order')) + 1;
        $marks = (float) ($data['marks'] ?? 1);

        foreach ($data['question_ids'] as $questionId) {
            $exam->examQuestions()->firstOrCreate(
                [
                    'cbt_question_id' => $questionId,
                ],
                [
                    'school_id' => $school->id,
                    'marks' => $marks,
                    'sort_order' => $nextOrder++,
                    'is_required' => true,
                ]
            );
        }

        $this->syncExamTotals($exam);

        return back()->with('success', __('cbt.questions_attached'));
    }

    public function open(CbtExam $exam)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($exam, $school);
        $this->syncExamTotals($exam);

        $exam->update([
            'status' => 'open',
            'published_at' => now(),
            'published_by' => auth()->id(),
        ]);

        return back()->with('success', __('cbt.exam_opened'));
    }

    public function generateCodes(Request $request, CbtExam $exam)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($exam, $school);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'expires_at' => ['nullable', 'date'],
        ]);

        for ($i = 0; $i < (int) $data['quantity']; $i++) {
            CbtAccessCode::create([
                'school_id' => $school->id,
                'cbt_exam_id' => $exam->id,
                'code' => $this->uniqueCode(),
                'usage_limit' => $data['usage_limit'] ?? 1,
                'ends_at' => $data['expires_at'] ?? $exam->ends_at,
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('success', __('cbt.access_codes_generated'));
    }

    public function publishResults(CbtExam $exam, CbtResultIntegrationService $integration)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($exam, $school);

        $publication = $integration->publishExam($exam, $school, auth()->id());

        return back()->with('success', __('cbt.results_published', ['count' => data_get($publication->metadata, 'attempts', 0)]));
    }

    private function validateExam(Request $request, School $school): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string'],
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'exam_type' => ['required', Rule::in(CbtExam::TYPES)],
            'access_type' => ['required', Rule::in(CbtExam::ACCESS_TYPES)],
            'result_type' => ['required', 'string', 'max:60'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'open'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'pass_mark' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'randomize_questions' => ['nullable', 'boolean'],
            'randomize_options' => ['nullable', 'boolean'],
            'allow_resume' => ['nullable', 'boolean'],
            'auto_submit' => ['nullable', 'boolean'],
            'show_result_immediately' => ['nullable', 'boolean'],
            'supports_public_candidates' => ['nullable', 'boolean'],
            'require_fullscreen' => ['nullable', 'boolean'],
        ]) + [
            'randomize_questions' => false,
            'randomize_options' => false,
            'allow_resume' => true,
            'auto_submit' => true,
            'show_result_immediately' => false,
            'supports_public_candidates' => false,
            'require_fullscreen' => false,
        ];
    }

    private function formData(School $school, CbtExam $exam): array
    {
        return [
            'school' => $school,
            'exam' => $exam,
            'subjects' => $school->subjects()->where('status', 'active')->orderBy('name')->get(),
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'sessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->latest()->get(),
            'examTypes' => CbtExam::TYPES,
            'accessTypes' => CbtExam::ACCESS_TYPES,
        ];
    }

    private function syncExamTotals(CbtExam $exam): void
    {
        $exam->load('examQuestions');
        $exam->update([
            'question_count' => $exam->examQuestions->count(),
            'total_marks' => $exam->examQuestions->sum(fn ($item) => (float) $item->marks),
        ]);
    }

    private function authorizeSchoolScope(CbtExam $exam, School $school): void
    {
        if ((int) $exam->school_id !== (int) $school->id) {
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

    private function uniqueSlug(School $school, string $title): string
    {
        $base = Str::slug($title) ?: 'cbt-exam';
        $slug = $base;
        $counter = 2;

        while (CbtExam::where('school_id', $school->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function uniqueCode(): string
    {
        do {
            $code = 'EX-'.strtoupper(Str::random(10));
        } while (CbtAccessCode::where('code', $code)->exists());

        return $code;
    }
}
