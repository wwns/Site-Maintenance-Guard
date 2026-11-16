<?php
/**
 * Automated verification and WordPress.org compliance test suite.
 *
 * @package Site_Maintenance_Guard
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
$failures = 0;

function smg_assert( bool $condition, string $message ): void {
	global $failures;
	if ( $condition ) {
		echo "[PASS] {$message}\n";
	} else {
		echo "[FAIL] {$message}\n";
		$failures++;
	}
}

// 1. Check ABSPATH / WP_UNINSTALL_PLUGIN guards and forbidden patterns.
$php_files = array(
	$root . '/site-maintenance-guard.php',
	$root . '/uninstall.php',
	$root . '/includes/class-smg-plugin.php',
	$root . '/includes/class-smg-settings.php',
	$root . '/includes/class-smg-frontend.php',
	$root . '/templates/maintenance-page.php',
);

foreach ( $php_files as $file ) {
	$content = file_get_contents( $file );
	$rel     = str_replace( $root . '/', '', $file );

	if ( 'uninstall.php' === $rel ) {
		smg_assert( false !== strpos( $content, 'WP_UNINSTALL_PLUGIN' ), "{$rel} checks WP_UNINSTALL_PLUGIN" );
	} else {
		smg_assert( false !== strpos( $content, 'ABSPATH' ), "{$rel} checks ABSPATH" );
	}

	smg_assert( 0 === preg_match( '/\b(eval|create_function|extract|base64_decode)\s*\(/', $content ), "{$rel} has no forbidden PHP functions" );
	smg_assert( 0 === preg_match( '/<\?(?!php|=)/', $content ), "{$rel} has no short open tags" );
}

// 2. Validate readme.txt structure.
$readme = file_get_contents( $root . '/readme.txt' );
smg_assert( 0 === strpos( $readme, '=== Site Maintenance Guard ===' ), 'readme.txt starts with plugin title' );
smg_assert( false !== strpos( $readme, 'Stable tag: 1.1.0' ), 'readme.txt has Stable tag' );
smg_assert( false !== strpos( $readme, 'License: GPLv2 or later' ), 'readme.txt has GPLv2 or later license' );
smg_assert( false !== strpos( $readme, '== Description ==' ), 'readme.txt has Description section' );
smg_assert( false !== strpos( $readme, '== Installation ==' ), 'readme.txt has Installation section' );
smg_assert( false !== strpos( $readme, '== Changelog ==' ), 'readme.txt has Changelog section' );

$lines = explode( "\n", $readme );
$short_desc = '';
foreach ( $lines as $idx => $line ) {
	if ( '' === trim( $line ) && $idx > 4 ) {
		$short_desc = trim( $lines[ $idx + 1 ] ?? '' );
		break;
	}
}
smg_assert( '' !== $short_desc && strlen( $short_desc ) <= 150, 'readme.txt short description is <= 150 chars (' . strlen( $short_desc ) . ')' );

// 3. Mock WordPress functions to test SMG_Settings and SMG_Frontend logic.
define( 'ABSPATH', $root . '/' );
define( 'SMG_OPTION_NAME', 'smg_settings' );
define( 'SMG_VERSION', '1.1.0' );
define( 'SMG_PLUGIN_FILE', $root . '/site-maintenance-guard.php' );
define( 'SMG_PLUGIN_DIR', $root . '/' );
define( 'SMG_PLUGIN_URL', 'https://example.com/wp-content/plugins/site-maintenance-guard/' );

$GLOBALS['smg_mock_options'] = array();
$GLOBALS['smg_mock_logged_in'] = false;

function __( $text, $domain = 'default' ) { return $text; }
function add_action( $hook, $cb, $prio = 10, $args = 1 ) {}
function add_filter( $hook, $cb, $prio = 10, $args = 1 ) {}
function apply_filters( $hook, $value, ...$args ) { return $value; }
function get_option( $name, $default = false ) { return $GLOBALS['smg_mock_options'][ $name ] ?? $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function wp_generate_password( $len = 12, $special = true, $extra = false ) { return str_repeat( 'a', $len ); }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
function sanitize_text_field( $str ) { return trim( strip_tags( (string) $str ) ); }
function sanitize_hex_color( $color ) { return preg_match( '/^#[a-f0-9]{6}$/i', (string) $color ) ? $color : ''; }
function wp_kses_post( $data ) { return preg_replace( '#<script(.*?)>(.*?)</script>#is', '', (string) $data ); }
function wp_strip_all_tags( $str ) { return strip_tags( (string) $str ); }
function absint( $val ) { return abs( (int) $val ); }
function wp_unslash( $val ) { return $val; }
function wp_roles() {
	$obj = new stdClass();
	$obj->roles = array(
		'administrator' => array( 'name' => 'Administrator' ),
		'editor'        => array( 'name' => 'Editor' ),
		'subscriber'    => array( 'name' => 'Subscriber' ),
	);
	return $obj;
}
function is_user_logged_in() { return $GLOBALS['smg_mock_logged_in']; }
function wp_get_current_user() {
	$u = new stdClass();
	$u->roles = array();
	return $u;
}
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function untrailingslashit( $str ) { return rtrim( $str, '/\\' ); }
class WP_Error {
	public $code;
	public $message;
	public $data;
	public function __construct( $code, $message, $data = array() ) {
		$this->code = $code;
		$this->message = $message;
		$this->data = $data;
	}
}

require_once $root . '/includes/class-smg-settings.php';
require_once $root . '/includes/class-smg-frontend.php';

$settings_handler = new SMG_Settings();
$sanitized = $settings_handler->sanitize_settings(
	array(
		'enabled'        => '1',
		'mode'           => 'invalid_mode',
		'retry_after'    => '5',
		'status_badge'   => '<b>Scheduled Update</b>',
		'page_title'     => '<script>alert(1)</script>Maintenance',
		'heading'        => '<b>Back Soon</b>',
		'message'        => '<p>Hello</p><script>evil()</script>',
		'return_time'    => 'Today at 14:30 CET',
		'contact_info'   => 'support@example.com',
		'visual_preset'  => 'teaser_macro',
		'color_scheme'   => 'auto',
		'bg_color'       => 'not-a-color',
		'accent_color'   => '#123abc',
		'custom_css'     => 'body { color: red; }</style><script>x()</script>',
		'allowed_roles'  => array( 'editor', 'nonexistent_role' ),
		'excluded_paths' => "privacy-policy\n/contact/",
	)
);

smg_assert( true === $sanitized['enabled'], 'Sanitize sets enabled boolean' );
smg_assert( 'maintenance' === $sanitized['mode'], 'Sanitize falls back invalid mode to maintenance' );
smg_assert( 60 === $sanitized['retry_after'], 'Sanitize clamps retry_after minimum to 60' );
smg_assert( 'Scheduled Update' === $sanitized['status_badge'], 'Sanitize strips tags from status_badge' );
smg_assert( 'alert(1)Maintenance' === $sanitized['page_title'], 'Sanitize strips tags from page_title' );
smg_assert( 'Back Soon' === $sanitized['heading'], 'Sanitize strips tags from heading' );
smg_assert( '<p>Hello</p>' === $sanitized['message'], 'Sanitize strips script tags from message' );
smg_assert( 'Today at 14:30 CET' === $sanitized['return_time'], 'Sanitize preserves valid return_time' );
smg_assert( 'support@example.com' === $sanitized['contact_info'], 'Sanitize preserves valid contact_info' );
smg_assert( 'teaser_macro' === $sanitized['visual_preset'], 'Sanitize preserves valid visual_preset' );
smg_assert( 'auto' === $sanitized['color_scheme'], 'Sanitize preserves valid color_scheme' );
smg_assert( '#f4f6f8' === $sanitized['bg_color'], 'Sanitize falls back invalid hex color to default' );
smg_assert( '#123abc' === $sanitized['accent_color'], 'Sanitize preserves valid hex color' );
smg_assert( false === strpos( $sanitized['custom_css'], '<script>' ), 'Sanitize strips tags from custom_css' );
smg_assert( in_array( 'administrator', $sanitized['allowed_roles'], true ), 'Sanitize always keeps administrator role' );
smg_assert( in_array( 'editor', $sanitized['allowed_roles'], true ), 'Sanitize keeps valid editor role' );
smg_assert( ! in_array( 'nonexistent_role', $sanitized['allowed_roles'], true ), 'Sanitize drops invalid roles' );
smg_assert( "/privacy-policy\n/contact/" === $sanitized['excluded_paths'], 'Sanitize normalizes excluded_paths' );

$invalid_visual = $settings_handler->sanitize_settings(
	array(
		'visual_preset' => 'nonexistent_preset',
		'color_scheme'  => 'neon_pink',
	)
);
smg_assert( 'none' === $invalid_visual['visual_preset'], 'Sanitize falls back invalid visual_preset to none' );
smg_assert( 'light' === $invalid_visual['color_scheme'], 'Sanitize falls back invalid color_scheme to light' );

foreach ( SMG_Settings::get_visual_presets() as $preset_key => $preset ) {
	if ( '' !== $preset['file'] ) {
		$asset_path = $root . '/assets/images/' . $preset['file'];
		smg_assert( file_exists( $asset_path ), "Visual preset asset exists: {$preset['file']}" );
		smg_assert( filesize( $asset_path ) < 102400, "Visual preset asset < 100 KB: {$preset['file']}" );
	}
}

$frontend = new SMG_Frontend();
$test_settings = array_merge(
	SMG_Settings::get_defaults(),
	array(
		'enabled'        => true,
		'bypass_enabled' => true,
		'bypass_token'   => 'secret123456',
		'excluded_paths' => '/privacy-policy',
	)
);

smg_assert( false === $frontend->should_bypass( $test_settings ), 'Visitor without cookie/path is blocked' );

$_COOKIE[ SMG_Frontend::BYPASS_COOKIE ] = 'wrong_token';
smg_assert( false === $frontend->should_bypass( $test_settings ), 'Visitor with wrong bypass cookie is blocked' );

$_COOKIE[ SMG_Frontend::BYPASS_COOKIE ] = hash( 'sha256', 'secret123456' );
smg_assert( true === $frontend->should_bypass( $test_settings ), 'Visitor with valid hashed bypass cookie can bypass' );
unset( $_COOKIE[ SMG_Frontend::BYPASS_COOKIE ] );

$_SERVER['REQUEST_URI'] = '/privacy-policy/';
smg_assert( true === $frontend->should_bypass( $test_settings ), 'Visitor on excluded path can bypass' );
unset( $_SERVER['REQUEST_URI'] );

$rest_result = $frontend->maybe_restrict_rest_api( null );
smg_assert( true === ( null === $rest_result ), 'REST API not restricted when plugin disabled in DB' );

$GLOBALS['smg_mock_options'][ SMG_OPTION_NAME ] = $test_settings;
$rest_blocked = $frontend->maybe_restrict_rest_api( null );
smg_assert( $rest_blocked instanceof WP_Error && 503 === $rest_blocked->data['status'], 'REST API returns WP_Error 503 when maintenance active' );

if ( $failures > 0 ) {
	echo "\nTotal failures: {$failures}\n";
	exit( 1 );
}

echo "\nAll verification checks passed!\n";
exit( 0 );
