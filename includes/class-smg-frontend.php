<?php
/**
 * Frontend maintenance / coming soon interception handler.
 *
 * @package Site_Maintenance_Guard
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SMG_Frontend
 *
 * Handles HTTP headers, bypass verification, and maintenance template rendering.
 *
 * @since 1.0.0
 */
class SMG_Frontend {

	/**
	 * Cookie name used for secret bypass link.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const BYPASS_COOKIE = 'smg_bypass_token';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle_maintenance_mode' ), 0 );
		add_filter( 'rest_authentication_errors', array( $this, 'maybe_restrict_rest_api' ), 99 );
	}

	/**
	 * Restrict public REST API requests when maintenance mode is active.
	 *
	 * @since 1.0.0
	 * @param WP_Error|null|bool $result Existing authentication result.
	 * @return WP_Error|null|bool
	 */
	public function maybe_restrict_rest_api( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		$settings = SMG_Settings::get_settings();
		if ( empty( $settings['enabled'] ) || ! empty( $settings['allow_rest_api'] ) ) {
			return $result;
		}

		if ( $this->user_has_allowed_role( $settings ) || $this->has_valid_bypass_cookie( $settings ) ) {
			return $result;
		}

		if ( apply_filters( 'smg_can_bypass', false, $settings ) ) {
			return $result;
		}

		return new WP_Error(
			'smg_maintenance_mode',
			__( 'The site is currently undergoing maintenance.', 'site-maintenance-guard' ),
			array( 'status' => 503 )
		);
	}

	/**
	 * Intercept frontend requests when Maintenance / Coming Soon mode is enabled.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_maintenance_mode() {
		$settings = SMG_Settings::get_settings();

		if ( empty( $settings['enabled'] ) ) {
			return;
		}

		if ( $this->is_system_request() ) {
			return;
		}

		$this->maybe_handle_bypass_query( $settings );

		if ( $this->should_bypass( $settings ) ) {
			return;
		}

		$this->render_maintenance_page( $settings );
		exit;
	}

	/**
	 * Determine whether the current request is a WordPress system request.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	private function is_system_request() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return true;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		if ( function_exists( 'is_login' ) && is_login() ) {
			return true;
		}

		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Process ?smg_bypass=<token> query parameter, set cookie, and redirect cleanly.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return void
	 */
	private function maybe_handle_bypass_query( $settings ) {
		if ( empty( $settings['bypass_enabled'] ) || empty( $settings['bypass_token'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['smg_bypass'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$provided_token = sanitize_text_field( wp_unslash( $_GET['smg_bypass'] ) );
		$expected_token = (string) $settings['bypass_token'];

		if ( '' !== $provided_token && hash_equals( $expected_token, $provided_token ) ) {
			$cookie_value   = function_exists( 'wp_hash' ) ? wp_hash( $expected_token ) : hash( 'sha256', $expected_token );
			$cookie_options = array(
				'expires'  => time() + DAY_IN_SECONDS,
				'path'     => defined( 'COOKIEPATH' ) ? COOKIEPATH : '/',
				'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			);
			setcookie( self::BYPASS_COOKIE, $cookie_value, $cookie_options );

			wp_safe_redirect( remove_query_arg( 'smg_bypass' ) );
			exit;
		}
	}

	/**
	 * Determine whether the current visitor/request can bypass maintenance mode.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return bool
	 */
	public function should_bypass( $settings ) {
		if ( $this->user_has_allowed_role( $settings ) ) {
			return true;
		}

		if ( $this->has_valid_bypass_cookie( $settings ) ) {
			return true;
		}

		if ( $this->is_excluded_path( $settings ) ) {
			return true;
		}

		return (bool) apply_filters( 'smg_can_bypass', false, $settings );
	}

	/**
	 * Check whether the logged-in user has an allowed role.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return bool
	 */
	private function user_has_allowed_role( $settings ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( empty( $user->roles ) || ! is_array( $user->roles ) ) {
			return false;
		}

		$allowed_roles = isset( $settings['allowed_roles'] ) && is_array( $settings['allowed_roles'] )
			? $settings['allowed_roles']
			: array( 'administrator' );

		return ! empty( array_intersect( $user->roles, $allowed_roles ) );
	}

	/**
	 * Validate the bypass cookie using constant-time comparison.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return bool
	 */
	private function has_valid_bypass_cookie( $settings ) {
		if ( empty( $settings['bypass_enabled'] ) || empty( $settings['bypass_token'] ) ) {
			return false;
		}

		if ( ! isset( $_COOKIE[ self::BYPASS_COOKIE ] ) ) {
			return false;
		}

		$cookie_token  = sanitize_text_field( wp_unslash( $_COOKIE[ self::BYPASS_COOKIE ] ) );
		$expected_hash = function_exists( 'wp_hash' ) ? wp_hash( (string) $settings['bypass_token'] ) : hash( 'sha256', (string) $settings['bypass_token'] );
		return '' !== $cookie_token && hash_equals( $expected_hash, $cookie_token );
	}

	/**
	 * Check whether the current request path is in the excluded paths list.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return bool
	 */
	private function is_excluded_path( $settings ) {
		if ( empty( $settings['excluded_paths'] ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$request_uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$current_path = wp_parse_url( $request_uri, PHP_URL_PATH );
		if ( ! is_string( $current_path ) || '' === $current_path ) {
			return false;
		}

		$current_path = untrailingslashit( $current_path );
		$paths        = array_filter( array_map( 'trim', explode( "\n", (string) $settings['excluded_paths'] ) ) );

		foreach ( $paths as $excluded ) {
			if ( untrailingslashit( $excluded ) === $current_path ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Send HTTP status/cache/robots headers and render the maintenance template.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return void
	 */
	private function render_maintenance_page( $settings ) {
		nocache_headers();

		if ( 'maintenance' === $settings['mode'] ) {
			status_header( 503 );
			header( 'Retry-After: ' . absint( $settings['retry_after'] ) );
			header( 'X-Robots-Tag: noindex, nofollow' );
		} else {
			status_header( 200 );
			if ( ! empty( $settings['noindex_coming_soon'] ) ) {
				header( 'X-Robots-Tag: noindex, nofollow' );
			}
		}

		$logo_url = '';
		if ( ! empty( $settings['logo_id'] ) ) {
			$maybe_url = wp_get_attachment_image_url( absint( $settings['logo_id'] ), 'medium' );
			if ( is_string( $maybe_url ) ) {
				$logo_url = $maybe_url;
			}
		}

		$hero_image_url = '';
		$visual_preset  = isset( $settings['visual_preset'] ) ? (string) $settings['visual_preset'] : 'none';
		$presets        = SMG_Settings::get_visual_presets();
		if ( isset( $presets[ $visual_preset ] ) && ! empty( $presets[ $visual_preset ]['file'] ) ) {
			$preset_file = $presets[ $visual_preset ]['file'];
			if ( file_exists( SMG_PLUGIN_DIR . 'assets/images/' . $preset_file ) ) {
				$hero_image_url = SMG_PLUGIN_URL . 'assets/images/' . $preset_file;
			}
		}

		$color_scheme = isset( $settings['color_scheme'] ) && in_array( $settings['color_scheme'], array( 'light', 'dark', 'auto' ), true )
			? $settings['color_scheme']
			: 'light';

		$template_vars = apply_filters(
			'smg_template_vars',
			array(
				'mode'           => $settings['mode'],
				'status_badge'   => $settings['status_badge'] ?? '',
				'page_title'     => $settings['page_title'],
				'heading'        => $settings['heading'],
				'message'        => $settings['message'],
				'return_time'    => $settings['return_time'] ?? '',
				'contact_info'   => $settings['contact_info'] ?? '',
				'visual_preset'  => $visual_preset,
				'hero_image_url' => $hero_image_url,
				'color_scheme'   => $color_scheme,
				'logo_url'       => $logo_url,
				'site_name'      => get_bloginfo( 'name' ),
				'bg_color'       => $settings['bg_color'],
				'card_bg_color'  => $settings['card_bg_color'],
				'text_color'     => $settings['text_color'],
				'accent_color'   => $settings['accent_color'],
				'custom_css'     => $settings['custom_css'],
				'noindex'        => ( 'maintenance' === $settings['mode'] || ! empty( $settings['noindex_coming_soon'] ) ),
			)
		);

		wp_enqueue_style(
			'smg-frontend',
			SMG_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SMG_VERSION
		);

		$dynamic_css = sprintf(
			':root { --smg-bg: %1$s; --smg-card-bg: %2$s; --smg-text: %3$s; --smg-accent: %4$s; }',
			sanitize_hex_color( $template_vars['bg_color'] ) ?: '#f4f6f8',
			sanitize_hex_color( $template_vars['card_bg_color'] ) ?: '#ffffff',
			sanitize_hex_color( $template_vars['text_color'] ) ?: '#1e293b',
			sanitize_hex_color( $template_vars['accent_color'] ) ?: '#2563eb'
		);

		if ( ! empty( $template_vars['custom_css'] ) ) {
			$dynamic_css .= "\n" . wp_strip_all_tags( (string) $template_vars['custom_css'] );
		}

		wp_add_inline_style( 'smg-frontend', $dynamic_css );

		do_action( 'smg_before_render_template', $template_vars );

		$template_path = SMG_PLUGIN_DIR . 'templates/maintenance-page.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
	}
}
