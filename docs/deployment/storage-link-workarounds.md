# Storage Link Workarounds

Uploaded public assets normally use:

```bash
php artisan storage:link
```

This creates `public/storage` pointing to `storage/app/public`.

## When Symlinks Work

- Run the command once after deployment.
- Confirm `FILESYSTEM_DISK=public`.
- Confirm `APP_URL` is correct.
- Confirm uploaded logos/images render.

## When Symlinks Are Blocked

On shared hosting, symlinks may be disabled. Options:

- Ask hosting support to enable symlinks for the account.
- Use cPanel File Manager to create an equivalent link if supported.
- Configure uploads through a host-approved public upload folder.
- Use cloud/object storage in a future supported configuration.

Do not expose private storage folders to solve a missing public link.

## Troubleshooting

- Missing images: check `APP_URL`, `FILESYSTEM_DISK`, storage permissions, and cached config.
- 403 on images: check folder permissions and document root.
- Broken link after deployment: recreate the link or apply the host-approved workaround.
