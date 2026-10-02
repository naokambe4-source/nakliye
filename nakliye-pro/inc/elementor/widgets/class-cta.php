<?php
/**
 * Harekete geçirici şerit (CTA).
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Cta extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-cta';
	}

	public function get_title() {
		return __( 'Nakliye CTA Şeridi', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-call-to-action';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_cta', array( 'label' => __( 'İçerik', 'nakliye' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Başlık', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Bugün arayın, %10 indirim kazanın!', 'nakliye' ), 'label_block' => true ) );
		$this->add_control( 'text', array( 'label' => __( 'Metin', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'Online teklif alan müşterilerimize ücretsiz paketleme malzemesi hediye.', 'nakliye' ) ) );
		$this->add_control( 'btn', array( 'label' => __( 'Buton', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Hemen Teklif Al', 'nakliye' ) ) );
		$this->add_control( 'link', array( 'label' => __( 'Bağlantı', 'nakliye' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#teklif' ) ) );
		$this->add_control( 'show_phone', array( 'label' => __( 'Telefon butonu', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'image', array( 'label' => __( 'Arka plan görseli', 'nakliye' ), 'type' => Controls_Manager::MEDIA, 'selectors' => array( '{{WRAPPER}} .nk-cta' => 'background-image: url({{URL}});' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_cta', array( 'label' => __( 'Şerit', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bg', array( 'label' => __( 'Arka plan', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-cta::before' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'color', array( 'label' => __( 'Yazı rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-cta, {{WRAPPER}} .nk-cta h2' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		?>
		<div class="nk-cta">
			<div class="nk-cta__content">
				<h2><?php echo esc_html( $s['title'] ); ?></h2>
				<p><?php echo esc_html( $s['text'] ); ?></p>
			</div>
			<div class="nk-cta__actions">
				<?php if ( 'yes' === $s['show_phone'] && nakliye_option( 'phone' ) ) : ?>
					<a class="nk-btn nk-btn--light" href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo nakliye_icon( 'phone', 18 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'phone' ) ); ?></a>
				<?php endif; ?>
				<?php if ( $s['btn'] ) : ?>
					<a class="nk-btn nk-btn--dark" <?php echo $this->link_attrs( $s['link'] ); // phpcs:ignore ?>><?php echo esc_html( $s['btn'] ); ?> <?php echo nakliye_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
