# Staging Seed And Demo Data Checklist

Use staging-only data for validation. Do not copy real student, parent, staff, payment, license, or mailbox data into staging unless it has been approved and sanitized.

## Data Rules

- [ ] Use fake names, fake emails, fake phone numbers, and fake addresses.
- [ ] Use approved staging domains such as `example.test`.
- [ ] Do not use production payment credentials.
- [ ] Do not use real license keys in docs, screenshots, tickets, or seed files.
- [ ] Do not send seed-generated notifications to real users.
- [ ] Keep demo reset disabled unless a reviewed demo-only reset pattern exists.

## Minimum Seed Coverage

- [ ] One Super Admin.
- [ ] One staging school.
- [ ] One School Admin.
- [ ] One teacher.
- [ ] One result officer.
- [ ] One parent.
- [ ] One student.
- [ ] One class, subject, session, and term.
- [ ] One result workflow sample.
- [ ] One CBT sample.
- [ ] One support thread sample.
- [ ] One communication sample using staging-only recipients.

## Demo And Trial Coverage

- [ ] Demo request form creates a staging lead.
- [ ] Demo session credentials are visible only to authorized admins.
- [ ] Demo expiry behavior is recorded.
- [ ] Trial onboarding progress is visible where expected.
- [ ] Sales tasks and marketing activities use staging-only contacts.

## Boundaries

- Full billing automation remains planned.
- Trial-to-paid conversion remains planned.
- Demo conversion to paid billing remains planned.
