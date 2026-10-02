<?php
/**
 * Arama sonuçları.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();
nakliye_page_header();
?>
<div class="nk-container nk-section nk-layout">
	<div class="nk-layout__content">
		<?php if ( have_posts() ) : ?>
			<div class="nk-post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', get_post_type() );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
