# Final Preflight Checklist

- `php artisan release:check-readiness`
- All focused test filters
- `php artisan test`
- `php artisan route:list`
- `php artisan deployment:check-readiness`
- `php artisan performance:audit`
- `php artisan security:audit`
- `php artisan marketplace:validate-package`
- `git diff --check`
- Changelog updated
- Release notes prepared
- Known risk register reviewed
- Backup-before-release checklist complete
- Rollback validation complete
- Marketplace/white-label/managed checklists complete where applicable
- Go/no-go approval recorded
