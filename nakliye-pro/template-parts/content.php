<?php
/**
 * Liste görünümünde yazı kartı.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'nk-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="nk-post-card__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'nakliye-card' ); ?></a>
	<?php endif; ?>
	<div class="nk-post-card__body">
		<?php nakliye_post_meta(); ?>
		<h2 class="nk-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<a class="nk-link-arrow" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Devamını oku', 'nakliye' ); ?> <?php echo nakliye_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
	</div>
</article>
