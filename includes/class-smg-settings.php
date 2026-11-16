<?php
/**
 * Admin settings and sanitization handler.
 *
 * @package Site_Maintenance_Guard
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SMG_Settings
 *
 * Manages plugin options, Settings API registration, sanitization, and admin UI.
 *
 * @since 1.0.0
 */
class SMG_Settings {

	/**
	 * Admin page slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const PAGE_SLUG = 'site-maintenance-guard';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_post_smg_regenerate_token', array( $this, 'handle_regenerate_token' ) );
	}

	/**
	 * Return default plugin settings.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public static function get_defaults() {
		return array(
			'enabled'             => false,
			'mode'                => 'maintenance',
			'retry_after'         => 3600,
			'noindex_coming_soon' => true,
			'page_title'          => __( 'Site Under Maintenance', 'site-maintenance-guard' ),
			'heading'             => __( 'We will be back shortly', 'site-maintenance-guard' ),
			'message'             => __( 'Our website is currently undergoing scheduled maintenance. Thank you for your patience.', 'site-maintenance-guard' ),
			'logo_id'             => 0,
			'bg_color'            => '#f4f6f8',
			'card_bg_color'       => '#ffffff',
			'text_color'          => '#1e293b',
			'accent_color'        => '#2563eb',
			'custom_css'          => '',
			'allowed_roles'       => array( 'administrator' ),
			'bypass_enabled'      => false,
			'bypass_token'        => function_exists( 'wp_generate_password' ) ? wp_generate_password( 32, false, false ) : '',
			'excluded_paths'      => '',
			'allow_rest_api'      => false,
		);
	}

	/**
	 * Retrieve merged plugin settings.
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public static function get_settings() {
		$defaults = self::get_defaults();
		$saved    = get_option( SMG_OPTION_NAME, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = wp_parse_args( $saved, $defaults );

		if ( empty( $settings['bypass_token'] ) && function_exists( 'wp_generate_password' ) ) {
			$settings['bypass_token'] = wp_generate_password( 32, false, false );
		}

		return $settings;
	}

	/**
	 * Register Settings -> Maintenance Guard submenu page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_menu_page() {
		add_options_page(
			__( 'Site Maintenance Guard', 'site-maintenance-guard' ),
			__( 'Maintenance Guard', 'site-maintenance-guard' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register option with WordPress Settings API.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'smg_settings_group',
			SMG_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => self::get_defaults(),
			)
		);
	}

	/**
	 * Enqueue admin scripts and styles only on our settings page.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();

		wp_enqueue_style(
			'smg-admin',
			SMG_PLUGIN_URL . 'assets/css/admin.css',
			array( 'wp-color-picker' ),
			SMG_VERSION
		);

		wp_enqueue_script(
			'smg-admin',
			SMG_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			SMG_VERSION,
			true
		);

		wp_localize_script(
			'smg-admin',
			'smgAdminL10n',
			array(
				'mediaTitle'  => __( 'Select Logo Image', 'site-maintenance-guard' ),
				'mediaButton' => __( 'Use this image', 'site-maintenance-guard' ),
				'copiedText'  => __( 'Copied!', 'site-maintenance-guard' ),
				'copyText'    => __( 'Copy URL', 'site-maintenance-guard' ),
			)
		);
	}

	/**
	 * Sanitize and validate all settings before saving.
	 *
	 * @since 1.0.0
	 * @param mixed $input Raw submitted settings array.
	 * @return array<string, mixed>
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$existing = self::get_settings();
		$defaults = self::get_defaults();
		$output   = array();

		$output['enabled']             = ! empty( $input['enabled'] );
		$output['noindex_coming_soon'] = ! empty( $input['noindex_coming_soon'] );
		$output['bypass_enabled']      = ! empty( $input['bypass_enabled'] );
		$output['allow_rest_api']      = ! empty( $input['allow_rest_api'] );

		$allowed_modes  = array( 'maintenance', 'coming_soon' );
		$submitted_mode = isset( $input['mode'] ) ? sanitize_key( $input['mode'] ) : 'maintenance';
		$output['mode'] = in_array( $submitted_mode, $allowed_modes, true ) ? $submitted_mode : 'maintenance';

		$retry_after           = isset( $input['retry_after'] ) ? absint( $input['retry_after'] ) : 3600;
		$output['retry_after'] = max( 60, min( 604800, $retry_after ) );

		$output['page_title'] = isset( $input['page_title'] )
			? sanitize_text_field( $input['page_title'] )
			: $defaults['page_title'];

		$output['heading'] = isset( $input['heading'] )
			? sanitize_text_field( $input['heading'] )
			: $defaults['heading'];

		$output['message'] = isset( $input['message'] )
			? wp_kses_post( $input['message'] )
			: $defaults['message'];

		$output['logo_id'] = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;

		$output['bg_color']      = self::sanitize_hex_or_default( $input['bg_color'] ?? '', $defaults['bg_color'] );
		$output['card_bg_color'] = self::sanitize_hex_or_default( $input['card_bg_color'] ?? '', $defaults['card_bg_color'] );
		$output['text_color']    = self::sanitize_hex_or_default( $input['text_color'] ?? '', $defaults['text_color'] );
		$output['accent_color']  = self::sanitize_hex_or_default( $input['accent_color'] ?? '', $defaults['accent_color'] );

		$output['custom_css'] = isset( $input['custom_css'] )
			? wp_strip_all_tags( $input['custom_css'] )
			: '';

		$valid_roles = array();
		if ( function_exists( 'wp_roles' ) ) {
			$valid_roles = array_keys( wp_roles()->roles );
		}
		$submitted_roles = isset( $input['allowed_roles'] ) && is_array( $input['allowed_roles'] )
			? array_map( 'sanitize_key', $input['allowed_roles'] )
			: array();

		if ( ! empty( $valid_roles ) ) {
			$submitted_roles = array_values( array_intersect( $submitted_roles, $valid_roles ) );
		}
		if ( ! in_array( 'administrator', $submitted_roles, true ) ) {
			array_unshift( $submitted_roles, 'administrator' );
		}
		$output['allowed_roles'] = array_unique( $submitted_roles );

		// Preserve existing token unless explicitly regenerated via dedicated nonce-protected action.
		$output['bypass_token'] = ! empty( $existing['bypass_token'] )
			? preg_replace( '/[^a-zA-Z0-9]/', '', (string) $existing['bypass_token'] )
			: $defaults['bypass_token'];

		$raw_paths   = isset( $input['excluded_paths'] ) ? (string) $input['excluded_paths'] : '';
		$lines       = preg_split( '/\r\n|\r|\n/', $raw_paths );
		$clean_paths = array();
		if ( is_array( $lines ) ) {
			foreach ( $lines as $line ) {
				$line = trim( sanitize_text_field( $line ) );
				if ( '' !== $line ) {
					$clean_paths[] = '/' . ltrim( $line, '/' );
				}
			}
		}
		$output['excluded_paths'] = implode( "\n", array_unique( $clean_paths ) );

		return $output;
	}

	/**
	 * Sanitize a hex color string or fall back to default.
	 *
	 * @since 1.0.0
	 * @param string $color   Raw color value.
	 * @param string $default Default hex color.
	 * @return string
	 */
	private static function sanitize_hex_or_default( $color, $default ) {
		$sanitized = sanitize_hex_color( (string) $color );
		return ! empty( $sanitized ) ? $sanitized : $default;
	}

	/**
	 * Handle nonce-protected bypass token regeneration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_regenerate_token() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'site-maintenance-guard' ), 403 );
		}

		check_admin_referer( 'smg_regenerate_token_action', 'smg_regenerate_nonce' );

		$settings                 = self::get_settings();
		$settings['bypass_token'] = wp_generate_password( 32, false, false );

		update_option( SMG_OPTION_NAME, $settings );

		$redirect_url = add_query_arg(
			array(
				'page'              => self::PAGE_SLUG,
				'tab'               => 'access',
				'smg-token-updated' => '1',
			),
			admin_url( 'options-general.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render the admin settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = self::get_settings();
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tabs       = apply_filters(
			'smg_settings_tabs',
			array(
				'general'    => __( 'General', 'site-maintenance-guard' ),
				'design'     => __( 'Design', 'site-maintenance-guard' ),
				'access'     => __( 'Access & Bypass', 'site-maintenance-guard' ),
				'extensions' => __( 'Extensions', 'site-maintenance-guard' ),
			)
		);

		if ( ! array_key_exists( $active_tab, $tabs ) ) {
			$active_tab = 'general';
		}

		$bypass_url = add_query_arg( 'smg_bypass', $settings['bypass_token'], home_url( '/' ) );
		?>
		<div class="wrap smg-wrap">
			<h1><?php esc_html_e( 'Site Maintenance Guard', 'site-maintenance-guard' ); ?></h1>

			<?php if ( ! empty( $_GET['smg-token-updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Secret bypass link has been regenerated.', 'site-maintenance-guard' ); ?></p>
				</div>
			<?php endif; ?>

			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Settings sections', 'site-maintenance-guard' ); ?>">
				<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
					<?php
					$tab_url = add_query_arg(
						array(
							'page' => self::PAGE_SLUG,
							'tab'  => $tab_key,
						),
						admin_url( 'options-general.php' )
					);
					$classes = ( $active_tab === $tab_key ) ? 'nav-tab nav-tab-active' : 'nav-tab';
					?>
					<a href="<?php echo esc_url( $tab_url ); ?>" class="<?php echo esc_attr( $classes ); ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<?php if ( 'extensions' === $active_tab ) : ?>
				<?php $this->render_extensions_tab(); ?>
			<?php else : ?>
				<form method="post" action="options.php" class="smg-settings-form">
					<?php
					settings_fields( 'smg_settings_group' );
					$this->render_hidden_other_tabs( $active_tab, $settings );
					?>

					<?php if ( 'general' === $active_tab ) : ?>
						<?php $this->render_general_tab( $settings ); ?>
					<?php elseif ( 'design' === $active_tab ) : ?>
						<?php $this->render_design_tab( $settings ); ?>
					<?php elseif ( 'access' === $active_tab ) : ?>
						<?php $this->render_access_tab( $settings, $bypass_url ); ?>
					<?php endif; ?>

					<?php submit_button(); ?>
				</form>

				<?php if ( 'access' === $active_tab ) : ?>
					<div class="smg-token-regenerate-box">
						<h2><?php esc_html_e( 'Regenerate Secret Bypass Token', 'site-maintenance-guard' ); ?></h2>
						<p class="description">
							<?php esc_html_e( 'If your secret bypass URL was shared unintentionally, regenerate the token below to invalidate previous links.', 'site-maintenance-guard' ); ?>
						</p>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="smg_regenerate_token" />
							<?php wp_nonce_field( 'smg_regenerate_token_action', 'smg_regenerate_nonce' ); ?>
							<?php submit_button( __( 'Regenerate Bypass Link', 'site-maintenance-guard' ), 'secondary', 'submit', false ); ?>
						</form>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render hidden inputs for settings belonging to inactive tabs so they are preserved on save.
	 *
	 * @since 1.0.0
	 * @param string               $active_tab Current active tab.
	 * @param array<string, mixed> $settings   Current settings.
	 * @return void
	 */
	private function render_hidden_other_tabs( $active_tab, $settings ) {
		if ( 'general' !== $active_tab ) {
			if ( ! empty( $settings['enabled'] ) ) {
				echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[enabled]" value="1" />';
			}
			if ( ! empty( $settings['noindex_coming_soon'] ) ) {
				echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[noindex_coming_soon]" value="1" />';
			}
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[mode]" value="' . esc_attr( $settings['mode'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[retry_after]" value="' . esc_attr( (string) $settings['retry_after'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[page_title]" value="' . esc_attr( $settings['page_title'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[heading]" value="' . esc_attr( $settings['heading'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[message]" value="' . esc_attr( $settings['message'] ) . '" />';
		}

		if ( 'design' !== $active_tab ) {
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[logo_id]" value="' . esc_attr( (string) $settings['logo_id'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[bg_color]" value="' . esc_attr( $settings['bg_color'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[card_bg_color]" value="' . esc_attr( $settings['card_bg_color'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[text_color]" value="' . esc_attr( $settings['text_color'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[accent_color]" value="' . esc_attr( $settings['accent_color'] ) . '" />';
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[custom_css]" value="' . esc_attr( $settings['custom_css'] ) . '" />';
		}

		if ( 'access' !== $active_tab ) {
			if ( ! empty( $settings['bypass_enabled'] ) ) {
				echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[bypass_enabled]" value="1" />';
			}
			if ( ! empty( $settings['allow_rest_api'] ) ) {
				echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[allow_rest_api]" value="1" />';
			}
			foreach ( (array) $settings['allowed_roles'] as $role ) {
				echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[allowed_roles][]" value="' . esc_attr( $role ) . '" />';
			}
			echo '<input type="hidden" name="' . esc_attr( SMG_OPTION_NAME ) . '[excluded_paths]" value="' . esc_attr( $settings['excluded_paths'] ) . '" />';
		}
	}

	/**
	 * Render General settings tab.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Current settings.
	 * @return void
	 */
	private function render_general_tab( $settings ) {
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'site-maintenance-guard' ); ?></th>
				<td>
					<label for="smg_enabled">
						<input type="checkbox" id="smg_enabled" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?> />
						<?php esc_html_e( 'Enable Maintenance / Coming Soon mode for visitors', 'site-maintenance-guard' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_mode"><?php esc_html_e( 'Mode', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<select id="smg_mode" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[mode]">
						<option value="maintenance" <?php selected( $settings['mode'], 'maintenance' ); ?>>
							<?php esc_html_e( 'Maintenance Mode (HTTP 503 + Retry-After)', 'site-maintenance-guard' ); ?>
						</option>
						<option value="coming_soon" <?php selected( $settings['mode'], 'coming_soon' ); ?>>
							<?php esc_html_e( 'Coming Soon Mode (HTTP 200)', 'site-maintenance-guard' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Use HTTP 503 for temporary downtime so search engines do not de-index your site. Use HTTP 200 for new sites under construction.', 'site-maintenance-guard' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_retry_after"><?php esc_html_e( 'Retry-After (seconds)', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="number" id="smg_retry_after" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[retry_after]" value="<?php echo esc_attr( (string) $settings['retry_after'] ); ?>" min="60" max="604800" class="small-text" />
					<p class="description">
						<?php esc_html_e( 'Sent in the HTTP Retry-After header when Maintenance Mode (503) is active (default: 3600 seconds = 1 hour).', 'site-maintenance-guard' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Search Engine Visibility', 'site-maintenance-guard' ); ?></th>
				<td>
					<label for="smg_noindex_coming_soon">
						<input type="checkbox" id="smg_noindex_coming_soon" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[noindex_coming_soon]" value="1" <?php checked( ! empty( $settings['noindex_coming_soon'] ) ); ?> />
						<?php esc_html_e( 'Send X-Robots-Tag: noindex, nofollow in Coming Soon mode', 'site-maintenance-guard' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_page_title"><?php esc_html_e( 'Browser Page Title', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_page_title" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[page_title]" value="<?php echo esc_attr( $settings['page_title'] ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_heading"><?php esc_html_e( 'Headline', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_heading" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[heading]" value="<?php echo esc_attr( $settings['heading'] ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_message"><?php esc_html_e( 'Message', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<?php
					wp_editor(
						$settings['message'],
						'smg_message',
						array(
							'textarea_name' => SMG_OPTION_NAME . '[message]',
							'textarea_rows' => 6,
							'media_buttons' => false,
							'teeny'         => true,
						)
					);
					?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Design settings tab.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Current settings.
	 * @return void
	 */
	private function render_design_tab( $settings ) {
		$logo_id  = absint( $settings['logo_id'] );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Logo', 'site-maintenance-guard' ); ?></th>
				<td>
					<div class="smg-logo-field">
						<input type="hidden" id="smg_logo_id" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[logo_id]" value="<?php echo esc_attr( (string) $logo_id ); ?>" />
						<div id="smg_logo_preview" class="smg-logo-preview">
							<?php if ( $logo_url ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="" />
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="smg_select_logo"><?php esc_html_e( 'Select Logo', 'site-maintenance-guard' ); ?></button>
						<button type="button" class="button <?php echo $logo_url ? '' : 'hidden'; ?>" id="smg_remove_logo"><?php esc_html_e( 'Remove Logo', 'site-maintenance-guard' ); ?></button>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_bg_color"><?php esc_html_e( 'Background Color', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_bg_color" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[bg_color]" value="<?php echo esc_attr( $settings['bg_color'] ); ?>" class="smg-color-picker" data-default-color="#f4f6f8" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_card_bg_color"><?php esc_html_e( 'Card Background Color', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_card_bg_color" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[card_bg_color]" value="<?php echo esc_attr( $settings['card_bg_color'] ); ?>" class="smg-color-picker" data-default-color="#ffffff" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_text_color"><?php esc_html_e( 'Text Color', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_text_color" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[text_color]" value="<?php echo esc_attr( $settings['text_color'] ); ?>" class="smg-color-picker" data-default-color="#1e293b" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_accent_color"><?php esc_html_e( 'Accent Color', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<input type="text" id="smg_accent_color" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[accent_color]" value="<?php echo esc_attr( $settings['accent_color'] ); ?>" class="smg-color-picker" data-default-color="#2563eb" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_custom_css"><?php esc_html_e( 'Custom CSS', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<textarea id="smg_custom_css" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[custom_css]" rows="6" class="large-text code"><?php echo esc_textarea( $settings['custom_css'] ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Access & Bypass settings tab.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings   Current settings.
	 * @param string               $bypass_url Generated secret bypass URL.
	 * @return void
	 */
	private function render_access_tab( $settings, $bypass_url ) {
		$all_roles = wp_roles()->roles;
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Allowed User Roles', 'site-maintenance-guard' ); ?></th>
				<td>
					<fieldset>
						<?php foreach ( $all_roles as $role_slug => $role_data ) : ?>
							<?php
							$is_admin = ( 'administrator' === $role_slug );
							$checked  = $is_admin || in_array( $role_slug, (array) $settings['allowed_roles'], true );
							?>
							<label class="smg-role-checkbox">
								<input type="checkbox"
									name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[allowed_roles][]"
									value="<?php echo esc_attr( $role_slug ); ?>"
									<?php checked( $checked ); ?>
									<?php disabled( $is_admin ); ?> />
								<?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
							</label><br />
						<?php endforeach; ?>
						<p class="description">
							<?php esc_html_e( 'Logged-in users with selected roles can view the live site normally. Administrators always retain access.', 'site-maintenance-guard' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Secret Bypass Link', 'site-maintenance-guard' ); ?></th>
				<td>
					<label for="smg_bypass_enabled">
						<input type="checkbox" id="smg_bypass_enabled" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[bypass_enabled]" value="1" <?php checked( ! empty( $settings['bypass_enabled'] ) ); ?> />
						<?php esc_html_e( 'Allow visitors with the secret link to bypass Maintenance / Coming Soon mode', 'site-maintenance-guard' ); ?>
					</label>
					<div class="smg-bypass-url-wrap">
						<input type="text" readonly="readonly" id="smg_bypass_url_input" value="<?php echo esc_attr( $bypass_url ); ?>" class="large-text code" />
						<button type="button" class="button" id="smg_copy_bypass_url"><?php esc_html_e( 'Copy URL', 'site-maintenance-guard' ); ?></button>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="smg_excluded_paths"><?php esc_html_e( 'Excluded URL Paths', 'site-maintenance-guard' ); ?></label></th>
				<td>
					<textarea id="smg_excluded_paths" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[excluded_paths]" rows="4" class="large-text code" placeholder="/privacy-policy"><?php echo esc_textarea( $settings['excluded_paths'] ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter one relative path per line (e.g. /privacy-policy) that should remain publicly accessible.', 'site-maintenance-guard' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'REST API', 'site-maintenance-guard' ); ?></th>
				<td>
					<label for="smg_allow_rest_api">
						<input type="checkbox" id="smg_allow_rest_api" name="<?php echo esc_attr( SMG_OPTION_NAME ); ?>[allow_rest_api]" value="1" <?php checked( ! empty( $settings['allow_rest_api'] ) ); ?> />
						<?php esc_html_e( 'Allow public REST API (/wp-json/) requests while maintenance mode is active', 'site-maintenance-guard' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render Freemium / Developer Extensions tab (WordPress.org compliant).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function render_extensions_tab() {
		?>
		<div class="smg-extensions-panel">
			<h2><?php esc_html_e( 'Developer Hooks & Pro Extensibility', 'site-maintenance-guard' ); ?></h2>
			<p>
				<?php esc_html_e( 'Site Maintenance Guard provides clean WordPress filter and action hooks so agencies, themes, and optional add-ons can customize behavior without modifying core plugin files.', 'site-maintenance-guard' ); ?>
			</p>
			<ul class="smg-hooks-list">
				<li><code>smg_can_bypass</code> — <?php esc_html_e( 'Filter whether the current request should bypass maintenance mode (e.g., IP allowlists or scheduled windows).', 'site-maintenance-guard' ); ?></li>
				<li><code>smg_template_vars</code> — <?php esc_html_e( 'Filter variables passed to the maintenance page template.', 'site-maintenance-guard' ); ?></li>
				<li><code>smg_settings_tabs</code> — <?php esc_html_e( 'Register additional settings tabs for modular extensions.', 'site-maintenance-guard' ); ?></li>
				<li><code>smg_before_render_template</code> — <?php esc_html_e( 'Action fired right before the maintenance template is rendered.', 'site-maintenance-guard' ); ?></li>
			</ul>
		</div>
		<?php
	}
}
