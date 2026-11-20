=== Gatedevx Maintenance Guard ===
Contributors: gatedevx
Tags: maintenance mode, coming soon, under construction, 503, retry-after
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, standards-compliant Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon (HTTP 200) plugin with role bypass and secret access links.

== Description ==

**Gatedevx Maintenance Guard** lets you safely put your WordPress website into Maintenance Mode or Coming Soon mode without bloating your site or violating SEO best practices.

= Key Features =

* **1-Click Quick Toggle:** Turn Maintenance or Coming Soon mode ON/OFF in one click from the WordPress Admin Bar or the plugin settings banner.
* **True Maintenance Mode (HTTP 503 + Retry-After):** Informs search engines that your site is temporarily down so your SEO rankings are preserved.
* **Coming Soon Mode (HTTP 200):** Ideal for new websites under construction, with optional `X-Robots-Tag: noindex, nofollow` protection.
* **Role-Based Access:** Allow selected user roles (Administrators always have access) to browse the live site normally while logged in.
* **Secret Bypass Link:** Share a secure, constant-time verified bypass URL with clients or stakeholders (stored via `HttpOnly; SameSite=Lax` hashed cookie)—regenerate it anytime with one click.
* **Multi-Language & WPML / Polylang Ready:** Full i18n support with `.pot` template and `wpml-config.xml` for translating custom maintenance messages.
* **Excluded Paths & REST API Control:** Keep specific relative paths (such as `/privacy-policy`) accessible and control public `/wp-json/` REST API access.
* **3 Built-In Non-Cliché Visual Presets:** Choose from bundled lightweight (`< 75 KB`, 1:1) artwork presets—*Teaser Macro*, *Authentic Backstage (IT Desk)*, or *Retro UI Sleep Mode Cat*—designed without cliché cones or gears.
* **Dark Mode & Auto OS Theme:** Choose Light, Dark, or Auto (`prefers-color-scheme: dark`) so the maintenance screen and artwork maintain crisp contrast on every visitor device.
* **Structured Maintenance UX:** Display an optional pulsing **Status Badge**, concrete **Expected Return Time**, and clickable **Emergency Contact** (email `mailto:` or link) alongside your message.
* **Clean Customization & Zero External CDNs:** Customize the browser title, headline, rich message, logo (via WordPress Media Library), and colors—all assets load locally with zero telemetry.
* **Developer & Freemium Friendly:** Clean filter and action hooks (`gtvx_can_bypass`, `gtvx_template_vars`, `gtvx_settings_tabs`, `gtvx_before_render_template`).

== Installation ==

1. Upload the `gatedevx-maintenance-guard` folder to the `/wp-content/plugins/` directory, or install the plugin directly through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings -> Maintenance Guard** to configure your mode, design, and access rules.

== Frequently Asked Questions ==

= What is the difference between Maintenance Mode and Coming Soon Mode? =

Maintenance Mode returns an `HTTP 503 Service Unavailable` status with a `Retry-After` header, telling search engines to come back later without de-indexing your pages. Coming Soon Mode returns `HTTP 200 OK` (with optional `noindex`) for new sites that are not yet launched.

= Can I give a client access without creating a WordPress account? =

Yes. Enable the **Secret Bypass Link** under **Settings -> Maintenance Guard -> Access & Bypass** and share the generated URL. You can regenerate the token at any time to revoke previous links.

= Does this plugin load any external fonts, scripts, or tracking? =

No. All assets are bundled locally within the plugin. There are no external CDN calls and no telemetry.

== Screenshots ==

1. General settings tab for switching between HTTP 503 Maintenance and HTTP 200 Coming Soon modes.
2. Design settings tab with WordPress Media Library logo picker and color controls.
3. Access & Bypass settings tab with role selection and secret bypass link management.

== Changelog ==

= 1.1.0 =
* Added 3 built-in lightweight (`< 75 KB`, 1:1) visual artwork presets in the Design tab: *Teaser Macro*, *Authentic Backstage (IT Desk)*, and *Retro UI (Sleep Mode Cat)*.
* Added Color Scheme & Dark Mode support (`Light`, `Dark`, and `Auto` via `prefers-color-scheme: dark`).
* Added structured maintenance UX fields in the General tab: optional pulsing Status Badge, Expected Return Time, and Emergency Contact (auto-linked for email/URL).
* Added WPML / Polylang `wpml-config.xml` entries for the new text fields and updated Polish (`pl_PL`) translations.
* Fixed `wp_die()` HTTP 403 status argument in admin permission checks.

= 1.0.0 =
* Initial release.
* Added Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon Mode (HTTP 200).
* Added role-based bypass, secret bypass link with constant-time verification, excluded URL paths, and REST API restriction toggle.
* Added design customization with local assets and full WordPress.org compliance.

== Upgrade Notice ==

= 1.1.0 =
Adds 3 built-in non-cliché visual presets, Dark Mode / Auto theme support, pulsing status badge, expected return time, and emergency contact fields.
