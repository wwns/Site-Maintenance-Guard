<?php
/**
 * Plugin Name:       Site Maintenance Guard
 * Plugin URI:        https://github.com/wwns/site-maintenance-guard
 * Description:       Lightweight, standards-compliant Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon (HTTP 200) mode with role bypass, secret access links, and clean customization.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            wwns
 * Author URI:        https://github.com/wwns
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       site-maintenance-guard
 * Domain Path:       /languages
 *
 * @package Site_Maintenance_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SMG_VERSION', '1.1.0' );
define( 'SMG_PLUGIN_FILE', __FILE__ );
define( 'SMG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMG_OPTION_NAME', 'smg_settings' );

require_once SMG_PLUGIN_DIR . 'includes/class-smg-settings.php';
require_once SMG_PLUGIN_DIR . 'includes/class-smg-frontend.php';
require_once SMG_PLUGIN_DIR . 'includes/class-smg-plugin.php';

/**
 * Set default plugin options on activation.
 *
 * @since 1.0.0
 * @return void
 */
function smg_activate_plugin() {
	$existing = get_option( SMG_OPTION_NAME, false );
	if ( false === $existing ) {
		add_option( SMG_OPTION_NAME, SMG_Settings::get_defaults() );
	}
}
register_activation_hook( __FILE__, 'smg_activate_plugin' );

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return SMG_Plugin
 */
function smg_init_plugin() {
	return SMG_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'smg_init_plugin' );
