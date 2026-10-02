<?php
/**
 * Tema dosya bütünlüğü koruması.
 *
 * Derleme sırasında (tools/build-theme.php) kritik dosyaların SHA-256 özetleri
 * çıkarılır ve lisans sunucusunun özel anahtarıyla imzalanır. Tema her yüklemede
 * imzayı açık anahtarla doğrular ve dosyaları karşılaştırır. Lisans kontrolünü
 * devre dışı bırakmak için dosyalarla oynanırsa tema kilitli moda geçer.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_Integrity {

	const STATUS_OK        = 'ok';
	const STATUS_MISSING   = 'missing';
	const STATUS_SIGNATURE = 'invalid_signature';
	const STATUS_TAMPERED  = 'tampered';
	const STATUS_NO_KEY    = 'no_key';

	const CACHE_KEY = 'nakliye_integrity_state';

	/** @var array|null */
	private static $result = null;

	/**
	 * Bütünlük durumunu döndürür. İstek başına bir kez hesaplanır, dosyalar
	 * değişmedikçe transient önbelleğinden okunur.
	 *
	 * @return array{status:string,files:array}
	 */
	public static function check() {
		if ( null !== self::$result ) {
			return self::$result;
		}

		if ( ! nakliye_public_key_ready() ) {
			return self::$result = array( 'status' => self::STATUS_NO_KEY, 'files' => array() );
		}

		$manifest_file = NAKLIYE_DIR . '/inc/core/manifest.json';
		if ( ! is_readable( $manifest_file ) ) {
			return self::$result = array( 'status' => self::STATUS_MISSING, 'files' => array() );
		}

		$raw      = file_get_contents( $manifest_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$manifest = json_decode( (string) $raw, true );
		if ( ! is_array( $manifest ) || empty( $manifest['payload'] ) || empty( $manifest['sig'] ) ) {
			return self::$result = array( 'status' => self::STATUS_SIGNATURE, 'files' => array() );
		}

		if ( ! nakliye_verify_signature( $manifest['payload'], $manifest['sig'] ) ) {
			return self::$result = array( 'status' => self::STATUS_SIGNATURE, 'files' => array() );
		}

		$payload = json_decode( base64_decode( $manifest['payload'] ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$files   = isset( $payload['files'] ) && is_array( $payload['files'] ) ? $payload['files'] : array();

		// Dosya imzası (boyut + değişiklik zamanı) aynıysa önbellekteki sonucu kullan.
		$fingerprint = md5( $manifest['sig'] . self::fingerprint( array_keys( $files ) ) );
		$cached      = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) && isset( $cached['fp'] ) && $cached['fp'] === $fingerprint ) {
			return self::$result = $cached['result'];
		}

		$bad = array();
		foreach ( $files as $relative => $hash ) {
			$path = NAKLIYE_DIR . '/' . ltrim( $relative, '/' );
			if ( ! is_file( $path ) || ! hash_equals( (string) $hash, hash_file( 'sha256', $path ) ) ) {
				$bad[] = $relative;
			}
		}

		// Manifestte olmayan yeni PHP dosyaları çekirdek klasörlere eklenmiş mi?
		foreach ( array( 'inc/core', 'inc/elementor', 'inc/admin' ) as $dir ) {
			foreach ( self::php_files( NAKLIYE_DIR . '/' . $dir ) as $file ) {
				$relative = ltrim( str_replace( NAKLIYE_DIR, '', $file ), '/' );
				if ( ! isset( $files[ $relative ] ) ) {
					$bad[] = $relative;
				}
			}
		}

		$result = array(
			'status' => empty( $bad ) ? self::STATUS_OK : self::STATUS_TAMPERED,
			'files'  => array_values( array_unique( $bad ) ),
		);

		set_transient( self::CACHE_KEY, array( 'fp' => $fingerprint, 'result' => $result ), 12 * HOUR_IN_SECONDS );

		return self::$result = $result;
	}

	/**
	 * Bütünlük sağlam mı?
	 *
	 * @return bool
	 */
	public static function is_intact() {
		$state = self::check();
		return self::STATUS_OK === $state['status'];
	}

	/**
	 * Durum için okunabilir açıklama.
	 *
	 * @param string $status Durum kodu.
	 * @return string
	 */
	public static function label( $status ) {
		$labels = array(
			self::STATUS_OK        => __( 'Tema dosyaları orijinal.', 'nakliye' ),
			self::STATUS_MISSING   => __( 'Bütünlük manifesti bulunamadı (tema derlenmemiş).', 'nakliye' ),
			self::STATUS_SIGNATURE => __( 'Bütünlük manifestinin imzası geçersiz.', 'nakliye' ),
			self::STATUS_TAMPERED  => __( 'Tema dosyaları değiştirilmiş.', 'nakliye' ),
			self::STATUS_NO_KEY    => __( 'Lisans açık anahtarı yapılandırılmamış.', 'nakliye' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * Önbelleği temizler (tema güncellemesinden sonra çağrılır).
	 */
	public static function flush() {
		delete_transient( self::CACHE_KEY );
		self::$result = null;
	}

	/**
	 * @param string[] $relative_files Göreli dosya yolları.
	 * @return string
	 */
	private static function fingerprint( array $relative_files ) {
		$parts = array();
		foreach ( $relative_files as $relative ) {
			$path    = NAKLIYE_DIR . '/' . $relative;
			$parts[] = $relative . ':' . ( is_file( $path ) ? filesize( $path ) . ':' . filemtime( $path ) : 'x' );
		}
		foreach ( array( 'inc/core', 'inc/elementor', 'inc/admin' ) as $dir ) {
			$parts[] = implode( ',', self::php_files( NAKLIYE_DIR . '/' . $dir ) );
		}
		return implode( '|', $parts );
	}

	/**
	 * @param string $dir Klasör.
	 * @return string[]
	 */
	private static function php_files( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return array();
		}
		$out      = array();
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( 'php' === strtolower( $file->getExtension() ) ) {
				$out[] = $file->getPathname();
			}
		}
		sort( $out );
		return $out;
	}
}
