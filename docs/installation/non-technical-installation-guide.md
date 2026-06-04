# Non-Technical Installation Guide

This guide helps buyers choose the right Sanfaani Schools setup path before they touch hosting, files, or `/install`.

## Choose The Right Mode

| Mode | Choose this if | Installation required from the customer |
| --- | --- | --- |
| SaaS | You are a school owner and want to start using Sanfaani Schools from your browser. | No. SaaS customers do not install anything. |
| Managed | You want Sanfaani to set up the system for you and hand over login details. | No self-install. Sanfaani team handles setup under the managed agreement. |
| Standalone | You bought a single-school license and have a technical person or hosting provider ready to configure the app. | Yes. A technical operator uploads the app, prepares hosting, and uses `/install`. |

## If You Are A SaaS Customer

Use the hosted Sanfaani website. Request a demo, talk to sales, create or receive your school workspace, complete onboarding, and start adding your school records. You do not need Git, Composer, npm, SSH, cPanel, or terminal access.

## If You Want Sanfaani To Handle Setup

Choose managed setup. The Sanfaani team handles hosting review, app configuration, database setup, migrations, school profile setup, owner login creation, and handover notes according to the agreed service scope.

## If You Are Installing Standalone

Standalone setup is for a technical buyer or hosting provider. The safe order is:

1. Upload the application outside public web access where possible.
2. Point the domain document root to Laravel `public`, or use the documented public-folder mapping.
3. Create a MySQL or MariaDB database and database user.
4. Put the database name, username, password, host, and port in `.env`.
5. Confirm PHP version and required extensions.
6. Confirm `storage` and `bootstrap/cache` are writable.
7. Open `/install`.
8. Follow requirements, permissions, database, environment, app key, migrations, owner, school, SMTP, review, and completion steps.

## Ask Your Hosting Provider

- Can the domain document root point to the Laravel `public` folder?
- Which PHP version is enabled for the domain?
- Are required PHP extensions available: ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, and xml?
- What are the database credentials: database name, username, password, host, and port?
- Can file permissions be set for `storage` and `bootstrap/cache`?
- Is there terminal or task-runner access for safe Laravel commands?
- If terminal access is unavailable, how should migrations and storage links be handled?
- Which SMTP host, port, encryption, username, and from address should be used?

## Important Safety Notes

- Do not put `.env`, logs, backups, SQL dumps, `vendor`, `node_modules`, or private files inside public web access.
- The installer does not replace hosting setup.
- The installer does not automatically create databases in cPanel.
- The installer does not run destructive commands.
- The installer writes an installation lock after completion so `/install` cannot be reused.
