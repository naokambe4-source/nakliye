<?php
/**
 * Yorumlar.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="nk-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="nk-comments__title">
			<?php
			/* translators: %s: yorum sayısı */
			printf( esc_html( _n( '%s yorum', '%s yorum', get_comments_number(), 'nakliye' ) ), esc_html( number_format_i18n( get_comments_number() ) ) );
			?>
		</h2>
		<ol class="nk-comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 48 ) ); ?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php comment_form( array( 'class_submit' => 'nk-btn nk-btn--primary' ) ); ?>
</section>
