# Production Test Checklist

## Local Test

- `php artisan migrate` passes.
- `php artisan optimize:clear` passes.
- `npm.cmd run build` passes.
- `php artisan route:list` shows public, admin, school, legal, and auth routes.
- `/`, `/features`, `/pricing`, `/contact`, `/demo`, `/privacy-policy`, `/terms`, `/login`, and `/result-checker` open.

## Platform Branding Test

- Super Admin opens Platform Settings.
- Platform logo uploads and renders.
- Favicon uploads and renders.
- Login background uploads and renders.
- Public pages fall back cleanly when no image is uploaded.

## School Onboarding Test

- Create a school.
- Confirm school code is present.
- Upload school logo from Super Admin edit.
- Create school admin user.
- Log in as school admin.
- Update school profile and logo.
- Set up classes, subjects, sessions, terms, and grading scales.

## Student Test

- Create a student with auto admission number.
- Create a student with manual admission number.
- Confirm admission number uniqueness inside the school.
- Upload students with blank admission numbers if the upload supports the column.
- Guardian email notification is logged or delivered when configured.

## Result Test

- Enter manual result.
- Upload class result file.
- Review validation errors.
- Publish result.
- Confirm guardian result-published notification is logged or delivered when configured.
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
- Confirm used-card limits work.

## Public Result Checker Test

- Check with valid details.
- Check with wrong school.
- Check with wrong admission number.
- Check with wrong session/term.
- Check with wrong serial/PIN.
- Confirm unpublished result status is not disclosed without valid access.
- Print result page.
- Verify result link.

## Payment Placeholder Test

- Manual payment remains active.
- Paystack shows Coming Soon or Not enabled unless enabled in `.env`.
- Flutterwave shows Coming Soon or Not enabled unless enabled in `.env`.
- No payment secret appears in page source.

## Mobile and Language Test

- Test landing page on mobile width.
- Test login on mobile width.
- Test result checker in English.
- Test result checker in French.
- Test result checker in Arabic and confirm RTL layout.
