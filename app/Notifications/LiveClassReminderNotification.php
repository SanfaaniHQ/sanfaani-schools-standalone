<?php

namespace App\Notifications;

use App\Models\LiveClass;
use App\Models\LiveClassParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LiveClassReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private LiveClass $liveClass,
        private LiveClassParticipant $participant
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $startsAt = $this->liveClass->starts_at?->timezone($this->liveClass->timezone ?: config('app.timezone'));

        return [
            'title' => 'Live class reminder',
            'body' => trim($this->liveClass->title.' starts '.($startsAt?->format('d M Y H:i') ?? 'soon').'.'),
            'action_url' => route('portal.live-classes.show', $this->liveClass),
            'school_id' => $this->liveClass->school_id,
            'live_class_id' => $this->liveClass->id,
            'live_class_participant_id' => $this->participant->id,
            'starts_at' => $this->liveClass->starts_at?->toIso8601String(),
            'reminder_due_at' => $this->participant->reminder_due_at?->toIso8601String(),
        ];
    }
}
