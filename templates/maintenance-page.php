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
<body class="smg-body">
	<main class="smg-container" role="main">
		<section class="smg-card" aria-labelledby="smg-heading">
			<?php if ( ! empty( $template_vars['logo_url'] ) ) : ?>
				<div class="smg-logo">
					<img src="<?php echo esc_url( (string) $template_vars['logo_url'] ); ?>" alt="<?php echo esc_attr( (string) $template_vars['site_name'] ); ?>" />
				</div>
			<?php endif; ?>

			<h1 id="smg-heading" class="smg-heading">
				<?php echo esc_html( (string) $template_vars['heading'] ); ?>
			</h1>

			<div class="smg-message">
				<?php echo wp_kses_post( wpautop( (string) $template_vars['message'] ) ); ?>
			</div>
		</section>
	</main>
</body>
</html>
