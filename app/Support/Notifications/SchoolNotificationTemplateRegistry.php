<?php

namespace App\Support\Notifications;

use App\Models\SchoolNotificationTemplate;
use Illuminate\Support\Collection;

class SchoolNotificationTemplateRegistry
{
    public function all(): Collection
    {
        return collect([
            $this->item('admission_application_received', 'Admission Application Received', 'Acknowledges a submitted admission application.', ['school_name', 'student_name', 'parent_name', 'application_number', 'login_url'], 'Admission application received', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('admission_application_approved', 'Admission Application Approved', 'Notifies a parent or applicant that an application was approved.', ['school_name', 'student_name', 'parent_name', 'application_number', 'login_url'], 'Admission application approved', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('admission_application_rejected', 'Admission Application Rejected', 'Notifies a parent or applicant that an application was not approved.', ['school_name', 'student_name', 'parent_name', 'application_number'], 'Admission application update', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('admission_offer_sent', 'Admission Offer Sent', 'Shares admission offer details with the parent or applicant.', ['school_name', 'student_name', 'parent_name', 'application_number', 'login_url'], 'Admission offer from {{school_name}}', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('student_enrollment_created', 'Student Enrollment Created', 'Welcomes a newly enrolled student and guardian.', ['school_name', 'student_name', 'parent_name', 'admission_number', 'login_url'], 'Student enrollment created', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('parent_welcome', 'Parent Welcome', 'Introduces parents to the school portal.', ['school_name', 'parent_name', 'student_name', 'login_url'], 'Welcome to {{school_name}}', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('staff_welcome', 'Staff Welcome', 'Welcomes a staff member and shares sign-in guidance.', ['school_name', 'staff_name', 'role', 'login_url'], 'Welcome to {{school_name}}', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('password_reset', 'Password Reset', 'Supports password reset notifications.', ['school_name', 'name', 'login_url'], 'Password reset request', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('fee_invoice_created', 'Fee Invoice Created', 'Notifies guardians when a fee invoice is created.', ['school_name', 'student_name', 'parent_name', 'amount', 'due_date', 'login_url'], 'New fee invoice', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('payment_received', 'Payment Received', 'Confirms a recorded school fee payment.', ['school_name', 'student_name', 'parent_name', 'amount', 'receipt_number'], 'Payment received', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('result_published', 'Result Published', 'Informs guardians or students when results are published.', ['school_name', 'student_name', 'parent_name', 'admission_number', 'login_url'], 'Result published', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('attendance_absence_alert', 'Attendance Absence Alert', 'Alerts a guardian when a student is marked absent.', ['school_name', 'student_name', 'parent_name', 'date'], 'Attendance alert', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('homework_assigned', 'Homework Assigned', 'Notifies students or parents about assigned homework.', ['school_name', 'student_name', 'class_name', 'subject_name', 'due_date', 'login_url'], 'Homework assigned', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('announcement_sent', 'Announcement Sent', 'Sends a general school announcement.', ['school_name', 'recipient_name', 'announcement_title', 'login_url'], 'School announcement', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('exam_schedule_notice', 'Exam Schedule Notice', 'Shares exam timetable or schedule reminders.', ['school_name', 'student_name', 'class_name', 'exam_name', 'date'], 'Exam schedule notice', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('live_class_notice', 'Live Class Notice', 'Reminds students and staff about a live class.', ['school_name', 'student_name', 'teacher_name', 'class_name', 'subject_name', 'start_time', 'login_url'], 'Live class notice', SchoolNotificationTemplate::CHANNEL_EMAIL),
            $this->item('general_notification', 'General Notification', 'Reusable message for school-wide operational notifications.', ['school_name', 'recipient_name', 'login_url'], 'Notification from {{school_name}}', SchoolNotificationTemplate::CHANNEL_DATABASE),
        ]);
    }

    public function keys(): array
    {
        return $this->all()->pluck('key')->all();
    }

    public function find(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        return $this->all()->firstWhere('key', $key);
    }

    public function channels(): array
    {
        return [
            SchoolNotificationTemplate::CHANNEL_EMAIL => [
                'label' => 'Email',
                'description' => 'Sends to the recipient email inbox when mail delivery is configured.',
            ],
            SchoolNotificationTemplate::CHANNEL_SMS => [
                'label' => 'SMS',
                'description' => 'Sends short text messages when an SMS provider is configured.',
            ],
            SchoolNotificationTemplate::CHANNEL_WHATSAPP => [
                'label' => 'WhatsApp',
                'description' => 'Sends WhatsApp messages when a WhatsApp provider is configured.',
            ],
            SchoolNotificationTemplate::CHANNEL_DATABASE => [
                'label' => 'In-app notification',
                'description' => 'Shows the notification inside the portal.',
            ],
            SchoolNotificationTemplate::CHANNEL_LOG => [
                'label' => 'Log only',
                'description' => 'Records the message without sending it externally.',
            ],
        ];
    }

    private function item(
        string $key,
        string $label,
        string $description,
        array $placeholders,
        string $subject,
        string $channel
    ): array {
        return compact('key', 'label', 'description', 'placeholders', 'subject', 'channel');
    }
}
