# VPS Hosting Deployment

VPS hosting is recommended for SaaS, managed clients, and schools that need reliable queues, scheduler control, monitoring, SSL, and repeatable operations.

## Assumptions

- Ubuntu/Debian-style server or equivalent.
- Nginx or Apache points to Laravel `public`.
- PHP 8.3+ with required extensions.
- MySQL or MariaDB.
- Composer and Node.js.
- Supervisor or systemd for queue workers.
- Modern MySQL/MariaDB on VPS usually avoids the 1000-byte shared-hosting key limit when configured properly, but migrations should still remain safe for shared-hosting compatibility.

## Folder Structure

- `/var/www/sanfaani-schools/current`: release directory.
- `/var/www/sanfaani-schools/shared/.env`: production env managed by the operator.
- `/var/www/sanfaani-schools/shared/storage`: persistent storage if using release directories.
- Web root: `/var/www/sanfaani-schools/current/public`.

## Nginx Example

```nginx
server {
    listen 80;
    server_name school.example.com;
    root /var/www/sanfaani-schools/current/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
}
```

Use equivalent Apache virtual host rules when running Apache.

## Deployment Steps

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Run these only after a verified backup and reviewed `.env`.

## Permissions

Set ownership to the deploy user and web server group. Make `storage` and `bootstrap/cache` writable by PHP-FPM. Do not make secrets world-readable.

Confirm `.env`, logs, backups, update packages, private storage, and SQL dumps are outside public access. The web root must point to `public`.

## Queue Workers

Use Supervisor or systemd for `php artisan queue:work`. Example Supervisor command:

```bash
php /var/www/sanfaani-schools/current/artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Restart workers after deployments.

Redis may be available on VPS and can be used where configured, but database queues remain a safe baseline. The ZIP extension is optional for baseline app serving but affects package, backup, and archive workflows.

## Scheduler

Add cron:

```bash
* * * * * cd /var/www/sanfaani-schools/current && php artisan schedule:run >> /dev/null 2>&1
```

## SSL

Use Let's Encrypt, host-managed SSL, or cloud load balancer TLS. Set `APP_URL=https://...` after SSL is active.

## Backup And Rollback

- Export database before migrations.
- Preserve uploaded files and `.env`.
- Rollback means reverting files and restoring database/storage backups manually or through operator-approved tooling.
- The backup manager records metadata and restore guidance only.
- The update preflight and backup foundations do not perform full automated restore or real update application.

## Security Checklist

- `.env` outside web root.
- Web root points to `public`.
- `APP_DEBUG=false`.
- Firewall permits only required ports.
- Supervisor logs are private.
- Backups are private.
- Update packages are not extracted from the browser.
