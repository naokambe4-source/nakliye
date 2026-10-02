<?php
/**
 * Alt alan.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) :
	get_template_part( 'template-parts/site-footer' );
endif;

get_template_part( 'template-parts/floating-buttons' );

wp_footer();
?>
</body>
</html>
