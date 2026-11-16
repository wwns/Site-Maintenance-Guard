=== Site Maintenance Guard ===
Contributors: wwns
Tags: maintenance mode, coming soon, under construction, 503, retry-after
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, standards-compliant Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon (HTTP 200) plugin with role bypass and secret access links.

== Description ==

**Site Maintenance Guard** lets you safely put your WordPress website into Maintenance Mode or Coming Soon mode without bloating your site or violating SEO best practices.

= Key Features =

* **True Maintenance Mode (HTTP 503 + Retry-After):** Informs search engines that your site is temporarily down so your rankings are preserved.
* **Coming Soon Mode (HTTP 200):** Ideal for new websites under construction, with optional `X-Robots-Tag: noindex, nofollow` protection.
* **Role-Based Access:** Allow selected user roles (Administrators always have access) to browse the live site normally while logged in.
* **Secret Bypass Link:** Share a secure, constant-time verified bypass URL with clients or stakeholders—regenerate it anytime with one click.
* **Excluded Paths & REST API Control:** Keep specific relative paths (such as `/privacy-policy`) accessible and control public `/wp-json/` REST API access.
* **Clean Customization:** Customize the browser title, headline, rich message, logo (via WordPress Media Library), colors, and custom CSS—with zero external CDN requests.
* **Developer & Freemium Friendly:** Clean filter and action hooks (`smg_can_bypass`, `smg_template_vars`, `smg_settings_tabs`, `smg_before_render_template`).

== Installation ==

1. Upload the `site-maintenance-guard` folder to the `/wp-content/plugins/` directory, or install the plugin directly through the WordPress Plugins screen.
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

= 1.0.0 =
* Initial release.
* Added Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon Mode (HTTP 200).
* Added role-based bypass, secret bypass link with constant-time verification, excluded URL paths, and REST API restriction toggle.
* Added design customization with local assets and full WordPress.org compliance.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Site Maintenance Guard.
