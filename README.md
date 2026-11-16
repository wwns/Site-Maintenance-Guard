# Site Maintenance Guard

Lightweight, WordPress.org-compliant Maintenance Mode (`HTTP 503` + `Retry-After`) and Coming Soon (`HTTP 200`) plugin for WordPress.

## Features

- **Maintenance Mode (`HTTP 503`)**: Sends `Retry-After` and `X-Robots-Tag: noindex, nofollow` headers so search engines do not de-index your site during downtime.
- **Coming Soon Mode (`HTTP 200`)**: Ideal for new sites under construction, with optional `noindex` header.
- **Role-Based Bypass**: Logged-in users with allowed roles bypass the screen automatically.
- **Secret Bypass Link**: Cryptographically generated token with constant-time (`hash_equals`) verification, `HttpOnly; SameSite=Lax` cookie, and one-click regeneration.
- **Excluded Paths & REST API Control**: Exclude specific relative URL paths and optionally restrict public `/wp-json/` endpoints.
- **Zero External Dependencies**: All CSS, JS, and templates are self-contained with no external CDN requests.

## Developer & Freemium Hooks

- `smg_can_bypass` (`bool $bypass, array $settings`) — Override bypass decision (e.g., IP allowlists or schedules).
- `smg_template_vars` (`array $template_vars`) — Filter variables passed to `templates/maintenance-page.php`.
- `smg_settings_tabs` (`array $tabs`) — Register custom tabs in **Settings → Maintenance Guard**.
- `smg_before_render_template` (`array $template_vars`) — Action fired immediately before rendering the maintenance template.

## Development & Verification

```bash
# PHP syntax check
find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l

# Run built-in verification suite
php tests/verify-plugin.php
```

## License

GPLv2 or later — see [LICENSE](LICENSE).
