# Include And Exclude List

This manifest defines packaging intent. It is not a generated archive.

## Include

- `app`
- `bootstrap`
- `config`
- `database`
- `docs`
- `public` assets, excluding unsafe generated archives
- `resources`
- `routes`
- `tests` when the buyer/developer package allows verification tests
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `vite.config.js`
- `.env.example`
- `.env.marketplace.example`
- `README.md`

## Exclude

- `.env`
- `.env.*.local`
- `vendor`
- `node_modules`
- `storage/logs`
- `storage/framework/cache`
- `storage/framework/sessions`
- `storage/framework/views`
- `storage/app/backups`
- `storage/app/private`
- `storage/app/database`
- `storage/app/updates`
- `public/build.zip`
- test databases such as `database/*.sqlite`
- local IDE files such as `.idea` and `.vscode`
- OS files such as `.DS_Store` and `Thumbs.db`
- secrets such as `*.key`, `*.pem`, and `*.p12`
- temporary files, SQL dumps, debug dumps, coverage reports, and generated archives

## Safety Notes

`public/build.zip` must not be modified or included. If built frontend assets are shipped later, include the built asset directory through a reviewed release process, not through a stale archive.
