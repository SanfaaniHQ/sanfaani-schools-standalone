# Roles and Permissions

## Super Admin

Super Admin users operate the platform across schools. They can create and archive schools, manage subscriptions and feature overrides, define access policies, confirm manual payments, approve and generate scratch cards, revoke cards, and inspect audit logs.

## School Admin

School Admin users operate one school. They can set up classes, subjects, sessions, terms, grading scales, students, admission number settings, staff users, results, publishing, and scratch card requests.

## Result Officer

Result Officers are staff users. They use email or `staff_code` with password. They should never use student admission numbers. Their access is focused on student viewing, result entry, upload, and review workflows where enabled.

## Teacher Future Role

Teachers should be created as staff users with email or `staff_code`. A future teacher workflow can limit access to assigned classes and subjects.

## Parent Future Role

Parents do not need portal accounts for the production launch. They currently use the public result checker with student admission number and scratch card access.

## Student Future Role

Students do not have dashboard login in the production launch. Student identity is the school-specific admission number. A later student portal can use a password or linked user account without changing staff identity rules.

## Identity Boundary

- Student: `admission_number`.
- Teacher/Result Officer: `staff_code` or email.
- School Admin: email or staff code.
- School: `school_code` and `slug`.
