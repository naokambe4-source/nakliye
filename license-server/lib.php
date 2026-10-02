<?php
/**
 * Lisans sunucusu ortak fonksiyonları.
 */

if ( ! defined( 'NKLS' ) ) {
	http_response_code( 403 );
	exit;
}

/**
 * @return array
 */
function nkls_config() {
	static $config = null;
	if ( null === $config ) {
		$config = require __DIR__ . '/config.php';
	}
	return $config;
}

/**
 * @return PDO
 */
function nkls_db() {
	static $pdo = null;
	if ( null !== $pdo ) {
		return $pdo;
	}
	$path = nkls_config()['db_path'];
	if ( ! is_dir( dirname( $path ) ) ) {
		mkdir( dirname( $path ), 0700, true );
	}
	$pdo = new PDO( 'sqlite:' . $path );
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	$pdo->exec( 'PRAGMA foreign_keys = ON' );
	$pdo->exec(
		'CREATE TABLE IF NOT EXISTS licenses (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			license_key TEXT NOT NULL UNIQUE,
			product TEXT NOT NULL,
			customer TEXT NOT NULL DEFAULT "",
			email TEXT NOT NULL DEFAULT "",
			max_sites INTEGER NOT NULL DEFAULT 1,
			expires_at INTEGER NOT NULL DEFAULT 0,
			status TEXT NOT NULL DEFAULT "active",
			note TEXT NOT NULL DEFAULT "",
			created_at INTEGER NOT NULL
		)'
	);
	$pdo->exec(
		'CREATE TABLE IF NOT EXISTS activations (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			license_id INTEGER NOT NULL REFERENCES licenses(id) ON DELETE CASCADE,
			domain TEXT NOT NULL,
			site_url TEXT NOT NULL DEFAULT "",
			version TEXT NOT NULL DEFAULT "",
			ip TEXT NOT NULL DEFAULT "",
			activated_at INTEGER NOT NULL,
			last_seen INTEGER NOT NULL,
			UNIQUE(license_id, domain)
		)'
	);
	$pdo->exec(
		'CREATE TABLE IF NOT EXISTS events (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			license_id INTEGER,
			action TEXT NOT NULL,
			domain TEXT NOT NULL DEFAULT "",
			ip TEXT NOT NULL DEFAULT "",
			result TEXT NOT NULL DEFAULT "",
			created_at INTEGER NOT NULL
		)'
	);
	$pdo->exec( 'CREATE TABLE IF NOT EXISTS hits ( ip TEXT NOT NULL, ts INTEGER NOT NULL )' );
	return $pdo;
}

/**
 * Yeni lisans anahtarı: NKP-XXXX-XXXX-XXXX-XXXX (karışan karakterler hariç).
 *
 * @return string
 */
function nkls_generate_key() {
	$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$groups   = array();
	for ( $g = 0; $g < 4; $g++ ) {
		$part = '';
		for ( $i = 0; $i < 4; $i++ ) {
			$part .= $alphabet[ random_int( 0, strlen( $alphabet ) - 1 ) ];
		}
		$groups[] = $part;
	}
	return 'NKP-' . implode( '-', $groups );
}

/**
 * @param string $domain Alan adı.
 * @return string
 */
function nkls_normalize_domain( $domain ) {
	$domain = strtolower( trim( (string) $domain ) );
	$domain = preg_replace( '#^https?://#', '', $domain );
	$domain = preg_replace( '#[/:].*$#', '', $domain );
	return preg_replace( '/^www\./', '', $domain );
}

/**
 * Veriyi özel anahtarla imzalar.
 *
 * @param array $payload Veri.
 * @return array{token:string,signature:string}
 */
function nkls_sign( array $payload ) {
	$key_path = nkls_config()['private_key_path'];
	$key      = is_readable( $key_path ) ? openssl_pkey_get_private( file_get_contents( $key_path ) ) : false;
	if ( ! $key ) {
		throw new RuntimeException( 'Özel anahtar okunamadı. tools/generate-keys.php çalıştırın.' );
	}
	$token = base64_encode( json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	openssl_sign( $token, $signature, $key, OPENSSL_ALGO_SHA256 );
	return array( 'token' => $token, 'signature' => base64_encode( $signature ) );
}

/**
 * @return string
 */
function nkls_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

/**
 * @param int|null $license_id Lisans.
 * @param string   $action     İşlem.
 * @param string   $domain     Alan adı.
 * @param string   $result     Sonuç.
 */
function nkls_log( $license_id, $action, $domain, $result ) {
	$stmt = nkls_db()->prepare( 'INSERT INTO events (license_id, action, domain, ip, result, created_at) VALUES (?, ?, ?, ?, ?, ?)' );
	$stmt->execute( array( $license_id, $action, $domain, nkls_ip(), $result, time() ) );
}

/**
 * @param string $s Metin.
 * @return string
 */
function nkls_e( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}
