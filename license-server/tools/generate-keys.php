<?php
/**
 * RSA anahtar çifti üretir ve açık anahtarı temaya yazar.
 *
 * Kullanım: php license-server/tools/generate-keys.php [--force]
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "Yalnızca komut satırından çalıştırılabilir.\n" );
}

$root      = dirname( __DIR__, 2 );
$priv_path = dirname( __DIR__ ) . '/keys/private.pem';
$pub_php   = $root . '/nakliye-pro/inc/core/public-key.php';
$force     = in_array( '--force', $argv, true );

if ( is_file( $priv_path ) && ! $force ) {
	fwrite( STDERR, "Özel anahtar zaten var: $priv_path\nYeniden üretmek mevcut tüm lisans jetonlarını geçersiz kılar. Emin iseniz --force ekleyin.\n" );
	exit( 1 );
}

$res = openssl_pkey_new( array( 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA ) );
if ( ! $res ) {
	fwrite( STDERR, "OpenSSL anahtar üretemedi.\n" );
	exit( 1 );
}
openssl_pkey_export( $res, $private );
$public = openssl_pkey_get_details( $res )['key'];

if ( ! is_dir( dirname( $priv_path ) ) ) {
	mkdir( dirname( $priv_path ), 0700, true );
}
file_put_contents( $priv_path, $private );
chmod( $priv_path, 0600 );

$php = "<?php\n/**\n * Lisans sunucusunun RSA açık anahtarı.\n *\n * Bu dosya `php license-server/tools/generate-keys.php` tarafından otomatik yazılır.\n * Elle düzenlemeyin; özel anahtar (private.pem) asla temayla dağıtılmaz.\n *\n * @package Nakliye\n */\n\ndefined( 'ABSPATH' ) || exit;\n\ndefine( 'NAKLIYE_PUBLIC_KEY', " . var_export( $public, true ) . " );\n";
file_put_contents( $pub_php, $php );

echo "✓ Özel anahtar : $priv_path (gizli tutun!)\n";
echo "✓ Açık anahtar : $pub_php\n";
echo "Sonraki adım   : php tools/build-theme.php\n";
