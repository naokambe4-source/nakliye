<?php
/**
 * Template Name: Nakliye — Boş Tuval (Elementor)
 * Template Post Type: page
 *
 * Üst/alt alan olmadan yalnızca içerik. Kampanya ve açılış sayfaları içindir.
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
<body <?php body_class( 'nk-canvas' ); ?>>
<?php
wp_body_open();
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
get_template_part( 'template-parts/floating-buttons' );
wp_footer();
?>
</body>
</html>
