# Pilot Test Checklist

## Local Test

- `php artisan migrate` passes.
- `php artisan optimize:clear` passes.
- `npm.cmd run build` passes.
- `php artisan route:list` shows public, admin, school, and auth routes.
- `/`, `/features`, `/pricing`, `/contact`, `/demo`, `/login`, and `/result-checker` open.

## School Onboarding Test

- Create a school.
- Confirm school code is present.
- Create school admin user.
- Log in as school admin.
- Set up classes, subjects, sessions, terms, and grading scales.

## Student Test

- Create a student with auto admission number.
- Create a student with manual admission number.
- Confirm admission number uniqueness inside the school.
- Upload students with blank admission numbers if the upload supports the column.

## Result Test

- Enter manual result.
- Upload class result file.
- Review validation errors.
- Publish result.
- Unpublish result.
- Confirm unpublished result does not show publicly.

## Scratch Card Test

- School requests scratch cards.
- Super Admin confirms manual payment.
- Super Admin generates cards.
- Download cards.
- Check result with valid card.
- Try invalid card.
- Try revoked card.
- Try expired card if available.

## Public Result Checker Test

- Check with valid details.
- Check with wrong school.
- Check with wrong admission number.
- Check with wrong session/term.
- Check with wrong serial/PIN.
- Print result page.
- Verify result link.

## Payment Placeholder Test

- Manual payment remains active.
- Paystack shows Coming Soon / Not enabled unless enabled in `.env`.
- Flutterwave shows Coming Soon / Not enabled unless enabled in `.env`.
- No payment secret appears in page source.

## Mobile and Language Test

- Test landing page on mobile width.
- Test login on mobile width.
- Test result checker in English.
- Test result checker in French.
- Test result checker in Arabic and confirm RTL layout.
