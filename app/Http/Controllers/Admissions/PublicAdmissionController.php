<?php

namespace App\Http\Controllers\Admissions;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\Admissions\AdmissionApplicationService;
use App\Services\Admissions\AdmissionWebsiteIntegrationService;
use Illuminate\Http\Request;

class PublicAdmissionController extends Controller
{
    public function __construct(
        private readonly AdmissionApplicationService $applications,
        private readonly AdmissionWebsiteIntegrationService $integration
    ) {
    }

    public function index()
    {
        [$school, $cycle] = $this->publicContext();

        return view('admissions.index', compact('school', 'cycle'));
    }

    public function create(Request $request)
    {
        [$school, $cycle] = $this->publicContext();

        return view('admissions.apply', [
            'school' => $school,
            'cycle' => $cycle,
            'classes' => $this->classes($school),
            'sourceChannel' => $this->integration->sourceChannel($school, $request->query('channel')),
            'embed' => false,
        ]);
    }

    public function store(Request $request)
    {
        [$school] = $this->publicContext(requireOpenCycle: true);
        $validated = $request->validate($this->applications->validationRules($school));
        $result = $this->applications->submit($school, $validated);

        return response()->view('admissions.acknowledgement', [
            'school' => $school,
            'application' => $result['application'],
            'trackingToken' => $result['tracking_token'],
        ], 201);
    }

    public function trackForm()
    {
        [$school] = $this->publicContext();
        abort_unless(config('admissions.tracking_enabled'), 404);

        return view('admissions.track', compact('school'));
    }

    public function track(Request $request)
    {
        [$school] = $this->publicContext();
        abort_unless(config('admissions.tracking_enabled'), 404);

        $validated = $request->validate([
            'application_number' => ['required', 'string', 'max:64'],
            'tracking_token' => ['nullable', 'string', 'max:100', 'required_without:guardian_phone'],
            'guardian_phone' => ['nullable', 'string', 'max:50', 'required_without:tracking_token'],
        ]);

        $application = $this->applications->track(
            $validated['application_number'],
            $validated['tracking_token'] ?? null,
            $validated['guardian_phone'] ?? null
        );

        if (! $application || (int) $application->school_id !== (int) $school->id) {
            return back()
                ->withInput($request->only('application_number'))
                ->withErrors(['application_number' => 'The application details could not be verified.']);
        }

        $application->load([
            'notes' => fn ($query) => $query->where('visibility', 'public')->latest(),
        ]);

        return view('admissions.status', compact('school', 'application'));
    }

    public function embed(Request $request)
    {
        abort_unless(config('admissions.embed_enabled'), 404);
        [$school, $cycle] = $this->publicContext();

        $response = response()->view('admissions.apply', [
            'school' => $school,
            'cycle' => $cycle,
            'classes' => $this->classes($school),
            'sourceChannel' => $this->integration->sourceChannel($school, $request->query('channel'), 'embed'),
            'embed' => true,
        ]);

        return $response
            ->header('Content-Security-Policy', "frame-ancestors 'self' https: http:")
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function publicContext(bool $requireOpenCycle = false): array
    {
        abort_unless($this->integration->publicEnabled(), 404);
        $school = $this->integration->resolvePortalSchool();
        abort_unless($school, 404);

        $cycle = $this->integration->currentCycle($school);
        if ($requireOpenCycle) {
            abort_unless($cycle, 403, 'Admissions are not currently accepting applications.');
        }

        return [$school, $cycle];
    }

    private function classes(School $school)
    {
        return $school->schoolClasses()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
