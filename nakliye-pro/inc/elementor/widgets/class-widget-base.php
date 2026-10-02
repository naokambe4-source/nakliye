<?php
/**
 * Tüm Nakliye Pro bileşenlerinin taban sınıfı.
 *
 * Lisans kilidi burada uygulanır: geçerli lisans yoksa bileşen ziyaretçiye
 * hiçbir şey göstermez, editörde ise kilit uyarısı çıkar.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

abstract class Nakliye_Widget_Base extends \Elementor\Widget_Base {

	/**
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'nakliye' );
	}

	/**
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'nakliye', 'nakliyat', 'taşıma', 'lojistik' );
	}

	/**
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'nakliye-main' );
	}

	/**
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'nakliye-main' );
	}

	/**
	 * Lisans kontrolü yapılmış çıktı.
	 */
	final protected function render() {
		if ( ! Nakliye_License::instance()->is_active() ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || current_user_can( 'edit_posts' ) ) {
				echo '<div class="nk-locked">' . nakliye_icon( 'shield', 28 ) . '<div><strong>Nakliye Pro</strong><p>' . wp_kses_post( nakliye_locked_message() ) . '</p></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			return;
		}
		$this->render_widget();
	}

	/**
	 * Bileşen çıktısı.
	 */
	abstract protected function render_widget();

	/**
	 * Tema ikon seçenekleri.
	 *
	 * @return array
	 */
	protected function icon_choices() {
		return array(
			'truck'     => __( 'Kamyon', 'nakliye' ),
			'box'       => __( 'Koli', 'nakliye' ),
			'building'  => __( 'Ofis / Bina', 'nakliye' ),
			'warehouse' => __( 'Depo', 'nakliye' ),
			'piano'     => __( 'Piyano', 'nakliye' ),
			'globe'     => __( 'Dünya', 'nakliye' ),
			'lift'      => __( 'Asansör', 'nakliye' ),
			'shield'    => __( 'Kalkan / Sigorta', 'nakliye' ),
			'users'     => __( 'Ekip', 'nakliye' ),
			'clock'     => __( 'Saat', 'nakliye' ),
			'calendar'  => __( 'Takvim', 'nakliye' ),
			'map'       => __( 'Konum', 'nakliye' ),
			'phone'     => __( 'Telefon', 'nakliye' ),
			'check'     => __( 'Onay', 'nakliye' ),
			'star'      => __( 'Yıldız', 'nakliye' ),
		);
	}

	/**
	 * Bölüm başlığı denetimleri (üst başlık, başlık, açıklama, hizalama).
	 *
	 * @param array $defaults Varsayılanlar.
	 */
	protected function add_heading_controls( array $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array( 'eyebrow' => '', 'title' => '', 'desc' => '' ) );

		$this->start_controls_section( 'section_heading', array( 'label' => __( 'Bölüm Başlığı', 'nakliye' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Üst başlık', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['eyebrow'], 'label_block' => true ) );
		$this->add_control( 'title', array( 'label' => __( 'Başlık', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => $defaults['title'], 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => __( 'Açıklama', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA, 'default' => $defaults['desc'] ) );
		$this->add_responsive_control(
			'heading_align',
			array(
				'label'     => __( 'Hizalama', 'nakliye' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => __( 'Sol', 'nakliye' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Orta', 'nakliye' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => __( 'Sağ', 'nakliye' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default'   => 'center',
				'selectors' => array( '{{WRAPPER}} .nk-heading' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style_heading', array( 'label' => __( 'Bölüm Başlığı', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'eyebrow_color', array( 'label' => __( 'Üst başlık rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-heading__eyebrow' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'title_color', array( 'label' => __( 'Başlık rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-heading__title' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .nk-heading__title' ) );
		$this->add_control( 'desc_color', array( 'label' => __( 'Açıklama rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-heading__desc' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Bölüm başlığını basar.
	 *
	 * @param array $s Ayarlar.
	 */
	protected function render_heading( array $s ) {
		if ( empty( $s['eyebrow'] ) && empty( $s['title'] ) && empty( $s['description'] ) ) {
			return;
		}
		echo '<div class="nk-heading">';
		if ( ! empty( $s['eyebrow'] ) ) {
			echo '<span class="nk-heading__eyebrow">' . esc_html( $s['eyebrow'] ) . '</span>';
		}
		if ( ! empty( $s['title'] ) ) {
			echo '<h2 class="nk-heading__title">' . esc_html( $s['title'] ) . '</h2>';
		}
		if ( ! empty( $s['description'] ) ) {
			echo '<p class="nk-heading__desc">' . esc_html( $s['description'] ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Kart stil denetimleri.
	 *
	 * @param string $selector Kart seçicisi.
	 */
	protected function add_card_style_controls( $selector ) {
		$this->start_controls_section( 'style_card', array( 'label' => __( 'Kartlar', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'card_bg', array( 'label' => __( 'Arka plan', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'card_title_color', array( 'label' => __( 'Başlık rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector . ' h3' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'card_text_color', array( 'label' => __( 'Metin rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector . ' p' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'card_icon_color', array( 'label' => __( 'İkon rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector . ' .nk-icon' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'İç boşluk', 'nakliye' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'Köşe yuvarlaklığı', 'nakliye' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Sütun sayısı denetimi.
	 *
	 * @param int $default Varsayılan.
	 */
	protected function add_columns_control( $default = 3 ) {
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Sütun', 'nakliye' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => (string) $default,
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
				'selectors'      => array( '{{WRAPPER}} .nk-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);
	}

	/**
	 * Bağlantı özniteliklerini döndürür.
	 *
	 * @param array $link URL denetimi değeri.
	 * @return string
	 */
	protected function link_attrs( $link ) {
		if ( empty( $link['url'] ) ) {
			return 'href="#"';
		}
		$attrs = 'href="' . esc_url( $link['url'] ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		if ( ! empty( $link['nofollow'] ) || ! empty( $link['is_external'] ) ) {
			$attrs .= ' rel="' . ( ! empty( $link['nofollow'] ) ? 'nofollow ' : '' ) . 'noopener"';
		}
		return $attrs;
	}
}
