# Known Risk Register

Use this file to record release risks before approval.

| Risk | Area | Severity | Mitigation | Owner | Status |
| --- | --- | --- | --- | --- | --- |
| Protected dirty files accidentally included | Packaging | High | Keep them listed in `config/release.php` and review Git status | Release owner | Open |
| Shared-hosting shell limitations | Deployment | Medium | Use manual guidance and read-only diagnostics | Deployment owner | Open |
| Update package application not implemented | Updates | Medium | Label application as planned and run preflight only | Product owner | Open |
| Automated restore not implemented | Backups | Medium | Provide manual restore guidance only | Support owner | Open |
