# Marketplace Release Checklist

- Run `php artisan marketplace:validate-package`.
- Confirm `.env.marketplace.example` contains placeholders only.
- Confirm include/exclude list blocks `.env`, vendor, node_modules, logs, cache, sessions, private storage, and `public/build.zip`.
- Confirm buyer installation checklist, screenshot checklist, demo checklist, and listing copy are current.
- Confirm no final ZIP is generated in this step.
- Confirm planned features are labeled as planned.
