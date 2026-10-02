<?php
/**
 * (İsteğe bağlı) Komut satırından müşteriye verilecek tema zip'ini üretir.
 * Aynı işi lisans panelindeki "Tema zip'ini oluştur ve indir" butonu da yapar.
 *
 * Kullanım:
 *   php tools/build-theme.php --server=https://lisans.siteniz.com/klasor [--purchase=URL] [--obfuscate]
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "Yalnızca komut satırından çalıştırılabilir.\n" );
}

define( 'NKLS', true );
$root = dirname( __DIR__ );
require $root . '/license-server/lib.php';
require $root . '/license-server/lib-build.php';

$args = array();
foreach ( array_slice( $argv, 1 ) as $arg ) {
	if ( preg_match( '/^--([a-z]+)(?:=(.*))?$/', $arg, $m ) ) {
		$args[ $m[1] ] = isset( $m[2] ) ? $m[2] : true;
	}
}

$key_path = nkls_config()['private_key_path'];
if ( ! is_readable( $key_path ) ) {
	fwrite( STDERR, "✗ Özel anahtar yok: $key_path\n  Önce: php license-server/tools/generate-keys.php\n" );
	exit( 1 );
}

$server = isset( $args['server'] ) ? $args['server'] : '';
if ( ! $server ) {
	// Temadaki mevcut değeri kullan.
	preg_match( "/'NAKLIYE_LICENSE_SERVER',\s*'([^']+)'/", (string) file_get_contents( $root . '/nakliye-pro/inc/core/config.php' ), $m );
	$server = isset( $m[1] ) ? $m[1] : '';
}

@mkdir( $root . '/dist', 0755, true );
try {
	$out = nkls_build_theme(
		array(
			'source'      => $root . '/nakliye-pro',
			'private_pem' => file_get_contents( $key_path ),
			'server_url'  => $server,
			'purchase'    => isset( $args['purchase'] ) ? $args['purchase'] : '',
			'obfuscate'   => ! empty( $args['obfuscate'] ),
			'zip_path'    => $root . '/dist/nakliye-pro-' . 'VERSION' . '.zip',
		)
	);
	$final = str_replace( 'VERSION', $out['version'], $out['zip'] );
	rename( $out['zip'], $final );
	echo "✓ {$out['files']} dosya imzalandı\n✓ Sunucu: $server\n✓ Paket: " . substr( $final, strlen( $root ) + 1 ) . "\n";
} catch ( Throwable $e ) {
	fwrite( STDERR, '✗ ' . $e->getMessage() . "\n" );
	exit( 1 );
}
