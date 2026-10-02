<?php
/**
 * Varsayılan üst alan.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="nk-header" id="nk-header">
	<?php if ( nakliye_option( 'topbar' ) ) : ?>
		<div class="nk-topbar">
			<div class="nk-container nk-topbar__inner">
				<div class="nk-topbar__info">
					<?php if ( nakliye_option( 'phone' ) ) : ?>
						<a href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo nakliye_icon( 'phone', 14 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'phone' ) ); ?></a>
					<?php endif; ?>
					<?php if ( nakliye_option( 'email' ) ) : ?>
						<a href="mailto:<?php echo esc_attr( nakliye_option( 'email' ) ); ?>"><?php echo nakliye_icon( 'mail', 14 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'email' ) ); ?></a>
					<?php endif; ?>
					<?php if ( nakliye_option( 'hours' ) ) : ?>
						<span class="nk-hide-sm"><?php echo nakliye_icon( 'clock', 14 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'hours' ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="nk-topbar__right">
					<?php if ( nakliye_option( 'topbar_text' ) ) : ?>
						<span class="nk-topbar__note nk-hide-sm"><?php echo esc_html( nakliye_option( 'topbar_text' ) ); ?></span>
					<?php endif; ?>
					<?php nakliye_social_links(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="nk-navbar">
		<div class="nk-container nk-navbar__inner">
			<?php nakliye_site_logo( 'dark' === nakliye_option( 'header_style' ) ); ?>

			<nav class="nk-nav" id="nk-nav" aria-label="<?php esc_attr_e( 'Ana menü', 'nakliye' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'nk-menu',
						'fallback_cb'    => 'nakliye_menu_fallback',
						'depth'          => 3,
					)
				);
				?>
				<?php if ( nakliye_option( 'header_cta' ) ) : ?>
					<a class="nk-btn nk-btn--primary nk-nav__cta-mobile" href="<?php echo esc_url( nakliye_option( 'header_cta_url' ) ); ?>"><?php echo esc_html( nakliye_option( 'header_cta_text' ) ); ?></a>
				<?php endif; ?>
			</nav>

			<div class="nk-navbar__actions">
				<?php if ( nakliye_option( 'phone' ) ) : ?>
					<a class="nk-navbar__phone" href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>">
						<span class="nk-navbar__phone-icon"><?php echo nakliye_icon( 'phone', 18 ); // phpcs:ignore ?></span>
						<span class="nk-navbar__phone-text"><small><?php esc_html_e( 'Hemen arayın', 'nakliye' ); ?></small><?php echo esc_html( nakliye_option( 'phone' ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( nakliye_option( 'header_cta' ) ) : ?>
					<a class="nk-btn nk-btn--primary nk-navbar__cta" href="<?php echo esc_url( nakliye_option( 'header_cta_url' ) ); ?>"><?php echo esc_html( nakliye_option( 'header_cta_text' ) ); ?></a>
				<?php endif; ?>
				<button class="nk-burger" type="button" aria-controls="nk-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menüyü aç', 'nakliye' ); ?>">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</div>
</header>
