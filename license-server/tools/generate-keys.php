<?php
/**
 * (İsteğe bağlı) Komut satırından RSA anahtarı üretir.
 * Paylaşımlı hostingde gerekmez: install.php aynı işi tarayıcıdan yapar.
 *
 * Kullanım: php license-server/tools/generate-keys.php [--force]
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "Yalnızca komut satırından çalıştırılabilir.\n" );
}

define( 'NKLS', true );
require dirname( __DIR__ ) . '/lib.php';

$path = nkls_config()['private_key_path'];
if ( is_file( $path ) && ! in_array( '--force', $argv, true ) ) {
	fwrite( STDERR, "Özel anahtar zaten var: $path\nYeniden üretmek verilen TÜM lisansları geçersiz kılar. Emin iseniz --force ekleyin.\n" );
	exit( 1 );
}
$pem = nkls_generate_private_key();
if ( ! $pem ) {
	fwrite( STDERR, "OpenSSL anahtar üretemedi.\n" );
	exit( 1 );
}
@mkdir( dirname( $path ), 0700, true );
file_put_contents( $path, $pem );
chmod( $path, 0600 );
echo "✓ Özel anahtar: $path (gizli tutun, yedekleyin!)\n";
echo "Sonraki adım : php tools/build-theme.php --server=https://lisans.siteniz.com/klasor\n";
