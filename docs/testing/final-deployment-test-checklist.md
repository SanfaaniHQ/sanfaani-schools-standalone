# Final Deployment Test Checklist

Use this checklist before deploying Sanfaani Schools to `https://schools.sanfaani.net`.

Official support contact: `sanfaanisaas@gmail.com`, `+2349010172138`.

## Public

1. Open `/`.
2. Open `/features`.
3. Open `/pricing`.
4. Open `/contact`.
5. Open `/demo`.
6. Open `/privacy-policy`.
7. Open `/terms`.
8. Open `/login`.
9. Open `/admin/login`.
10. Open `/result-checker`.
11. Confirm mobile view works on public pages.

## Contact and Demo

12. Submit a contact request.
13. Submit a demo request.
14. View both requests in Super Admin > Lead Requests.

## Auth

15. Log in as Super Admin through `/admin/login`.
16. Log in as School Admin through `/login`.
17. Log in as Result Officer through `/login`.
18. Confirm wrong-role users are blocked from protected areas.

## School Setup

19. Create a new school.
20. Confirm it has no copied Darusohabat or other school data.
    The new school must start without another school's students, results, scratch cards, classes, subjects, sessions, terms, users, or uploaded files.
21. Upload a school logo.
22. Edit the school profile.

## Academic

23. Create a class.
24. Create a subject.
25. Create an academic session.
26. Create a term.
27. Create a student.
28. Open the Student 360 profile.
29. Run student promotion if enabled.

## Result

30. Enter manual results.
31. Upload CSV/Excel results.
32. Confirm grading scale applies.
33. Publish a result.
34. Unpublish a result.
35. Open the School Result System page.
36. Open the School Result Access Policy page.

## Scratch Card

37. Request scratch cards as School Admin.
38. Confirm payment as Super Admin.
39. Generate cards as Super Admin.
40. Download cards as School Admin.
41. Revoke a card or batch and confirm access fails safely.

## Public Result

42. Confirm no school dropdown or public school list appears.
43. Confirm invalid card details fail with a safe message.
    Expected copy: `Invalid result access details.`
44. Confirm unpublished results do not show.
    Expected copy: `This result is not currently available. Please contact the school.`
45. Confirm published results show.
46. Confirm usage count increments only after successful published result access.
47. Confirm print result works.

## Settings

48. Upload platform logo.
49. Upload school logo.
50. Use System Maintenance to Clear All Cache and Optimize Application.
51. Confirm payment gateway config remains env-only.
52. Confirm SMTP config remains env-only.

## Security

53. Confirm School Admin cannot access another school's records.
54. Confirm Result Officer cannot publish/unpublish unless intentionally allowed.
55. Confirm Admin routes are blocked for non-admin users.
56. Confirm production docs require `APP_DEBUG=false`.
57. Confirm `.env` is not tracked or publicly accessible.

## Deployment Commands

Run these on deployment or immediately after deployment:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Back up files, database, `.env`, and `storage/app/public` before migrations or updates.
