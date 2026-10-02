<?php
/**
 * Lisans sunucusu yapılandırması.
 *
 * Normalde bu dosyayı düzenlemeniz gerekmez: kurulum sihirbazı (install.php)
 * parolayı ve anahtarı data/ ve keys/ klasörlerine kendisi yazar.
 *
 * Gizli dosyalar ".ht" önekiyle saklanır; Apache ve LiteSpeed bu dosyaları
 * .htaccess çalışmasa bile varsayılan olarak dışarıya kapatır.
 */

$config = array(
	// SQLite veritabanı.
	'db_path'             => __DIR__ . '/data/.ht-licenses.sqlite',

	// RSA özel anahtarı — ASLA temayla dağıtmayın, depoya eklemeyin.
	'private_key_path'    => __DIR__ . '/keys/.ht-private.pem',

	// Sihirbazın yazdığı ayar dosyası (parola özeti vb.).
	'settings_path'       => __DIR__ . '/data/.ht-settings.php',

	// Panel parolasının password_hash() çıktısı. Sihirbaz doldurur.
	'admin_password_hash' => '',

	// Panelden tema zip'i üretmek için tema kaynak klasörü.
	'theme_source'        => is_dir( __DIR__ . '/tema-kaynak/nakliye-pro' ) ? __DIR__ . '/tema-kaynak/nakliye-pro' : dirname( __DIR__ ) . '/nakliye-pro',

	// Ürün kimliği (tema ile aynı olmalı).
	'product'             => 'nakliye-pro',

	// IP başına dakikada en fazla API isteği.
	'rate_limit'          => 30,
);

// Sihirbazın yazdığı ayarlar.
if ( is_file( $config['settings_path'] ) ) {
	$saved = include $config['settings_path'];
	if ( is_array( $saved ) ) {
		$config = array_merge( $config, $saved );
	}
}

// Elle ayar yapmak isteyenler için (isteğe bağlı).
if ( is_file( __DIR__ . '/config.local.php' ) ) {
	$local = include __DIR__ . '/config.local.php';
	if ( is_array( $local ) ) {
		$config = array_merge( $config, $local );
	}
}

return $config;
