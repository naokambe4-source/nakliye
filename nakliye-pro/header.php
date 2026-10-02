<?php
/**
 * Üst alan.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( nakliye_option( 'preloader' ) ) : ?>
	<div class="nk-preloader" aria-hidden="true"><span class="nk-preloader__truck"><?php echo nakliye_icon( 'truck', 42 ); // phpcs:ignore ?></span></div>
<?php endif; ?>
<a class="skip-link screen-reader-text" href="#nk-content"><?php esc_html_e( 'İçeriğe geç', 'nakliye' ); ?></a>

<?php
// Elementor Pro Tema Oluşturucu ile özel üst alan tasarlandıysa onu kullan.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) :
	get_template_part( 'template-parts/site-header' );
endif;
?>

<main id="nk-content" class="nk-main">
