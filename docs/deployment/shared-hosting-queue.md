# Shared Hosting Queue Operations

Use the database queue driver on cPanel/Namecheap-style hosting unless a VPS worker stack is available.

```bash
php artisan optimize:clear
php artisan queue:work --queue=mail,exports --sleep=3 --tries=3 --timeout=60
```

For a broader worker that also processes reports:

```bash
php artisan queue:work --queue=mail,sms,database,exports,reports --sleep=3 --tries=3 --timeout=60
```
