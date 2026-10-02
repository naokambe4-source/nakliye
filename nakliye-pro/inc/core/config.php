<?php
/**
 * Lisans altyapısı yapılandırması.
 *
 * NAKLIYE_LICENSE_SERVER: license-server/ klasörünü yüklediğiniz adres (sonunda / olmadan).
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'NAKLIYE_LICENSE_SERVER' ) ) {
	define( 'NAKLIYE_LICENSE_SERVER', 'https://lisans.guvenyolnakliyat.com' );
}

// Uzak doğrulama aralığı ve sunucuya ulaşılamadığında tanınan ek süre.
define( 'NAKLIYE_LICENSE_CHECK_INTERVAL', DAY_IN_SECONDS );
define( 'NAKLIYE_LICENSE_GRACE_PERIOD', 14 * DAY_IN_SECONDS );

// Satın alma sayfası (lisans ekranındaki "Lisans satın al" butonu).
define( 'NAKLIYE_PURCHASE_URL', 'https://example.com/nakliye-pro' );
