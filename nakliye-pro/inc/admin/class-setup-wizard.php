<?php
/**
 * Kurulum sihirbazı.
 *
 * Adımlar: Hoş geldiniz → Lisans → Eklentiler → Firma bilgileri → Demo içerik → Hazır.
 * Tema etkinleştirildiğinde otomatik açılır.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_Setup_Wizard {

	const PAGE = 'nakliye-setup';

	/**
	 * Kurulabilir eklentiler (beyaz liste).
	 *
	 * @return array
	 */
	public static function plugins() {
		return array(
			'elementor'     => array( 'name' => 'Elementor', 'file' => 'elementor/elementor.php', 'required' => true, 'desc' => __( 'Sayfa oluşturucu — tüm sayfaların sürükle-bırak düzenlenmesi için gerekli.', 'nakliye' ) ),
			'wp-mail-smtp'  => array( 'name' => 'WP Mail SMTP', 'file' => 'wp-mail-smtp/wp_mail_smtp.php', 'required' => false, 'desc' => __( 'Teklif e-postalarının spam’e düşmeden ulaşması için önerilir.', 'nakliye' ) ),
			'wordpress-seo' => array( 'name' => 'Yoast SEO', 'file' => 'wordpress-seo/wp-seo.php', 'required' => false, 'desc' => __( 'Arama motoru optimizasyonu için önerilir.', 'nakliye' ) ),
		);
	}

	/**
	 * @return array
	 */
	private static function steps() {
		return array(
			'welcome' => __( 'Hoş geldiniz', 'nakliye' ),
			'license' => __( 'Lisans', 'nakliye' ),
			'plugins' => __( 'Eklentiler', 'nakliye' ),
			'company' => __( 'Firma', 'nakliye' ),
			'demo'    => __( 'Demo içerik', 'nakliye' ),
			'done'    => __( 'Hazır', 'nakliye' ),
		);
	}

	public static function init() {
		add_action( 'after_switch_theme', array( __CLASS__, 'flag_redirect' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect' ), 1 );
		add_action( 'admin_init', array( __CLASS__, 'standalone' ), 20 );
		add_action( 'wp_ajax_nakliye_install_plugin', array( __CLASS__, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_nakliye_import_demo', array( __CLASS__, 'ajax_import_demo' ) );
	}

	public static function flag_redirect() {
		if ( ! get_option( 'nakliye_setup_done' ) ) {
			set_transient( 'nakliye_setup_redirect', 1, 60 );
		}
	}

	public static function maybe_redirect() {
		if ( ! get_transient( 'nakliye_setup_redirect' ) || wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		delete_transient( 'nakliye_setup_redirect' );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE ) );
		exit;
	}

	/**
	 * Sihirbazı WordPress menüsü olmadan tam ekran çizer.
	 */
	public static function standalone() {
		if ( empty( $_GET['page'] ) || self::PAGE !== $_GET['page'] || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		$step = self::current_step();

		// Form gönderimleri.
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nakliye_wizard_nonce'] ) ) { // phpcs:ignore
			check_admin_referer( 'nakliye_wizard', 'nakliye_wizard_nonce' );
			self::save_step( $step );
		}

		self::render_page( $step );
		exit;
	}

	/**
	 * Menü geri çağrısı (standalone() zaten çıktı verip çıkıyor).
	 */
	public static function render() {}

	/**
	 * @return string
	 */
	private static function current_step() {
		$step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : 'welcome'; // phpcs:ignore WordPress.Security.NonceVerification
		return array_key_exists( $step, self::steps() ) ? $step : 'welcome';
	}

	/**
	 * @param string $step Adım.
	 * @return string
	 */
	private static function next_url( $step ) {
		$keys = array_keys( self::steps() );
		$i    = array_search( $step, $keys, true );
		$next = isset( $keys[ $i + 1 ] ) ? $keys[ $i + 1 ] : 'done';
		return admin_url( 'admin.php?page=' . self::PAGE . '&step=' . $next );
	}

	/**
	 * @param string $step Adım.
	 */
	private static function save_step( $step ) {
		if ( 'company' === $step ) {
			$fields = array( 'company_name', 'phone', 'whatsapp', 'email', 'address', 'hours', 'color_primary', 'color_secondary', 'logo' );
			$input  = array();
			foreach ( $fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$input[ $field ] = wp_unslash( $_POST[ $field ] ); // phpcs:ignore
				}
			}
			update_option( 'nakliye_options', nakliye_sanitize_options( $input ) );
			if ( ! empty( $input['company_name'] ) ) {
				update_option( 'blogname', sanitize_text_field( $input['company_name'] ) );
			}
			wp_safe_redirect( self::next_url( $step ) );
			exit;
		}

		if ( 'license' === $step ) {
			$key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : ''; // phpcs:ignore
			if ( $key && ! Nakliye_License::instance()->activate( $key ) ) {
				set_transient( 'nakliye_wizard_error', Nakliye_License::instance()->last_error(), 60 );
				wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE . '&step=license' ) );
				exit;
			}
			wp_safe_redirect( self::next_url( $step ) );
			exit;
		}
	}

	/* --------------------------------------------------------------------- */
	/* AJAX                                                                  */
	/* --------------------------------------------------------------------- */

	public static function ajax_install_plugin() {
		check_ajax_referer( 'nakliye_admin', 'nonce' );
		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Eklenti kurma yetkiniz yok.', 'nakliye' ) ) );
		}

		$slug    = isset( $_POST['slug'] ) ? sanitize_key( $_POST['slug'] ) : '';
		$plugins = self::plugins();
		if ( ! isset( $plugins[ $slug ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Bilinmeyen eklenti.', 'nakliye' ) ) );
		}
		$file = $plugins[ $slug ]['file'];

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
			$api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}
			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) || ! $result ) {
				wp_send_json_error( array( 'message' => is_wp_error( $result ) ? $result->get_error_message() : __( 'Kurulum başarısız. Dosya izinlerini kontrol edin.', 'nakliye' ) ) );
			}
		}

		if ( ! is_plugin_active( $file ) ) {
			$activated = activate_plugin( $file, '', false, true );
			if ( is_wp_error( $activated ) ) {
				wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
			}
		}

		if ( 'elementor' === $slug ) {
			Nakliye_Elementor::set_defaults();
			// Elementor'un kendi karşılama yönlendirmesini engelle.
			delete_transient( 'elementor_activation_redirect' );
		}

		wp_send_json_success( array( 'message' => __( 'Kuruldu ve etkinleştirildi', 'nakliye' ) ) );
	}

	public static function ajax_import_demo() {
		check_ajax_referer( 'nakliye_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Yetkiniz yok.', 'nakliye' ) ) );
		}
		if ( ! Nakliye_License::instance()->is_active() ) {
			wp_send_json_error( array( 'message' => wp_strip_all_tags( nakliye_locked_message() ) ) );
		}

		$parts = isset( $_POST['parts'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['parts'] ) ) : array();
		if ( empty( $parts ) ) {
			wp_send_json_error( array( 'message' => __( 'En az bir içerik seçin.', 'nakliye' ) ) );
		}
		if ( in_array( 'pages', $parts, true ) && ! did_action( 'elementor/loaded' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sayfaları yüklemek için önce Elementor’u kurun.', 'nakliye' ) ) );
		}

		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore
		}
		$log = ( new Nakliye_Demo_Importer() )->run( $parts );
		wp_send_json_success( array( 'log' => $log ) );
	}

	/* --------------------------------------------------------------------- */
	/* Görünüm                                                               */
	/* --------------------------------------------------------------------- */

	/**
	 * @param string $step Adım.
	 */
	private static function render_page( $step ) {
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'nakliye-wizard', NAKLIYE_URI . '/assets/css/wizard.css', array( 'dashicons', 'buttons' ), NAKLIYE_VERSION );
		wp_enqueue_script( 'nakliye-admin', NAKLIYE_URI . '/assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), NAKLIYE_VERSION, true );
		wp_localize_script(
			'nakliye-admin',
			'NakliyeAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nakliye_admin' ),
				'i18n'    => array(
					'choose'     => __( 'Logo seç', 'nakliye' ),
					'use'        => __( 'Bu görseli kullan', 'nakliye' ),
					'installing' => __( 'Kuruluyor…', 'nakliye' ),
					'installed'  => __( 'Etkin', 'nakliye' ),
					'importing'  => __( 'Yükleniyor… Bu işlem bir dakika sürebilir.', 'nakliye' ),
					'confirm'    => __( 'Emin misiniz?', 'nakliye' ),
				),
			)
		);

		$steps = self::steps();
		$keys  = array_keys( $steps );
		$index = array_search( $step, $keys, true );
		?>
		<!doctype html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php esc_html_e( 'Nakliye Pro Kurulum Sihirbazı', 'nakliye' ); ?></title>
			<?php
			// Eski emoji çıktısı kancası WP 6.4+ sürümlerinde kullanımdan kalkma uyarısı üretir.
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			wp_print_styles();
			?>
		</head>
		<body class="nk-wizard-body wp-core-ui">
			<div class="nk-wizard">
				<header class="nk-wizard__head">
					<span class="nk-wizard__logo"><?php echo nakliye_icon( 'truck', 30 ); // phpcs:ignore ?> Nakliye Pro</span>
					<ol class="nk-wizard__steps">
						<?php foreach ( $keys as $i => $key ) : ?>
							<li class="<?php echo $i < $index ? 'is-done' : ( $i === $index ? 'is-current' : '' ); ?>"><span><?php echo (int) $i + 1; ?></span><?php echo esc_html( $steps[ $key ] ); ?></li>
						<?php endforeach; ?>
					</ol>
				</header>
				<main class="nk-wizard__card">
					<?php call_user_func( array( __CLASS__, 'step_' . $step ) ); ?>
				</main>
				<footer class="nk-wizard__foot">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=nakliye' ) ); ?>"><?php esc_html_e( '← Panele dön', 'nakliye' ); ?></a>
				</footer>
			</div>
			<?php
			do_action( 'admin_footer' ); // Medya şablonları bu kancada basılır.
			wp_print_footer_scripts();
			?>
		</body>
		</html>
		<?php
	}

	private static function step_welcome() {
		?>
		<div class="nk-wizard__center">
			<span class="nk-wizard__big-icon"><?php echo nakliye_icon( 'truck', 64 ); // phpcs:ignore ?></span>
			<h1><?php esc_html_e( 'Nakliye Pro’ya hoş geldiniz!', 'nakliye' ); ?></h1>
			<p><?php esc_html_e( 'Bu sihirbaz birkaç dakika içinde lisansınızı etkinleştirecek, gerekli eklentileri kuracak, firma bilgilerinizi kaydedecek ve sitenizi Elementor ile düzenlenebilir demo içerikle dolduracak.', 'nakliye' ); ?></p>
			<ul class="nk-wizard__list">
				<li><?php echo nakliye_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( '12 özel Elementor bileşeni', 'nakliye' ); ?></li>
				<li><?php echo nakliye_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( 'Teklif formu, fiyat hesaplayıcı ve taşıma takibi', 'nakliye' ); ?></li>
				<li><?php echo nakliye_icon( 'check', 16 ); // phpcs:ignore ?> <?php esc_html_e( '7 hazır sayfa, hizmetler, filo ve yorumlar', 'nakliye' ); ?></li>
			</ul>
			<div class="nk-wizard__actions">
				<a class="button button-primary button-hero" href="<?php echo esc_url( self::next_url( 'welcome' ) ); ?>"><?php esc_html_e( 'Kuruluma başla', 'nakliye' ); ?></a>
				<a class="button button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=nakliye' ) ); ?>"><?php esc_html_e( 'Şimdi değil', 'nakliye' ); ?></a>
			</div>
		</div>
		<?php
	}

	private static function step_license() {
		$status = Nakliye_License::instance()->status();
		$error  = get_transient( 'nakliye_wizard_error' );
		delete_transient( 'nakliye_wizard_error' );
		?>
		<h1><?php esc_html_e( 'Lisansınızı etkinleştirin', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Premium bileşenler, demo içerik ve fiyat hesaplayıcı geçerli bir lisans gerektirir.', 'nakliye' ); ?></p>
		<?php if ( $error ) : ?>
			<div class="nk-wizard__alert nk-wizard__alert--error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>
		<?php if ( in_array( $status['code'], array( 'active', 'dev' ), true ) ) : ?>
			<div class="nk-wizard__alert nk-wizard__alert--success"><?php echo esc_html( $status['label'] ); ?> ✓</div>
			<div class="nk-wizard__actions"><a class="button button-primary button-hero" href="<?php echo esc_url( self::next_url( 'license' ) ); ?>"><?php esc_html_e( 'Devam', 'nakliye' ); ?></a></div>
		<?php else : ?>
			<form method="post">
				<?php wp_nonce_field( 'nakliye_wizard', 'nakliye_wizard_nonce' ); ?>
				<p><input type="text" name="license_key" class="nk-wizard__input" placeholder="NKP-XXXX-XXXX-XXXX-XXXX" autocomplete="off"></p>
				<p class="description"><?php printf( esc_html__( 'Alan adı: %s', 'nakliye' ), '<code>' . esc_html( $status['domain'] ) . '</code>' ); // phpcs:ignore ?></p>
				<div class="nk-wizard__actions">
					<button class="button button-primary button-hero"><?php esc_html_e( 'Etkinleştir ve devam et', 'nakliye' ); ?></button>
					<a class="button button-hero" href="<?php echo esc_url( self::next_url( 'license' ) ); ?>"><?php esc_html_e( 'Daha sonra', 'nakliye' ); ?></a>
				</div>
			</form>
		<?php endif; ?>
		<?php
	}

	private static function step_plugins() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		?>
		<h1><?php esc_html_e( 'Eklentiler', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Tek tıkla kurun. Elementor zorunludur; diğerleri önerilir.', 'nakliye' ); ?></p>
		<ul class="nk-wizard__plugins">
			<?php foreach ( self::plugins() as $slug => $plugin ) : $active = is_plugin_active( $plugin['file'] ); ?>
				<li>
					<label>
						<input type="checkbox" data-plugin="<?php echo esc_attr( $slug ); ?>" <?php checked( $plugin['required'] || ! $active ); ?> <?php disabled( $active ); ?>>
						<span>
							<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
							<?php if ( $plugin['required'] ) : ?><em class="nk-req"><?php esc_html_e( 'Gerekli', 'nakliye' ); ?></em><?php endif; ?>
							<small><?php echo esc_html( $plugin['desc'] ); ?></small>
						</span>
					</label>
					<span class="nk-wizard__state" data-state="<?php echo esc_attr( $slug ); ?>"><?php echo $active ? esc_html__( 'Etkin', 'nakliye' ) : ''; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="nk-wizard__actions">
			<button class="button button-primary button-hero" data-install-plugins data-next="<?php echo esc_url( self::next_url( 'plugins' ) ); ?>"><?php esc_html_e( 'Kur ve devam et', 'nakliye' ); ?></button>
			<a class="button button-hero" href="<?php echo esc_url( self::next_url( 'plugins' ) ); ?>"><?php esc_html_e( 'Atla', 'nakliye' ); ?></a>
		</div>
		<?php
	}

	private static function step_company() {
		$logo = nakliye_option( 'logo' );
		?>
		<h1><?php esc_html_e( 'Firma bilgileri', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Bu bilgiler üst/alt alanda, iletişim kartlarında ve yapısal veride kullanılır. Daha sonra Tema Ayarları’ndan değiştirebilirsiniz.', 'nakliye' ); ?></p>
		<form method="post" class="nk-wizard__form">
			<?php wp_nonce_field( 'nakliye_wizard', 'nakliye_wizard_nonce' ); ?>
			<div class="nk-wizard__grid">
				<label><?php esc_html_e( 'Firma adı', 'nakliye' ); ?><input type="text" name="company_name" value="<?php echo esc_attr( nakliye_option( 'company_name' ) ); ?>"></label>
				<label><?php esc_html_e( 'Telefon', 'nakliye' ); ?><input type="text" name="phone" value="<?php echo esc_attr( nakliye_option( 'phone' ) ); ?>"></label>
				<label><?php esc_html_e( 'WhatsApp (90 ile)', 'nakliye' ); ?><input type="text" name="whatsapp" value="<?php echo esc_attr( nakliye_option( 'whatsapp' ) ); ?>"></label>
				<label><?php esc_html_e( 'E-posta', 'nakliye' ); ?><input type="email" name="email" value="<?php echo esc_attr( nakliye_option( 'email' ) ); ?>"></label>
				<label class="nk-wizard__full"><?php esc_html_e( 'Adres', 'nakliye' ); ?><input type="text" name="address" value="<?php echo esc_attr( nakliye_option( 'address' ) ); ?>"></label>
				<label><?php esc_html_e( 'Çalışma saatleri', 'nakliye' ); ?><input type="text" name="hours" value="<?php echo esc_attr( nakliye_option( 'hours' ) ); ?>"></label>
				<div class="nk-wizard__colors">
					<label><?php esc_html_e( 'Ana renk', 'nakliye' ); ?><input type="text" class="nk-color" name="color_primary" value="<?php echo esc_attr( nakliye_option( 'color_primary' ) ); ?>"></label>
					<label><?php esc_html_e( 'İkincil renk', 'nakliye' ); ?><input type="text" class="nk-color" name="color_secondary" value="<?php echo esc_attr( nakliye_option( 'color_secondary' ) ); ?>"></label>
				</div>
				<div class="nk-wizard__full nk-media" data-nk-media>
					<span><?php esc_html_e( 'Logo', 'nakliye' ); ?></span>
					<input type="hidden" name="logo" value="<?php echo esc_attr( $logo ); ?>">
					<div class="nk-media__preview"><?php echo $logo ? wp_get_attachment_image( (int) $logo, 'medium' ) : ''; ?></div>
					<button type="button" class="button" data-nk-media-select><?php esc_html_e( 'Logo yükle', 'nakliye' ); ?></button>
					<button type="button" class="button-link-delete" data-nk-media-remove><?php esc_html_e( 'Kaldır', 'nakliye' ); ?></button>
				</div>
			</div>
			<div class="nk-wizard__actions">
				<button class="button button-primary button-hero"><?php esc_html_e( 'Kaydet ve devam et', 'nakliye' ); ?></button>
			</div>
		</form>
		<?php
	}

	private static function step_demo() {
		$licensed = Nakliye_License::instance()->is_active();
		$imported = get_option( 'nakliye_demo_imported' );
		?>
		<h1><?php esc_html_e( 'Demo içerik', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Sitenizi hazır içerikle doldurun. Tüm sayfalar Elementor ile oluşturulur ve tamamen düzenlenebilir. Mevcut içerikleriniz silinmez.', 'nakliye' ); ?></p>
		<?php if ( $imported ) : ?>
			<div class="nk-wizard__alert"><?php printf( esc_html__( 'Demo içerik daha önce %s tarihinde yüklendi. Tekrar yüklemek yalnızca eksik öğeleri ekler.', 'nakliye' ), esc_html( wp_date( 'd.m.Y H:i', (int) $imported ) ) ); ?></div>
		<?php endif; ?>
		<?php if ( ! $licensed ) : ?>
			<div class="nk-wizard__alert nk-wizard__alert--error"><?php echo wp_kses_post( nakliye_locked_message() ); ?></div>
		<?php endif; ?>
		<div class="nk-wizard__options" data-demo-parts>
			<label><input type="checkbox" value="content" checked> <strong><?php esc_html_e( 'İçerikler', 'nakliye' ); ?></strong> <small><?php esc_html_e( '6 hizmet, 3 araç, 6 yorum, 2 blog yazısı', 'nakliye' ); ?></small></label>
			<label><input type="checkbox" value="pages" checked> <strong><?php esc_html_e( 'Elementor sayfaları', 'nakliye' ); ?></strong> <small><?php esc_html_e( 'Ana sayfa, Hakkımızda, Hizmetler, Fiyat Hesapla, Teklif Al, Takip, İletişim', 'nakliye' ); ?></small></label>
			<label><input type="checkbox" value="menus" checked> <strong><?php esc_html_e( 'Menüler', 'nakliye' ); ?></strong> <small><?php esc_html_e( 'Ana menü ve alt menü', 'nakliye' ); ?></small></label>
			<label><input type="checkbox" value="widgets" checked> <strong><?php esc_html_e( 'Widgetlar', 'nakliye' ); ?></strong> <small><?php esc_html_e( 'Blog kenar çubuğu', 'nakliye' ); ?></small></label>
		</div>
		<div class="nk-wizard__log" data-demo-log hidden></div>
		<div class="nk-wizard__actions">
			<button class="button button-primary button-hero" data-import-demo data-next="<?php echo esc_url( self::next_url( 'demo' ) ); ?>" <?php disabled( ! $licensed ); ?>><?php esc_html_e( 'Demo içeriği yükle', 'nakliye' ); ?></button>
			<a class="button button-hero" href="<?php echo esc_url( self::next_url( 'demo' ) ); ?>"><?php esc_html_e( 'Atla', 'nakliye' ); ?></a>
		</div>
		<?php
	}

	private static function step_done() {
		update_option( 'nakliye_setup_done', time() );
		$home = (int) get_option( 'page_on_front' );
		?>
		<div class="nk-wizard__center">
			<span class="nk-wizard__big-icon nk-wizard__big-icon--ok"><?php echo nakliye_icon( 'check', 64 ); // phpcs:ignore ?></span>
			<h1><?php esc_html_e( 'Siteniz hazır! 🎉', 'nakliye' ); ?></h1>
			<p><?php esc_html_e( 'Artık sayfalarınızı Elementor ile düzenleyebilir, renkleri ve iletişim bilgilerini Tema Ayarları’ndan değiştirebilirsiniz.', 'nakliye' ); ?></p>
			<div class="nk-wizard__actions">
				<a class="button button-primary button-hero" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'Siteyi görüntüle', 'nakliye' ); ?></a>
				<?php if ( $home && did_action( 'elementor/loaded' ) ) : ?>
					<a class="button button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $home . '&action=elementor' ) ); ?>"><?php esc_html_e( 'Ana sayfayı Elementor ile düzenle', 'nakliye' ); ?></a>
				<?php endif; ?>
				<a class="button button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=nakliye' ) ); ?>"><?php esc_html_e( 'Panele git', 'nakliye' ); ?></a>
			</div>
		</div>
		<?php
	}
}

Nakliye_Setup_Wizard::init();
