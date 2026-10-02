<?php
/**
 * Teklif formu bileşeni.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Quote_Form extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-quote-form';
	}

	public function get_title() {
		return __( 'Nakliye Teklif Formu', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Ücretsiz Teklif', 'nakliye' ),
				'title'   => __( '30 dakikada fiyat teklifinizi alın', 'nakliye' ),
			)
		);

		$this->start_controls_section( 'section_form', array( 'label' => __( 'Form', 'nakliye' ) ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Görünüm', 'nakliye' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'full',
				'options' => array( 'full' => __( 'Detaylı', 'nakliye' ), 'compact' => __( 'Kısa (4 alan)', 'nakliye' ) ),
			)
		);
		$this->add_control( 'button', array( 'label' => __( 'Buton yazısı', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Ücretsiz Teklif Al', 'nakliye' ) ) );
		$this->add_control( 'success', array( 'label' => __( 'Başarı mesajı (boşsa varsayılan)', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'show_side', array( 'label' => __( 'Yanında iletişim kartı', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_form', array( 'label' => __( 'Form', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'form_bg', array( 'label' => __( 'Form arka planı', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-quote-box' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'label_color', array( 'label' => __( 'Etiket rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-field label' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_bg', array( 'label' => __( 'Buton rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-btn--primary' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		?>
		<div class="nk-quote-section<?php echo 'yes' === $s['show_side'] ? ' nk-quote-section--side' : ''; ?>">
			<div class="nk-quote-box">
				<?php $this->render_heading( $s ); ?>
				<?php nakliye_render_quote_form( array( 'compact' => 'compact' === $s['layout'], 'button' => $s['button'], 'success' => $s['success'] ) ); ?>
			</div>
			<?php if ( 'yes' === $s['show_side'] ) : ?>
				<aside class="nk-contact-card">
					<h3><?php esc_html_e( 'Hemen ulaşın', 'nakliye' ); ?></h3>
					<p><?php esc_html_e( 'Formla uğraşmak istemiyorsanız bizi doğrudan arayabilir ya da WhatsApp’tan yazabilirsiniz.', 'nakliye' ); ?></p>
					<ul>
						<li><?php echo nakliye_icon( 'phone', 18 ); // phpcs:ignore ?> <a href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>"><?php echo esc_html( nakliye_option( 'phone' ) ); ?></a></li>
						<li><?php echo nakliye_icon( 'mail', 18 ); // phpcs:ignore ?> <a href="mailto:<?php echo esc_attr( nakliye_option( 'email' ) ); ?>"><?php echo esc_html( nakliye_option( 'email' ) ); ?></a></li>
						<li><?php echo nakliye_icon( 'clock', 18 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'hours' ) ); ?></li>
						<li><?php echo nakliye_icon( 'map', 18 ); // phpcs:ignore ?> <?php echo esc_html( nakliye_option( 'address' ) ); ?></li>
					</ul>
					<?php if ( nakliye_option( 'whatsapp' ) ) : ?>
						<a class="nk-btn nk-btn--wa nk-btn--block" href="<?php echo esc_url( nakliye_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo nakliye_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'WhatsApp ile yazın', 'nakliye' ); ?></a>
					<?php endif; ?>
				</aside>
			<?php endif; ?>
		</div>
		<?php
	}
}
