<?php

namespace App\Notifications;

use App\Models\TeacherReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TeacherReviewSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private TeacherReview $teacherReview
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New teacher review submitted',
            'body' => 'A new teacher review is awaiting moderation.',
            'action_url' => route('school.teacher-reviews.index'),
            'teacher_review_id' => $this->teacherReview->id,
        ];
    }
}
