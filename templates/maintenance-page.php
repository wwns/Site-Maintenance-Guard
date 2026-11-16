<?php
/**
 * Standalone Maintenance / Coming Soon HTML5 template.
 *
 * @package Site_Maintenance_Guard
 * @since   1.0.0
 *
 * @var array<string, mixed> $template_vars Variables passed from SMG_Frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$smg_color_scheme = isset( $template_vars['color_scheme'] ) && in_array( $template_vars['color_scheme'], array( 'light', 'dark', 'auto' ), true )
	? $template_vars['color_scheme']
	: 'light';
$smg_body_class   = 'smg-body smg-scheme-' . $smg_color_scheme;
$smg_contact      = isset( $template_vars['contact_info'] ) ? trim( (string) $template_vars['contact_info'] ) : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php if ( ! empty( $template_vars['noindex'] ) ) : ?>
		<meta name="robots" content="noindex, nofollow" />
	<?php endif; ?>
	<title><?php echo esc_html( (string) $template_vars['page_title'] ); ?></title>
	<?php wp_print_styles( 'smg-frontend' ); ?>
</head>
<body class="<?php echo esc_attr( $smg_body_class ); ?>">
	<main class="smg-container" role="main">
		<section class="smg-card" aria-labelledby="smg-heading">
			<?php if ( ! empty( $template_vars['logo_url'] ) ) : ?>
				<div class="smg-logo">
					<img src="<?php echo esc_url( (string) $template_vars['logo_url'] ); ?>" alt="<?php echo esc_attr( (string) $template_vars['site_name'] ); ?>" />
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $template_vars['status_badge'] ) ) : ?>
				<div class="smg-status-pill">
					<span class="smg-pulse-dot" aria-hidden="true"></span>
					<span><?php echo esc_html( (string) $template_vars['status_badge'] ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $template_vars['hero_image_url'] ) ) : ?>
				<div class="smg-hero">
					<img src="<?php echo esc_url( (string) $template_vars['hero_image_url'] ); ?>" alt="" decoding="async" />
				</div>
			<?php endif; ?>

			<h1 id="smg-heading" class="smg-heading">
				<?php echo esc_html( (string) $template_vars['heading'] ); ?>
			</h1>

			<div class="smg-message">
				<?php echo wp_kses_post( wpautop( (string) $template_vars['message'] ) ); ?>
			</div>

			<?php if ( ! empty( $template_vars['return_time'] ) || '' !== $smg_contact ) : ?>
				<div class="smg-meta">
					<?php if ( ! empty( $template_vars['return_time'] ) ) : ?>
						<div class="smg-meta-item">
							<span class="smg-meta-label"><?php esc_html_e( 'Expected return:', 'site-maintenance-guard' ); ?></span>
							<span class="smg-meta-value"><?php echo esc_html( (string) $template_vars['return_time'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $smg_contact ) : ?>
						<div class="smg-meta-item">
							<span class="smg-meta-label"><?php esc_html_e( 'Contact:', 'site-maintenance-guard' ); ?></span>
							<span class="smg-meta-value">
								<?php if ( is_email( $smg_contact ) ) : ?>
									<a href="<?php echo esc_url( 'mailto:' . $smg_contact ); ?>"><?php echo esc_html( $smg_contact ); ?></a>
								<?php elseif ( preg_match( '#^https?://#i', $smg_contact ) ) : ?>
									<a href="<?php echo esc_url( $smg_contact ); ?>" rel="noopener noreferrer"><?php echo esc_html( $smg_contact ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $smg_contact ); ?>
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>
	</main>
</body>
</html>
