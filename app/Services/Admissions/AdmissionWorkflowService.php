<?php

namespace App\Services\Admissions;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionStatusLog;
use App\Models\User;
use App\Notifications\Admissions\AdmissionDecisionNotification;
use App\Notifications\Admissions\AdmissionStatusChangedNotification;
use App\Notifications\Admissions\MissingDocumentNotification;
use App\Services\Communications\SchoolNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdmissionWorkflowService
{
    private const TRANSITIONS = [
        'submitted' => ['under_review', 'missing_documents', 'entrance_exam_scheduled', 'interview_scheduled', 'accepted', 'rejected', 'waitlisted'],
        'under_review' => ['missing_documents', 'entrance_exam_scheduled', 'interview_scheduled', 'accepted', 'rejected', 'waitlisted'],
        'missing_documents' => ['under_review', 'rejected'],
        'entrance_exam_scheduled' => ['under_review', 'interview_scheduled', 'accepted', 'rejected', 'waitlisted'],
        'interview_scheduled' => ['under_review', 'accepted', 'rejected', 'waitlisted'],
        'waitlisted' => ['under_review', 'accepted', 'rejected'],
        'accepted' => ['payment_pending', 'admitted', 'rejected', 'converted_to_student'],
        'payment_pending' => ['accepted', 'admitted', 'rejected'],
        'admitted' => ['converted_to_student'],
        'rejected' => [],
        'converted_to_student' => [],
    ];

    public function changeStatus(
        AdmissionApplication $application,
        string $toStatus,
        ?int $changedBy = null,
        ?string $note = null,
        bool $notify = true
    ): AdmissionApplication {
        if (! in_array($toStatus, AdmissionApplication::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Unknown admission status.']);
        }

        $transitionedFrom = null;

        $updated = DB::transaction(function () use ($application, $toStatus, $changedBy, $note, &$transitionedFrom) {
            $locked = AdmissionApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $locked->status;

            if ($fromStatus === $toStatus) {
                return $locked;
            }

            if (! in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => "Status cannot move from {$fromStatus} to {$toStatus}.",
                ]);
            }

            $updates = ['status' => $toStatus];
            if ($toStatus === AdmissionApplication::STATUS_UNDER_REVIEW && ! $locked->reviewed_at) {
                $updates['reviewed_at'] = now();
            }
            if (in_array($toStatus, [
                AdmissionApplication::STATUS_ACCEPTED,
                AdmissionApplication::STATUS_REJECTED,
                AdmissionApplication::STATUS_WAITLISTED,
                AdmissionApplication::STATUS_ADMITTED,
            ], true)) {
                $updates['decided_at'] = now();
            }

            $locked->update($updates);
            $transitionedFrom = $fromStatus;

            AdmissionStatusLog::create([
                'admission_application_id' => $locked->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => $changedBy,
                'note' => $note,
            ]);

            return $locked->fresh(['school', 'guardians']);
        });

        if ($notify) {
            $this->notifyApplicant($updated, $note);
        }

        if ($transitionedFrom !== null) {
            app(SchoolNotificationService::class)->logAdmissionStatusChanged(
                $updated,
                $changedBy ? User::query()->find($changedBy) : null
            );
        }

        return $updated;
    }

    public function availableTransitions(AdmissionApplication $application): array
    {
        return self::TRANSITIONS[$application->status] ?? [];
    }

    private function notifyApplicant(AdmissionApplication $application, ?string $publicNote): void
    {
        $email = $application->guardians->first()?->email;
        if (! $email) {
            return;
        }

        try {
            $notification = match ($application->status) {
                AdmissionApplication::STATUS_MISSING_DOCUMENTS => new MissingDocumentNotification($application, $publicNote),
                AdmissionApplication::STATUS_ACCEPTED,
                AdmissionApplication::STATUS_REJECTED => new AdmissionDecisionNotification($application, $publicNote),
                default => new AdmissionStatusChangedNotification($application, $publicNote),
            };

            Notification::route('mail', $email)->notify($notification);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
