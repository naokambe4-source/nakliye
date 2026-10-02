<?php
/**
 * Tekil yazı.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	if ( nakliye_is_elementor_page() ) :
		the_content();
		continue;
	endif;
	nakliye_page_header();
	?>
	<div class="nk-container nk-section nk-layout">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'nk-layout__content nk-entry' ); ?>>
			<?php nakliye_post_meta(); ?>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="nk-entry__media"><?php the_post_thumbnail( 'nakliye-wide' ); ?></div>
			<?php endif; ?>
			<div class="nk-entry__content"><?php the_content(); ?></div>
			<?php wp_link_pages(); ?>
			<footer class="nk-entry__footer"><?php the_tags( '<div class="nk-tags">', '', '</div>' ); ?></footer>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<small>' . esc_html__( 'Önceki yazı', 'nakliye' ) . '</small>%title',
					'next_text' => '<small>' . esc_html__( 'Sonraki yazı', 'nakliye' ) . '</small>%title',
				)
			);
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</article>
		<?php get_sidebar(); ?>
	</div>
	<?php
endwhile;

get_footer();
