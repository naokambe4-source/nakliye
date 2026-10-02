<?php
/**
 * Nakliye Pro dağıtım paketi oluşturucu.
 *
 *  1. Temayı dist/nakliye-pro klasörüne kopyalar.
 *  2. (--obfuscate) Çekirdek PHP dosyalarındaki yorum ve boşlukları siler.
 *  3. Kritik dosyaların SHA-256 özetlerini çıkarır, lisans sunucusunun özel
 *     anahtarıyla imzalar ve inc/core/manifest.json olarak yazar.
 *  4. dist/nakliye-pro-<sürüm>.zip oluşturur (WordPress'e yüklenecek dosya).
 *
 * Kullanım: php tools/build-theme.php [--obfuscate]
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "Yalnızca komut satırından çalıştırılabilir.\n" );
}

$root      = dirname( __DIR__ );
$src       = $root . '/nakliye-pro';
$dist_root = $root . '/dist';
$dist      = $dist_root . '/nakliye-pro';
$priv_path = $root . '/license-server/keys/private.pem';
$obfuscate = in_array( '--obfuscate', $argv, true );

// --- Ön kontroller ---------------------------------------------------------
if ( ! is_readable( $priv_path ) ) {
	fail( "Özel anahtar bulunamadı. Önce çalıştırın: php license-server/tools/generate-keys.php" );
}
if ( false === strpos( (string) file_get_contents( $src . '/inc/core/public-key.php' ), 'BEGIN PUBLIC KEY' ) ) {
	fail( 'inc/core/public-key.php boş. generate-keys.php çalıştırın.' );
}
$private = openssl_pkey_get_private( file_get_contents( $priv_path ) );
if ( ! $private ) {
	fail( 'Özel anahtar okunamadı.' );
}
// Temadaki açık anahtar bu özel anahtarla eşleşiyor mu?
$pub_from_priv = openssl_pkey_get_details( $private )['key'];
if ( false === strpos( file_get_contents( $src . '/inc/core/public-key.php' ), trim( $pub_from_priv ) ) ) {
	fail( 'Temadaki açık anahtar, license-server/keys/private.pem ile eşleşmiyor.' );
}

preg_match( '/^Version:\s*(.+)$/mi', file_get_contents( $src . '/style.css' ), $m );
$version = isset( $m[1] ) ? trim( $m[1] ) : '0.0.0';

// --- Kopyala ---------------------------------------------------------------
rrmdir( $dist );
@mkdir( $dist_root, 0755, true );
rcopy( $src, $dist );
@unlink( $dist . '/inc/core/manifest.json' );
echo "✓ Kopyalandı → dist/nakliye-pro\n";

// --- Karartma (isteğe bağlı) ----------------------------------------------
if ( $obfuscate ) {
	$n = 0;
	foreach ( php_files( $dist . '/inc' ) as $file ) {
		file_put_contents( $file, php_strip_whitespace( $file ) );
		++$n;
	}
	file_put_contents( $dist . '/functions.php', php_strip_whitespace( $dist . '/functions.php' ) );
	echo "✓ $n çekirdek dosyadan yorum/boşluk temizlendi\n";
}

// --- Manifest ---------------------------------------------------------------
$files = array( 'functions.php' => hash_file( 'sha256', $dist . '/functions.php' ) );
foreach ( php_files( $dist . '/inc' ) as $file ) {
	$relative           = ltrim( str_replace( $dist, '', $file ), '/' );
	$files[ $relative ] = hash_file( 'sha256', $file );
}
ksort( $files );

$payload = base64_encode( json_encode( array( 'version' => $version, 'built' => time(), 'files' => $files ), JSON_UNESCAPED_SLASHES ) );
openssl_sign( $payload, $signature, $private, OPENSSL_ALGO_SHA256 );
file_put_contents(
	$dist . '/inc/core/manifest.json',
	json_encode( array( 'payload' => $payload, 'sig' => base64_encode( $signature ) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
);
echo '✓ ' . count( $files ) . " dosya için imzalı bütünlük manifesti yazıldı\n";

// --- Zip --------------------------------------------------------------------
$zip_path = $dist_root . '/nakliye-pro-' . $version . '.zip';
@unlink( $zip_path );
$zip = new ZipArchive();
if ( true !== $zip->open( $zip_path, ZipArchive::CREATE ) ) {
	fail( 'Zip oluşturulamadı.' );
}
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dist, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iterator as $file ) {
	$zip->addFile( $file->getPathname(), 'nakliye-pro/' . ltrim( str_replace( $dist, '', $file->getPathname() ), '/' ) );
}
$zip->close();
echo "✓ Paket hazır: dist/" . basename( $zip_path ) . "\n";

// --- Yardımcılar ------------------------------------------------------------
function fail( $msg ) {
	fwrite( STDERR, "✗ $msg\n" );
	exit( 1 );
}

function php_files( $dir ) {
	$out = array();
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) ) as $f ) {
		if ( 'php' === strtolower( $f->getExtension() ) ) {
			$out[] = $f->getPathname();
		}
	}
	sort( $out );
	return $out;
}

function rcopy( $from, $to ) {
	@mkdir( $to, 0755, true );
	foreach ( new DirectoryIterator( $from ) as $item ) {
		if ( $item->isDot() || in_array( $item->getFilename(), array( '.git', '.DS_Store', 'node_modules' ), true ) ) {
			continue;
		}
		$target = $to . '/' . $item->getFilename();
		if ( $item->isDir() ) {
			rcopy( $item->getPathname(), $target );
		} else {
			copy( $item->getPathname(), $target );
		}
	}
}

function rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST ) as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
}
