# LMS Foundation

Stage 14 adds the first online LMS foundation for Sanfaani Schools Standalone. It is deliberately scoped to existing school academics and does not create duplicate student, class, subject, session, term, CBT, storage, audit, or dashboard systems.

## Scope

The LMS foundation provides:

- school-scoped LMS classrooms tied to an existing class, subject, optional academic session, and optional term;
- optional topics/modules inside a classroom;
- teacher and school-admin lesson, note, resource, and assignment-material posts;
- draft, published, and archived material states;
- private local file upload and authorized download;
- audit logs for classroom, material, publish/unpublish/archive, upload, and download actions;
- school admin and teacher dashboard/sidebar entry points.

## Permissions

School Admin users can manage all LMS classrooms and materials inside their current school workspace.

Teachers can view LMS and manage materials only for class/subject/session/term scopes allowed by the existing teacher assignment service. They cannot manage unassigned class/subject content or cross-school records.

Accountants and Result Officers do not receive LMS access by default.

Students and parents do not receive an LMS portal in this stage. Student LMS viewing is deferred because the inspected user/student identity mapping is not safe enough to prove that a user account belongs to exactly one student.

## File Security

LMS resources use the private `local` disk and are stored below:

```text
lms/schools/{school_id}/materials/{material_id}/...
```

Allowed extensions are `pdf`, `doc`, `docx`, `ppt`, `pptx`, `xls`, `xlsx`, `txt`, `jpg`, `jpeg`, `png`, and `webp`. The default maximum size is 10 MB.

The UI shows original file names, MIME type, and file size. It does not expose the disk or raw storage path. Downloads are streamed only after authorization.

## Deferred

CBT integration remains Stage 15. Live class foundation remains Stage 16. Provider abstraction remains Stage 17.

Offline LMS, assignment submissions/grading, forums/discussions, advanced analytics, video hosting/transcoding, parent LMS portal, and payment-gated content are not implemented.
