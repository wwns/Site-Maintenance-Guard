<?php
/**
 * Standalone Maintenance / Coming Soon HTML5 template.
 *
 * @package Gatedevx_Maintenance_Guard
 * @since   1.0.0
 *
 * @var array<string, mixed> $template_vars Variables passed from GTVX_Frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$gtvx_color_scheme = isset( $template_vars['color_scheme'] ) && in_array( $template_vars['color_scheme'], array( 'light', 'dark', 'auto' ), true )
	? $template_vars['color_scheme']
	: 'light';
$gtvx_body_class   = 'gtvx-body gtvx-scheme-' . $gtvx_color_scheme;
$gtvx_contact      = isset( $template_vars['contact_info'] ) ? trim( (string) $template_vars['contact_info'] ) : '';
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
	<?php wp_print_styles( 'gtvx-frontend' ); ?>
</head>
<body class="<?php echo esc_attr( $gtvx_body_class ); ?>">
	<main class="gtvx-container" role="main">
		<section class="gtvx-card" aria-labelledby="gtvx-heading">
			<?php if ( ! empty( $template_vars['logo_url'] ) ) : ?>
				<div class="gtvx-logo">
					<img src="<?php echo esc_url( (string) $template_vars['logo_url'] ); ?>" alt="<?php echo esc_attr( (string) $template_vars['site_name'] ); ?>" />
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $template_vars['status_badge'] ) ) : ?>
				<div class="gtvx-status-pill">
					<span class="gtvx-pulse-dot" aria-hidden="true"></span>
					<span><?php echo esc_html( (string) $template_vars['status_badge'] ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $template_vars['hero_image_url'] ) ) : ?>
				<div class="gtvx-hero">
					<img src="<?php echo esc_url( (string) $template_vars['hero_image_url'] ); ?>" alt="" decoding="async" />
				</div>
			<?php endif; ?>

			<h1 id="gtvx-heading" class="gtvx-heading">
				<?php echo esc_html( (string) $template_vars['heading'] ); ?>
			</h1>

			<div class="gtvx-message">
				<?php echo wp_kses_post( wpautop( (string) $template_vars['message'] ) ); ?>
			</div>

			<?php if ( ! empty( $template_vars['return_time'] ) || '' !== $gtvx_contact ) : ?>
				<div class="gtvx-meta">
					<?php if ( ! empty( $template_vars['return_time'] ) ) : ?>
						<div class="gtvx-meta-item">
							<span class="gtvx-meta-label"><?php esc_html_e( 'Expected return:', 'gatedevx-maintenance-guard' ); ?></span>
							<span class="gtvx-meta-value"><?php echo esc_html( (string) $template_vars['return_time'] ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $gtvx_contact ) : ?>
						<div class="gtvx-meta-item">
							<span class="gtvx-meta-label"><?php esc_html_e( 'Contact:', 'gatedevx-maintenance-guard' ); ?></span>
							<span class="gtvx-meta-value">
								<?php if ( is_email( $gtvx_contact ) ) : ?>
									<a href="<?php echo esc_url( 'mailto:' . $gtvx_contact ); ?>"><?php echo esc_html( $gtvx_contact ); ?></a>
								<?php elseif ( preg_match( '#^https?://#i', $gtvx_contact ) ) : ?>
									<a href="<?php echo esc_url( $gtvx_contact ); ?>" rel="noopener noreferrer"><?php echo esc_html( $gtvx_contact ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $gtvx_contact ); ?>
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
