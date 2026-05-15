<?php

namespace App\Http\Controllers\Public;

use App\Events\StudentTransactionalEmailRequested;
use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolPublicPage;
use App\Models\ScratchCard;
use App\Models\Student;
use App\Models\Term;
use App\Services\AuditLogService;
use App\Services\AuditService;
use App\Services\PlatformSettingService;
use App\Services\PublicResultAccessService;
use App\Services\ReportCardService;
use App\Services\ResultGradingService;
use App\Services\ScratchCardAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResultCheckerController extends Controller
{
    private const CONTEXT_KEY = 'result_checker_context';

    private const CONTEXT_TTL_MINUTES = 15;

    public function __construct(
        private PublicResultAccessService $resultAccess,
        private ScratchCardAccessService $scratchCardAccess,
        private AuditLogService $auditLog
    ) {}

    public function index(Request $request, ?School $school = null, ?SchoolPublicPage $publicPage = null)
    {
        if ($school && ! $this->schoolResultCheckerAvailable($school, $publicPage)) {
            abort(404);
        }

        if ($request->boolean('reset')) {
            $request->session()->forget(self::CONTEXT_KEY);
        }

        [$locale, $rtl] = $this->setPublicLocale($request, $school);

        $viewData = [
            'step' => 1,
            'locale' => $locale,
            'rtl' => $rtl,
            'languages' => $this->languages(),
            'selectedSchool' => $school,
            'publicPage' => $publicPage,
            'publicPageSlug' => $publicPage?->slug,
            'resultCheckerSlug' => $request->attributes->get('result_checker_slug'),
            'isBrandedSchoolRoute' => (bool) $school,
            'contextSchool' => null,
            'contextStudent' => null,
            'scratchCard' => null,
            'academicSessions' => collect(),
            'terms' => collect(),
            'lockedAcademicSession' => null,
            'lockedTerm' => null,
            'lockedResultType' => null,
            'selectedAcademicSessionId' => null,
            'selectedTermId' => null,
            'selectedResultType' => 'term_result',
        ];

        if ($context = $this->resultCheckerContext($request, $school)) {
            $viewData = array_merge($viewData, $this->contextViewData($request, $context));
        }

        return view('public.results.check', $viewData);
    }

    public function identify(Request $request, ?School $school = null, ?SchoolPublicPage $publicPage = null)
    {
        if ($school && ! $this->schoolResultCheckerAvailable($school, $publicPage)) {
            abort(404);
        }

        [$locale] = $this->setPublicLocale($request, $school);

        AuditService::log('result', 'public_result_identify_attempt', [
            'school_id' => $school?->id,
            'admission_number' => $request->input('admission_number'),
            'branded_route' => (bool) $school,
        ]);

        $data = $request->validate([
            'admission_number' => ['required', 'string', 'max:100'],
            'scratch_card_serial' => ['required', 'string', 'max:100'],
            'scratch_card_pin' => ['required', 'string', 'max:100'],
            'website_url' => ['nullable', 'max:0'],
            'lang' => ['nullable', Rule::in(array_keys($this->languages()))],
        ]);

        $cardCheck = $this->scratchCardAccess->verifyCardCredentials(
            $data['scratch_card_serial'],
            $data['scratch_card_pin']
        );

        if (! $cardCheck['success']) {
            return $this->redirectToChecker($locale, $school, $cardCheck['message'], true, $request, $publicPage);
        }

        $card = $cardCheck['scratchCard'];
        $identifiedSchool = School::where('status', 'active')->find($card->school_id);

        if (! $identifiedSchool || ($school && (int) $school->id !== (int) $identifiedSchool->id)) {
            return $this->redirectToChecker($locale, $school, __('public_result.invalid_access_details'), true, $request, $publicPage);
        }

        $student = Student::withTrashed()
            ->where('school_id', $identifiedSchool->id)
            ->where('admission_number', trim($data['admission_number']))
            ->first();

        if (! $student) {
            return $this->redirectToChecker($locale, $school, __('public_result.invalid_access_details'), true, $request, $publicPage);
        }

        $request->session()->put(self::CONTEXT_KEY, [
            'school_id' => $identifiedSchool->id,
            'student_id' => $student->id,
            'scratch_card_id' => $card->id,
            'admission_number' => $student->admission_number,
            'created_at' => now()->timestamp,
        ]);

        return redirect()->route(
            $this->checkerRouteName($school, $publicPage, 'index'),
            $this->checkerRouteParameters($locale, $school, $publicPage)
        );
    }

    public function check(Request $request, ?School $school = null, ?SchoolPublicPage $publicPage = null)
    {
        if ($school && ! $this->schoolResultCheckerAvailable($school, $publicPage)) {
            abort(404);
        }

        $routeSchool = $school;
        [$locale] = $this->setPublicLocale($request, $school);

        AuditService::log('result', 'public_result_check_attempt', [
            'school_id' => $school?->id,
            'academic_session_id' => $request->input('academic_session_id'),
            'term_id' => $request->input('term_id'),
            'result_type' => $request->input('result_type'),
            'branded_route' => (bool) $school,
        ]);

        $context = $this->resultCheckerContext($request, $school);

        if (! $context) {
            return $this->redirectToChecker($locale, $routeSchool, __('public_result.context_expired'), publicPage: $publicPage);
        }

        $models = $this->loadContextModels($context);

        if (! $models) {
            $request->session()->forget(self::CONTEXT_KEY);

            return $this->redirectToChecker($locale, $routeSchool, __('public_result.context_expired'), publicPage: $publicPage);
        }

        $contextSchool = $models['school'];
        $student = $models['student'];
        $card = $models['scratchCard'];

        $data = $request->validate([
            'academic_session_id' => ['required', 'integer'],
            'term_id' => ['required', 'integer'],
            'result_type' => ['required', Rule::in(['term_result'])],
            'website_url' => ['nullable', 'max:0'],
            'lang' => ['nullable', Rule::in(array_keys($this->languages()))],
        ]);

        $academicSession = AcademicSession::where('school_id', $contextSchool->id)
            ->find($data['academic_session_id']);

        $term = $academicSession
            ? Term::where('school_id', $contextSchool->id)
                ->where('academic_session_id', $academicSession->id)
                ->find($data['term_id'])
            : null;

        if (! $academicSession || ! $term) {
            return $this->redirectToChecker($locale, $routeSchool, __('public_result.card_not_valid_for_result'), publicPage: $publicPage);
        }

        $access = $this->resultAccess->evaluateAccess($contextSchool, $academicSession, $term, $data['result_type']);

        if (! $access['success']) {
            return $this->redirectToChecker($locale, $routeSchool, $access['message'], publicPage: $publicPage);
        }

        $scratchCardAccess = $this->scratchCardAccess->validateCardForResult(
            $card,
            $contextSchool,
            $student,
            $academicSession,
            $term,
            $data['result_type']
        );

        if (! $scratchCardAccess['success']) {
            return $this->redirectToChecker($locale, $routeSchool, $scratchCardAccess['message'], publicPage: $publicPage);
        }

        if (! $this->resultAccess->hasPublishedResults($contextSchool, $student, $academicSession, $term, $data['result_type'])) {
            return $this->redirectToChecker($locale, $routeSchool, __('public_result.result_not_available'), publicPage: $publicPage);
        }

        $scratchCardAccess = $this->scratchCardAccess->recordSuccessfulUsage(
            $card,
            $contextSchool,
            $student,
            $academicSession,
            $term,
            $data['result_type'],
            $request
        );

        if (! $scratchCardAccess['success']) {
            return $this->redirectToChecker($locale, $routeSchool, $scratchCardAccess['message'], publicPage: $publicPage);
        }

        $token = $this->resultAccess->createToken(
            $request,
            $contextSchool,
            $student,
            $academicSession,
            $term,
            $data['result_type'],
            $locale
        );

        $this->auditLog->log('public_result_checked', $student, $contextSchool, metadata: [
            'academic_session_id' => $academicSession->id,
            'term_id' => $term->id,
            'result_type' => $data['result_type'],
            'scratch_card_id' => $scratchCardAccess['scratchCard']?->id,
        ], request: $request);

        StudentTransactionalEmailRequested::dispatch(
            StudentTransactionalEmailRequested::resultAvailable($student->loadMissing('school'), $academicSession, $term, [
                'result_type' => $data['result_type'],
                'scratch_card_id' => $scratchCardAccess['scratchCard']?->id,
            ])
        );

        $request->session()->forget(self::CONTEXT_KEY);

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
        $student = Student::withTrashed()
            ->where('school_id', $tokenData['school_id'] ?? null)
            ->find($tokenData['student_id'] ?? null);
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
            'reportCard' => app(ReportCardService::class)->displayData(
                $school,
                $student,
                $academicSession,
                $term,
                $results,
                true
            ),
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

    private function contextViewData(Request $request, array $context): array
    {
        $models = $this->loadContextModels($context);

        if (! $models) {
            $request->session()->forget(self::CONTEXT_KEY);

            return ['step' => 1];
        }

        $school = $models['school'];
        $card = $models['scratchCard'];
        $student = $models['student'];
        $lockedTerm = $card->term_id
            ? Term::where('school_id', $school->id)->find($card->term_id)
            : null;
        $lockedAcademicSession = $card->academic_session_id
            ? AcademicSession::where('school_id', $school->id)->find($card->academic_session_id)
            : (
                $lockedTerm
                    ? AcademicSession::where('school_id', $school->id)->find($lockedTerm->academic_session_id)
                    : null
            );

        $selectedAcademicSessionId = (int) old(
            'academic_session_id',
            $lockedAcademicSession?->id ?: $lockedTerm?->academic_session_id
        );
        $selectedTermId = (int) old('term_id', $lockedTerm?->id);

        return [
            'step' => 2,
            'contextSchool' => $school,
            'contextStudent' => $student,
            'scratchCard' => $card,
            'academicSessions' => AcademicSession::where('school_id', $school->id)
                ->orderByDesc('starts_at')
                ->orderByDesc('id')
                ->get(),
            'terms' => Term::where('school_id', $school->id)
                ->orderBy('academic_session_id')
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get(),
            'lockedAcademicSession' => $lockedAcademicSession,
            'lockedTerm' => $lockedTerm,
            'lockedResultType' => $card->result_type,
            'selectedAcademicSessionId' => $selectedAcademicSessionId ?: null,
            'selectedTermId' => $selectedTermId ?: null,
            'selectedResultType' => old('result_type', $card->result_type ?: 'term_result'),
        ];
    }

    private function resultCheckerContext(Request $request, ?School $school = null): ?array
    {
        $context = $request->session()->get(self::CONTEXT_KEY);

        if (! is_array($context) || ! isset($context['school_id'], $context['student_id'], $context['scratch_card_id'], $context['created_at'])) {
            return null;
        }

        if ((int) $context['created_at'] < now()->subMinutes(self::CONTEXT_TTL_MINUTES)->timestamp) {
            $request->session()->forget(self::CONTEXT_KEY);

            return null;
        }

        if ($school && (int) $context['school_id'] !== (int) $school->id) {
            $request->session()->forget(self::CONTEXT_KEY);

            return null;
        }

        return $context;
    }

    private function loadContextModels(array $context): ?array
    {
        $school = School::where('status', 'active')->find($context['school_id'] ?? null);

        if (! $school) {
            return null;
        }

        $student = Student::withTrashed()
            ->where('school_id', $school->id)
            ->find($context['student_id'] ?? null);
        $scratchCard = ScratchCard::where('school_id', $school->id)->find($context['scratch_card_id'] ?? null);

        if (! $student || ! $scratchCard) {
            return null;
        }

        return [
            'school' => $school,
            'student' => $student,
            'scratchCard' => $scratchCard,
        ];
    }

    private function redirectToChecker(
        string $locale,
        ?School $school,
        string $message,
        bool $withInput = false,
        ?Request $request = null,
        ?SchoolPublicPage $publicPage = null
    ) {
        $redirect = redirect()
            ->route(
                $this->checkerRouteName($school, $publicPage, 'index'),
                $this->checkerRouteParameters($locale, $school, $publicPage)
            )
            ->with('error', $message);

        if ($withInput && $request) {
            $redirect->withInput($request->except('scratch_card_pin'));
        }

        return $redirect;
    }

    private function checkerRouteParameters(string $locale, ?School $school = null, ?SchoolPublicPage $publicPage = null): array
    {
        $parameters = ['lang' => $locale];

        if ($slug = request()->attributes->get('result_checker_slug')) {
            return array_merge(['slug' => $slug], $parameters);
        }

        if ($publicPage) {
            return array_merge(['slug' => $publicPage->slug], $parameters);
        }

        if ($school) {
            return array_merge(['school' => $school->slug ?: $school->getKey()], $parameters);
        }

        return $parameters;
    }

    private function checkerRouteName(?School $school = null, ?SchoolPublicPage $publicPage = null, string $action = 'index'): string
    {
        if (request()->attributes->has('result_checker_slug')) {
            return "public.results.slug.{$action}";
        }

        if ($publicPage) {
            return "public.schools.results.{$action}";
        }

        if ($school) {
            return "public.school.results.{$action}";
        }

        return "public.results.{$action}";
    }

    private function schoolResultCheckerAvailable(School $school, ?SchoolPublicPage $publicPage = null): bool
    {
        if ($school->status !== 'active') {
            return false;
        }

        if (request()->attributes->has('result_checker_slug') && ! (bool) $school->is_result_checker_enabled) {
            return false;
        }

        $settings = app(PlatformSettingService::class);

        if (! $settings->publicResultCheckerEnabled()) {
            return false;
        }

        $publicPage ??= $school->publicPage()->with('school.websiteSetting')->first();

        if (! $publicPage) {
            return true;
        }

        return $publicPage->is_active
            && $publicPage->result_checker_enabled
            && (bool) $school->websiteSetting?->result_checker_enabled;
    }
}
