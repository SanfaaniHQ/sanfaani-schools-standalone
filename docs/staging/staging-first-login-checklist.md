# Staging First Login Checklist

Use this checklist after deployment and migrations to verify access control and first-use behavior.

## Super Admin

- [ ] Super Admin can reach `/admin/login`.
- [ ] Super Admin can sign in with staging-only credentials.
- [ ] Dashboard loads without server errors.
- [ ] School list and platform settings visibility match the selected mode.
- [ ] Security, performance, updates, backups, and branding diagnostics are visible where expected.

## School Admin

- [ ] School Admin can sign in.
- [ ] School dashboard loads.
- [ ] School profile, mail settings, branding, classes, subjects, sessions, terms, students, and staff screens load where expected.
- [ ] Result publishing and CBT screens load where expected.
- [ ] Unauthorized platform routes remain blocked.

## Other Roles

- [ ] Teacher can access assigned workflows only.
- [ ] Result officer can access result workflows only.
- [ ] Parent and student access is limited to implemented workflows.
- [ ] Multi-role users can choose the correct workspace.

## Safety

- [ ] No real credentials are recorded in this checklist.
- [ ] Password reset uses staging SMTP only.
- [ ] Audit logs record staging login events without exposing passwords or tokens.
- [ ] Staging users can be disabled after validation.
