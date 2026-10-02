<?php
/**
 * Sayfa şablonu. Elementor ile düzenlenen sayfalar tam genişlikte, başlıksız gösterilir.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	if ( nakliye_is_elementor_page() ) :
		the_content();
	else :
		nakliye_page_header();
		?>
		<div class="nk-container nk-section">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'nk-entry' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="nk-entry__media"><?php the_post_thumbnail( 'nakliye-wide' ); ?></div>
				<?php endif; ?>
				<div class="nk-entry__content"><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
		<?php
	endif;
endwhile;

get_footer();
