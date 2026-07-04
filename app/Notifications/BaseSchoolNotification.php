<?php

namespace App\Notifications;

use App\Contracts\SchoolAwareMailNotification;
use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use stdClass;

abstract class BaseSchoolNotification extends Notification implements SchoolAwareMailNotification, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    protected ?int $mailSchoolId = null;

    public function __construct()
    {
        $this->onQueue('mail');
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    public function schoolIdForMail(object $notifiable): ?int
    {
        $schoolId = $this->mailSchoolId ?: data_get($notifiable, 'school_id');

        return filled($schoolId) ? (int) $schoolId : null;
    }

    protected function getSchoolBranding(object $notifiable): stdClass
    {
        $schoolId = data_get($notifiable, 'school_id');
        $school = $schoolId ? School::find($schoolId) : null;

        return (object) [
            'school_id' => $school?->id,
            'name' => $school?->name,
            'logo_path' => $school?->logo_path ?: $school?->logo,
            'logo_url' => $school?->logo_path ? asset('storage/'.ltrim($school->logo_path, '/')) : $school?->logoUrl(),
            'primary_color' => $school?->primary_color ?: '#4f46e5',
            'sender_email' => $school?->sender_email ?: $school?->email,
            'sender_name' => $school?->sender_name ?: $school?->name,
        ];
    }
}
