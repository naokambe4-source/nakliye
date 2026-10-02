<?php
/**
 * Lisans sunucusu yapılandırması.
 *
 * Değerleri değiştirmek için bu dosyayı düzenlemek yerine aynı klasörde
 * config.local.php oluşturup yalnızca değişen anahtarları döndürün:
 *
 *   <?php return array( 'admin_password_hash' => '$2y$10$....' );
 *
 * Parola özeti üretmek için: php -r "echo password_hash('GüçlüParola', PASSWORD_DEFAULT);"
 */

$config = array(
	// SQLite veritabanı (web kökünün dışında tutmanız önerilir).
	'db_path'             => __DIR__ . '/data/licenses.sqlite',

	// RSA özel anahtarı — ASLA temayla dağıtmayın, depoya eklemeyin.
	'private_key_path'    => __DIR__ . '/keys/private.pem',

	// Yönetim paneli parolasının password_hash() çıktısı. Boşsa panel kapalıdır.
	'admin_password_hash' => '',

	// Ürün kimliği (tema ile aynı olmalı).
	'product'             => 'nakliye-pro',

	// IP başına dakikada en fazla API isteği.
	'rate_limit'          => 30,
);

if ( is_file( __DIR__ . '/config.local.php' ) ) {
	$local = require __DIR__ . '/config.local.php';
	if ( is_array( $local ) ) {
		$config = array_merge( $config, $local );
	}
}

return $config;
