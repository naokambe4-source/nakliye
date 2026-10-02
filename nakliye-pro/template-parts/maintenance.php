<?php
/**
 * Bakım modu sayfası.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'Bakımdayız', 'nakliye' ); ?></title>
	<style>
		body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,sans-serif;background:<?php echo esc_attr( nakliye_option( 'color_secondary' ) ); ?>;color:#fff;text-align:center;padding:24px}
		.box{max-width:520px} h1{font-size:2rem;margin:.5em 0} a{color:<?php echo esc_attr( nakliye_option( 'color_primary' ) ); ?>;font-weight:700;text-decoration:none}
		svg{color:<?php echo esc_attr( nakliye_option( 'color_primary' ) ); ?>}
	</style>
</head>
<body>
	<div class="box">
		<?php echo nakliye_icon( 'truck', 64 ); // phpcs:ignore ?>
		<h1><?php esc_html_e( 'Kısa bir moladayız', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Sitemiz güncelleniyor. Taşıma talepleriniz için bizi arayabilirsiniz:', 'nakliye' ); ?></p>
		<p><a href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo esc_html( nakliye_option( 'phone' ) ); ?></a></p>
	</div>
</body>
</html>
