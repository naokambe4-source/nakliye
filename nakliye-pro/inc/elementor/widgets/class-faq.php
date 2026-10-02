<?php
/**
 * Sıkça sorulan sorular (FAQPage şemalı).
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Nakliye_Widget_Faq extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-faq';
	}

	public function get_title() {
		return __( 'Nakliye SSS', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'SSS', 'nakliye' ),
				'title'   => __( 'Merak edilenler', 'nakliye' ),
			)
		);
		$this->start_controls_section( 'section_faq', array( 'label' => __( 'Sorular', 'nakliye' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'q', array( 'label' => __( 'Soru', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => __( 'Soru', 'nakliye' ) ) );
		$rep->add_control( 'a', array( 'label' => __( 'Cevap', 'nakliye' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ q }}}',
				'default'     => array(
					array( 'q' => __( 'Fiyatlarınız neye göre belirleniyor?', 'nakliye' ), 'a' => __( 'Eşya miktarı, mesafe, kat durumu, asansör ihtiyacı ve ek hizmetler (paketleme, montaj, depolama) fiyatı belirler. Ücretsiz ekspertiz sonrası net fiyat verilir.', 'nakliye' ) ),
					array( 'q' => __( 'Eşyalarım sigortalı mı?', 'nakliye' ), 'a' => __( 'Evet, tüm taşımalarımız anlaşmalı sigorta şirketimiz tarafından teminat altındadır.', 'nakliye' ) ),
					array( 'q' => __( 'Ne kadar önceden rezervasyon yapmalıyım?', 'nakliye' ), 'a' => __( 'Ay sonları ve yaz aylarında en az 1-2 hafta önce rezervasyon öneriyoruz.', 'nakliye' ) ),
					array( 'q' => __( 'Mobilyaları siz mi söküp kuruyorsunuz?', 'nakliye' ), 'a' => __( 'Evet, marangoz ekibimiz söküm ve montajı ücretsiz olarak yapar.', 'nakliye' ) ),
				),
			)
		);
		$this->add_control( 'schema', array( 'label' => __( 'FAQPage yapısal verisi', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s      = $this->get_settings_for_display();
		$schema = array();
		echo '<div class="nk-faq">';
		$this->render_heading( $s );
		echo '<div class="nk-accordion">';
		foreach ( (array) $s['items'] as $i => $item ) {
			echo '<details class="nk-accordion__item"' . ( 0 === $i ? ' open' : '' ) . '><summary>' . esc_html( $item['q'] ) . '<span class="nk-accordion__icon"></span></summary><div class="nk-accordion__body">' . wp_kses_post( wpautop( $item['a'] ) ) . '</div></details>';
			$schema[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $item['a'] ) ),
			);
		}
		echo '</div></div>';
		if ( 'yes' === $s['schema'] && $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $schema ), JSON_UNESCAPED_UNICODE ) . '</script>';
		}
	}
}
