<?php
/**
 * Main plugin orchestrator class.
 *
 * @package Gatedevx_Maintenance_Guard
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GTVX_Plugin
 *
 * Coordinates admin settings, frontend interception, and admin bar indicators.
 *
 * @since 1.0.0
 */
final class GTVX_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var GTVX_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Settings handler instance.
	 *
	 * @since 1.0.0
	 * @var GTVX_Settings
	 */
	public $settings;

	/**
	 * Frontend handler instance.
	 *
	 * @since 1.0.0
	 * @var GTVX_Frontend
	 */
	public $frontend;

	/**
	 * Retrieve singleton instance.
	 *
	 * @since 1.0.0
	 * @return GTVX_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->settings = new GTVX_Settings();
		$this->frontend = new GTVX_Frontend();

		add_filter(
			'plugin_action_links_' . plugin_basename( GTVX_PLUGIN_FILE ),
			array( $this, 'add_action_links' )
		);
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_indicator' ), 100 );
		add_action( 'admin_post_gtvx_quick_toggle', array( $this, 'handle_quick_toggle' ) );
	}

	/**
	 * Add Settings link to the Plugins list table.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $links Existing action links.
	 * @return array<int, string>
	 */
	public function add_action_links( $links ) {
		$settings_url  = admin_url( 'options-general.php?page=gatedevx-maintenance-guard' );
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_url ),
			esc_html__( 'Settings', 'gatedevx-maintenance-guard' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Show status & 1-click quick toggle in the WordPress Admin Bar.
	 *
	 * @since 1.0.0
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_indicator( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options    = GTVX_Settings::get_settings();
		$is_enabled = ! empty( $options['enabled'] );

		if ( $is_enabled ) {
			$is_coming_soon = ( 'coming_soon' === $options['mode'] );
			$label          = $is_coming_soon
				? __( 'Coming Soon: ON', 'gatedevx-maintenance-guard' )
				: __( 'Maintenance: ON', 'gatedevx-maintenance-guard' );
			$toggle_label   = __( 'Turn OFF Maintenance Mode', 'gatedevx-maintenance-guard' );
			$node_class     = 'gtvx-admin-bar-active';
		} else {
			$label        = __( 'Maintenance: OFF', 'gatedevx-maintenance-guard' );
			$toggle_label = __( 'Turn ON Maintenance Mode', 'gatedevx-maintenance-guard' );
			$node_class   = 'gtvx-admin-bar-inactive';
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'gtvx-status-indicator',
				'title' => '<span class="ab-icon dashicons dashicons-shield"></span><span class="ab-label">' . esc_html( $label ) . '</span>',
				'href'  => esc_url( admin_url( 'options-general.php?page=gatedevx-maintenance-guard' ) ),
				'meta'  => array(
					'class' => $node_class,
					'title' => esc_attr__( 'Configure Gatedevx Maintenance Guard', 'gatedevx-maintenance-guard' ),
				),
			)
		);

		$toggle_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=gtvx_quick_toggle' ),
			'gtvx_quick_toggle',
			'gtvx_toggle_nonce'
		);

		$wp_admin_bar->add_node(
			array(
				'id'     => 'gtvx-quick-toggle',
				'parent' => 'gtvx-status-indicator',
				'title'  => esc_html( $toggle_label ),
				'href'   => esc_url( $toggle_url ),
			)
		);
	}

	/**
	 * Handle 1-click quick toggle from the Admin Bar or Settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_quick_toggle() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'gatedevx-maintenance-guard' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( 'gtvx_quick_toggle', 'gtvx_toggle_nonce' );

		$settings            = GTVX_Settings::get_settings();
		$settings['enabled'] = empty( $settings['enabled'] );

		update_option( GTVX_OPTION_NAME, $settings );

		$redirect_url = wp_get_referer();
		if ( ! $redirect_url ) {
			$redirect_url = admin_url( 'options-general.php?page=gatedevx-maintenance-guard' );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
