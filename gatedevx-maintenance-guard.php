<?php
/**
 * Plugin Name:       Gatedevx Maintenance Guard
 * Plugin URI:        https://github.com/wwns/site-maintenance-guard
 * Description:       Lightweight, standards-compliant Maintenance Mode (HTTP 503 + Retry-After) and Coming Soon (HTTP 200) mode with role bypass, secret access links, and clean customization.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            wwns
 * Author URI:        https://github.com/wwns
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatedevx-maintenance-guard
 * Domain Path:       /languages
 *
 * @package Gatedevx_Maintenance_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GTVX_VERSION', '1.1.0' );
define( 'GTVX_PLUGIN_FILE', __FILE__ );
define( 'GTVX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GTVX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GTVX_OPTION_NAME', 'gtvx_settings' );

require_once GTVX_PLUGIN_DIR . 'includes/class-gtvx-settings.php';
require_once GTVX_PLUGIN_DIR . 'includes/class-gtvx-frontend.php';
require_once GTVX_PLUGIN_DIR . 'includes/class-gtvx-plugin.php';

/**
 * Set default plugin options on activation.
 *
 * @since 1.0.0
 * @return void
 */
function gtvx_activate_plugin() {
	$existing = get_option( GTVX_OPTION_NAME, false );
	if ( false === $existing ) {
		add_option( GTVX_OPTION_NAME, GTVX_Settings::get_defaults() );
	}
}
register_activation_hook( __FILE__, 'gtvx_activate_plugin' );

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return GTVX_Plugin
 */
function gtvx_init_plugin() {
	return GTVX_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'gtvx_init_plugin' );
