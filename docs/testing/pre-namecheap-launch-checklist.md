# Pre-Namecheap Launch Checklist

Run this checklist before deploying or redeploying `https://schools.sanfaani.net`.

## Student Promotion

- Create a new academic session.
- Promote an entire class from the old session into the new session.
- Promote selected students only.
- Repeat one student into the new session.
- Graduate a final class student.
- Mark one test student as transferred or withdrawn.
- Confirm skipped students remain in their previous/current class.
- Confirm Student 360 shows class history.
- Confirm old results still show under the old class/session.
- Confirm manual result entry still uses the selected class/session/term.
- Confirm result upload still uses the selected class/session/term.
- Confirm public result checker still works after promotion.

## Report Card Settings

- Open Report Card Settings as School Admin.
- Change colors and layout options.
- Upload class teacher and head teacher signatures.
- Toggle school logo, address, phone, email, teacher remark, class teacher, and head teacher fields.
- Enable automated comments and confirm preview renders safely.
- Open a public result and confirm the report-card display settings apply without changing scores.

## Access Control

- Confirm School Admin can access promotion and report-card settings.
- Confirm Result Officer cannot access promotion routes.
- Confirm Result Officer cannot change report-card settings.
- Confirm School Admin cannot promote students from another school.
- Confirm `/admin/login` loads and accepts Super Admin only.
- Confirm `/login` still works for School Admin and Result Officer.
- Confirm Super Admin support access starts, shows a banner, and exits safely.
- Create a new school and confirm students, results, scratch cards, and users are zero unless explicitly created.

## Public Result Checker Privacy

- Confirm `/result-checker` does not show a school dropdown.
- Confirm Step 1 asks only for admission number, scratch card serial number, and PIN.
- Confirm a valid scratch card identifies the school privately.
- Confirm Step 2 shows the identified school name and read-only admission number.
- Confirm session and term dropdowns are scoped to the identified school.
- Confirm session/term/result type restrictions on a scratch card are preselected and locked.
- Confirm an invalid card does not reveal school or student details.
- Confirm unpublished results never show.
- Confirm scratch card usage increments only after a published result opens.
- Confirm a promoted student can still view old published results for the old session/class.

## Admin Modules

- Create or edit a result access policy as Super Admin.
- View the result access policy as School Admin.
- Create a subscription plan and assign it to a school.
- View the school subscription page as School Admin.
- Open Admin Result System and School Result System.
- Submit demo and contact requests, then verify they appear in Lead Requests.
- Open System Updates and upload only a safe test ZIP in a non-production environment.
- Open System Maintenance and verify fixed cache/storage actions are visible.

## Production Commands

- `php artisan migrate`
- `php artisan optimize:clear`
- `npm.cmd run build`
- `php artisan route:list`
