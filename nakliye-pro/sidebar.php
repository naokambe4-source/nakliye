<?php
/**
 * Kenar çubuğu.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="nk-sidebar" aria-label="<?php esc_attr_e( 'Kenar çubuğu', 'nakliye' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
