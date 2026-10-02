<?php
/**
 * Şablon yardımcıları.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

/**
 * Satır içi SVG ikonu.
 *
 * @param string $name  İkon adı.
 * @param int    $size  Boyut.
 * @return string
 */
function nakliye_icon( $name, $size = 20 ) {
	$paths = array(
		'phone'     => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.1 9.9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'map'       => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'truck'     => '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
		'box'       => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"/>',
		'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'building'  => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
		'warehouse' => '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35z"/><path d="M6 18h12M6 14h12M6 10h12"/>',
		'piano'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 4v10M10 4v10M14 4v10M18 4v10M2 14h20"/>',
		'globe'     => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
		'lift'      => '<path d="M4 22V2M4 6h12l4 4M10 6v12M8 18h6"/>',
		'check'     => '<path d="M20 6 9 17l-5-5"/>',
		'arrow'     => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'search'    => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
		'menu'      => '<path d="M3 12h18M3 6h18M3 18h18"/>',
		'close'     => '<path d="M18 6 6 18M6 6l12 12"/>',
		'star'      => '<path fill="currentColor" d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>',
		'up'        => '<path d="m18 15-6-6-6 6"/>',
		'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
		'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'whatsapp'  => '<path fill="currentColor" stroke="none" d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89a11.82 11.82 0 0 0-3.48-8.41"/>',
		'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37zM17.5 6.5h.01"/>',
		'x'         => '<path d="M4 4l16 16M20 4 4 20"/>',
		'youtube'   => '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><path d="m9.75 15.02 5.75-3.27-5.75-3.27v6.54z"/>',
		'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="nk-icon nk-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Logo veya site adı.
 *
 * @param bool $light Açık renk logo.
 */
function nakliye_site_logo( $light = false ) {
	$url = nakliye_logo_url( $light );
	echo '<a class="nk-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
	} else {
		echo '<span class="nk-logo__mark">' . nakliye_icon( 'truck', 26 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<span class="nk-logo__text">' . esc_html( nakliye_option( 'company_name', get_bloginfo( 'name' ) ) ) . '</span>';
	}
	echo '</a>';
}

/**
 * Sosyal medya bağlantıları.
 */
function nakliye_social_links() {
	$networks = array( 'facebook', 'instagram', 'x', 'youtube', 'linkedin' );
	$out      = '';
	foreach ( $networks as $network ) {
		$url = nakliye_option( $network );
		if ( $url ) {
			$out .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( ucfirst( $network ) ) . '">' . nakliye_icon( $network, 16 ) . '</a>';
		}
	}
	if ( $out ) {
		echo '<div class="nk-social">' . $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * tel: bağlantısı için temiz numara.
 *
 * @param string $phone Telefon.
 * @return string
 */
function nakliye_tel( $phone ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', (string) $phone );
}

/**
 * WhatsApp bağlantısı.
 *
 * @param string $text Ön dolu mesaj.
 * @return string
 */
function nakliye_whatsapp_url( $text = '' ) {
	$number = preg_replace( '/\D/', '', (string) nakliye_option( 'whatsapp' ) );
	$text   = $text ? $text : __( 'Merhaba, taşıma için fiyat teklifi almak istiyorum.', 'nakliye' );
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( $text );
}

/**
 * Telif metni.
 *
 * @return string
 */
function nakliye_copyright() {
	return str_replace(
		array( '{year}', '{site}' ),
		array( gmdate( 'Y' ), get_bloginfo( 'name' ) ),
		(string) nakliye_option( 'copyright' )
	);
}

/**
 * Sayfa başlığı alanı + içerik yolu (breadcrumb).
 *
 * @param string|null $title Başlık.
 */
function nakliye_page_header( $title = null ) {
	if ( null === $title ) {
		if ( is_home() && ! is_front_page() ) {
			$title = single_post_title( '', false );
		} elseif ( is_archive() ) {
			$title = get_the_archive_title();
		} elseif ( is_search() ) {
			/* translators: %s: arama ifadesi */
			$title = sprintf( __( 'Arama: %s', 'nakliye' ), get_search_query() );
		} elseif ( is_404() ) {
			$title = __( 'Sayfa bulunamadı', 'nakliye' );
		} else {
			$title = get_the_title();
		}
	}
	?>
	<header class="nk-page-header">
		<div class="nk-container">
			<h1 class="nk-page-header__title"><?php echo wp_kses_post( $title ); ?></h1>
			<?php nakliye_breadcrumbs(); ?>
		</div>
	</header>
	<?php
}

/**
 * İçerik yolu.
 */
function nakliye_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Ana Sayfa', 'nakliye' ) . '</a>' );

	if ( is_singular( 'nakliye_hizmet' ) ) {
		$items[] = '<a href="' . esc_url( get_post_type_archive_link( 'nakliye_hizmet' ) ) . '">' . esc_html__( 'Hizmetler', 'nakliye' ) . '</a>';
	} elseif ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = '<a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
		}
	}

	if ( is_singular() ) {
		$items[] = '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span>' . wp_strip_all_tags( get_the_archive_title() ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span>' . esc_html__( 'Arama sonuçları', 'nakliye' ) . '</span>';
	} elseif ( is_404() ) {
		$items[] = '<span>404</span>';
	} elseif ( is_home() ) {
		$items[] = '<span>' . esc_html( single_post_title( '', false ) ) . '</span>';
	}

	echo '<nav class="nk-breadcrumbs" aria-label="' . esc_attr__( 'İçerik yolu', 'nakliye' ) . '">' . implode( '<span class="sep">/</span>', $items ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Yazı bilgisi.
 */
function nakliye_post_meta() {
	printf(
		'<div class="nk-post-meta">%1$s <time datetime="%2$s">%3$s</time> <span>·</span> %4$s</div>',
		nakliye_icon( 'calendar', 14 ), // phpcs:ignore WordPress.Security.EscapeOutput
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_html( get_the_author() )
	);
}

add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * Menü atanmamışsa sayfa listesini gösterir.
 */
function nakliye_menu_fallback() {
	echo '<ul class="nk-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Ana Sayfa', 'nakliye' ) . '</a></li>';
	wp_list_pages( array( 'title_li' => '', 'depth' => 1, 'number' => 6 ) );
	echo '</ul>';
}

/**
 * Hizmet kartı.
 *
 * @param WP_Post|int $post Hizmet.
 */
function nakliye_service_card( $post ) {
	$post = get_post( $post );
	$icon = get_post_meta( $post->ID, '_nk_icon', true );
	?>
	<article class="nk-service-card">
		<?php if ( has_post_thumbnail( $post ) ) : ?>
			<a class="nk-service-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo get_the_post_thumbnail( $post, 'nakliye-card' ); ?></a>
		<?php endif; ?>
		<div class="nk-service-card__body">
			<span class="nk-service-card__icon"><?php echo nakliye_render_icon( $icon ? $icon : 'truck' ); // phpcs:ignore ?></span>
			<h3 class="nk-service-card__title"><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<p><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
			<a class="nk-link-arrow" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Detaylı bilgi', 'nakliye' ); ?> <?php echo nakliye_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
		</div>
	</article>
	<?php
}

/**
 * İkon değeri: tema ikon adı, dashicon sınıfı veya emoji.
 *
 * @param string $icon Değer.
 * @param int    $size Boyut.
 * @return string
 */
function nakliye_render_icon( $icon, $size = 32 ) {
	$svg = nakliye_icon( $icon, $size );
	if ( $svg ) {
		return $svg;
	}
	if ( 0 === strpos( $icon, 'dashicons' ) ) {
		return '<span class="dashicons ' . esc_attr( $icon ) . '"></span>';
	}
	return '<span class="nk-emoji">' . esc_html( $icon ) . '</span>';
}
