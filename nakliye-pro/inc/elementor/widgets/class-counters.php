<?php
/**
 * Animasyonlu sayaçlar.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

class Nakliye_Widget_Counters extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-counters';
	}

	public function get_title() {
		return __( 'Nakliye Sayaçlar', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-counter';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_counters', array( 'label' => __( 'Sayaçlar', 'nakliye' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'İkon', 'nakliye' ), 'type' => Controls_Manager::SELECT, 'default' => 'truck', 'options' => $this->icon_choices() ) );
		$rep->add_control( 'number', array( 'label' => __( 'Sayı', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'default' => 100 ) );
		$rep->add_control( 'suffix', array( 'label' => __( 'Son ek', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => '+' ) );
		$rep->add_control( 'label', array( 'label' => __( 'Etiket', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Etiket', 'nakliye' ) ) );
		$this->add_control(
			'counters',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'icon' => 'users', 'number' => 12500, 'suffix' => '+', 'label' => __( 'Mutlu müşteri', 'nakliye' ) ),
					array( 'icon' => 'truck', 'number' => 45, 'suffix' => '', 'label' => __( 'Araçlık filo', 'nakliye' ) ),
					array( 'icon' => 'globe', 'number' => 81, 'suffix' => '', 'label' => __( 'İle hizmet', 'nakliye' ) ),
					array( 'icon' => 'star', 'number' => 22, 'suffix' => ' yıl', 'label' => __( 'Tecrübe', 'nakliye' ) ),
				),
			)
		);
		$this->add_columns_control( 4 );
		$this->add_control(
			'skin',
			array(
				'label'   => __( 'Görünüm', 'nakliye' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'dark',
				'options' => array( 'dark' => __( 'Koyu şerit', 'nakliye' ), 'light' => __( 'Açık', 'nakliye' ), 'primary' => __( 'Ana renk', 'nakliye' ) ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style_counter', array( 'label' => __( 'Sayaç', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'num_color', array( 'label' => __( 'Sayı rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-counter__num' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'num_typo', 'selector' => '{{WRAPPER}} .nk-counter__num' ) );
		$this->add_control( 'label_color', array( 'label' => __( 'Etiket rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-counter__label' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		echo '<div class="nk-counters nk-counters--' . esc_attr( $s['skin'] ) . '"><div class="nk-grid nk-grid--4">';
		foreach ( (array) $s['counters'] as $c ) {
			printf(
				'<div class="nk-counter"><span class="nk-counter__icon">%1$s</span><span class="nk-counter__num"><span data-nk-count="%2$s">0</span>%3$s</span><span class="nk-counter__label">%4$s</span></div>',
				nakliye_render_icon( $c['icon'], 30 ), // phpcs:ignore
				esc_attr( (float) $c['number'] ),
				esc_html( $c['suffix'] ),
				esc_html( $c['label'] )
			);
		}
		echo '</div></div>';
	}
}
