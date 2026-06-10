<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionStatusLog;
use App\Models\School;
use App\Notifications\Admissions\ApplicationSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdmissionApplicationService
{
    public function __construct(
        private readonly AdmissionNumberGenerator $numberGenerator,
        private readonly AdmissionWebsiteIntegrationService $integration
    ) {
    }

    public function validationRules(School $school): array
    {
        $maxKb = max(1, (int) config('admissions.max_upload_mb', 5)) * 1024;
        $mimes = implode(',', config('admissions.allowed_document_mimes', ['pdf', 'jpg', 'jpeg', 'png']));

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'requested_class_id' => [
                'nullable',
                Rule::exists('school_classes', 'id')->where('school_id', $school->id),
            ],
            'previous_school' => ['nullable', 'string', 'max:191'],
            'guardian_name' => ['required', 'string', 'max:191'],
            'guardian_relationship' => ['required', 'string', 'max:80'],
            'guardian_phone' => ['required', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email', 'max:191'],
            'guardian_address' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
            'source_channel' => ['nullable', 'string', 'max:100'],
            'documents' => [
                Rule::excludeIf(! config('admissions.allow_document_uploads')),
                'nullable',
                'array',
                'max:5',
            ],
            'documents.*' => ['file', 'mimes:'.$mimes, 'max:'.$maxKb],
            'document_types' => ['nullable', 'array'],
            'document_types.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function submit(School $school, array $data, string $fallbackChannel = 'portal'): array
    {
        $cycle = $this->integration->currentCycle($school);

        if (! $cycle) {
            throw ValidationException::withMessages([
                'admissions' => 'Admissions are not currently accepting applications.',
            ]);
        }

        $trackingToken = Str::random(48);
        $documents = $data['documents'] ?? [];
        $documentTypes = $data['document_types'] ?? [];

        $application = DB::transaction(function () use (
            $school,
            $cycle,
            $data,
            $trackingToken,
            $documents,
            $documentTypes,
            $fallbackChannel
        ) {
            $application = AdmissionApplication::create([
                'school_id' => $school->id,
                'admission_cycle_id' => $cycle->id,
                'application_number' => $this->numberGenerator->generate($school),
                'tracking_token' => hash('sha256', $trackingToken),
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'other_names' => filled($data['other_names'] ?? null) ? trim($data['other_names']) : null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'requested_class_id' => $data['requested_class_id'] ?? null,
                'previous_school' => $data['previous_school'] ?? null,
                'status' => AdmissionApplication::STATUS_SUBMITTED,
                'source_channel' => $this->integration->sourceChannel(
                    $school,
                    $data['source_channel'] ?? null,
                    $fallbackChannel
                ),
                'payment_status' => config('admissions.payments_enabled')
                    ? AdmissionApplication::PAYMENT_PENDING
                    : AdmissionApplication::PAYMENT_NOT_REQUIRED,
                'submitted_at' => now(),
                'meta' => [
                    'privacy_consent_at' => now()->toIso8601String(),
                    'online_payment_required' => false,
                ],
            ]);

            $application->guardians()->create([
                'name' => trim($data['guardian_name']),
                'relationship' => trim($data['guardian_relationship']),
                'phone' => trim($data['guardian_phone']),
                'email' => filled($data['guardian_email'] ?? null) ? trim($data['guardian_email']) : null,
                'address' => $data['guardian_address'] ?? null,
            ]);

            AdmissionStatusLog::create([
                'admission_application_id' => $application->id,
                'from_status' => null,
                'to_status' => AdmissionApplication::STATUS_SUBMITTED,
                'note' => 'Application submitted through '.$application->source_channel.'.',
            ]);

            foreach ($documents as $index => $document) {
                if ($document instanceof UploadedFile) {
                    $this->storeDocument($application, $document, $documentTypes[$index] ?? 'supporting_document');
                }
            }

            return $application;
        });

        $application->load(['school', 'guardians', 'documents', 'requestedClass', 'cycle']);
        $this->sendAcknowledgement($application, $trackingToken);

        return ['application' => $application, 'tracking_token' => $trackingToken];
    }

    public function track(string $applicationNumber, ?string $trackingToken, ?string $guardianPhone): ?AdmissionApplication
    {
        $application = AdmissionApplication::query()
            ->with(['school', 'cycle', 'requestedClass', 'guardians', 'statusLogs' => fn ($query) => $query->latest()])
            ->where('application_number', trim($applicationNumber))
            ->first();

        if (! $application) {
            return null;
        }

        $tokenMatches = filled($trackingToken)
            && hash_equals($application->tracking_token, hash('sha256', trim((string) $trackingToken)));
        $guardianMatches = filled($guardianPhone)
            && $application->guardians->contains(
                fn ($guardian) => hash_equals($this->normalizePhone($guardian->phone), $this->normalizePhone($guardianPhone))
            );

        return $tokenMatches || $guardianMatches ? $application : null;
    }

    private function storeDocument(AdmissionApplication $application, UploadedFile $file, string $type): void
    {
        $disk = (string) config('admissions.document_disk', 'local');
        $path = $file->store(
            'admissions/'.$application->school_id.'/'.$application->application_number,
            $disk
        );

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            throw ValidationException::withMessages(['documents' => 'A document could not be stored securely.']);
        }

        $application->documents()->create([
            'document_type' => Str::snake($type ?: 'supporting_document'),
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'status' => 'pending',
        ]);
    }

    private function sendAcknowledgement(AdmissionApplication $application, string $trackingToken): void
    {
        $email = $application->guardians->first()?->email;
        if (! $email) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new ApplicationSubmittedNotification($application, $trackingToken));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?: '';
    }
}
