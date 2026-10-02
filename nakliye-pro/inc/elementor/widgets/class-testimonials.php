<?php
/**
 * Müşteri yorumları kaydırıcısı.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Testimonials extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-testimonials';
	}

	public function get_title() {
		return __( 'Nakliye Müşteri Yorumları', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-testimonial-carousel';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Müşteri Yorumları', 'nakliye' ),
				'title'   => __( 'Bize güvenen binlerce aile', 'nakliye' ),
			)
		);
		$this->start_controls_section( 'section_slider', array( 'label' => __( 'Kaydırıcı', 'nakliye' ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'Yorum sayısı', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'default' => 9 ) );
		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => __( 'Görünen kart', 'nakliye' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3' ),
				'selectors'      => array( '{{WRAPPER}} .nk-slider' => '--per-view: {{VALUE}};' ),
			)
		);
		$this->add_control( 'autoplay', array( 'label' => __( 'Otomatik oynat', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
		$this->add_card_style_controls( '.nk-testimonial' );
	}

	protected function render_widget() {
		$s     = $this->get_settings_for_display();
		$posts = get_posts( array( 'post_type' => 'nakliye_yorum', 'numberposts' => max( 1, (int) $s['limit'] ) ) );
		echo '<div class="nk-testimonials">';
		$this->render_heading( $s );
		if ( ! $posts ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p>' . esc_html__( 'Henüz müşteri yorumu eklenmemiş.', 'nakliye' ) . '</p>';
			}
			echo '</div>';
			return;
		}
		echo '<div class="nk-slider" data-nk-slider data-autoplay="' . ( 'yes' === $s['autoplay'] ? '1' : '0' ) . '"><div class="nk-slider__track">';
		foreach ( $posts as $post ) {
			$rating = max( 1, min( 5, (int) get_post_meta( $post->ID, '_nk_rating', true ) ? (int) get_post_meta( $post->ID, '_nk_rating', true ) : 5 ) );
			echo '<figure class="nk-slider__slide nk-testimonial">';
			echo '<div class="nk-stars" aria-label="' . esc_attr( $rating ) . '/5">' . str_repeat( nakliye_icon( 'star', 16 ), $rating ) . '</div>'; // phpcs:ignore
			echo '<blockquote><p>' . esc_html( wp_strip_all_tags( $post->post_content ) ) . '</p></blockquote>';
			echo '<figcaption>';
			if ( has_post_thumbnail( $post ) ) {
				echo get_the_post_thumbnail( $post, 'thumbnail', array( 'class' => 'nk-testimonial__avatar' ) );
			} else {
				echo '<span class="nk-testimonial__avatar nk-testimonial__avatar--initial">' . esc_html( mb_substr( $post->post_title, 0, 1 ) ) . '</span>';
			}
			echo '<span><strong>' . esc_html( $post->post_title ) . '</strong><small>' . esc_html( get_post_meta( $post->ID, '_nk_role', true ) ) . '</small></span>';
			echo '</figcaption></figure>';
		}
		echo '</div>';
		echo '<div class="nk-slider__nav"><button type="button" data-prev aria-label="' . esc_attr__( 'Önceki', 'nakliye' ) . '">‹</button><div class="nk-slider__dots" data-dots></div><button type="button" data-next aria-label="' . esc_attr__( 'Sonraki', 'nakliye' ) . '">›</button></div>';
		echo '</div></div>';
	}
}
