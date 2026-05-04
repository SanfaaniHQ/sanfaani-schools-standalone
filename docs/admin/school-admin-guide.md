# School Admin Guide

## Initial Setup

1. Set up classes.
2. Set up subjects.
3. Set up academic sessions.
4. Set up terms.
5. Set up grading scales.
6. Review admission number settings.

## Students

Add students manually or upload students in bulk. If an admission number is blank and auto-generation is selected, the system generates a school-specific admission number. Manual admission numbers are allowed when unique inside the school.

## Student Promotion

Use Student Promotions when moving learners into a new academic session or class. Promotion moves students forward without deleting previous results or rewriting old academic records.

To promote an entire class, choose the from session/class, choose the to session/class, select the entire-class option, preview the list, then confirm. To promote selected students, choose the same source and target context, select only the students who should move, review each row action, then confirm.

Students who repeat should use the repeat action and the appropriate target class for the new session. Final-year students can be marked graduated. Students who leave the school can be marked transferred or withdrawn. Students left unchecked or marked skip remain in their previous/current placement.

## Staff

Create staff users for result officers and future teachers. Staff users should use email or `staff_code`, not admission numbers.

## Results

Use manual result entry for smaller batches or corrections. Use result upload for class-based work. Validate uploaded rows before publishing.

## Publishing

Publish only after review. Unpublish results before major corrections. Published results control what parents can check publicly.

## Report Card Settings

Open Report Card Settings to control how result slips are displayed. These settings change the report-card appearance only; they do not change student scores. Configure colors, header style, visible school/student fields, teacher and head teacher titles/names, optional signatures, and automated comment switches.

Use the preview page before relying on a new layout in production. Advanced PDF, QR, and full report designer features remain future upgrades unless enabled later.

## Result System

Use the Result System page as the main result workspace. It links grading scales, manual result entry, CSV upload, result publishing, report-card settings, result access policy, public result checker, and scratch cards.

## Result Access Policy and Subscription

School Admin users can view the active result access policy and current subscription details. Super Admin controls policy and plan changes. Use the displayed request/change contact path when a school needs an upgrade.

## Uploaded Images

School logos and report-card signatures are stored on the public disk. If an upload succeeds but does not display, ask Super Admin to run System Maintenance > Storage Link and Clear All Cache, then confirm `APP_URL` and file permissions on hosting.

## Scratch Cards

Request scratch cards from the school portal. Cards become available after Super Admin approval and generation.

The public result checker now verifies admission number plus card serial/PIN first, then shows the verified school's sessions and terms. Card usage should only increase after a published result opens successfully.

## Student 360 Profile

Use the Student 360 profile to review a student's records, current class, class history, promotion source, and result history.
