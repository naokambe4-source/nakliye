<?php
/**
 * Çekirdek yardımcı fonksiyonlar.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

/**
 * Açık anahtar yapılandırılmış mı?
 *
 * @return bool
 */
function nakliye_public_key_ready() {
	return defined( 'NAKLIYE_PUBLIC_KEY' ) && false !== strpos( NAKLIYE_PUBLIC_KEY, 'BEGIN PUBLIC KEY' ) && function_exists( 'openssl_verify' );
}

/**
 * Lisans sunucusunun RSA-SHA256 imzasını doğrular.
 *
 * @param string $data       İmzalanan veri.
 * @param string $signature  Base64 imza.
 * @return bool
 */
function nakliye_verify_signature( $data, $signature ) {
	if ( ! nakliye_public_key_ready() || ! is_string( $data ) || ! is_string( $signature ) ) {
		return false;
	}
	$key = openssl_pkey_get_public( NAKLIYE_PUBLIC_KEY );
	if ( ! $key ) {
		return false;
	}
	$raw = base64_decode( $signature, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( false === $raw ) {
		return false;
	}
	return 1 === openssl_verify( $data, $raw, $key, OPENSSL_ALGO_SHA256 );
}

/**
 * Lisansın bağlandığı alan adı (www. olmadan, küçük harf).
 *
 * @return string
 */
function nakliye_current_domain() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = strtolower( (string) $host );
	return preg_replace( '/^www\./', '', $host );
}

/**
 * Geliştirme / yerel ortam mı? Bu alan adlarında lisans gerekmez.
 *
 * @param string|null $domain Alan adı.
 * @return bool
 */
function nakliye_is_dev_domain( $domain = null ) {
	$domain = null === $domain ? nakliye_current_domain() : $domain;

	if ( in_array( $domain, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
		return true;
	}
	if ( preg_match( '/\.(local|test|localhost|invalid|example)$/', $domain ) ) {
		return true;
	}
	if ( filter_var( $domain, FILTER_VALIDATE_IP ) && ! filter_var( $domain, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
		return true;
	}
	return false;
}

/**
 * Premium özellikler açık mı? (Geçerli lisans + bozulmamış dosyalar veya yerel ortam.)
 *
 * @return bool
 */
function nakliye_is_licensed() {
	return Nakliye_License::instance()->is_active();
}

/**
 * Premium özellik kilidi için ortak mesaj.
 *
 * @return string
 */
function nakliye_locked_message() {
	return sprintf(
		/* translators: %s: lisans sayfası bağlantısı */
		__( 'Bu özellik geçerli bir Nakliye Pro lisansı gerektirir. <a href="%s">Lisansı etkinleştirin</a>.', 'nakliye' ),
		esc_url( admin_url( 'admin.php?page=nakliye-license' ) )
	);
}
