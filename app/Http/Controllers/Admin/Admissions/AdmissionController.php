<?php

namespace App\Http\Controllers\Admin\Admissions;

use App\Http\Controllers\Controller;
use App\Models\Admissions\AdmissionApiKey;
use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionChannel;
use App\Models\Admissions\AdmissionCycle;
use App\Models\Admissions\AdmissionDocument;
use App\Models\Admissions\AdmissionPayment;
use App\Models\School;
use App\Notifications\Admissions\AdmissionPaymentNotification;
use App\Services\Admissions\AdmissionConversionService;
use App\Services\Admissions\AdmissionWebsiteIntegrationService;
use App\Services\Admissions\AdmissionWorkflowService;
use App\Services\CurrentSchoolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class AdmissionController extends Controller
{
    public function index()
    {
        $school = $this->school();
        $query = $school->admissionApplications();

        return view('admin.admissions.index', [
            'school' => $school,
            'cycle' => app(AdmissionWebsiteIntegrationService::class)->currentCycle($school),
            'totalApplications' => (clone $query)->count(),
            'submittedApplications' => (clone $query)->where('status', AdmissionApplication::STATUS_SUBMITTED)->count(),
            'acceptedApplications' => (clone $query)->whereIn('status', [
                AdmissionApplication::STATUS_ACCEPTED,
                AdmissionApplication::STATUS_ADMITTED,
                AdmissionApplication::STATUS_CONVERTED,
            ])->count(),
            'pendingDocuments' => AdmissionDocument::whereHas(
                'application',
                fn ($query) => $query->where('school_id', $school->id)
            )->where('status', AdmissionDocument::STATUS_PENDING)->count(),
        ]);
    }

    public function applications(Request $request)
    {
        $school = $this->school();
        $applications = $school->admissionApplications()
            ->with(['cycle', 'requestedClass', 'guardians'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('requested_class_id'), fn ($query) => $query->where('requested_class_id', $request->integer('requested_class_id')))
            ->when($request->filled('source_channel'), fn ($query) => $query->where('source_channel', $request->input('source_channel')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->input('payment_status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(fn ($query) => $query
                    ->where('application_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.admissions.applications.index', [
            'school' => $school,
            'applications' => $applications,
            'classes' => $school->schoolClasses()->where('status', 'active')->orderBy('name')->get(),
            'statuses' => AdmissionApplication::STATUSES,
            'paymentStatuses' => AdmissionApplication::PAYMENT_STATUSES,
            'channels' => $school->admissionApplications()->whereNotNull('source_channel')->distinct()->orderBy('source_channel')->pluck('source_channel'),
        ]);
    }

    public function show(AdmissionApplication $application)
    {
        $school = $this->school();
        $this->authorizeApplication($application, $school);
        $application->load([
            'cycle',
            'requestedClass',
            'convertedStudent',
            'guardians',
            'documents.reviewer',
            'statusLogs.changedBy',
            'notes.user',
            'interviews',
            'payments.confirmedBy',
        ]);

        return view('admin.admissions.applications.show', [
            'school' => $school,
            'application' => $application,
            'transitions' => app(AdmissionWorkflowService::class)->availableTransitions($application),
        ]);
    }

    public function updateStatus(Request $request, AdmissionApplication $application)
    {
        $this->authorizeApplication($application, $this->school());
        $validated = $request->validate([
            'status' => ['required', Rule::in(AdmissionApplication::STATUSES)],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        app(AdmissionWorkflowService::class)->changeStatus(
            $application,
            $validated['status'],
            $request->user()->id,
            $validated['note'] ?? null
        );

        return back()->with('success', 'Application status updated.');
    }

    public function addNote(Request $request, AdmissionApplication $application)
    {
        $this->authorizeApplication($application, $this->school());
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', Rule::in(['internal', 'public'])],
        ]);

        $application->notes()->create($validated + ['user_id' => $request->user()->id]);

        return back()->with('success', 'Admission note added.');
    }

    public function reviewDocument(Request $request, AdmissionApplication $application, AdmissionDocument $document)
    {
        $this->authorizeNested($application, $document->admission_application_id);
        $validated = $request->validate([
            'status' => ['required', Rule::in(AdmissionDocument::STATUSES)],
        ]);

        $document->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Document review saved.');
    }

    public function downloadDocument(AdmissionApplication $application, AdmissionDocument $document)
    {
        $this->authorizeNested($application, $document->admission_application_id);
        $disk = (string) config('admissions.document_disk', 'local');
        abort_unless(Storage::disk($disk)->exists($document->storage_path), 404);

        return Storage::disk($disk)->download($document->storage_path, $document->original_name);
    }

    public function addPayment(Request $request, AdmissionApplication $application)
    {
        $this->authorizeApplication($application, $this->school());
        abort_unless(config('admissions.manual_payment_enabled'), 403);
        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'reference' => ['nullable', 'string', 'max:191'],
        ]);

        $application->payments()->create($validated + ['method' => 'manual', 'status' => 'pending']);
        $application->update(['payment_status' => AdmissionApplication::PAYMENT_PENDING]);

        return back()->with('success', 'Manual payment record added.');
    }

    public function confirmPayment(Request $request, AdmissionApplication $application, AdmissionPayment $payment)
    {
        $this->authorizeNested($application, $payment->admission_application_id);
        abort_unless(config('admissions.manual_payment_enabled'), 403);

        $payment->update([
            'status' => 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);
        $application->update(['payment_status' => AdmissionApplication::PAYMENT_CONFIRMED]);
        $this->notifyPayment($application->loadMissing('guardians'));

        return back()->with('success', 'Manual payment confirmed.');
    }

    public function scheduleInterview(Request $request, AdmissionApplication $application)
    {
        $this->authorizeApplication($application, $this->school());
        $validated = $request->validate([
            'type' => ['required', Rule::in(['interview', 'entrance_exam'])],
            'scheduled_at' => ['nullable', 'date'],
            'score' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->interviews()->create($validated);

        return back()->with('success', 'Interview or entrance exam saved.');
    }

    public function convert(Request $request, AdmissionApplication $application)
    {
        $this->authorizeApplication($application, $this->school());
        $student = app(AdmissionConversionService::class)->convert($application, $request->user()->id);

        return redirect()
            ->route('school.students.show', $student)
            ->with('success', 'Applicant converted to a student record.');
    }

    public function settings()
    {
        $school = $this->school();

        return view('admin.admissions.settings', [
            'school' => $school,
            'cycle' => $school->admissionCycles()->latest()->first(),
            'sessions' => $school->academicSessions()->latest()->get(),
            'channels' => $school->admissionChannels()->latest()->get(),
            'apiKeys' => $school->admissionApiKeys()->with('channel')->latest()->get(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $school = $this->school();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'academic_session_id' => [
                'nullable',
                Rule::exists('academic_sessions', 'id')->where('school_id', $school->id),
            ],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_open' => ['nullable', 'boolean'],
            'requirements' => ['nullable', 'string', 'max:5000'],
        ]);

        $cycle = $school->admissionCycles()->latest()->first() ?: new AdmissionCycle(['school_id' => $school->id]);
        $cycle->fill([
            'name' => $validated['name'],
            'academic_session_id' => $validated['academic_session_id'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_open' => $request->boolean('is_open'),
            'settings' => [
                'requirements' => collect(preg_split('/\r\n|\r|\n/', (string) ($validated['requirements'] ?? '')))
                    ->map(fn ($item) => trim($item))
                    ->filter()
                    ->values()
                    ->all(),
            ],
        ]);
        $cycle->save();

        return back()->with('success', 'Admission settings saved.');
    }

    public function addChannel(Request $request)
    {
        $school = $this->school();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admission_channels')->where('school_id', $school->id)],
            'type' => ['required', Rule::in(AdmissionChannel::TYPES)],
            'allowed_domain' => ['nullable', 'string', 'max:191'],
        ]);

        $school->admissionChannels()->create($validated + ['is_active' => true]);

        return back()->with('success', 'Admission channel added.');
    }

    public function createApiKey(Request $request)
    {
        $school = $this->school();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'channel_id' => [
                'nullable',
                Rule::exists('admission_channels', 'id')->where('school_id', $school->id),
            ],
            'allowed_domain' => ['nullable', 'string', 'max:191'],
        ]);
        $channel = filled($validated['channel_id'] ?? null)
            ? AdmissionChannel::where('school_id', $school->id)->findOrFail($validated['channel_id'])
            : null;
        $created = app(AdmissionWebsiteIntegrationService::class)->createApiKey(
            $school,
            $validated['name'],
            $channel,
            $validated['allowed_domain'] ?? null
        );

        return back()
            ->with('success', 'API key created. Store it now; only its hash is retained.')
            ->with('admission_api_plain_key', $created['plain_key']);
    }

    public function revokeApiKey(AdmissionApiKey $apiKey)
    {
        $school = $this->school();
        abort_unless((int) $apiKey->school_id === (int) $school->id, 404);
        $apiKey->update(['is_active' => false]);

        return back()->with('success', 'API key revoked.');
    }

    private function school(): School
    {
        $school = app(CurrentSchoolService::class)->get(request()->user());
        abort_unless($school, 403, 'A school workspace is required.');

        return $school;
    }

    private function authorizeApplication(AdmissionApplication $application, School $school): void
    {
        abort_unless((int) $application->school_id === (int) $school->id, 404);
    }

    private function authorizeNested(AdmissionApplication $application, int $applicationId): void
    {
        $this->authorizeApplication($application, $this->school());
        abort_unless((int) $application->id === (int) $applicationId, 404);
    }

    private function notifyPayment(AdmissionApplication $application): void
    {
        $email = $application->guardians->first()?->email;
        if (! $email) {
            return;
        }

        try {
            Notification::route('mail', $email)->notify(new AdmissionPaymentNotification($application));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
