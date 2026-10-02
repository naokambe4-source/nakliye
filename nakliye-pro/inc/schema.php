<?php
/**
 * MovingCompany yapısal verisi (JSON-LD).
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'nakliye_schema_output', 5 );
/**
 * Ana sayfada firma şemasını basar.
 */
function nakliye_schema_output() {
	if ( ! nakliye_option( 'schema' ) || ! is_front_page() ) {
		return;
	}
	$same_as = array_values( array_filter( array_map( 'nakliye_option', array( 'facebook', 'instagram', 'x', 'youtube', 'linkedin' ) ) ) );
	$data    = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'MovingCompany',
		'name'         => nakliye_option( 'company_name' ),
		'url'          => home_url( '/' ),
		'telephone'    => nakliye_option( 'phone' ),
		'email'        => nakliye_option( 'email' ),
		'address'      => array( '@type' => 'PostalAddress', 'streetAddress' => nakliye_option( 'address' ), 'addressCountry' => 'TR' ),
		'openingHours' => nakliye_option( 'hours' ),
		'priceRange'   => '₺₺',
	);
	$logo = nakliye_logo_url();
	if ( $logo ) {
		$data['logo']  = $logo;
		$data['image'] = $logo;
	}
	if ( $same_as ) {
		$data['sameAs'] = $same_as;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
