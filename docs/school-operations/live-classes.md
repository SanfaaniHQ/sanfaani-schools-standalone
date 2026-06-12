# School Live Classes Operations

The Live Classes area is available from the school workspace for School Admins and assigned Teachers.

## Scheduling Workflow

1. Open **Live Classes**.
2. Choose **Schedule Live Class**.
3. Select an existing class.
4. Optionally select a subject, session, term, LMS classroom, and LMS material.
5. Choose the active manual provider and paste a manual meeting link.
6. Add an optional meeting password, recording link, timezone, and reminder-minute value.
7. Save the scheduled session.

Live classes are online sessions. They require internet and a working external meeting provider link.

## Provider Abstraction

The active provider is **Manual link**. The provider registry stores safe capability metadata and labels so future automation can be added without changing the live-class workflow.

Google Meet, Zoom, and Microsoft Teams may appear only as disabled future provider metadata. Their API automation is not active, and no OAuth credentials, provider secrets, tokens, webhooks, or generated meeting rooms are stored or used.

## Teacher Rules

Teachers can manage live classes only when the teacher assignment service confirms they can teach the selected class and subject for the relevant session and term. Teachers cannot schedule unassigned class or subject sessions and cannot use cross-school records.

If a teacher schedules a live class, the live class is assigned to that teacher automatically.

## LMS Links

A live class can link to an existing LMS classroom or LMS material in the same school. The selected LMS scope must match the class, subject, session, and term of the live class.

The live-class foundation does not duplicate LMS classrooms or materials. It only stores a safe school-scoped link back to LMS.

## Status Workflow

Supported statuses are:

- scheduled;
- live;
- completed;
- cancelled.

Scheduled sessions can be marked live or cancelled. Live sessions can be marked completed or cancelled. Cancelled or completed sessions cannot be started.

## Recording Link

The recording field stores a URL supplied by the external provider. Sanfaani Schools does not upload, host, transcode, or process video recordings in this stage.

## Security

- Meeting and recording links must be valid `http` or `https` URLs.
- Provider API keys and OAuth secrets are not stored.
- Meeting passwords are visible only to authorized school users on the live-class detail page.
- Meeting passwords are not written to audit metadata.
- There are no public live-class list routes.
- Cross-school class, subject, LMS classroom, LMS material, and live-class access is blocked.

## Audit Trail

The live-class foundation writes audit logs for create, update, start, complete, cancel, and recording-link changes. Audit metadata contains safe IDs and workflow fields only.

## Deferred

Provider API automation, Google Meet/Zoom/Teams APIs, generated meeting rooms, OAuth, provider credentials, webhooks, offline live class, live-class attendance tracking, live chat, advanced analytics, video hosting, transcoding, payment-gated access, student portal live classes, and parent portal live classes are not implemented in Stage 17.
