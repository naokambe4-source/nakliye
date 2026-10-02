<?php
/**
 * Varsayılan alt alan.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

$columns = (int) nakliye_option( 'footer_columns' );
?>
<?php if ( nakliye_option( 'footer_cta' ) && ! is_page_template( 'page-templates/template-canvas.php' ) ) : ?>
	<section class="nk-footer-cta">
		<div class="nk-container nk-footer-cta__inner">
			<div>
				<h2><?php esc_html_e( 'Taşınmayı dert etmeyin, biz halledelim.', 'nakliye' ); ?></h2>
				<p><?php esc_html_e( 'Ücretsiz ekspertiz ve sigortalı taşıma için hemen teklif alın.', 'nakliye' ); ?></p>
			</div>
			<div class="nk-footer-cta__actions">
				<a class="nk-btn nk-btn--light" href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo nakliye_icon( 'phone', 18 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'phone' ) ); ?></a>
				<a class="nk-btn nk-btn--dark" href="<?php echo esc_url( nakliye_option( 'header_cta_url' ) ); ?>"><?php echo esc_html( nakliye_option( 'header_cta_text' ) ); ?> <?php echo nakliye_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
			</div>
		</div>
	</section>
<?php endif; ?>

<footer class="nk-footer">
	<div class="nk-container">
		<div class="nk-footer__grid nk-footer__grid--<?php echo esc_attr( $columns ); ?>">
			<?php for ( $i = 1; $i <= $columns; $i++ ) : ?>
				<div class="nk-footer__col">
					<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
						<?php dynamic_sidebar( 'footer-' . $i ); ?>
					<?php elseif ( 1 === $i ) : ?>
						<?php nakliye_site_logo( true ); ?>
						<p class="nk-footer__about"><?php echo esc_html( nakliye_option( 'footer_about' ) ); ?></p>
						<?php nakliye_social_links(); ?>
					<?php elseif ( 2 === $i ) : ?>
						<h3 class="widget-title"><?php esc_html_e( 'Hizmetlerimiz', 'nakliye' ); ?></h3>
						<ul class="nk-footer__links">
							<?php
							$services = get_posts( array( 'post_type' => 'nakliye_hizmet', 'numberposts' => 6, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
							foreach ( $services as $service ) {
								echo '<li><a href="' . esc_url( get_permalink( $service ) ) . '">' . esc_html( get_the_title( $service ) ) . '</a></li>';
							}
							?>
						</ul>
					<?php elseif ( $i === $columns ) : ?>
						<h3 class="widget-title"><?php esc_html_e( 'İletişim', 'nakliye' ); ?></h3>
						<ul class="nk-footer__contact">
							<li><?php echo nakliye_icon( 'map', 16 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'address' ) ); ?></li>
							<li><?php echo nakliye_icon( 'phone', 16 ); // phpcs:ignore ?> <a href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo esc_html( nakliye_option( 'phone' ) ); ?></a></li>
							<li><?php echo nakliye_icon( 'mail', 16 ); // phpcs:ignore ?> <a href="mailto:<?php echo esc_attr( nakliye_option( 'email' ) ); ?>"><?php echo esc_html( nakliye_option( 'email' ) ); ?></a></li>
							<li><?php echo nakliye_icon( 'clock', 16 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'hours' ) ); ?></li>
						</ul>
					<?php else : ?>
						<h3 class="widget-title"><?php esc_html_e( 'Kurumsal', 'nakliye' ); ?></h3>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => 'nk-footer__links',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
						?>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>
	<div class="nk-footer__bottom">
		<div class="nk-container nk-footer__bottom-inner">
			<p><?php echo esc_html( nakliye_copyright() ); ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'topbar',
					'container'      => false,
					'menu_class'     => 'nk-footer__legal',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
	</div>
</footer>
