<?php
/**
 * Komut satırından lisans oluşturur.
 *
 * Kullanım: php license-server/tools/create-license.php "Müşteri Adı" musteri@mail.com [site_limiti] [gun]
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "Yalnızca komut satırından çalıştırılabilir.\n" );
}

define( 'NKLS', true );
require dirname( __DIR__ ) . '/lib.php';

$customer = isset( $argv[1] ) ? $argv[1] : 'Test Müşteri';
$email    = isset( $argv[2] ) ? $argv[2] : '';
$sites    = isset( $argv[3] ) ? max( 1, (int) $argv[3] ) : 1;
$days     = isset( $argv[4] ) ? (int) $argv[4] : 0;
$key      = nkls_generate_key();

nkls_db()->prepare( 'INSERT INTO licenses (license_key, product, customer, email, max_sites, expires_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)' )
	->execute( array( $key, nkls_config()['product'], $customer, $email, $sites, $days > 0 ? time() + $days * 86400 : 0, time() ) );

echo $key . "\n";
