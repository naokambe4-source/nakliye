<?php
/**
 * 404 sayfası.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="nk-404 nk-section">
	<div class="nk-container">
		<div class="nk-404__code">4<span><?php echo nakliye_icon( 'box', 96 ); // phpcs:ignore ?></span>4</div>
		<h1><?php esc_html_e( 'Aradığınız sayfa başka bir adrese taşınmış olabilir.', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Ana sayfaya dönebilir veya aşağıdan arama yapabilirsiniz.', 'nakliye' ); ?></p>
		<?php get_search_form(); ?>
		<a class="nk-btn nk-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ana sayfaya dön', 'nakliye' ); ?></a>
	</div>
</section>
<?php
get_footer();
