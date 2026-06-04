# Non-Technical User Experience Roadmap

Sanfaani Schools supports three commercial paths. The product should explain each path in plain language so school owners understand what they need to do and what Sanfaani handles for them.

## Commercial Modes

| Mode | Best for | What the customer does | What Sanfaani or a technical operator does |
| --- | --- | --- | --- |
| SaaS | Normal school owners who want to start from a browser. | Sign up, request a demo, create or receive a school workspace, complete guided onboarding, and start adding school data. | Hosts the platform, maintains the application, and supports onboarding. |
| Managed | Premium clients who want Sanfaani to handle setup. | Provide school details, approve access, attend handover, and use the login details provided. | Installs, configures, verifies, and hands over the system according to the managed agreement. |
| Standalone | Technical buyers who want one single-school installation on their own hosting. | Upload the app, configure hosting, open `/install`, and follow each installer step. | Provides documentation and optional paid setup support. |

## SaaS Experience Principles

- SaaS customers do not install the app.
- SaaS customers do not need Git, Composer, npm, SSH, cPanel, or terminal access.
- The public website should invite them to request a demo or contact sales.
- After signup or handover, the app should guide them to create or enter their school workspace.
- Empty dashboards should explain what to do first: school profile, sessions, terms, classes, subjects, staff, students, and results.

## Managed Experience Principles

- Managed clients buy a service-backed setup, not a self-install journey.
- Sanfaani team handles hosting setup, database configuration, application configuration, migrations, initial school profile, owner login, and handover notes where the contract includes those items.
- Handover should include login URL, admin email, password delivery method, support contact, backup responsibility, update responsibility, and what the client should verify.

## Standalone Experience Principles

- Standalone buyers may use `/install` after the app is uploaded and configured for single-school mode.
- The installer should explain what each step checks and what still must be done outside the browser.
- The installer must not pretend that shared hosting, cPanel, database creation, document-root mapping, cron, or mail setup are fully automatic.
- The installer should warn that the domain document root must point to Laravel `public` or an equivalent safe public-folder mapping.
- The installer should clearly explain database credentials: database name, username, password, host, and port.

## Copy Improvements To Keep

- Say "school workspace" instead of technical tenant language for school owners.
- Say "open in your browser" for SaaS users.
- Say "ask your hosting provider" when a hosting decision is outside the app.
- Say "Sanfaani can install this for you" where a non-technical buyer may be stuck.
- Keep technical commands in docs for standalone buyers, but explain why and when each command is needed.
