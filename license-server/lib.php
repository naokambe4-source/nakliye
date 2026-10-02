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

/* ------------------------------------------------------------------------- */
/* Kurulum yardımcıları                                                      */
/* ------------------------------------------------------------------------- */

/**
 * Sunucu kurulmuş mu? (parola + özel anahtar mevcut)
 *
 * @return bool
 */
function nkls_installed() {
	$config = nkls_config();
	return ! empty( $config['admin_password_hash'] ) && is_readable( $config['private_key_path'] );
}

/**
 * Bu klasörün dışarıdan görünen adresi, ör. https://lisans.site.com/buryaa
 *
 * @return string
 */
function nkls_base_url() {
	$https = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== strtolower( (string) $_SERVER['HTTPS'] ) )
		|| ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( (string) $_SERVER['HTTP_X_FORWARDED_PROTO'] ) )
		|| ( isset( $_SERVER['SERVER_PORT'] ) && 443 === (int) $_SERVER['SERVER_PORT'] );
	$host  = isset( $_SERVER['HTTP_HOST'] ) ? preg_replace( '/[^A-Za-z0-9.\-:]/', '', (string) $_SERVER['HTTP_HOST'] ) : 'localhost';
	$path  = isset( $_SERVER['SCRIPT_NAME'] ) ? str_replace( '\\', '/', dirname( (string) $_SERVER['SCRIPT_NAME'] ) ) : '';
	$path  = rtrim( $path, '/.' );
	return ( $https ? 'https' : 'http' ) . '://' . $host . $path;
}

/**
 * Özel anahtardan türetilen açık anahtar (PEM).
 *
 * @return string
 */
function nkls_public_key() {
	$path = nkls_config()['private_key_path'];
	$key  = is_readable( $path ) ? openssl_pkey_get_private( file_get_contents( $path ) ) : false;
	if ( ! $key ) {
		return '';
	}
	$details = openssl_pkey_get_details( $key );
	return isset( $details['key'] ) ? $details['key'] : '';
}

/**
 * RSA-2048 anahtar üretir. Bazı hostinglerde openssl.cnf yolu gerekir; yedekleri dener.
 *
 * @return string|false PEM özel anahtar.
 */
function nkls_generate_private_key() {
	$base       = array( 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA );
	$candidates = array( null );
	foreach ( array( getenv( 'OPENSSL_CONF' ), '/etc/ssl/openssl.cnf', '/usr/lib/ssl/openssl.cnf', '/usr/local/ssl/openssl.cnf', 'C:/xampp/apache/conf/openssl.cnf' ) as $cnf ) {
		if ( $cnf && is_file( $cnf ) ) {
			$candidates[] = $cnf;
		}
	}
	foreach ( $candidates as $cnf ) {
		$opts = $cnf ? $base + array( 'config' => $cnf ) : $base;
		$res  = @openssl_pkey_new( $opts );
		if ( $res && @openssl_pkey_export( $res, $pem, null, $cnf ? array( 'config' => $cnf ) : array() ) ) {
			return $pem;
		}
	}
	return false;
}

/**
 * Sunucu gereksinimleri.
 *
 * @return array[] [etiket, değer, tamam mı, zorunlu mu]
 */
function nkls_requirements() {
	$config = nkls_config();
	$dirs   = array( dirname( $config['db_path'] ), dirname( $config['private_key_path'] ) );
	$write  = true;
	foreach ( $dirs as $dir ) {
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}
		$write = $write && is_dir( $dir ) && is_writable( $dir );
	}
	return array(
		array( 'PHP sürümü (7.4+)', PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ), true ),
		array( 'OpenSSL eklentisi', extension_loaded( 'openssl' ) ? 'Var' : 'Yok', extension_loaded( 'openssl' ), true ),
		array( 'PDO SQLite eklentisi', extension_loaded( 'pdo_sqlite' ) ? 'Var' : 'Yok', extension_loaded( 'pdo_sqlite' ), true ),
		array( 'data/ ve keys/ yazılabilir', $write ? 'Evet' : 'Hayır (izinleri 755 yapın)', $write, true ),
		array( 'Zip eklentisi (tema paketi için)', class_exists( 'ZipArchive' ) ? 'Var' : 'Yok', class_exists( 'ZipArchive' ), false ),
		array( 'Tema kaynağı (tema-kaynak/nakliye-pro)', is_dir( $config['theme_source'] ) ? 'Bulundu' : 'Yok', is_dir( $config['theme_source'] ), false ),
		array( 'HTTPS', 0 === strpos( nkls_base_url(), 'https://' ) ? 'Evet' : 'Hayır', 0 === strpos( nkls_base_url(), 'https://' ), false ),
	);
}

/**
 * Sihirbaz ayarlarını kaydeder.
 *
 * @param array $values Değerler.
 * @return bool
 */
function nkls_save_settings( array $values ) {
	$path    = nkls_config()['settings_path'];
	$current = is_file( $path ) ? include $path : array();
	$current = is_array( $current ) ? array_merge( $current, $values ) : $values;
	$php     = "<?php\n// Nakliye Pro lisans sunucusu ayarları (kurulum sihirbazı tarafından yazıldı).\nreturn " . var_export( $current, true ) . ";\n";
	return false !== @file_put_contents( $path, $php, LOCK_EX );
}

/**
 * Gizli dosyalar dışarıdan okunabiliyor mu? (sunucu kendi kendini dener)
 *
 * @return bool|null true = AÇIK (tehlikeli), false = kapalı, null = test edilemedi.
 */
function nkls_secrets_exposed() {
	if ( ! ini_get( 'allow_url_fopen' ) ) {
		return null;
	}
	$ctx  = stream_context_create( array( 'http' => array( 'timeout' => 4, 'ignore_errors' => true ), 'ssl' => array( 'verify_peer' => false, 'verify_peer_name' => false ) ) );
	$body = @file_get_contents( nkls_base_url() . '/keys/.ht-private.pem', false, $ctx );
	if ( false === $body ) {
		return null;
	}
	return false !== strpos( $body, 'PRIVATE KEY' );
}
