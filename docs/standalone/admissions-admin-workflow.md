# Admissions Admin Workflow

School administrators work at `/admin/admissions`. The workspace is tenant scoped even though the URL uses the familiar admin prefix.

## Daily workflow

1. Open the dashboard and review new submissions.
2. Filter applications by status, requested class, source channel, or payment status.
3. Review applicant and guardian details.
4. Download private documents and mark them approved, rejected, or needing re-upload.
5. Add internal notes or explicitly public tracking updates.
6. Move the application through valid statuses.
7. Schedule an interview or entrance exam and record a score when available.
8. Add and confirm a manual payment record when the school uses an admission fee.
9. Convert an accepted or admitted applicant into a student.

Conversion creates the existing Student record, uses the school student-number settings, assigns the requested class when available, records class placement, links the application, and retains the original application. Repeating conversion returns the existing student instead of creating a duplicate.

The notification foundation covers submission acknowledgement, general status changes, decisions, missing documents, and payment updates. Actual email delivery depends on the configured mailer. Production SMS and WhatsApp are later integrations.
