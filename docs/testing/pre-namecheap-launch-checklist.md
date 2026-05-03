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

## Production Commands

- `php artisan migrate`
- `php artisan optimize:clear`
- `npm.cmd run build`
- `php artisan route:list`
