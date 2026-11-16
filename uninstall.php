<?php
/**
 * Uninstall handler for Site Maintenance Guard.
 *
 * Removes plugin options when the plugin is deleted via WordPress Admin.
 *
 * @package Site_Maintenance_Guard
 * @since   1.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$smg_option_name = 'smg_settings';

if ( is_multisite() ) {
	$smg_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $smg_site_ids as $smg_site_id ) {
		switch_to_blog( (int) $smg_site_id );
		delete_option( $smg_option_name );
		restore_current_blog();
	}
} else {
	delete_option( $smg_option_name );
}
