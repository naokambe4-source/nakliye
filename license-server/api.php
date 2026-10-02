<?php
/**
 * Lisans API uç noktası.
 *
 * POST action=activate|check|deactivate, license_key, domain, nonce, product
 * Yanıt: {"success":true,"token":"...","signature":"..."} veya {"success":false,"message":"..."}
 */

define( 'NKLS', true );
require __DIR__ . '/lib.php';

header( 'Content-Type: application/json; charset=utf-8' );
header( 'Cache-Control: no-store' );
header( 'X-Content-Type-Options: nosniff' );

/**
 * @param array $data Yanıt.
 * @param int   $code HTTP kodu.
 */
function nkls_respond( array $data, $code = 200 ) {
	http_response_code( $code );
	echo json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}

if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
	nkls_respond( array( 'success' => false, 'message' => 'Yalnızca POST.' ), 405 );
}

try {
	$db     = nkls_db();
	$config = nkls_config();

	// Hız sınırı.
	$db->prepare( 'DELETE FROM hits WHERE ts < ?' )->execute( array( time() - 60 ) );
	$stmt = $db->prepare( 'SELECT COUNT(*) FROM hits WHERE ip = ?' );
	$stmt->execute( array( nkls_ip() ) );
	if ( (int) $stmt->fetchColumn() >= (int) $config['rate_limit'] ) {
		nkls_respond( array( 'success' => false, 'message' => 'Çok fazla istek. Lütfen bir dakika sonra deneyin.' ), 429 );
	}
	$db->prepare( 'INSERT INTO hits (ip, ts) VALUES (?, ?)' )->execute( array( nkls_ip(), time() ) );

	$action  = isset( $_POST['action'] ) ? (string) $_POST['action'] : '';
	$key     = strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', isset( $_POST['license_key'] ) ? (string) $_POST['license_key'] : '' ) );
	$domain  = nkls_normalize_domain( isset( $_POST['domain'] ) ? $_POST['domain'] : '' );
	$nonce   = substr( preg_replace( '/[^A-Za-z0-9]/', '', isset( $_POST['nonce'] ) ? (string) $_POST['nonce'] : '' ), 0, 64 );
	$product = isset( $_POST['product'] ) ? (string) $_POST['product'] : '';

	if ( ! in_array( $action, array( 'activate', 'check', 'deactivate' ), true ) || ! $key || ! $domain || strlen( $nonce ) < 16 ) {
		nkls_respond( array( 'success' => false, 'message' => 'Eksik veya geçersiz istek.' ), 400 );
	}
	if ( $product !== $config['product'] ) {
		nkls_respond( array( 'success' => false, 'message' => 'Bu lisans bu ürüne ait değil.' ), 400 );
	}

	$stmt = $db->prepare( 'SELECT * FROM licenses WHERE license_key = ? AND product = ?' );
	$stmt->execute( array( $key, $product ) );
	$license = $stmt->fetch();

	if ( ! $license ) {
		nkls_log( null, $action, $domain, 'not_found' );
		nkls_respond( array( 'success' => false, 'message' => 'Lisans anahtarı bulunamadı.' ) );
	}

	$lid = (int) $license['id'];

	if ( 'deactivate' === $action ) {
		$db->prepare( 'DELETE FROM activations WHERE license_id = ? AND domain = ?' )->execute( array( $lid, $domain ) );
		nkls_log( $lid, $action, $domain, 'ok' );
		nkls_respond( array( 'success' => true, 'message' => 'Lisans bu alan adından kaldırıldı.' ) );
	}

	if ( 'active' !== $license['status'] ) {
		nkls_log( $lid, $action, $domain, 'revoked' );
		nkls_respond( array( 'success' => false, 'message' => 'Bu lisans iptal edilmiş. Destek ekibiyle iletişime geçin.' ) );
	}
	if ( $license['expires_at'] && (int) $license['expires_at'] < time() ) {
		nkls_log( $lid, $action, $domain, 'expired' );
		nkls_respond( array( 'success' => false, 'message' => 'Lisansın süresi dolmuş. Lütfen yenileyin.' ) );
	}

	$stmt = $db->prepare( 'SELECT * FROM activations WHERE license_id = ? AND domain = ?' );
	$stmt->execute( array( $lid, $domain ) );
	$activation = $stmt->fetch();

	$site    = isset( $_POST['site'] ) ? substr( (string) $_POST['site'], 0, 255 ) : '';
	$version = isset( $_POST['version'] ) ? substr( (string) $_POST['version'], 0, 20 ) : '';

	if ( ! $activation ) {
		if ( 'check' === $action ) {
			nkls_log( $lid, $action, $domain, 'not_activated' );
			nkls_respond( array( 'success' => false, 'message' => 'Lisans bu alan adında etkin değil.' ) );
		}
		$stmt = $db->prepare( 'SELECT COUNT(*) FROM activations WHERE license_id = ?' );
		$stmt->execute( array( $lid ) );
		if ( (int) $stmt->fetchColumn() >= (int) $license['max_sites'] ) {
			nkls_log( $lid, $action, $domain, 'limit' );
			nkls_respond( array( 'success' => false, 'message' => sprintf( 'Bu lisans en fazla %d sitede kullanılabilir. Önce eski siteden kaldırın.', (int) $license['max_sites'] ) ) );
		}
		$db->prepare( 'INSERT INTO activations (license_id, domain, site_url, version, ip, activated_at, last_seen) VALUES (?, ?, ?, ?, ?, ?, ?)' )
			->execute( array( $lid, $domain, $site, $version, nkls_ip(), time(), time() ) );
	} else {
		$db->prepare( 'UPDATE activations SET last_seen = ?, version = ?, site_url = ?, ip = ? WHERE id = ?' )
			->execute( array( time(), $version, $site, nkls_ip(), $activation['id'] ) );
	}

	$stmt = $db->prepare( 'SELECT COUNT(*) FROM activations WHERE license_id = ?' );
	$stmt->execute( array( $lid ) );
	$used = (int) $stmt->fetchColumn();

	$signed = nkls_sign(
		array(
			'product'  => $product,
			'domain'   => $domain,
			'key_hash' => hash( 'sha256', $key ),
			'expires'  => (int) $license['expires_at'],
			'issued'   => time(),
			'nonce'    => $nonce,
			'customer' => $license['customer'],
			'sites'    => $used . '/' . (int) $license['max_sites'],
		)
	);

	nkls_log( $lid, $action, $domain, 'ok' );
	nkls_respond( array( 'success' => true ) + $signed );
} catch ( Throwable $e ) {
	error_log( '[nakliye-license] ' . $e->getMessage() );
	nkls_respond( array( 'success' => false, 'message' => 'Lisans sunucusunda geçici bir hata oluştu.' ), 500 );
}
