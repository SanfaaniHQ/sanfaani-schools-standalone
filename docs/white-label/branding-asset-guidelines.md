# Branding Asset Guidelines

Allowed asset types:

- PNG
- JPG/JPEG
- WEBP
- ICO

Blocked asset types:

- SVG by default, because unsanitized SVG may carry script content.
- PHP, JS, HTML, shell scripts, and executable formats.
- Files larger than configured limits.

Storage rules:

- Platform assets are stored under `branding/platform`.
- Managed assets are stored under `branding/managed`.
- School assets are stored under `branding/schools/{school_id}`.
- UI should display public URLs or safe filenames only, never absolute server paths.

This foundation does not delete previous assets automatically. Replace/delete workflows can be added later after audit and rollback rules are defined.
