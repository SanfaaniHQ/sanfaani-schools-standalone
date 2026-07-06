<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionStatusLog;
use App\Models\School;
use App\Notifications\Admissions\ApplicationSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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
    ) {}

    public function validationRules(School $school): array
    {
        $maxKb = max(1, (int) config('admissions.max_upload_mb', 5)) * 1024;
        $mimes = implode(',', config('admissions.allowed_document_mimes', ['pdf', 'jpg', 'jpeg', 'png']));
        $documentTypes = config('admissions.allowed_document_types', ['supporting_document']);

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
            'document_types.*' => ['nullable', 'string', 'max:100', Rule::in($documentTypes)],
        ];
    }

    public function guardAgainstSpam(Request $request): void
    {
        $honeypotField = (string) config('admissions.honeypot_field', 'admission_website');
        if ($honeypotField !== '' && filled($request->input($honeypotField))) {
            throw ValidationException::withMessages([
                'admissions' => 'The application could not be accepted. Please review the form and try again.',
            ]);
        }

        $timestampField = (string) config('admissions.form_timestamp_field', 'admission_started_at');
        $timestamp = $timestampField !== '' ? $request->input($timestampField) : null;
        $requiresLocalCaptchaFallback = (bool) config('admissions.require_captcha', false);

        if (! $requiresLocalCaptchaFallback) {
            return;
        }

        if (! $this->submissionTimestampIsValid($timestamp)) {
            throw ValidationException::withMessages([
                'admissions' => 'The application could not be accepted. Please review the form and try again.',
            ]);
        }
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

    public function track(
        string $applicationNumber,
        ?string $trackingToken,
        ?string $guardianPhone,
        ?string $dateOfBirth = null
    ): ?AdmissionApplication {
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

        if ($tokenMatches) {
            return $application;
        }

        if (! (bool) config('admissions.guardian_tracking_fallback_enabled', false)) {
            return null;
        }

        if ((bool) config('admissions.guardian_tracking_requires_date_of_birth', true)) {
            $guardianMatches = $guardianMatches && $this->dateOfBirthMatches($application, $dateOfBirth);
        }

        return $guardianMatches ? $application : null;
    }

    private function storeDocument(AdmissionApplication $application, UploadedFile $file, string $type): void
    {
        $disk = $this->privateDocumentDisk();
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

    private function privateDocumentDisk(): string
    {
        $disk = (string) config('admissions.document_disk', 'local');
        $privateDisks = array_map('strval', (array) config('admissions.private_document_disks', ['local']));

        if (! in_array($disk, $privateDisks, true)) {
            throw ValidationException::withMessages([
                'documents' => 'Documents could not be stored securely. Contact the school office.',
            ]);
        }

        return $disk;
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

    private function submissionTimestampIsValid(mixed $timestamp): bool
    {
        if (! is_numeric($timestamp)) {
            return false;
        }

        $startedAt = Carbon::createFromTimestamp((int) $timestamp);
        $minimumSeconds = max(0, (int) config('admissions.minimum_submission_seconds', 3));

        return $startedAt->lessThanOrEqualTo(now())
            && $startedAt->diffInSeconds(now()) >= $minimumSeconds;
    }

    private function dateOfBirthMatches(AdmissionApplication $application, ?string $dateOfBirth): bool
    {
        if (! filled($dateOfBirth) || ! $application->date_of_birth) {
            return false;
        }

        try {
            return hash_equals(
                $application->date_of_birth->toDateString(),
                Carbon::parse($dateOfBirth)->toDateString()
            );
        } catch (Throwable) {
            return false;
        }
    }
}
