<?php
/**
 * Template Name: Nakliye — Tam Genişlik (Elementor)
 * Template Post Type: page, post, nakliye_hizmet
 *
 * Üst ve alt alan korunur, içerik kenar boşluğu olmadan tam genişlikte basılır.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
get_footer();
