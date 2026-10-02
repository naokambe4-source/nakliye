<?php
/**
 * Tema ayarları: varsayılanlar, okuma ve dinamik CSS.
 *
 * Tüm ayarlar tek bir `nakliye_options` seçeneğinde saklanır ve
 * Yönetim → Nakliye Pro → Tema Ayarları ekranından düzenlenir.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ayar şeması: sekme → alan tanımları. Yönetim formu ve temizleme bu şemadan üretilir.
 *
 * @return array
 */
function nakliye_options_schema() {
	return array(
		'general'  => array(
			'label'  => __( 'Genel', 'nakliye' ),
			'icon'   => 'dashicons-admin-generic',
			'fields' => array(
				'company_name'    => array( 'type' => 'text', 'label' => __( 'Firma adı', 'nakliye' ), 'default' => 'Nakliye Pro Taşımacılık' ),
				'logo'            => array( 'type' => 'image', 'label' => __( 'Logo', 'nakliye' ), 'default' => '' ),
				'logo_light'      => array( 'type' => 'image', 'label' => __( 'Açık renk logo (koyu zemin)', 'nakliye' ), 'default' => '' ),
				'logo_height'     => array( 'type' => 'number', 'label' => __( 'Logo yüksekliği (px)', 'nakliye' ), 'default' => 48 ),
				'preloader'       => array( 'type' => 'toggle', 'label' => __( 'Sayfa yükleme animasyonu', 'nakliye' ), 'default' => 1 ),
				'back_to_top'     => array( 'type' => 'toggle', 'label' => __( 'Yukarı çık butonu', 'nakliye' ), 'default' => 1 ),
			),
		),
		'colors'   => array(
			'label'  => __( 'Renkler & Yazı', 'nakliye' ),
			'icon'   => 'dashicons-art',
			'fields' => array(
				'color_primary'   => array( 'type' => 'color', 'label' => __( 'Ana renk', 'nakliye' ), 'default' => '#f97316' ),
				'color_secondary' => array( 'type' => 'color', 'label' => __( 'İkincil renk', 'nakliye' ), 'default' => '#0f2a4a' ),
				'color_accent'    => array( 'type' => 'color', 'label' => __( 'Vurgu rengi', 'nakliye' ), 'default' => '#facc15' ),
				'color_text'      => array( 'type' => 'color', 'label' => __( 'Metin rengi', 'nakliye' ), 'default' => '#334155' ),
				'color_heading'   => array( 'type' => 'color', 'label' => __( 'Başlık rengi', 'nakliye' ), 'default' => '#0f172a' ),
				'color_bg_alt'    => array( 'type' => 'color', 'label' => __( 'Alternatif zemin', 'nakliye' ), 'default' => '#f1f5f9' ),
				'font_body'       => array(
					'type'    => 'select',
					'label'   => __( 'Gövde yazı tipi', 'nakliye' ),
					'default' => 'Inter',
					'choices' => nakliye_font_choices(),
				),
				'font_heading'    => array(
					'type'    => 'select',
					'label'   => __( 'Başlık yazı tipi', 'nakliye' ),
					'default' => 'Sora',
					'choices' => nakliye_font_choices(),
				),
				'border_radius'   => array( 'type' => 'number', 'label' => __( 'Köşe yuvarlaklığı (px)', 'nakliye' ), 'default' => 16 ),
			),
		),
		'contact'  => array(
			'label'  => __( 'İletişim', 'nakliye' ),
			'icon'   => 'dashicons-phone',
			'fields' => array(
				'phone'           => array( 'type' => 'text', 'label' => __( 'Telefon', 'nakliye' ), 'default' => '0850 123 45 67' ),
				'phone_2'         => array( 'type' => 'text', 'label' => __( 'Telefon 2 / GSM', 'nakliye' ), 'default' => '0532 123 45 67' ),
				'whatsapp'        => array( 'type' => 'text', 'label' => __( 'WhatsApp numarası (90 ile)', 'nakliye' ), 'default' => '905321234567' ),
				'email'           => array( 'type' => 'email', 'label' => __( 'E-posta', 'nakliye' ), 'default' => 'info@example.com' ),
				'quote_email'     => array( 'type' => 'email', 'label' => __( 'Teklif bildirim e-postası', 'nakliye' ), 'default' => '' ),
				'address'         => array( 'type' => 'textarea', 'label' => __( 'Adres', 'nakliye' ), 'default' => 'Atatürk Cad. No:1, Kadıköy / İstanbul' ),
				'hours'           => array( 'type' => 'text', 'label' => __( 'Çalışma saatleri', 'nakliye' ), 'default' => 'Pzt - Cmt: 08:00 - 20:00' ),
				'map_embed'       => array( 'type' => 'textarea', 'label' => __( 'Google Harita embed URL', 'nakliye' ), 'default' => '' ),
				'float_whatsapp'  => array( 'type' => 'toggle', 'label' => __( 'Yüzen WhatsApp butonu', 'nakliye' ), 'default' => 1 ),
				'float_call'      => array( 'type' => 'toggle', 'label' => __( 'Yüzen arama butonu (mobil)', 'nakliye' ), 'default' => 1 ),
			),
		),
		'header'   => array(
			'label'  => __( 'Üst Alan', 'nakliye' ),
			'icon'   => 'dashicons-align-wide',
			'fields' => array(
				'topbar'          => array( 'type' => 'toggle', 'label' => __( 'Üst bilgi çubuğu', 'nakliye' ), 'default' => 1 ),
				'topbar_text'     => array( 'type' => 'text', 'label' => __( 'Üst çubuk duyurusu', 'nakliye' ), 'default' => 'Sigortalı ve garantili taşımacılık — 7/24 hizmet' ),
				'sticky_header'   => array( 'type' => 'toggle', 'label' => __( 'Yapışkan menü', 'nakliye' ), 'default' => 1 ),
				'header_style'    => array(
					'type'    => 'select',
					'label'   => __( 'Üst alan stili', 'nakliye' ),
					'default' => 'light',
					'choices' => array( 'light' => __( 'Açık', 'nakliye' ), 'dark' => __( 'Koyu', 'nakliye' ), 'transparent' => __( 'Şeffaf (ana sayfada)', 'nakliye' ) ),
				),
				'header_cta'      => array( 'type' => 'toggle', 'label' => __( 'Menü butonu', 'nakliye' ), 'default' => 1 ),
				'header_cta_text' => array( 'type' => 'text', 'label' => __( 'Buton yazısı', 'nakliye' ), 'default' => 'Ücretsiz Teklif Al' ),
				'header_cta_url'  => array( 'type' => 'url', 'label' => __( 'Buton bağlantısı', 'nakliye' ), 'default' => '/teklif-al/' ),
			),
		),
		'footer'   => array(
			'label'  => __( 'Alt Alan', 'nakliye' ),
			'icon'   => 'dashicons-align-full-width',
			'fields' => array(
				'footer_about'    => array( 'type' => 'textarea', 'label' => __( 'Firma tanıtım metni', 'nakliye' ), 'default' => '20 yılı aşkın tecrübemizle evden eve nakliyat, ofis taşıma ve şehirler arası lojistikte güvenilir çözüm ortağınız.' ),
				'footer_columns'  => array(
					'type'    => 'select',
					'label'   => __( 'Widget sütun sayısı', 'nakliye' ),
					'default' => '4',
					'choices' => array( '2' => '2', '3' => '3', '4' => '4' ),
				),
				'copyright'       => array( 'type' => 'text', 'label' => __( 'Telif metni', 'nakliye' ), 'default' => '© {year} {site}. Tüm hakları saklıdır.' ),
				'footer_cta'      => array( 'type' => 'toggle', 'label' => __( 'Alt alan üstünde teklif şeridi', 'nakliye' ), 'default' => 1 ),
			),
		),
		'social'   => array(
			'label'  => __( 'Sosyal Medya', 'nakliye' ),
			'icon'   => 'dashicons-share',
			'fields' => array(
				'facebook'  => array( 'type' => 'url', 'label' => 'Facebook', 'default' => '' ),
				'instagram' => array( 'type' => 'url', 'label' => 'Instagram', 'default' => '' ),
				'x'         => array( 'type' => 'url', 'label' => 'X (Twitter)', 'default' => '' ),
				'youtube'   => array( 'type' => 'url', 'label' => 'YouTube', 'default' => '' ),
				'linkedin'  => array( 'type' => 'url', 'label' => 'LinkedIn', 'default' => '' ),
			),
		),
		'pricing'  => array(
			'label'   => __( 'Fiyat Hesaplama', 'nakliye' ),
			'icon'    => 'dashicons-calculator',
			'premium' => true,
			'fields'  => array(
				'price_currency'  => array( 'type' => 'text', 'label' => __( 'Para birimi', 'nakliye' ), 'default' => '₺' ),
				'price_1_1'       => array( 'type' => 'number', 'label' => __( '1+1 taban fiyat', 'nakliye' ), 'default' => 6000 ),
				'price_2_1'       => array( 'type' => 'number', 'label' => __( '2+1 taban fiyat', 'nakliye' ), 'default' => 9000 ),
				'price_3_1'       => array( 'type' => 'number', 'label' => __( '3+1 taban fiyat', 'nakliye' ), 'default' => 12500 ),
				'price_4_1'       => array( 'type' => 'number', 'label' => __( '4+1 ve üzeri taban fiyat', 'nakliye' ), 'default' => 16500 ),
				'price_per_km'    => array( 'type' => 'number', 'label' => __( 'Km başı ücret (şehirlerarası)', 'nakliye' ), 'default' => 22 ),
				'price_per_floor' => array( 'type' => 'number', 'label' => __( 'Asansörsüz kat başı ücret', 'nakliye' ), 'default' => 450 ),
				'price_packing'   => array( 'type' => 'number', 'label' => __( 'Paketleme hizmeti (%)', 'nakliye' ), 'default' => 15 ),
				'price_insurance' => array( 'type' => 'number', 'label' => __( 'Sigorta (%)', 'nakliye' ), 'default' => 5 ),
				'price_lift'      => array( 'type' => 'number', 'label' => __( 'Asansörlü (dış cephe) taşıma ücreti', 'nakliye' ), 'default' => 2500 ),
				'price_note'      => array( 'type' => 'textarea', 'label' => __( 'Hesaplama alt notu', 'nakliye' ), 'default' => 'Hesaplanan tutar tahminidir. Kesin fiyat için ücretsiz ekspertiz talep edin.' ),
			),
		),
		'advanced' => array(
			'label'   => __( 'Gelişmiş', 'nakliye' ),
			'icon'    => 'dashicons-admin-tools',
			'fields'  => array(
				'schema'          => array( 'type' => 'toggle', 'label' => __( 'Yapısal veri (MovingCompany schema)', 'nakliye' ), 'default' => 1 ),
				'maintenance'     => array( 'type' => 'toggle', 'label' => __( 'Bakım modu (yalnızca yöneticiler siteyi görür)', 'nakliye' ), 'default' => 0 ),
				'custom_login'    => array( 'type' => 'toggle', 'label' => __( 'Markalı giriş ekranı', 'nakliye' ), 'default' => 1 ),
				'head_code'       => array( 'type' => 'code', 'label' => __( '&lt;head&gt; kodu (Analytics vb.)', 'nakliye' ), 'default' => '' ),
				'custom_css'      => array( 'type' => 'code', 'label' => __( 'Özel CSS', 'nakliye' ), 'default' => '' ),
			),
		),
	);
}

/**
 * @return array
 */
function nakliye_font_choices() {
	$fonts = array( 'Sora', 'Plus Jakarta Sans', 'Space Grotesk', 'Inter', 'Manrope', 'Poppins', 'Montserrat', 'Outfit', 'Figtree', 'DM Sans', 'system-ui' );
	return array_combine( $fonts, $fonts );
}

/**
 * Tüm varsayılanlar.
 *
 * @return array
 */
function nakliye_option_defaults() {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = array();
		foreach ( nakliye_options_schema() as $tab ) {
			foreach ( $tab['fields'] as $id => $field ) {
				$defaults[ $id ] = $field['default'];
			}
		}
	}
	return $defaults;
}

/**
 * Tek bir ayarı okur.
 *
 * @param string $key     Anahtar.
 * @param mixed  $default Yedek değer.
 * @return mixed
 */
function nakliye_option( $key, $default = null ) {
	static $options = null;
	if ( null === $options || doing_action( 'update_option_nakliye_options' ) ) {
		$saved   = get_option( 'nakliye_options', array() );
		$options = wp_parse_args( is_array( $saved ) ? $saved : array(), nakliye_option_defaults() );
	}
	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	return null !== $default ? $default : ( isset( $options[ $key ] ) ? $options[ $key ] : '' );
}

/**
 * Ayar dizisini şemaya göre temizler.
 *
 * @param array $input Girdi.
 * @return array
 */
function nakliye_sanitize_options( $input ) {
	$input  = is_array( $input ) ? $input : array();
	$output = get_option( 'nakliye_options', array() );
	$output = is_array( $output ) ? $output : array();

	foreach ( nakliye_options_schema() as $tab ) {
		foreach ( $tab['fields'] as $id => $field ) {
			if ( 'toggle' === $field['type'] ) {
				// Formda yer alan sekmelerde kapalı anahtarlar gönderilmez.
				if ( isset( $input['__tabs'] ) && in_array( $id, (array) $input['__tabs'], true ) ) {
					$output[ $id ] = empty( $input[ $id ] ) ? 0 : 1;
				}
				continue;
			}
			if ( ! array_key_exists( $id, $input ) ) {
				continue;
			}
			$value = wp_unslash( $input[ $id ] );
			switch ( $field['type'] ) {
				case 'color':
					$output[ $id ] = sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : $field['default'];
					break;
				case 'number':
					$output[ $id ] = is_numeric( $value ) ? 0 + $value : $field['default'];
					break;
				case 'email':
					$output[ $id ] = sanitize_email( $value );
					break;
				case 'url':
					$output[ $id ] = esc_url_raw( $value );
					break;
				case 'image':
					$output[ $id ] = absint( $value );
					break;
				case 'select':
					$output[ $id ] = isset( $field['choices'][ $value ] ) ? $value : $field['default'];
					break;
				case 'textarea':
					$output[ $id ] = 'map_embed' === $id ? esc_url_raw( $value ) : nakliye_clean_text( $value, true );
					break;
				case 'code':
					$output[ $id ] = current_user_can( 'unfiltered_html' ) ? $value : wp_strip_all_tags( $value );
					break;
				default:
					$output[ $id ] = nakliye_clean_text( $value );
			}
		}
	}
	return $output;
}

/**
 * Metin temizleyici. sanitize_text_field() "%20 indirim" gibi ifadeleri URL kodu
 * sanıp sildiği için Türkçe yüzde kullanımını koruyan bir sürüm kullanılır.
 *
 * @param string $value     Değer.
 * @param bool   $multiline Satır sonları korunsun mu.
 * @return string
 */
function nakliye_clean_text( $value, $multiline = false ) {
	$value = wp_check_invalid_utf8( (string) $value );
	$value = wp_strip_all_tags( $value, ! $multiline );
	if ( ! $multiline ) {
		$value = preg_replace( '/[\r\n\t ]+/', ' ', $value );
	}
	return trim( $value );
}

/**
 * Seçeneklerden üretilen CSS değişkenleri.
 *
 * @return string
 */
function nakliye_dynamic_css() {
	$font_stack = static function ( $font ) {
		return 'system-ui' === $font ? 'system-ui, -apple-system, "Segoe UI", sans-serif' : '"' . $font . '", system-ui, sans-serif';
	};

	$vars = array(
		'--nk-primary'   => nakliye_option( 'color_primary' ),
		'--nk-secondary' => nakliye_option( 'color_secondary' ),
		'--nk-accent'    => nakliye_option( 'color_accent' ),
		'--nk-text'      => nakliye_option( 'color_text' ),
		'--nk-heading'   => nakliye_option( 'color_heading' ),
		'--nk-bg-alt'    => nakliye_option( 'color_bg_alt' ),
		'--nk-radius'    => absint( nakliye_option( 'border_radius' ) ) . 'px',
		'--nk-logo-h'    => absint( nakliye_option( 'logo_height' ) ) . 'px',
		'--nk-font'      => $font_stack( nakliye_option( 'font_body' ) ),
		'--nk-font-head' => $font_stack( nakliye_option( 'font_heading' ) ),
	);

	$css = ':root{';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	$css .= '}';

	$custom = nakliye_option( 'custom_css' );
	if ( $custom ) {
		$css .= wp_strip_all_tags( $custom );
	}
	return $css;
}

/**
 * Google Fonts adresi.
 *
 * @return string
 */
function nakliye_fonts_url() {
	$families = array_unique( array( nakliye_option( 'font_body' ), nakliye_option( 'font_heading' ) ) );
	$families = array_diff( $families, array( 'system-ui' ) );
	if ( empty( $families ) ) {
		return '';
	}
	$query = array();
	foreach ( $families as $family ) {
		$query[] = 'family=' . str_replace( ' ', '+', $family ) . ':wght@400;500;600;700;800';
	}
	return 'https://fonts.googleapis.com/css2?' . implode( '&', $query ) . '&display=swap';
}
