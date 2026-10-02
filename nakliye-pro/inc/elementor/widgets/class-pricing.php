<?php
/**
 * Paket / fiyat tabloları.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Nakliye_Widget_Pricing extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-pricing';
	}

	public function get_title() {
		return __( 'Nakliye Paket Fiyatları', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Paketler', 'nakliye' ),
				'title'   => __( 'Bütçenize uygun taşıma paketi', 'nakliye' ),
			)
		);
		$this->start_controls_section( 'section_plans', array( 'label' => __( 'Paketler', 'nakliye' ) ) );
		$rep = new Repeater();
		$rep->add_control( 'name', array( 'label' => __( 'Paket adı', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Ekonomik', 'nakliye' ) ) );
		$rep->add_control( 'price', array( 'label' => __( 'Fiyat', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => '6.000 ₺' ) );
		$rep->add_control( 'period', array( 'label' => __( 'Fiyat notu', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( '’den başlayan', 'nakliye' ) ) );
		$rep->add_control( 'features', array( 'label' => __( 'Özellikler (her satıra bir, iptal için başına -)', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA ) );
		$rep->add_control( 'featured', array( 'label' => __( 'Öne çıkar', 'nakliye' ), 'type' => Controls_Manager::SWITCHER ) );
		$rep->add_control( 'btn', array( 'label' => __( 'Buton', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Teklif Al', 'nakliye' ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Bağlantı', 'nakliye' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#teklif' ) ) );
		$this->add_control(
			'plans',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => __( 'Ekonomik', 'nakliye' ), 'price' => '6.000 ₺', 'features' => "Taşıma & yükleme\nBattaniye ile koruma\nŞehir içi taşıma\n-Paketleme hizmeti\n-Mobilya montajı" ),
					array( 'name' => __( 'Standart', 'nakliye' ), 'price' => '9.500 ₺', 'featured' => 'yes', 'features' => "Taşıma & yükleme\nProfesyonel paketleme\nMobilya söküm & montaj\nTaşıma sigortası\n-Yerleştirme & temizlik" ),
					array( 'name' => __( 'Premium', 'nakliye' ), 'price' => '14.000 ₺', 'features' => "Anahtar teslim taşıma\nTüm eşyaların paketlenmesi\nMontaj & yerleştirme\nTam kapsamlı sigorta\nTaşınma sonrası temizlik" ),
				),
			)
		);
		$this->add_columns_control( 3 );
		$this->end_controls_section();
		$this->add_card_style_controls( '.nk-plan' );
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		echo '<div class="nk-pricing">';
		$this->render_heading( $s );
		echo '<div class="nk-grid nk-grid--3">';
		foreach ( (array) $s['plans'] as $plan ) {
			$features = array_filter( array_map( 'trim', explode( "\n", (string) $plan['features'] ) ) );
			echo '<div class="nk-plan' . ( 'yes' === $plan['featured'] ? ' nk-plan--featured' : '' ) . '">';
			if ( 'yes' === $plan['featured'] ) {
				echo '<span class="nk-plan__badge">' . esc_html__( 'En çok tercih edilen', 'nakliye' ) . '</span>';
			}
			echo '<h3>' . esc_html( $plan['name'] ) . '</h3>';
			echo '<p class="nk-plan__price"><strong>' . esc_html( $plan['price'] ) . '</strong> <small>' . esc_html( $plan['period'] ) . '</small></p><ul>';
			foreach ( $features as $feature ) {
				$off = 0 === strpos( $feature, '-' );
				echo '<li class="' . ( $off ? 'is-off' : '' ) . '">' . ( $off ? nakliye_icon( 'close', 16 ) : nakliye_icon( 'check', 16 ) ) . ' ' . esc_html( ltrim( $feature, '- ' ) ) . '</li>'; // phpcs:ignore
			}
			echo '</ul>';
			if ( $plan['btn'] ) {
				echo '<a class="nk-btn ' . ( 'yes' === $plan['featured'] ? 'nk-btn--primary' : 'nk-btn--outline' ) . ' nk-btn--block" ' . $this->link_attrs( $plan['link'] ) . '>' . esc_html( $plan['btn'] ) . '</a>'; // phpcs:ignore
			}
			echo '</div>';
		}
		echo '</div></div>';
	}
}
