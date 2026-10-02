<?php
/**
 * Sonuç yok.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="nk-empty">
	<h2><?php esc_html_e( 'Sonuç bulunamadı', 'nakliye' ); ?></h2>
	<p><?php esc_html_e( 'Farklı kelimelerle aramayı deneyin.', 'nakliye' ); ?></p>
	<?php get_search_form(); ?>
</div>
