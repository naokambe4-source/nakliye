<?php
/**
 * Yüzen WhatsApp / arama / yukarı çık butonları.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="nk-float">
	<?php if ( nakliye_option( 'float_whatsapp' ) && nakliye_option( 'whatsapp' ) ) : ?>
		<a class="nk-float__btn nk-float__btn--wa" href="<?php echo esc_url( nakliye_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo nakliye_icon( 'whatsapp', 28 ); // phpcs:ignore ?></a>
	<?php endif; ?>
	<?php if ( nakliye_option( 'float_call' ) && nakliye_option( 'phone' ) ) : ?>
		<a class="nk-float__btn nk-float__btn--call" href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>" aria-label="<?php esc_attr_e( 'Ara', 'nakliye' ); ?>"><?php echo nakliye_icon( 'phone', 24 ); // phpcs:ignore ?></a>
	<?php endif; ?>
	<?php if ( nakliye_option( 'back_to_top' ) ) : ?>
		<button class="nk-float__btn nk-float__btn--top" type="button" aria-label="<?php esc_attr_e( 'Yukarı çık', 'nakliye' ); ?>"><?php echo nakliye_icon( 'up', 22 ); // phpcs:ignore ?></button>
	<?php endif; ?>
</div>
