<?php
/**
 * Araç filosu.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Fleet extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-fleet';
	}

	public function get_title() {
		return __( 'Nakliye Araç Filosu', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Filomuz', 'nakliye' ),
				'title'   => __( 'Her yüke uygun modern araçlar', 'nakliye' ),
			)
		);
		$this->start_controls_section( 'section_fleet', array( 'label' => __( 'Filo', 'nakliye' ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'Araç sayısı', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'default' => 3 ) );
		$this->add_columns_control( 3 );
		$this->end_controls_section();
		$this->add_card_style_controls( '.nk-vehicle' );
	}

	protected function render_widget() {
		$s     = $this->get_settings_for_display();
		$posts = get_posts( array( 'post_type' => 'nakliye_filo', 'numberposts' => max( 1, (int) $s['limit'] ), 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		echo '<div class="nk-fleet">';
		$this->render_heading( $s );
		echo '<div class="nk-grid nk-grid--3">';
		foreach ( $posts as $post ) {
			$specs = array(
				__( 'Tip', 'nakliye' )       => get_post_meta( $post->ID, '_nk_vehicle_type', true ),
				__( 'Kapasite', 'nakliye' )  => get_post_meta( $post->ID, '_nk_capacity', true ),
				__( 'Hacim', 'nakliye' )     => get_post_meta( $post->ID, '_nk_volume', true ),
				__( 'Uygun', 'nakliye' )     => get_post_meta( $post->ID, '_nk_suitable', true ),
			);
			echo '<article class="nk-vehicle">';
			echo '<div class="nk-vehicle__media">' . ( has_post_thumbnail( $post ) ? get_the_post_thumbnail( $post, 'nakliye-card' ) : nakliye_icon( 'truck', 72 ) ) . '</div>'; // phpcs:ignore
			echo '<div class="nk-vehicle__body"><h3>' . esc_html( $post->post_title ) . '</h3><dl>';
			foreach ( $specs as $label => $value ) {
				if ( $value ) {
					echo '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
				}
			}
			echo '</dl></div></article>';
		}
		if ( ! $posts && current_user_can( 'edit_posts' ) ) {
			echo '<p>' . esc_html__( 'Henüz araç eklenmemiş.', 'nakliye' ) . '</p>';
		}
		echo '</div></div>';
	}
}
