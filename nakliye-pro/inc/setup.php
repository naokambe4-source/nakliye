<?php
/**
 * Tema kurulumu: destekler, menüler, widget alanları, dosyalar.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'nakliye_setup' );
/**
 * Tema destekleri.
 */
function nakliye_setup() {
	load_theme_textdomain( 'nakliye', NAKLIYE_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'editor-styles' );

	// Elementor uyumu.
	add_theme_support( 'elementor' );
	add_theme_support( 'header-footer-elementor' );

	add_image_size( 'nakliye-card', 640, 440, true );
	add_image_size( 'nakliye-wide', 1280, 640, true );

	register_nav_menus(
		array(
			'primary' => __( 'Ana Menü', 'nakliye' ),
			'topbar'  => __( 'Üst Çubuk Menüsü', 'nakliye' ),
			'footer'  => __( 'Alt Menü', 'nakliye' ),
		)
	);

	$GLOBALS['content_width'] = 1200;
}

add_action( 'widgets_init', 'nakliye_widgets_init' );
/**
 * Widget alanları.
 */
function nakliye_widgets_init() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	);

	register_sidebar( array_merge( $common, array( 'name' => __( 'Kenar Çubuğu', 'nakliye' ), 'id' => 'sidebar-1' ) ) );
	register_sidebar( array_merge( $common, array( 'name' => __( 'Hizmet Kenar Çubuğu', 'nakliye' ), 'id' => 'sidebar-service' ) ) );

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar(
			array_merge(
				$common,
				array(
					/* translators: %d: sütun numarası */
					'name' => sprintf( __( 'Alt Alan %d. Sütun', 'nakliye' ), $i ),
					'id'   => 'footer-' . $i,
				)
			)
		);
	}
}

add_action( 'wp_enqueue_scripts', 'nakliye_enqueue' );
/**
 * Ön yüz dosyaları.
 */
function nakliye_enqueue() {
	$fonts = nakliye_fonts_url();
	if ( $fonts ) {
		wp_enqueue_style( 'nakliye-fonts', $fonts, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
	wp_enqueue_style( 'nakliye-main', NAKLIYE_URI . '/assets/css/main.css', array(), NAKLIYE_VERSION );
	wp_add_inline_style( 'nakliye-main', nakliye_dynamic_css() );

	wp_enqueue_script( 'nakliye-main', NAKLIYE_URI . '/assets/js/main.js', array(), NAKLIYE_VERSION, true );
	wp_localize_script(
		'nakliye-main',
		'NakliyeData',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'nakliye_front' ),
			'currency' => nakliye_option( 'price_currency' ),
			'pricing'  => nakliye_pricing_config(),
			'i18n'     => array(
				'sending'  => __( 'Gönderiliyor…', 'nakliye' ),
				'error'    => __( 'Bir hata oluştu, lütfen tekrar deneyin.', 'nakliye' ),
				'required' => __( 'Lütfen zorunlu alanları doldurun.', 'nakliye' ),
				'notFound' => __( 'Bu takip numarasıyla kayıt bulunamadı.', 'nakliye' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

/**
 * Fiyat hesaplayıcı yapılandırması (JS'e aktarılır).
 *
 * @return array
 */
function nakliye_pricing_config() {
	return array(
		'base'      => array(
			'1+1' => (float) nakliye_option( 'price_1_1' ),
			'2+1' => (float) nakliye_option( 'price_2_1' ),
			'3+1' => (float) nakliye_option( 'price_3_1' ),
			'4+1' => (float) nakliye_option( 'price_4_1' ),
		),
		'perKm'     => (float) nakliye_option( 'price_per_km' ),
		'perFloor'  => (float) nakliye_option( 'price_per_floor' ),
		'packing'   => (float) nakliye_option( 'price_packing' ),
		'insurance' => (float) nakliye_option( 'price_insurance' ),
		'lift'      => (float) nakliye_option( 'price_lift' ),
	);
}

add_action( 'wp_head', 'nakliye_head_code', 99 );
/**
 * Kullanıcının head kodu (yalnızca unfiltered_html yetkisiyle kaydedilebilir).
 */
function nakliye_head_code() {
	$code = nakliye_option( 'head_code' );
	if ( $code ) {
		echo "\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

add_filter( 'body_class', 'nakliye_body_classes' );
/**
 * @param array $classes Sınıflar.
 * @return array
 */
function nakliye_body_classes( $classes ) {
	$classes[] = 'nk-header-' . sanitize_html_class( nakliye_option( 'header_style' ) );
	if ( nakliye_option( 'sticky_header' ) ) {
		$classes[] = 'nk-sticky';
	}
	if ( is_page_template( 'page-templates/template-fullwidth.php' ) || nakliye_is_elementor_page() ) {
		$classes[] = 'nk-fullwidth';
	}
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'nk-no-sidebar';
	}
	return $classes;
}

/**
 * Sayfa Elementor ile mi düzenlenmiş?
 *
 * @param int|null $post_id Yazı kimliği.
 * @return bool
 */
function nakliye_is_elementor_page( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	return $post_id && did_action( 'elementor/loaded' ) && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

add_action( 'template_redirect', 'nakliye_maintenance_mode' );
/**
 * Bakım modu.
 */
function nakliye_maintenance_mode() {
	if ( ! nakliye_option( 'maintenance' ) || current_user_can( 'edit_posts' ) ) {
		return;
	}
	status_header( 503 );
	header( 'Retry-After: 3600' );
	get_template_part( 'template-parts/maintenance' );
	exit;
}

add_action( 'login_enqueue_scripts', 'nakliye_login_branding' );
/**
 * Markalı giriş ekranı.
 */
function nakliye_login_branding() {
	if ( ! nakliye_option( 'custom_login' ) ) {
		return;
	}
	$logo = nakliye_logo_url();
	?>
	<style>
		body.login{background:linear-gradient(135deg,<?php echo esc_attr( nakliye_option( 'color_secondary' ) ); ?> 0%,#020617 100%);}
		body.login #login h1 a{<?php echo $logo ? 'background-image:url(' . esc_url( $logo ) . ');' : ''; ?>background-size:contain;width:100%;height:80px;}
		body.login form{border-radius:14px;border:0;box-shadow:0 20px 50px rgba(0,0,0,.35);}
		body.login .button-primary{background:<?php echo esc_attr( nakliye_option( 'color_primary' ) ); ?>;border-color:<?php echo esc_attr( nakliye_option( 'color_primary' ) ); ?>;}
		body.login #nav a,body.login #backtoblog a{color:#cbd5e1;}
	</style>
	<?php
}
add_filter( 'login_headerurl', static function () { return home_url( '/' ); } );
add_filter( 'login_headertext', static function () { return get_bloginfo( 'name' ); } );

/**
 * Logo adresi (tema ayarı → özel logo → boş).
 *
 * @param bool $light Koyu zemin için açık logo.
 * @return string
 */
function nakliye_logo_url( $light = false ) {
	$id = $light && nakliye_option( 'logo_light' ) ? nakliye_option( 'logo_light' ) : nakliye_option( 'logo' );
	if ( ! $id ) {
		$id = get_theme_mod( 'custom_logo' );
	}
	return $id ? (string) wp_get_attachment_image_url( (int) $id, 'full' ) : '';
}

add_filter( 'excerpt_length', static function () { return 24; } );
add_filter( 'excerpt_more', static function () { return '…'; } );

add_action( 'after_switch_theme', 'nakliye_set_turkish_locale' );
/**
 * Site dilini Türkçeye çevirir ve WordPress + Elementor dil paketlerini indirir.
 *
 * Yönetim panelindeki menüler, blog, yorum formu gibi WordPress'in kendi
 * metinleri yalnızca site dili tr_TR olduğunda Türkçe görünür. Tema bu ayarı
 * etkinleştirilince otomatik yapar.
 */
function nakliye_set_turkish_locale() {
	if ( 'tr_TR' === get_locale() ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/translation-install.php';

	// WordPress çekirdek dil paketini indir.
	if ( function_exists( 'wp_download_language_pack' ) ) {
		$pack = wp_download_language_pack( 'tr_TR' );
		if ( $pack ) {
			update_option( 'WPLANG', 'tr_TR' );
			if ( function_exists( 'switch_to_locale' ) ) {
				switch_to_locale( 'tr_TR' );
			}
		}
	}

	// Kurulu eklentilerin (Elementor dahil) Türkçe paketlerini indir.
	if ( function_exists( 'wp_get_available_translations' ) ) {
		nakliye_update_translations();
	}
}

/**
 * Eklenti/tema çevirilerini günceller.
 */
function nakliye_update_translations() {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	if ( ! class_exists( 'Language_Pack_Upgrader' ) ) {
		return;
	}
	$upgrader = new Language_Pack_Upgrader( new Automatic_Upgrader_Skin() );
	$upgrader->bulk_upgrade();
}
