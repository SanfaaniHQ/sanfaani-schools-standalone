# School LMS Operations

The LMS area is available from the school workspace as **Learning Materials** for School Admins and assigned Teachers.

## Classroom Workflow

1. Create a classroom for an existing class and subject.
2. Optionally bind it to an academic session and term.
3. Add topics/modules if the class needs grouped content.
4. Create materials as drafts.
5. Upload private resources to saved materials.
6. Link existing CBT activities where assessment is needed.
7. Publish when the content should become visible to authorized users.

Duplicate classrooms for the same school, class, subject, session, and term are blocked.

## Material Types

The supported material types are:

- lesson;
- note;
- resource;
- assignment.

The assignment type is an assignment post only. Student submissions and grading are deferred.

## Teacher Rules

Teachers can create and manage LMS material only when the existing teacher assignment service confirms that they can teach the selected class and subject for the relevant session and term. Unassigned teachers are denied.

Teachers can link or unlink CBT activities only when the CBT item belongs to the same school, class, and subject as the assigned LMS classroom/material scope.

## CBT Activity Links

LMS classrooms and materials can link to existing CBT exams, quizzes, or assessments. The LMS does not create or duplicate CBT items; it only stores a school-scoped link.

The existing CBT entry, candidate, attempt, scoring, and result-release rules remain unchanged. LMS views do not show raw CBT question content, answer payloads, scores, access codes, tokens, or private attempt data.

## Download Rules

Resources are stored on the private local disk and downloaded through authorized LMS routes. Raw storage paths are not shown in the UI.

## Audit Trail

The LMS writes audit logs for:

- `lms_classroom_created`;
- `lms_classroom_updated`;
- `lms_material_created`;
- `lms_material_updated`;
- `lms_material_published`;
- `lms_material_unpublished`;
- `lms_material_archived`;
- `lms_resource_uploaded`;
- `lms_resource_downloaded`;
- `lms_cbt_activity_linked`;
- `lms_cbt_activity_unlinked`;
- `lms_cbt_activity_link_failed`.

Audit metadata includes safe IDs, status, type, MIME, extension, and size. It does not include raw file paths or file contents.

## Boundaries

LMS is online-first in this stage. It includes safe links to existing CBT items but does not include offline LMS, live classes, provider abstraction, submissions/grading, discussion forums, advanced analytics, video hosting, parent LMS, or payment-gated content.
