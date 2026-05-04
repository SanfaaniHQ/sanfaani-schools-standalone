# Legacy Pilot Test Checklist

This file is kept for older internal references. Sanfaani Schools is now in production launch preparation for `https://schools.sanfaani.net`, not pilot mode.

Use [Final Deployment Test Checklist](final-deployment-test-checklist.md) and [Pre-Namecheap Launch Checklist](pre-namecheap-launch-checklist.md) for the current launch.

Minimum legacy smoke checks:

- Public pages load: `/`, `/features`, `/pricing`, `/contact`, `/demo`, `/result-checker`.
- Super Admin can log in through `/admin/login`.
- School Admin and Result Officer can log in through `/login`.
- Demo/contact requests are saved and visible in Super Admin > Lead Requests.
- A new school does not inherit another school's students, results, scratch cards, classes, subjects, sessions, or terms.
- Platform and school logos upload to the public disk and display after `php artisan storage:link`.
- Public result checker has no public school dropdown.
- Invalid card details fail safely.
- Unpublished results do not show.
- Published results show only after valid card access.
- Scratch card usage increments only after successful published result viewing.
- Manual result entry, CSV/Excel upload, publishing, unpublishing, scratch card request, and card generation still work.
- `APP_DEBUG=false`, no `.env`, no logs, no backups, and no real data dumps are included in deployment packages.
