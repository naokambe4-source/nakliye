<?php
/**
 * Nakliye Pro yönetim paneli.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_Admin {

	const CAP = 'manage_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'reorder_menu' ), 99 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_nakliye_license', array( __CLASS__, 'handle_license' ) );
		add_action( 'admin_post_nakliye_reset_options', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_post_nakliye_export', array( __CLASS__, 'handle_export' ) );
		add_action( 'admin_post_nakliye_import', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar' ), 90 );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'dashboard_widget' ) );
	}

	public static function menu() {
		$count = nakliye_quote_counts();
		$new   = isset( $count['yeni'] ) ? $count['yeni'] : 0;
		$badge = $new ? ' <span class="awaiting-mod">' . (int) $new . '</span>' : '';

		add_menu_page( 'Nakliye Pro', 'Nakliye Pro' . $badge, self::CAP, 'nakliye', array( __CLASS__, 'page_dashboard' ), self::menu_icon(), 3 );
		add_submenu_page( 'nakliye', __( 'Pano', 'nakliye' ), __( 'Pano', 'nakliye' ), self::CAP, 'nakliye', array( __CLASS__, 'page_dashboard' ) );
		add_submenu_page( 'nakliye', __( 'Tema Ayarları', 'nakliye' ), __( 'Tema Ayarları', 'nakliye' ), self::CAP, 'nakliye-options', array( __CLASS__, 'page_options' ) );
		add_submenu_page( 'nakliye', __( 'Lisans', 'nakliye' ), __( 'Lisans', 'nakliye' ), self::CAP, 'nakliye-license', array( __CLASS__, 'page_license' ) );
		add_submenu_page( 'nakliye', __( 'Kurulum Sihirbazı', 'nakliye' ), __( 'Kurulum Sihirbazı', 'nakliye' ), self::CAP, 'nakliye-setup', array( 'Nakliye_Setup_Wizard', 'render' ) );
		add_submenu_page( 'nakliye', __( 'Sistem Durumu', 'nakliye' ), __( 'Sistem Durumu', 'nakliye' ), self::CAP, 'nakliye-status', array( __CLASS__, 'page_status' ) );
	}

	/**
	 * "Teklif Talepleri" alt menüsünü Pano'nun hemen altına taşır.
	 */
	public static function reorder_menu() {
		global $submenu;
		if ( empty( $submenu['nakliye'] ) ) {
			return;
		}
		$quotes = array();
		$rest   = array();
		foreach ( $submenu['nakliye'] as $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=nakliye_teklif' === $item[2] ) {
				$quotes[] = $item;
			} else {
				$rest[] = $item;
			}
		}
		$submenu['nakliye'] = array_merge( array_slice( $rest, 0, 1 ), $quotes, array_slice( $rest, 1 ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	/**
	 * @return string
	 */
	private static function menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	}

	public static function register_settings() {
		register_setting(
			'nakliye_options_group',
			'nakliye_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);
	}

	/**
	 * Lisanssızken premium sekmelerdeki değerler değiştirilemez.
	 *
	 * @param array $input Girdi.
	 * @return array
	 */
	public static function sanitize( $input ) {
		if ( ! Nakliye_License::instance()->is_active() && is_array( $input ) ) {
			foreach ( nakliye_options_schema() as $tab ) {
				if ( ! empty( $tab['premium'] ) ) {
					foreach ( array_keys( $tab['fields'] ) as $id ) {
						unset( $input[ $id ] );
					}
				}
			}
		}
		return nakliye_sanitize_options( $input );
	}

	/**
	 * @param string $hook Sayfa kancası.
	 */
	public static function assets( $hook ) {
		wp_enqueue_style( 'nakliye-admin', NAKLIYE_URI . '/assets/css/admin.css', array(), NAKLIYE_VERSION );

		if ( false === strpos( $hook, 'nakliye' ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'nakliye-admin', NAKLIYE_URI . '/assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), NAKLIYE_VERSION, true );
		wp_localize_script(
			'nakliye-admin',
			'NakliyeAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'nakliye_admin' ),
				'i18n'    => array(
					'choose'  => __( 'Görsel seç', 'nakliye' ),
					'use'     => __( 'Bu görseli kullan', 'nakliye' ),
					'confirm' => __( 'Emin misiniz?', 'nakliye' ),
				),
			)
		);
	}

	/* --------------------------------------------------------------------- */
	/* Ortak yerleşim                                                        */
	/* --------------------------------------------------------------------- */

	/**
	 * Üst marka çubuğu ve sekme menüsü.
	 *
	 * @param string $current Etkin sayfa.
	 */
	public static function header( $current ) {
		$license = Nakliye_License::instance()->status();
		$pages   = array(
			'nakliye'         => array( __( 'Pano', 'nakliye' ), 'dashicons-dashboard' ),
			'nakliye-options' => array( __( 'Tema Ayarları', 'nakliye' ), 'dashicons-admin-appearance' ),
			'nakliye-license' => array( __( 'Lisans', 'nakliye' ), 'dashicons-lock' ),
			'nakliye-setup'   => array( __( 'Kurulum', 'nakliye' ), 'dashicons-admin-plugins' ),
			'nakliye-status'  => array( __( 'Sistem Durumu', 'nakliye' ), 'dashicons-heart' ),
		);
		?>
		<div class="nk-admin">
			<header class="nk-admin__bar">
				<div class="nk-admin__brand">
					<span class="nk-admin__logo"><?php echo nakliye_icon( 'truck', 26 ); // phpcs:ignore ?></span>
					<div>
						<strong>Nakliye Pro</strong>
						<small>v<?php echo esc_html( NAKLIYE_VERSION ); ?></small>
					</div>
				</div>
				<nav class="nk-admin__tabs">
					<?php foreach ( $pages as $slug => $page ) : ?>
						<a class="<?php echo $current === $slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"><span class="dashicons <?php echo esc_attr( $page[1] ); ?>"></span> <?php echo esc_html( $page[0] ); ?></a>
					<?php endforeach; ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nakliye_teklif' ) ); ?>"><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Teklifler', 'nakliye' ); ?></a>
				</nav>
				<span class="nk-pill nk-pill--<?php echo esc_attr( in_array( $license['code'], array( 'active', 'dev' ), true ) ? 'ok' : 'bad' ); ?>">
					<span class="dashicons dashicons-<?php echo in_array( $license['code'], array( 'active', 'dev' ), true ) ? 'yes-alt' : 'warning'; ?>"></span>
					<?php echo esc_html( $license['label'] ); ?>
				</span>
			</header>
			<div class="nk-admin__body">
		<?php
		settings_errors( 'nakliye' );
	}

	public static function footer() {
		echo '</div></div>';
	}

	/* --------------------------------------------------------------------- */
	/* Sayfalar                                                              */
	/* --------------------------------------------------------------------- */

	public static function page_dashboard() {
		self::header( 'nakliye' );
		require NAKLIYE_DIR . '/inc/admin/views/dashboard.php';
		self::footer();
	}

	public static function page_options() {
		self::header( 'nakliye-options' );
		require NAKLIYE_DIR . '/inc/admin/views/options.php';
		self::footer();
	}

	public static function page_license() {
		self::header( 'nakliye-license' );
		require NAKLIYE_DIR . '/inc/admin/views/license.php';
		self::footer();
	}

	public static function page_status() {
		self::header( 'nakliye-status' );
		require NAKLIYE_DIR . '/inc/admin/views/status.php';
		self::footer();
	}

	/**
	 * Ayar alanını çizer.
	 *
	 * @param string $id     Alan kimliği.
	 * @param array  $field  Tanım.
	 * @param bool   $locked Kilitli mi.
	 */
	public static function render_field( $id, array $field, $locked = false ) {
		$name     = 'nakliye_options[' . $id . ']';
		$value    = nakliye_option( $id );
		$disabled = $locked ? ' disabled' : '';

		echo '<div class="nk-field-row nk-field-row--' . esc_attr( $field['type'] ) . '">';
		echo '<label class="nk-field-row__label" for="nk-' . esc_attr( $id ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
		echo '<div class="nk-field-row__control">';

		switch ( $field['type'] ) {
			case 'toggle':
				printf(
					'<input type="hidden" name="nakliye_options[__tabs][]" value="%1$s"><label class="nk-switch"><input type="checkbox" id="nk-%1$s" name="%2$s" value="1"%3$s%4$s><span></span></label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( 1, (int) $value, false ),
					esc_attr( $disabled )
				);
				break;
			case 'color':
				printf( '<input type="text" class="nk-color" id="nk-%1$s" name="%2$s" value="%3$s" data-default-color="%4$s"%5$s>', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( $field['default'] ), esc_attr( $disabled ) );
				break;
			case 'image':
				$url = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
				printf(
					'<div class="nk-media" data-nk-media><input type="hidden" name="%1$s" value="%2$s"><div class="nk-media__preview">%3$s</div><button type="button" class="button" data-nk-media-select%5$s>%4$s</button> <button type="button" class="button-link-delete" data-nk-media-remove%5$s>%6$s</button></div>',
					esc_attr( $name ),
					esc_attr( $value ),
					$url ? '<img src="' . esc_url( $url ) . '" alt="">' : '',
					esc_html__( 'Görsel seç', 'nakliye' ),
					esc_attr( $disabled ),
					esc_html__( 'Kaldır', 'nakliye' )
				);
				break;
			case 'select':
				echo '<select id="nk-' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . esc_attr( $disabled ) . '>';
				foreach ( $field['choices'] as $key => $label ) {
					echo '<option value="' . esc_attr( $key ) . '"' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'textarea':
				printf( '<textarea id="nk-%1$s" name="%2$s" rows="3"%4$s>%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ), esc_attr( $disabled ) );
				break;
			case 'code':
				printf( '<textarea class="nk-code" id="nk-%1$s" name="%2$s" rows="8" spellcheck="false"%4$s>%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ), esc_attr( $disabled ) );
				break;
			default:
				$type = in_array( $field['type'], array( 'number', 'email', 'url' ), true ) ? $field['type'] : 'text';
				printf( '<input type="%1$s" id="nk-%2$s" name="%3$s" value="%4$s"%5$s%6$s>', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), 'number' === $type ? ' step="any"' : '', esc_attr( $disabled ) );
		}
		echo '</div></div>';
	}

	/* --------------------------------------------------------------------- */
	/* Form işleyicileri                                                     */
	/* --------------------------------------------------------------------- */

	public static function handle_license() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Yetkiniz yok.', 'nakliye' ) );
		}
		check_admin_referer( 'nakliye_license' );

		$license  = Nakliye_License::instance();
		$redirect = isset( $_POST['redirect'] ) ? esc_url_raw( wp_unslash( $_POST['redirect'] ) ) : admin_url( 'admin.php?page=nakliye-license' );
		$action   = isset( $_POST['license_action'] ) ? sanitize_key( $_POST['license_action'] ) : '';

		if ( 'deactivate' === $action ) {
			$license->deactivate();
			$msg = array( 'type' => 'success', 'text' => __( 'Lisans bu siteden kaldırıldı.', 'nakliye' ) );
		} elseif ( 'check' === $action ) {
			$license->remote_check();
			Nakliye_Integrity::flush();
			$msg = array( 'type' => 'info', 'text' => __( 'Lisans durumu yeniden doğrulandı.', 'nakliye' ) );
		} else {
			$key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
			if ( $license->activate( $key ) ) {
				$msg = array( 'type' => 'success', 'text' => __( 'Lisans başarıyla etkinleştirildi. Tüm premium özellikler açıldı!', 'nakliye' ) );
			} else {
				$msg = array( 'type' => 'error', 'text' => $license->last_error() );
			}
		}

		set_transient( 'nakliye_admin_msg_' . get_current_user_id(), $msg, 60 );
		wp_safe_redirect( $redirect );
		exit;
	}

	public static function handle_reset() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Yetkiniz yok.', 'nakliye' ) );
		}
		check_admin_referer( 'nakliye_reset_options' );
		delete_option( 'nakliye_options' );
		set_transient( 'nakliye_admin_msg_' . get_current_user_id(), array( 'type' => 'success', 'text' => __( 'Ayarlar varsayılana döndürüldü.', 'nakliye' ) ), 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=nakliye-options' ) );
		exit;
	}

	public static function handle_export() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Yetkiniz yok.', 'nakliye' ) );
		}
		check_admin_referer( 'nakliye_export' );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=nakliye-ayarlar-' . gmdate( 'Y-m-d' ) . '.json' );
		echo wp_json_encode( get_option( 'nakliye_options', array() ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		exit;
	}

	public static function handle_import() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Yetkiniz yok.', 'nakliye' ) );
		}
		check_admin_referer( 'nakliye_import' );
		$msg = array( 'type' => 'error', 'text' => __( 'Geçersiz dosya.', 'nakliye' ) );
		if ( ! empty( $_FILES['nakliye_import']['tmp_name'] ) ) {
			$json = json_decode( (string) file_get_contents( $_FILES['nakliye_import']['tmp_name'] ), true ); // phpcs:ignore
			if ( is_array( $json ) ) {
				update_option( 'nakliye_options', self::sanitize( $json ) );
				$msg = array( 'type' => 'success', 'text' => __( 'Ayarlar içe aktarıldı.', 'nakliye' ) );
			}
		}
		set_transient( 'nakliye_admin_msg_' . get_current_user_id(), $msg, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=nakliye-options&tab=tools' ) );
		exit;
	}

	/**
	 * Yönlendirme sonrası tek seferlik mesaj.
	 */
	public static function flash() {
		$key = 'nakliye_admin_msg_' . get_current_user_id();
		$msg = get_transient( $key );
		if ( $msg ) {
			delete_transient( $key );
			echo '<div class="nk-alert nk-alert--' . esc_attr( $msg['type'] ) . '">' . esc_html( $msg['text'] ) . '</div>';
		}
	}

	/* --------------------------------------------------------------------- */
	/* Diğer                                                                 */
	/* --------------------------------------------------------------------- */

	/**
	 * @param WP_Admin_Bar $bar Yönetim çubuğu.
	 */
	public static function admin_bar( $bar ) {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		$bar->add_node( array( 'id' => 'nakliye', 'title' => '🚚 Nakliye Pro', 'href' => admin_url( 'admin.php?page=nakliye' ) ) );
		$bar->add_node( array( 'id' => 'nakliye-options', 'parent' => 'nakliye', 'title' => __( 'Tema Ayarları', 'nakliye' ), 'href' => admin_url( 'admin.php?page=nakliye-options' ) ) );
		$bar->add_node( array( 'id' => 'nakliye-quotes', 'parent' => 'nakliye', 'title' => __( 'Teklif Talepleri', 'nakliye' ), 'href' => admin_url( 'edit.php?post_type=nakliye_teklif' ) ) );
	}

	/**
	 * @param string $text Metin.
	 * @return string
	 */
	public static function footer_text( $text ) {
		$screen = get_current_screen();
		if ( $screen && false !== strpos( $screen->id, 'nakliye' ) ) {
			return esc_html__( 'Nakliye Pro ile güçlendirildi.', 'nakliye' );
		}
		return $text;
	}

	public static function dashboard_widget() {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'nakliye_quotes',
			__( 'Nakliye Pro — Son Teklif Talepleri', 'nakliye' ),
			static function () {
				$posts = get_posts( array( 'post_type' => 'nakliye_teklif', 'numberposts' => 5 ) );
				if ( ! $posts ) {
					echo '<p>' . esc_html__( 'Henüz talep yok.', 'nakliye' ) . '</p>';
					return;
				}
				$labels = nakliye_quote_statuses();
				echo '<ul>';
				foreach ( $posts as $post ) {
					$status = get_post_meta( $post->ID, '_nk_status', true );
					printf(
						'<li><a href="%1$s">%2$s</a> <span class="nk-badge nk-badge--%3$s">%4$s</span></li>',
						esc_url( get_edit_post_link( $post->ID ) ),
						esc_html( $post->post_title ),
						esc_attr( $status ),
						esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status )
					);
				}
				echo '</ul>';
			}
		);
	}
}

Nakliye_Admin::init();
