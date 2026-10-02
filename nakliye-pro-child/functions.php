<?php
/**
 * Nakliye Pro alt teması.
 *
 * Ana temanın dosyalarını düzenlemek yerine buraya kancalar ekleyin.
 * Şablon dosyalarını (ör. template-parts/site-header.php) bu klasöre aynı
 * yolla kopyalayıp düzenleyebilirsiniz.
 *
 * @package Nakliye_Child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'nakliye-child', get_stylesheet_uri(), array( 'nakliye-main' ), wp_get_theme()->get( 'Version' ) );
	},
	20
);
