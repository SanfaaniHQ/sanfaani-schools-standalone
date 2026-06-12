# Live Class Foundation

Stage 16 adds the first safe live-class foundation for Sanfaani Schools Standalone.

Live classes sit beside LMS as an online learning-delivery tool. LMS remains the learning-material hub, CBT remains the assessment engine, and live classes provide manually scheduled internet meeting sessions.

## Available In This Stage

- School Admins can schedule, edit, start, complete, cancel, and review school live classes.
- Teachers can schedule and manage live classes only for assigned class and subject scopes.
- Live classes can be assigned to existing classes, subjects, sessions, and terms.
- Live classes can optionally link to an LMS classroom or LMS material in the same school.
- Each live class stores a manual meeting URL, optional meeting password, optional recording URL, start/end time, timezone, and reminder-minute metadata.
- Status workflow is local: scheduled, live, completed, cancelled.
- Live class actions write school-scoped audit logs.
- Dashboards and navigation expose Live Classes only to allowed school roles.

## Manual Meeting Link Workflow

1. Create the room in the external provider manually.
2. Copy the provider meeting URL.
3. Schedule the live class inside Sanfaani Schools.
4. Select the class, optional subject, session, and term.
5. Optionally link the related LMS classroom or material.
6. Share the join/start view with authorized school staff.
7. Add a recording URL later if the provider supplies one.

The system validates meeting and recording URLs as `http` or `https` links. It does not generate rooms.

## Permissions

- School Admin: full oversight and management inside the current school.
- Teacher: assigned-scope view and management only.
- Accountant: no live class access by default.
- Result Officer: no live class access by default.
- Student: live class portal visibility is deferred until safe student identity and class membership are available.
- Parent: live class portal visibility is deferred.
- Super Admin: support/admin visibility only inside a school context.

## Audit Logging

The foundation audits:

- `live_class_created`;
- `live_class_updated`;
- `live_class_started`;
- `live_class_completed`;
- `live_class_cancelled`;
- `recording_link_added`;
- `recording_link_updated`.

Audit metadata stores safe IDs and workflow fields only: school, live class, class, subject, session, term, LMS classroom, teacher, status, start time, and actor. Meeting passwords are not stored in audit metadata.

## Deferred To Later Stages

- Provider abstraction is Stage 17.
- Google Meet, Zoom, and Microsoft Teams API integration is not implemented.
- OAuth, provider credentials, and generated meeting rooms are not implemented.
- Offline live class is not implemented.
- Video hosting and transcoding are not implemented.
- Live-class attendance tracking is not implemented.
- Live chat, discussion, analytics, payment-gated access, and parent/student live-class portals are not implemented.

Live classes require internet.
