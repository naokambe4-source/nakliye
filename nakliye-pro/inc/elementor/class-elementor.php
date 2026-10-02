<?php
/**
 * Elementor entegrasyonu.
 *
 * - "Nakliye Pro" bileşen kategorisi ve 12 özel bileşen
 * - Elementor Pro Tema Oluşturucu konumları (header/footer/single/archive)
 * - Editör önizlemesinde JS yeniden başlatma
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_Elementor {

	/**
	 * Bileşen sınıfları → dosya adları.
	 *
	 * @var array
	 */
	private static $widgets = array(
		'Nakliye_Widget_Hero'            => 'hero',
		'Nakliye_Widget_Services'        => 'services',
		'Nakliye_Widget_Quote_Form'      => 'quote-form',
		'Nakliye_Widget_Price_Calculator'=> 'price-calculator',
		'Nakliye_Widget_Tracking'        => 'tracking',
		'Nakliye_Widget_Process'         => 'process',
		'Nakliye_Widget_Counters'        => 'counters',
		'Nakliye_Widget_Testimonials'    => 'testimonials',
		'Nakliye_Widget_Fleet'           => 'fleet',
		'Nakliye_Widget_Pricing'         => 'pricing',
		'Nakliye_Widget_Faq'             => 'faq',
		'Nakliye_Widget_Cta'             => 'cta',
	);

	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/theme/register_locations', array( __CLASS__, 'register_locations' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'preview_styles' ) );

		// "Kit silinmiş" hatasını önle: etkin kit yoksa yeniden oluştur.
		add_action( 'elementor/init', array( __CLASS__, 'ensure_kit' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'ensure_kit' ) );
	}

	/**
	 * Elementor "kit" (genel ayarlar) kaydı silinmiş/çöpe atılmışsa yeniden oluşturur.
	 * Böylece "Varsayılan kit silindi" hatası çıkmaz ve düzenleyici açılır.
	 */
	public static function ensure_kit() {
		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		$plugin = \Elementor\Plugin::$instance;
		if ( empty( $plugin->kits_manager ) ) {
			return;
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		$post   = $kit_id ? get_post( $kit_id ) : null;

		// Kit yok, çöpte ya da bozuksa yeniden oluştur.
		if ( $post && 'trash' !== $post->post_status && 'elementor_library' === $post->post_type ) {
			return;
		}

		// create_default_kit() option dolu olduğunda (bozuk bile olsa) hiçbir şey
		// yapmadığı için önce geçersiz kaydı temizliyoruz.
		delete_option( 'elementor_active_kit' );

		$new_id = false;
		if ( method_exists( $plugin->kits_manager, 'create_default' ) ) {
			$new_id = $plugin->kits_manager->create_default();
		} elseif ( method_exists( $plugin->kits_manager, 'create_default_kit' ) ) {
			$new_id = \Elementor\Core\Kits\Manager::create_default_kit();
		}
		if ( $new_id && ! is_wp_error( $new_id ) ) {
			update_option( 'elementor_active_kit', $new_id );
		}
	}

	/**
	 * @param \Elementor\Elements_Manager $elements_manager Yönetici.
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'nakliye',
			array(
				'title' => __( 'Nakliye Pro', 'nakliye' ),
				'icon'  => 'eicon-truck',
			)
		);
	}

	/**
	 * @param \Elementor\Widgets_Manager $widgets_manager Yönetici.
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/class-widget-base.php';
		foreach ( self::$widgets as $class => $file ) {
			require_once __DIR__ . '/widgets/class-' . $file . '.php';
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	/**
	 * Elementor Pro Tema Oluşturucu konumları.
	 *
	 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Yönetici.
	 */
	public static function register_locations( $manager ) {
		$manager->register_all_core_location();
	}

	public static function editor_styles() {
		wp_enqueue_style( 'nakliye-elementor-editor', NAKLIYE_URI . '/assets/css/elementor-editor.css', array(), NAKLIYE_VERSION );
	}

	public static function preview_styles() {
		wp_enqueue_style( 'nakliye-main' );
	}

	/**
	 * Elementor'un kendi renk ve yazı tiplerini kapatır; tema ayarları geçerli olur.
	 */
	public static function set_defaults() {
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		$cpt = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		$cpt = array_unique( array_merge( (array) $cpt, array( 'page', 'post', 'nakliye_hizmet' ) ) );
		update_option( 'elementor_cpt_support', $cpt );
		self::ensure_kit();
	}

	/**
	 * Elementor yüklü ve etkin mi?
	 *
	 * @return bool
	 */
	public static function is_active() {
		return did_action( 'elementor/loaded' ) > 0;
	}
}

// Eklentiler temadan önce yüklenir; Elementor zaten yüklendiyse doğrudan başlat.
if ( did_action( 'elementor/loaded' ) ) {
	Nakliye_Elementor::init();
} else {
	add_action( 'elementor/loaded', array( 'Nakliye_Elementor', 'init' ) );
}
add_action( 'after_switch_theme', array( 'Nakliye_Elementor', 'set_defaults' ) );
