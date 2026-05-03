# Step 18 Public Result Checker Manual Tests

1. Open `/result-checker`.
2. Select an active school, academic session, and term.
3. Enter the student's admission number.
4. Enter a valid generated scratch card serial number and PIN.
5. Confirm an unpublished, reviewed, or draft result does not show.
6. Publish the result and try again.
7. Confirm the result page shows student, class, session, term, subject scores, grades, remarks, and teacher remarks.
8. Try a wrong PIN and confirm it fails safely.
9. Try a revoked card and confirm it fails safely.
10. Try an expired card and confirm it fails safely.
11. Try a card at its usage limit and confirm it fails safely.
12. Try a card already used by another student and confirm it fails safely.
13. Confirm a successful check creates a `scratch_card_usages` record.
14. Confirm French and Arabic language selections render the public checker, with Arabic using RTL direction.
15. Click `Download PDF` and confirm the printable view opens without exposing IDs.
16. Open the verification URL and confirm it validates authenticity without showing full scores.
