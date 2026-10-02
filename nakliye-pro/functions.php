<?php
/**
 * Nakliye Pro - tema önyükleyici.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

define( 'NAKLIYE_VERSION', '1.0.0' );
define( 'NAKLIYE_SLUG', 'nakliye-pro' );
define( 'NAKLIYE_DIR', get_template_directory() );
define( 'NAKLIYE_URI', get_template_directory_uri() );

// Çekirdek: yapılandırma, lisans ve bütünlük koruması her şeyden önce yüklenir.
require_once NAKLIYE_DIR . '/inc/core/config.php';
require_once NAKLIYE_DIR . '/inc/core/public-key.php';
require_once NAKLIYE_DIR . '/inc/core/class-integrity.php';
require_once NAKLIYE_DIR . '/inc/core/class-license.php';
require_once NAKLIYE_DIR . '/inc/core/helpers.php';

// Tema özellikleri.
require_once NAKLIYE_DIR . '/inc/options.php';
require_once NAKLIYE_DIR . '/inc/setup.php';
require_once NAKLIYE_DIR . '/inc/post-types.php';
require_once NAKLIYE_DIR . '/inc/quotes.php';
require_once NAKLIYE_DIR . '/inc/template-tags.php';
require_once NAKLIYE_DIR . '/inc/schema.php';

// Yönetim paneli.
if ( is_admin() ) {
	require_once NAKLIYE_DIR . '/inc/admin/class-admin.php';
	require_once NAKLIYE_DIR . '/inc/admin/class-demo-importer.php';
	require_once NAKLIYE_DIR . '/inc/admin/class-setup-wizard.php';
}

// Elementor entegrasyonu.
require_once NAKLIYE_DIR . '/inc/elementor/class-elementor.php';

Nakliye_License::instance();
