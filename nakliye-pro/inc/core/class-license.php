<?php
/**
 * Lisans istemcisi.
 *
 * Akış:
 *  1. Kullanıcı lisans anahtarını girer → sunucuya /activate isteği gider.
 *  2. Sunucu alan adına bağlı, RSA ile imzalı bir jeton döndürür.
 *  3. Tema her istekte jetonun imzasını AÇIK anahtarla doğrular; veritabanında
 *     "aktif" yazan bir değeri elle değiştirmek işe yaramaz, imza tutmaz.
 *  4. Günlük cron ile sunucuya /check yapılır; sunucu her yanıta istemcinin
 *     gönderdiği tek kullanımlık nonce'u ekleyip imzalar (eski yanıtlar tekrar oynatılamaz).
 *  5. Sunucuya ulaşılamazsa NAKLIYE_LICENSE_GRACE_PERIOD kadar tolerans tanınır.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_License {

	const OPTION   = 'nakliye_license';
	const CRON     = 'nakliye_license_check';
	const PRODUCT  = 'nakliye-pro';

	/** @var Nakliye_License|null */
	private static $instance = null;

	/** @var bool|null İstek içi önbellek. */
	private $active = null;

	/** @var string|null Son hata mesajı. */
	private $last_error = null;

	/**
	 * @return Nakliye_License
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( self::CRON, array( $this, 'remote_check' ) );
		add_action( 'init', array( $this, 'schedule' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_footer', array( $this, 'frontend_watermark' ), 999 );
		add_action( 'switch_theme', array( $this, 'unschedule' ) );
		add_action( 'upgrader_process_complete', array( 'Nakliye_Integrity', 'flush' ) );
	}

	public function __clone() {
		_doing_it_wrong( __METHOD__, 'Singleton', NAKLIYE_VERSION );
	}

	/* --------------------------------------------------------------------- */
	/* Durum                                                                 */
	/* --------------------------------------------------------------------- */

	/**
	 * Premium özellikler açık mı?
	 *
	 * @return bool
	 */
	public function is_active() {
		if ( null !== $this->active ) {
			return $this->active;
		}

		if ( nakliye_is_dev_domain() ) {
			return $this->active = true;
		}

		// Dosyalar değiştirildiyse lisans ne olursa olsun kilitlenir.
		if ( ! Nakliye_Integrity::is_intact() ) {
			return $this->active = false;
		}

		return $this->active = ( true === $this->verify_stored_token() );
	}

	/**
	 * Ayrıntılı durum bilgisi (yönetim paneli için).
	 *
	 * @return array
	 */
	public function status() {
		$data      = $this->get_data();
		$payload   = $this->decode_payload( isset( $data['token'] ) ? $data['token'] : '' );
		$integrity = Nakliye_Integrity::check();
		$verified  = $this->verify_stored_token();

		if ( nakliye_is_dev_domain() ) {
			$code = 'dev';
		} elseif ( ! nakliye_public_key_ready() ) {
			$code = 'not_configured';
		} elseif ( Nakliye_Integrity::STATUS_OK !== $integrity['status'] ) {
			$code = 'tampered';
		} elseif ( true === $verified ) {
			$code = 'active';
		} elseif ( empty( $data['key'] ) ) {
			$code = 'inactive';
		} else {
			$code = is_string( $verified ) ? $verified : 'invalid';
		}

		return array(
			'code'        => $code,
			'label'       => $this->status_label( $code ),
			'key'         => isset( $data['key'] ) ? $this->mask( $data['key'] ) : '',
			'domain'      => nakliye_current_domain(),
			'expires'     => isset( $payload['expires'] ) ? (int) $payload['expires'] : 0,
			'customer'    => isset( $payload['customer'] ) ? $payload['customer'] : '',
			'sites'       => isset( $payload['sites'] ) ? $payload['sites'] : '',
			'last_check'  => isset( $data['last_check'] ) ? (int) $data['last_check'] : 0,
			'integrity'   => $integrity,
			'last_error'  => isset( $data['last_error'] ) ? $data['last_error'] : '',
		);
	}

	/**
	 * @param string $code Durum kodu.
	 * @return string
	 */
	public function status_label( $code ) {
		$labels = array(
			'active'         => __( 'Aktif', 'nakliye' ),
			'dev'            => __( 'Geliştirme ortamı (lisans gerekmez)', 'nakliye' ),
			'inactive'       => __( 'Etkinleştirilmemiş', 'nakliye' ),
			'expired'        => __( 'Süresi dolmuş', 'nakliye' ),
			'domain'         => __( 'Başka bir alan adına ait', 'nakliye' ),
			'stale'          => __( 'Doğrulanamadı (sunucuya ulaşılamıyor)', 'nakliye' ),
			'tampered'       => __( 'Tema dosyaları değiştirilmiş', 'nakliye' ),
			'not_configured' => __( 'Lisans altyapısı yapılandırılmamış', 'nakliye' ),
			'invalid'        => __( 'Geçersiz', 'nakliye' ),
		);
		return isset( $labels[ $code ] ) ? $labels[ $code ] : $code;
	}

	/**
	 * @return string|null
	 */
	public function last_error() {
		return $this->last_error;
	}

	/* --------------------------------------------------------------------- */
	/* Etkinleştirme / devre dışı bırakma                                    */
	/* --------------------------------------------------------------------- */

	/**
	 * @param string $key Lisans anahtarı.
	 * @return bool
	 */
	public function activate( $key ) {
		$key = strtoupper( trim( preg_replace( '/[^A-Za-z0-9\-]/', '', (string) $key ) ) );
		if ( strlen( $key ) < 16 ) {
			$this->last_error = __( 'Lisans anahtarı biçimi geçersiz.', 'nakliye' );
			return false;
		}

		$nonce    = wp_generate_password( 32, false, false );
		$response = $this->request( 'activate', array( 'license_key' => $key, 'nonce' => $nonce ) );
		if ( ! $response ) {
			return false;
		}

		$payload = $this->validate_response( $response, $nonce );
		if ( ! $payload ) {
			return false;
		}

		$this->save(
			array(
				'key'        => $key,
				'token'      => $response['token'],
				'sig'        => $response['signature'],
				'last_check' => time(),
				'last_error' => '',
			)
		);

		$this->active = null;
		return true;
	}

	/**
	 * @return bool
	 */
	public function deactivate() {
		$data = $this->get_data();
		if ( ! empty( $data['key'] ) ) {
			// Sunucu tarafında yer açılsın; başarısız olsa bile yerel veri silinir.
			$this->request( 'deactivate', array( 'license_key' => $data['key'], 'nonce' => wp_generate_password( 32, false, false ) ) );
		}
		delete_option( self::OPTION );
		$this->active = null;
		return true;
	}

	/**
	 * Günlük uzak doğrulama (cron).
	 */
	public function remote_check() {
		$data = $this->get_data();
		if ( empty( $data['key'] ) ) {
			return;
		}

		$nonce    = wp_generate_password( 32, false, false );
		$response = $this->request( 'check', array( 'license_key' => $data['key'], 'nonce' => $nonce ) );

		if ( ! $response ) {
			// Ağ hatası: mevcut jeton tolerans süresince geçerli kalır.
			$data['last_error'] = $this->last_error;
			$this->save( $data );
			return;
		}

		if ( empty( $response['success'] ) ) {
			// Sunucu lisansı açıkça reddetti (iptal, süre bitti, alan adı kaldırıldı).
			$data['token']      = '';
			$data['sig']        = '';
			$data['last_error'] = isset( $response['message'] ) ? sanitize_text_field( $response['message'] ) : __( 'Lisans sunucu tarafından reddedildi.', 'nakliye' );
			$this->save( $data );
			$this->active = null;
			return;
		}

		$payload = $this->validate_response( $response, $nonce );
		if ( $payload ) {
			$data['token']      = $response['token'];
			$data['sig']        = $response['signature'];
			$data['last_check'] = time();
			$data['last_error'] = '';
		} else {
			$data['last_error'] = $this->last_error;
		}
		$this->save( $data );
		$this->active = null;
	}

	public function schedule() {
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
		}
	}

	public function unschedule() {
		wp_clear_scheduled_hook( self::CRON );
	}

	/* --------------------------------------------------------------------- */
	/* Bildirimler                                                           */
	/* --------------------------------------------------------------------- */

	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) || $this->is_active() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && false !== strpos( $screen->id, 'nakliye-setup' ) ) {
			return;
		}
		$status = $this->status();
		$class  = 'tampered' === $status['code'] ? 'notice-error' : 'notice-warning';
		printf(
			'<div class="notice %1$s nakliye-notice"><p><strong>Nakliye Pro:</strong> %2$s — %3$s</p></div>',
			esc_attr( $class ),
			esc_html( $status['label'] ),
			wp_kses_post( nakliye_locked_message() )
		);
	}

	/**
	 * Lisanssız canlı sitelerde ön yüzde küçük bir uyarı şeridi gösterilir.
	 */
	public function frontend_watermark() {
		if ( $this->is_active() || is_admin() ) {
			return;
		}
		echo '<div class="nk-unlicensed" role="note">' . esc_html__( 'Bu sitede lisanssız Nakliye Pro teması kullanılmaktadır.', 'nakliye' ) . '</div>';
	}

	/* --------------------------------------------------------------------- */
	/* İç işleyiş                                                            */
	/* --------------------------------------------------------------------- */

	/**
	 * Kayıtlı jetonu doğrular.
	 *
	 * @return true|string True veya hata kodu.
	 */
	private function verify_stored_token() {
		$data = $this->get_data();
		if ( empty( $data['token'] ) || empty( $data['sig'] ) ) {
			return 'inactive';
		}
		if ( ! nakliye_verify_signature( $data['token'], $data['sig'] ) ) {
			return 'invalid';
		}
		$payload = $this->decode_payload( $data['token'] );
		if ( ! $payload || self::PRODUCT !== ( isset( $payload['product'] ) ? $payload['product'] : '' ) ) {
			return 'invalid';
		}
		if ( ! hash_equals( (string) $payload['domain'], nakliye_current_domain() ) ) {
			return 'domain';
		}
		if ( ! hash_equals( (string) $payload['key_hash'], hash( 'sha256', (string) $data['key'] ) ) ) {
			return 'invalid';
		}
		if ( ! empty( $payload['expires'] ) && (int) $payload['expires'] < time() ) {
			return 'expired';
		}
		// İmzalı jetonun içindeki "issued" değeri DB'deki last_check ile oynanmasını engeller.
		$issued = isset( $payload['issued'] ) ? (int) $payload['issued'] : 0;
		if ( $issued + NAKLIYE_LICENSE_CHECK_INTERVAL + NAKLIYE_LICENSE_GRACE_PERIOD < time() ) {
			return 'stale';
		}
		return true;
	}

	/**
	 * Sunucu yanıtını doğrular.
	 *
	 * @param array  $response Yanıt.
	 * @param string $nonce    Gönderilen nonce.
	 * @return array|false Payload.
	 */
	private function validate_response( array $response, $nonce ) {
		if ( empty( $response['success'] ) ) {
			$this->last_error = isset( $response['message'] ) ? sanitize_text_field( $response['message'] ) : __( 'Lisans etkinleştirilemedi.', 'nakliye' );
			return false;
		}
		if ( empty( $response['token'] ) || empty( $response['signature'] ) || ! nakliye_verify_signature( $response['token'], $response['signature'] ) ) {
			$this->last_error = __( 'Sunucu yanıtının imzası doğrulanamadı.', 'nakliye' );
			return false;
		}
		$payload = $this->decode_payload( $response['token'] );
		if ( ! $payload || ! isset( $payload['nonce'] ) || ! hash_equals( $nonce, (string) $payload['nonce'] ) ) {
			$this->last_error = __( 'Sunucu yanıtı bu isteğe ait değil.', 'nakliye' );
			return false;
		}
		if ( ! isset( $payload['domain'] ) || nakliye_current_domain() !== $payload['domain'] ) {
			$this->last_error = __( 'Lisans bu alan adı için düzenlenmemiş.', 'nakliye' );
			return false;
		}
		return $payload;
	}

	/**
	 * @param string $action Uç nokta.
	 * @param array  $body   Gövde.
	 * @return array|false
	 */
	private function request( $action, array $body ) {
		if ( ! nakliye_public_key_ready() ) {
			$this->last_error = __( 'Lisans açık anahtarı yapılandırılmamış. README dosyasındaki kurulum adımlarını izleyin.', 'nakliye' );
			return false;
		}

		$body = array_merge(
			$body,
			array(
				'action'  => $action,
				'product' => self::PRODUCT,
				'domain'  => nakliye_current_domain(),
				'site'    => home_url(),
				'version' => NAKLIYE_VERSION,
				'wp'      => get_bloginfo( 'version' ),
				'php'     => PHP_VERSION,
			)
		);

		$response = wp_remote_post(
			trailingslashit( NAKLIYE_LICENSE_SERVER ) . 'api.php',
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'headers'   => array( 'Accept' => 'application/json' ),
				'body'      => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			/* translators: %s: hata mesajı */
			$this->last_error = sprintf( __( 'Lisans sunucusuna bağlanılamadı: %s', 'nakliye' ), $response->get_error_message() );
			return false;
		}

		$json = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $json ) ) {
			$this->last_error = __( 'Lisans sunucusundan geçersiz yanıt alındı.', 'nakliye' );
			return false;
		}
		if ( empty( $json['success'] ) && ! empty( $json['message'] ) ) {
			$this->last_error = sanitize_text_field( $json['message'] );
		}
		return $json;
	}

	/**
	 * @param string $token Base64 JSON jeton.
	 * @return array|null
	 */
	private function decode_payload( $token ) {
		if ( ! is_string( $token ) || '' === $token ) {
			return null;
		}
		$json = json_decode( (string) base64_decode( $token, true ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		return is_array( $json ) ? $json : null;
	}

	/**
	 * @return array
	 */
	private function get_data() {
		$data = get_option( self::OPTION, array() );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * @param array $data Veri.
	 */
	private function save( array $data ) {
		update_option( self::OPTION, $data, false );
	}

	/**
	 * @param string $key Anahtar.
	 * @return string
	 */
	private function mask( $key ) {
		$len = strlen( $key );
		return $len > 8 ? substr( $key, 0, 4 ) . str_repeat( '•', max( 0, $len - 8 ) ) . substr( $key, -4 ) : $key;
	}
}
