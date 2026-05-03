<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\PublicResultAccessService;
use App\Services\ResultGradingService;
use App\Services\ScratchCardAccessService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResultCheckerController extends Controller
{
    public function __construct(
        private PublicResultAccessService $resultAccess,
        private ScratchCardAccessService $scratchCardAccess,
        private AuditLogService $auditLog
    ) {}

    public function index(Request $request, ?School $school = null)
    {
        if ($school && $school->status !== 'active') {
            abort(404);
        }

        [$locale, $rtl] = $this->setPublicLocale($request, $school);

        $selectedSchool = $school;

        if (! $selectedSchool && $request->filled('school_id')) {
            $selectedSchool = School::where('status', 'active')
                ->find($request->integer('school_id'));
        }

        return view('public.results.check', [
            'locale' => $locale,
            'rtl' => $rtl,
            'languages' => $this->languages(),
            'schools' => School::where('status', 'active')->orderBy('name')->get(),
            'selectedSchool' => $selectedSchool,
            'academicSessions' => $selectedSchool
                ? $selectedSchool->academicSessions()->where('status', 'active')->latest()->get()
                : collect(),
            'terms' => $selectedSchool
                ? $selectedSchool->terms()->with('academicSession')->where('status', 'active')->latest()->get()
                : collect(),
            'isBrandedSchoolRoute' => (bool) $school,
        ]);
    }

    public function check(Request $request, ?School $school = null)
    {
        if ($school && $school->status !== 'active') {
            abort(404);
        }

        [$locale] = $this->setPublicLocale($request, $school);
        $schoolId = $school?->id ?? $request->input('school_id');

        $data = $request->validate([
            'school_id' => [
                Rule::requiredIf(! $school),
                Rule::exists('schools', 'id')->where('status', 'active'),
            ],
            'admission_number' => ['required', 'string', 'max:100'],
            'academic_session_id' => [
                'required',
                Rule::exists('academic_sessions', 'id')->where('school_id', $schoolId),
            ],
            'term_id' => [
                'required',
                Rule::exists('terms', 'id')
                    ->where('school_id', $schoolId)
                    ->where('academic_session_id', $request->input('academic_session_id')),
            ],
            'result_type' => ['required', Rule::in(['term_result'])],
            'scratch_card_serial' => ['required', 'string', 'max:100'],
            'scratch_card_pin' => ['required', 'string', 'max:100'],
            'lang' => ['nullable', Rule::in(array_keys($this->languages()))],
        ], [
            'school_id.required' => __('public_result.select_school'),
            'scratch_card_serial.required' => __('public_result.scratch_card_serial_number'),
            'scratch_card_pin.required' => __('public_result.scratch_card_pin'),
        ]);

        $school = School::where('status', 'active')->find($schoolId);
        $academicSession = AcademicSession::where('school_id', $schoolId)->find($data['academic_session_id']);
        $term = Term::where('school_id', $schoolId)
            ->where('academic_session_id', $data['academic_session_id'])
            ->find($data['term_id']);

        if (! $school || ! $academicSession || ! $term) {
            return $this->backWithSafeError($request, __('public_result.check_details'));
        }

        $student = Student::where('school_id', $school->id)
            ->where('admission_number', trim($data['admission_number']))
            ->first();

        if (! $student) {
            return $this->backWithSafeError($request, __('public_result.check_details'));
        }

        if (! $this->resultAccess->hasPublishedResults($school, $student, $academicSession, $term, $data['result_type'])) {
            return $this->backWithSafeError($request, __('public_result.result_not_available'));
        }

        $access = $this->resultAccess->evaluateAccess($school, $academicSession, $term, $data['result_type']);

        if (! $access['success']) {
            return $this->backWithSafeError($request, $access['message']);
        }

        $scratchCardAccess = $this->scratchCardAccess->validateAndRecord(
            $school,
            $student,
            $academicSession,
            $term,
            $data['result_type'],
            $data['scratch_card_serial'],
            $data['scratch_card_pin'],
            $request
        );

        if (! $scratchCardAccess['success']) {
            return $this->backWithSafeError($request, $scratchCardAccess['message']);
        }

        $token = $this->resultAccess->createToken(
            $request,
            $school,
            $student,
            $academicSession,
            $term,
            $data['result_type'],
            $locale
        );

        $this->auditLog->log('public_result_checked', $student, $school, metadata: [
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $data['result_type'],
            'scratch_card_id' => $scratchCardAccess['scratchCard']?->id,
        ], request: $request);

        return redirect()->route('public.results.view', ['token' => $token, 'lang' => $locale]);
    }

    public function print(Request $request, string $token)
    {
        $request->merge(['print' => true]);

        return $this->view($request, $token);
    }

    public function view(Request $request, string $token)
    {
        $tokenData = $this->resultAccess->tokenData($request, $token);

        if (! $tokenData) {
            return redirect()
                ->route('public.results.index')
                ->with('error', __('public_result.result_not_available'));
        }

        [$locale, $rtl] = $this->setPublicLocale($request, null, $tokenData['locale'] ?? null);

        $school = School::where('status', 'active')->find($tokenData['school_id'] ?? null);
        $student = Student::where('school_id', $tokenData['school_id'] ?? null)->find($tokenData['student_id'] ?? null);
        $academicSession = AcademicSession::where('school_id', $tokenData['school_id'] ?? null)
            ->find($tokenData['academic_session_id'] ?? null);
        $term = Term::where('school_id', $tokenData['school_id'] ?? null)
            ->where('academic_session_id', $tokenData['academic_session_id'] ?? null)
            ->find($tokenData['term_id'] ?? null);

        if (! $school || ! $student || ! $academicSession || ! $term) {
            return redirect()
                ->route('public.results.index')
                ->with('error', __('public_result.result_not_available'));
        }

        $results = $this->resultAccess->publishedResults(
            $school,
            $student,
            $academicSession,
            $term,
            $tokenData['result_type'] ?? 'term_result'
        );

        if ($results->isEmpty()) {
            return redirect()
                ->route('public.results.index')
                ->with('error', __('public_result.result_not_available'));
        }

        $totalScore = $results->sum(fn ($result) => (float) $result->total_score);
        $averageScore = $results->count() > 0 ? round($totalScore / $results->count(), 2) : 0;
        $overall = app(ResultGradingService::class)->calculate($school, $averageScore);
        $resultType = $tokenData['result_type'] ?? 'term_result';
        $verification = $this->resultAccess->verificationFor(
            $school,
            $student,
            $academicSession,
            $term,
            $resultType
        );

        return view('public.results.view', [
            'locale' => $locale,
            'rtl' => $rtl,
            'school' => $school,
            'student' => $student->load('schoolClass'),
            'academicSession' => $academicSession,
            'term' => $term,
            'resultType' => $resultType,
            'results' => $results,
            'totalScore' => $totalScore,
            'averageScore' => $averageScore,
            'overall' => $overall,
            'verification' => $verification,
            'verificationUrl' => route('public.results.verify', $verification->verification_code),
            'printMode' => $request->boolean('print'),
        ]);
    }

    private function setPublicLocale(Request $request, ?School $school = null, ?string $preferred = null): array
    {
        $locale = $request->input('lang')
            ?: $preferred
            ?: $request->session()->get('public_result_locale')
            ?: $school?->default_language
            ?: 'en';

        if (! array_key_exists($locale, $this->languages())) {
            $locale = 'en';
        }

        app()->setLocale($locale);
        $request->session()->put('public_result_locale', $locale);

        return [$locale, $locale === 'ar'];
    }

    private function languages(): array
    {
        return [
            'en' => 'English',
            'fr' => 'French',
            'ar' => 'Arabic',
        ];
    }

    private function backWithSafeError(Request $request, string $message)
    {
        return back()
            ->withInput($request->except('scratch_card_pin'))
            ->with('error', $message);
    }
}
