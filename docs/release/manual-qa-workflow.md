# Manual QA Workflow

1. Review the release branch diff.
2. Walk through smoke tests for SaaS, single-school, managed, demo, and marketplace modes.
3. Confirm shared-hosting limitations are still documented.
4. Confirm update preflight does not apply updates automatically.
5. Confirm backup workflows create metadata and guidance only where automation is not complete.
6. Confirm branding uploads reject unsafe files.
7. Confirm email templates include unsubscribe/footer safety where required.
8. Confirm public pages and result views do not leak private paths.
9. Record browser, environment, user role, deployment mode, and license mode for each manual run.
10. Add known issues to the risk register before go/no-go approval.
