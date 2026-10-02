<?php
/**
 * Gönderi / taşıma takip bileşeni.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Tracking extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-tracking';
	}

	public function get_title() {
		return __( 'Nakliye Taşıma Takibi', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Online Takip', 'nakliye' ),
				'title'   => __( 'Eşyalarınız şu an nerede?', 'nakliye' ),
				'desc'    => __( 'Teklif talebinizde size iletilen takip numarasıyla taşımanızın durumunu anlık izleyin.', 'nakliye' ),
			)
		);
		$this->start_controls_section( 'style_box', array( 'label' => __( 'Kutu', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'box_bg', array( 'label' => __( 'Arka plan', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-tracking' => 'background-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		echo '<div class="nk-tracking-wrap">';
		$this->render_heading( $s );
		nakliye_render_tracking_form();
		echo '</div>';
	}
}
