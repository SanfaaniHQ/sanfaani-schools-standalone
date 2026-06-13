# Next.js School Website Add-On

The Next.js school website is a future optional add-on for Sanfaani Schools Standalone. It has not been built in this Laravel repository.

## Product Decision

Stage 25 chooses Option 2: the public school website should be created later in a separate repository, such as `sanfaani-school-website-template`.

This Laravel repository remains the private school management portal and source of truth.

## Website Purpose

The future website should be a public-facing front door for one school:

- Home.
- About.
- Admissions.
- Academics.
- Contact.
- Privacy and terms.
- Optional news or announcements placeholder.

It should link visitors into Laravel for portal login, admissions application, and application tracking.

## V1 Behavior

V1 should be link-based:

- Portal Login -> Laravel `/login` or configured portal login URL.
- Staff Login -> Laravel `/login`.
- Student Login -> Laravel `/login` only if the Laravel portal supports that role for the deployment.
- Parent Login -> Laravel `/login` only if the Laravel portal supports that role for the deployment.
- Apply Now -> Laravel `/admissions/apply`.
- Check Application -> Laravel `/admissions/track` when tracking is enabled.
- Contact School -> public email, phone, address, or contact method approved by the school.

## What The Website Must Not Do

- Build private dashboards.
- Authenticate users itself.
- Store student, staff, parent, or applicant accounts as the system of record.
- Duplicate the admissions engine.
- Convert applicants to students.
- Handle finance, fees, payment, invoice, or accounting workflows.
- Expose reports, attendance, LMS private materials, CBT private exam data, notification logs, audit logs, backups, updates, license, installer, or system health.
- Expose private file paths, `.env` values, API keys, tokens, credentials, or server internals.

## Commercial Positioning

The website add-on can be sold separately as a public branding and admissions front door. Hosting, domain, SSL, content writing, photography, design customization, SEO work, and ongoing website maintenance are separate commercial scopes.

Laravel remains the core school portal product.

## Related Docs

- [Separate Repo Strategy](separate-repo-strategy.md)
- [Website-Laravel Link Contract](website-laravel-link-contract.md)
- [Admissions Linking Guide](admissions-linking-guide.md)
- [Security And Privacy Boundaries](security-and-privacy-boundaries.md)
