<?php
/**
 * Uninstall handler for Gatedevx Maintenance Guard.
 *
 * Removes plugin options when the plugin is deleted via WordPress Admin.
 *
 * @package Gatedevx_Maintenance_Guard
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$gtvx_option_name = 'gtvx_settings';

if ( is_multisite() ) {
	$gtvx_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $gtvx_site_ids as $gtvx_site_id ) {
		switch_to_blog( (int) $gtvx_site_id );
		delete_option( $gtvx_option_name );
		restore_current_blog();
	}
} else {
	delete_option( $gtvx_option_name );
}
