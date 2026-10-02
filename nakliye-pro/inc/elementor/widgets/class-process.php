<?php
/**
 * Çalışma süreci adımları.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Nakliye_Widget_Process extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-process';
	}

	public function get_title() {
		return __( 'Nakliye Süreç Adımları', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-flow';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Nasıl Çalışıyoruz?', 'nakliye' ),
				'title'   => __( '4 adımda stressiz taşınma', 'nakliye' ),
			)
		);

		$this->start_controls_section( 'section_steps', array( 'label' => __( 'Adımlar', 'nakliye' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'İkon', 'nakliye' ), 'type' => Controls_Manager::SELECT, 'default' => 'phone', 'options' => $this->icon_choices() ) );
		$rep->add_control( 'title', array( 'label' => __( 'Başlık', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Adım', 'nakliye' ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Açıklama', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control(
			'steps',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => 'phone', 'title' => __( 'Bizi arayın', 'nakliye' ), 'text' => __( 'Formu doldurun ya da arayın, ihtiyacınızı dinleyelim.', 'nakliye' ) ),
					array( 'icon' => 'calendar', 'title' => __( 'Ücretsiz ekspertiz', 'nakliye' ), 'text' => __( 'Uzmanımız eşyalarınızı yerinde inceler, net fiyat verir.', 'nakliye' ) ),
					array( 'icon' => 'box', 'title' => __( 'Paketleme & yükleme', 'nakliye' ), 'text' => __( 'Eşyalar özel malzemelerle paketlenip güvenle yüklenir.', 'nakliye' ) ),
					array( 'icon' => 'check', 'title' => __( 'Teslim & kurulum', 'nakliye' ), 'text' => __( 'Yeni evinizde mobilyalar kurulur, yerleştirme yapılır.', 'nakliye' ) ),
				),
			)
		);
		$this->add_columns_control( 4 );
		$this->end_controls_section();

		$this->add_card_style_controls( '.nk-step' );
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		echo '<div class="nk-process">';
		$this->render_heading( $s );
		echo '<ol class="nk-grid nk-grid--4 nk-process__list">';
		foreach ( (array) $s['steps'] as $i => $step ) {
			printf(
				'<li class="nk-step"><span class="nk-step__num">%1$02d</span><span class="nk-step__icon">%2$s</span><h3>%3$s</h3><p>%4$s</p></li>',
				(int) $i + 1,
				nakliye_render_icon( $step['icon'], 28 ), // phpcs:ignore
				esc_html( $step['title'] ),
				esc_html( $step['text'] )
			);
		}
		echo '</ol></div>';
	}
}
