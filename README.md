# Gatedevx Maintenance Guard

Lightweight, WordPress.org-compliant Maintenance Mode (`HTTP 503` + `Retry-After`) and Coming Soon (`HTTP 200`) plugin for WordPress.

## Features

- **Maintenance Mode (`HTTP 503`)**: Sends `Retry-After` and `X-Robots-Tag: noindex, nofollow` headers so search engines do not de-index your site during downtime.
- **Coming Soon Mode (`HTTP 200`)**: Ideal for new sites under construction, with optional `noindex` header.
- **Role-Based Bypass**: Logged-in users with allowed roles bypass the screen automatically.
- **Secret Bypass Link**: Cryptographically generated token with constant-time (`hash_equals`) verification, `HttpOnly; SameSite=Lax` cookie, and one-click regeneration.
- **Excluded Paths & REST API Control**: Exclude specific relative URL paths and optionally restrict public `/wp-json/` endpoints.
- **3 Built-In Non-Cliché Visual Presets**: Pick from bundled `720×720` (`< 75 KB`) square artwork in **Settings → Maintenance Guard → Design**:
  - `assets/images/01-teaser-macro.jpg` — *Teaser Macro*: high-contrast glassmorphic UI edge teaser (`37.6 KB`).
  - `assets/images/02-backstage-desk.jpg` — *Authentic Backstage*: minimalist developer desk with coffee & code editor (`72.3 KB`).
  - `assets/images/03-retro-ui-cat.jpg` — *Retro UI (Sleep Mode Cat)*: lighthearted retro OS window with a cat sleeping on a keyboard (`54.8 KB`).
- **Dark Mode & Auto OS Theme**: Switch between `Light`, `Dark`, and `Auto` (`prefers-color-scheme: dark`) with high-contrast framing for both desktop and mobile viewports.
- **Structured Maintenance UX**: Optional pulsing **Status Badge**, specific **Expected Return Time**, and auto-linked **Emergency Contact** (`mailto:` / URL) on the maintenance card.
- **Zero External Dependencies**: All CSS, JS, images, and templates are self-contained with no external CDN requests.

## Developer & Freemium Hooks

- `gtvx_can_bypass` (`bool $bypass, array $settings`) — Override bypass decision (e.g., IP allowlists or schedules).
- `gtvx_template_vars` (`array $template_vars`) — Filter variables passed to `templates/maintenance-page.php`.
- `gtvx_settings_tabs` (`array $tabs`) — Register custom tabs in **Settings → Maintenance Guard**.
- `gtvx_before_render_template` (`array $template_vars`) — Action fired immediately before rendering the maintenance template.

## Development & Verification

```bash
# PHP syntax check
find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l

# Run built-in verification suite
php tests/verify-plugin.php
```

## License

GPLv2 or later — see [LICENSE](LICENSE).
