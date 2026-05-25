# Asset Optimization Guide

This guide keeps Sanfaani Schools deployable on shared hosting while preserving marketplace packaging safety.

## Build Assets

- Run `npm install` and `npm run build` in a safe development or CI environment.
- Upload reviewed `public/build` assets with the application package.
- Do not rely on `public/build.zip` as a runtime artifact.
- Do not modify or ship generated archives without a reviewed packaging step.

## Uploads

- Keep image uploads compressed.
- Limit large documents and media through server and Laravel validation.
- Watch `upload_max_filesize` and `post_max_size` on cPanel/Namecheap.
- Store private files outside public folders.

## Exclusions

Do not package or back up by default:

- `.env`
- `vendor`
- `node_modules`
- `storage/logs`
- `storage/framework/cache`
- `storage/framework/sessions`
- `storage/framework/views`
- `storage/app/backups`
- `storage/app/private`
- `public/build.zip`
- local IDE files, OS files, debug dumps, and coverage reports

## Images

- Use optimized PNG/JPEG/WebP where appropriate.
- Avoid uploading original camera files directly to shared hosting.
- Keep school logos and public page images at practical dimensions before upload.

## Backup Size

Uploaded assets can dominate backup size. Review large public uploads before update windows and managed client handovers.
