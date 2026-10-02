<?php
/**
 * Arama formu.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
$nk_id = wp_unique_id( 'nk-search-' );
?>
<form role="search" method="get" class="nk-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $nk_id ); ?>"><?php esc_html_e( 'Ara:', 'nakliye' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $nk_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Ne aramıştınız?', 'nakliye' ); ?>">
	<button type="submit" aria-label="<?php esc_attr_e( 'Ara', 'nakliye' ); ?>"><?php echo nakliye_icon( 'search', 18 ); // phpcs:ignore ?></button>
</form>
