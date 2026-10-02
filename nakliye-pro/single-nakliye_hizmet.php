<?php
/**
 * Tekil hizmet sayfası.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	if ( nakliye_is_elementor_page() ) :
		the_content();
		continue;
	endif;
	nakliye_page_header();
	$features = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( get_the_ID(), '_nk_features', true ) ) ) );
	$price    = get_post_meta( get_the_ID(), '_nk_price_from', true );
	?>
	<div class="nk-container nk-section nk-layout nk-layout--service">
		<article <?php post_class( 'nk-layout__content nk-entry' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="nk-entry__media"><?php the_post_thumbnail( 'nakliye-wide' ); ?></div>
			<?php endif; ?>
			<div class="nk-entry__content"><?php the_content(); ?></div>
			<?php if ( $features ) : ?>
				<ul class="nk-checklist">
					<?php foreach ( $features as $feature ) : ?>
						<li><?php echo nakliye_icon( 'check', 18 ); // phpcs:ignore ?> <?php echo esc_html( $feature ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</article>
		<aside class="nk-sidebar">
			<div class="nk-service-box">
				<h3><?php esc_html_e( 'Tüm Hizmetler', 'nakliye' ); ?></h3>
				<ul class="nk-service-nav">
					<?php
					$services = get_posts( array( 'post_type' => 'nakliye_hizmet', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
					foreach ( $services as $service ) {
						printf(
							'<li class="%1$s"><a href="%2$s">%3$s %4$s</a></li>',
							get_the_ID() === $service->ID ? 'is-active' : '',
							esc_url( get_permalink( $service ) ),
							esc_html( get_the_title( $service ) ),
							nakliye_icon( 'arrow', 14 ) // phpcs:ignore
						);
					}
					?>
				</ul>
			</div>
			<div class="nk-service-box nk-service-box--cta">
				<?php if ( $price ) : ?>
					<p class="nk-service-box__price"><small><?php esc_html_e( 'Başlayan fiyatlarla', 'nakliye' ); ?></small><?php echo esc_html( $price ); ?></p>
				<?php endif; ?>
				<h3><?php esc_html_e( 'Ücretsiz teklif alın', 'nakliye' ); ?></h3>
				<p><?php esc_html_e( '30 dakika içinde size dönüş yapalım.', 'nakliye' ); ?></p>
				<a class="nk-btn nk-btn--primary nk-btn--block" href="<?php echo esc_url( nakliye_option( 'header_cta_url' ) ); ?>"><?php echo esc_html( nakliye_option( 'header_cta_text' ) ); ?></a>
				<a class="nk-btn nk-btn--wa nk-btn--block" href="<?php echo esc_url( nakliye_whatsapp_url( get_the_title() . ' hakkında bilgi almak istiyorum.' ) ); ?>" target="_blank" rel="noopener"><?php echo nakliye_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
			</div>
			<?php dynamic_sidebar( 'sidebar-service' ); ?>
		</aside>
	</div>
	<?php
endwhile;

get_footer();
