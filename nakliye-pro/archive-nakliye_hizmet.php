<?php
/**
 * Hizmetler arşivi.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();
nakliye_page_header( __( 'Hizmetlerimiz', 'nakliye' ) );
?>
<div class="nk-container nk-section">
	<div class="nk-grid nk-grid--3">
		<?php
		while ( have_posts() ) :
			the_post();
			nakliye_service_card( get_post() );
		endwhile;
		?>
	</div>
	<?php the_posts_pagination(); ?>
</div>
<?php
get_footer();
