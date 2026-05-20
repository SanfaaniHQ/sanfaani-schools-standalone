<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CbtQuestionBank;
use App\Models\School;
use App\Services\CbtQuestionImportService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CbtQuestionBankController extends Controller
{
    public function index(Request $request)
    {
        $school = $this->currentSchoolOrFail();

        return view('school.cbt.question-banks.index', [
            'school' => $school,
            'banks' => $school->cbtQuestionBanks()
                ->with(['subject', 'schoolClass'])
                ->withCount('questions')
                ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->input('search').'%'))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'search' => $request->input('search'),
        ]);
    }

    public function create()
    {
        $school = $this->currentSchoolOrFail();

        return view('school.cbt.question-banks.create', $this->formData($school, new CbtQuestionBank));
    }

    public function store(Request $request)
    {
        $school = $this->currentSchoolOrFail();
        $data = $this->validateBank($request, $school);
        $data['school_id'] = $school->id;
        $data['code'] = $data['code'] ?: $this->uniqueCode($school, $data['title']);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['tags'] = $this->tags($request->input('tags'));

        $bank = CbtQuestionBank::create($data);

        return redirect()
            ->route('school.cbt.question-banks.show', $bank)
            ->with('success', __('cbt.question_bank_created'));
    }

    public function show(CbtQuestionBank $questionBank)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($questionBank, $school);

        return view('school.cbt.question-banks.show', [
            'school' => $school,
            'bank' => $questionBank->load(['subject', 'schoolClass']),
            'questions' => $questionBank->questions()
                ->with('options')
                ->latest()
                ->paginate(10),
            'questionTypes' => $this->questionTypes(),
        ]);
    }

    public function import(Request $request, CbtQuestionBank $questionBank, CbtQuestionImportService $importer)
    {
        $school = $this->currentSchoolOrFail();
        $this->authorizeSchoolScope($questionBank, $school);

        $data = $request->validate([
            'question_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $importer->importCsv($school, $questionBank, $data['question_file']->getRealPath(), $request->user());

        return back()
            ->with('success', __('cbt.questions_imported', ['count' => $importer->createdCount]))
            ->with('import_errors', $importer->errors);
    }

    private function validateBank(Request $request, School $school): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:80', Rule::unique('cbt_question_banks', 'code')->where('school_id', $school->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'subject_id' => ['nullable', Rule::exists('subjects', 'id')->where('school_id', $school->id)],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_id', $school->id)],
            'academic_session_id' => ['nullable', Rule::exists('academic_sessions', 'id')->where('school_id', $school->id)],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $school->id)],
            'category' => ['nullable', 'string', 'max:120'],
            'topic' => ['nullable', 'string', 'max:160'],
            'difficulty' => ['required', Rule::in(['mixed', 'easy', 'medium', 'hard', 'advanced'])],
            'default_locale' => ['required', Rule::in(array_keys(config('sanfaani.languages', [])))],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'is_reusable' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]) + [
            'is_reusable' => true,
        ];
    }

    private function formData(School $school, CbtQuestionBank $bank): array
    {
        return [
            'school' => $school,
            'bank' => $bank,
            'subjects' => $school->subjects()->where('status', 'active')->orderBy('name')->get(),
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'sessions' => $school->academicSessions()->where('status', 'active')->latest()->get(),
            'terms' => $school->terms()->where('status', 'active')->latest()->get(),
            'languages' => config('sanfaani.languages', []),
        ];
    }

    private function authorizeSchoolScope(CbtQuestionBank $bank, School $school): void
    {
        if ((int) $bank->school_id !== (int) $school->id) {
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

    private function uniqueCode(School $school, string $title): string
    {
        $base = Str::upper(Str::slug($title, '-')) ?: 'BANK';
        $code = Str::limit($base, 70, '');
        $suffix = 2;

        while (CbtQuestionBank::where('school_id', $school->id)->where('code', $code)->exists()) {
            $code = Str::limit($base, 65, '').'-'.$suffix++;
        }

        return $code;
    }

    private function tags(?string $tags): array
    {
        return collect(explode(',', (string) $tags))->map(fn ($tag) => trim($tag))->filter()->values()->all();
    }

    private function questionTypes(): array
    {
        return [
            'multiple_choice' => __('cbt.multiple_choice'),
            'checkbox' => __('cbt.checkbox'),
            'true_false' => __('cbt.true_false'),
            'fill_blank' => __('cbt.fill_blank'),
            'short_answer' => __('cbt.short_answer'),
            'long_answer' => __('cbt.long_answer'),
            'essay' => __('cbt.essay'),
            'matching' => __('cbt.matching'),
            'practical_instruction' => __('cbt.practical_instruction'),
            'image_based' => __('cbt.image_based'),
            'diagram_based' => __('cbt.diagram_based'),
            'table_based' => __('cbt.table_based'),
            'comprehension' => __('cbt.comprehension'),
        ];
    }
}
