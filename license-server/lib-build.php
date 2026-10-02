<?php
/**
 * Tema paketleyici: açık anahtarı ve sunucu adresini temaya yazar,
 * kritik dosyaları imzalar (manifest.json) ve yüklenebilir zip üretir.
 *
 * Hem yönetim paneli (admin.php) hem tools/build-theme.php kullanır.
 */

if ( ! defined( 'NKLS' ) ) {
	http_response_code( 403 );
	exit;
}

/**
 * @param array $opts {
 *     @type string $source      Tema kaynak klasörü (nakliye-pro).
 *     @type string $private_pem Özel anahtar (PEM).
 *     @type string $server_url  Lisans sunucusu adresi (ör. https://lisans.site.com/buryaa).
 *     @type string $purchase    Satın alma sayfası adresi (isteğe bağlı).
 *     @type bool   $obfuscate   Çekirdek dosyalardan yorum/boşluk silinsin mi.
 *     @type string $zip_path    Zip'in yazılacağı yol (boşsa geçici dosya).
 * }
 * @return array{zip:string,version:string,files:int}
 * @throws RuntimeException Hata durumunda.
 */
function nkls_build_theme( array $opts ) {
	$opts = array_merge( array( 'source' => '', 'private_pem' => '', 'server_url' => '', 'purchase' => '', 'obfuscate' => false, 'zip_path' => '' ), $opts );

	if ( ! is_file( $opts['source'] . '/style.css' ) || ! is_file( $opts['source'] . '/inc/core/config.php' ) ) {
		throw new RuntimeException( 'Tema kaynağı bulunamadı: ' . $opts['source'] );
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		throw new RuntimeException( 'Sunucuda PHP Zip eklentisi yok. Hosting panelinden "zip" eklentisini açın.' );
	}
	$private = openssl_pkey_get_private( $opts['private_pem'] );
	if ( ! $private ) {
		throw new RuntimeException( 'Özel anahtar okunamadı.' );
	}
	$public = openssl_pkey_get_details( $private )['key'];

	$server = rtrim( trim( $opts['server_url'] ), '/' );
	if ( ! preg_match( '#^https?://[^\s\'"\\\\]+$#', $server ) ) {
		throw new RuntimeException( 'Lisans sunucusu adresi geçersiz: ' . $server );
	}

	preg_match( '/^Version:\s*(.+)$/mi', (string) file_get_contents( $opts['source'] . '/style.css' ), $m );
	$version = isset( $m[1] ) ? trim( $m[1] ) : '1.0.0';

	// 1) Geçici klasöre kopyala.
	$work = rtrim( sys_get_temp_dir(), '/\\' ) . '/nkls-build-' . bin2hex( random_bytes( 6 ) );
	$dist = $work . '/nakliye-pro';
	nkls_rcopy( $opts['source'], $dist );
	@unlink( $dist . '/inc/core/manifest.json' );

	try {
		// 2) Açık anahtar.
		file_put_contents(
			$dist . '/inc/core/public-key.php',
			"<?php\n/**\n * Lisans sunucusunun RSA açık anahtarı (paketleyici tarafından yazıldı).\n *\n * @package Nakliye\n */\n\ndefined( 'ABSPATH' ) || exit;\n\ndefine( 'NAKLIYE_PUBLIC_KEY', " . var_export( $public, true ) . " );\n"
		);

		// 3) Sunucu ve satın alma adresi.
		$cfg = file_get_contents( $dist . '/inc/core/config.php' );
		$cfg = preg_replace( "/define\(\s*'NAKLIYE_LICENSE_SERVER',\s*'[^']*'\s*\)/", "define( 'NAKLIYE_LICENSE_SERVER', " . var_export( $server, true ) . ' )', $cfg );
		if ( $opts['purchase'] ) {
			$cfg = preg_replace( "/define\(\s*'NAKLIYE_PURCHASE_URL',\s*'[^']*'\s*\)/", "define( 'NAKLIYE_PURCHASE_URL', " . var_export( (string) $opts['purchase'], true ) . ' )', $cfg );
		}
		file_put_contents( $dist . '/inc/core/config.php', $cfg );

		// 4) Karartma (isteğe bağlı).
		if ( $opts['obfuscate'] ) {
			foreach ( nkls_php_files( $dist . '/inc' ) as $file ) {
				file_put_contents( $file, php_strip_whitespace( $file ) );
			}
			file_put_contents( $dist . '/functions.php', php_strip_whitespace( $dist . '/functions.php' ) );
		}

		// 5) İmzalı bütünlük manifesti.
		$files = array( 'functions.php' => hash_file( 'sha256', $dist . '/functions.php' ) );
		foreach ( nkls_php_files( $dist . '/inc' ) as $file ) {
			$files[ ltrim( str_replace( '\\', '/', substr( $file, strlen( $dist ) ) ), '/' ) ] = hash_file( 'sha256', $file );
		}
		ksort( $files );
		$payload = base64_encode( json_encode( array( 'version' => $version, 'built' => time(), 'files' => $files ), JSON_UNESCAPED_SLASHES ) );
		openssl_sign( $payload, $signature, $private, OPENSSL_ALGO_SHA256 );
		file_put_contents( $dist . '/inc/core/manifest.json', json_encode( array( 'payload' => $payload, 'sig' => base64_encode( $signature ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

		// 6) Zip.
		$zip_path = $opts['zip_path'] ? $opts['zip_path'] : $work . '.zip';
		@unlink( $zip_path );
		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path, ZipArchive::CREATE ) ) {
			throw new RuntimeException( 'Zip dosyası oluşturulamadı: ' . $zip_path );
		}
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dist, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			$relative = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dist ) ) ), '/' );
			$zip->addFile( $file->getPathname(), 'nakliye-pro/' . $relative );
		}
		$zip->close();
	} finally {
		nkls_rrmdir( $work );
	}

	return array( 'zip' => $zip_path, 'version' => $version, 'files' => count( $files ) );
}

/**
 * @param string $dir Klasör.
 * @return string[]
 */
function nkls_php_files( $dir ) {
	$out = array();
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) ) as $f ) {
		if ( 'php' === strtolower( $f->getExtension() ) ) {
			$out[] = $f->getPathname();
		}
	}
	sort( $out );
	return $out;
}

/**
 * @param string $from Kaynak.
 * @param string $to   Hedef.
 */
function nkls_rcopy( $from, $to ) {
	if ( ! is_dir( $to ) && ! mkdir( $to, 0755, true ) ) {
		throw new RuntimeException( 'Geçici klasör oluşturulamadı: ' . $to );
	}
	foreach ( new DirectoryIterator( $from ) as $item ) {
		if ( $item->isDot() || in_array( $item->getFilename(), array( '.git', '.DS_Store', 'node_modules', '.htaccess' ), true ) ) {
			continue;
		}
		$target = $to . '/' . $item->getFilename();
		if ( $item->isDir() ) {
			nkls_rcopy( $item->getPathname(), $target );
		} else {
			copy( $item->getPathname(), $target );
		}
	}
}

/**
 * @param string $dir Klasör.
 */
function nkls_rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST ) as $f ) {
		$f->isDir() ? @rmdir( $f->getPathname() ) : @unlink( $f->getPathname() );
	}
	@rmdir( $dir );
}
