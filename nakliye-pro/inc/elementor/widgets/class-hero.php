<?php
/**
 * Hero / manşet bileşeni.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

class Nakliye_Widget_Hero extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-hero';
	}

	public function get_title() {
		return __( 'Nakliye Manşet (Hero)', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-header';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'İçerik', 'nakliye' ) ) );

		$this->add_control( 'badge', array( 'label' => __( 'Rozet', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( '⭐ 4.9/5 — 12.000+ mutlu müşteri', 'nakliye' ), 'label_block' => true ) );
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Başlık', 'nakliye' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'Eşyalarınız [güvende], taşınmanız zamanında.', 'nakliye' ),
				'description' => __( 'Vurgulamak istediğiniz kelimeyi [köşeli parantez] içine alın.', 'nakliye' ),
			)
		);
		$this->add_control( 'subtitle', array( 'label' => __( 'Alt başlık', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'Asansörlü, sigortalı ve profesyonel paketlemeli evden eve nakliyat. Ücretsiz ekspertiz için hemen teklif alın.', 'nakliye' ) ) );

		$features = new Repeater();
		$features->add_control( 'text', array( 'label' => __( 'Metin', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Sigortalı taşıma', 'nakliye' ) ) );
		$this->add_control(
			'features',
			array(
				'label'       => __( 'Öne çıkanlar', 'nakliye' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $features->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array( 'text' => __( 'Sigortalı taşıma', 'nakliye' ) ),
					array( 'text' => __( 'Asansörlü sistem', 'nakliye' ) ),
					array( 'text' => __( 'Profesyonel paketleme', 'nakliye' ) ),
				),
			)
		);

		$this->add_control( 'btn1_text', array( 'label' => __( '1. buton', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Ücretsiz Teklif Al', 'nakliye' ), 'separator' => 'before' ) );
		$this->add_control( 'btn1_link', array( 'label' => __( '1. buton bağlantısı', 'nakliye' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#teklif' ) ) );
		$this->add_control( 'btn2_text', array( 'label' => __( '2. buton', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Fiyat Hesapla', 'nakliye' ) ) );
		$this->add_control( 'btn2_link', array( 'label' => __( '2. buton bağlantısı', 'nakliye' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '#fiyat-hesapla' ) ) );
		$this->add_control( 'show_phone', array( 'label' => __( 'Telefon kutusunu göster', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );

		$this->add_control( 'show_form', array( 'label' => __( 'Sağda hızlı teklif formu', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before' ) );
		$this->add_control( 'form_title', array( 'label' => __( 'Form başlığı', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Hızlı Teklif Alın', 'nakliye' ), 'condition' => array( 'show_form' => 'yes' ) ) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_bg', array( 'label' => __( 'Arka Plan', 'nakliye' ) ) );
		$this->add_control( 'bg_image', array( 'label' => __( 'Görsel', 'nakliye' ), 'type' => Controls_Manager::MEDIA, 'selectors' => array( '{{WRAPPER}} .nk-hero' => 'background-image: url({{URL}});' ) ) );
		$this->add_control( 'overlay', array( 'label' => __( 'Kaplama rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'default' => 'rgba(15, 42, 74, 0.86)', 'selectors' => array( '{{WRAPPER}} .nk-hero::before' => 'background-color: {{VALUE}};' ) ) );
		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => __( 'Minimum yükseklik', 'nakliye' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 300, 'max' => 1200 ), 'vh' => array( 'min' => 30, 'max' => 100 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 680 ),
				'selectors'  => array( '{{WRAPPER}} .nk-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style_text', array( 'label' => __( 'Yazılar', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'title_color', array( 'label' => __( 'Başlık rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-hero__title' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'highlight_color', array( 'label' => __( 'Vurgu rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-hero__title mark' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'selector' => '{{WRAPPER}} .nk-hero__title' ) );
		$this->add_control( 'subtitle_color', array( 'label' => __( 'Alt başlık rengi', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-hero__subtitle' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'subtitle_typo', 'selector' => '{{WRAPPER}} .nk-hero__subtitle' ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s     = $this->get_settings_for_display();
		$title = preg_replace( '/\[(.+?)\]/u', '<mark>$1</mark>', esc_html( $s['title'] ) );
		?>
		<section class="nk-hero<?php echo 'yes' === $s['show_form'] ? ' nk-hero--with-form' : ''; ?>">
			<div class="nk-container nk-hero__inner">
				<div class="nk-hero__content">
					<?php if ( $s['badge'] ) : ?>
						<span class="nk-hero__badge"><?php echo esc_html( $s['badge'] ); ?></span>
					<?php endif; ?>
					<h1 class="nk-hero__title"><?php echo wp_kses( $title, array( 'mark' => array() ) ); ?></h1>
					<?php if ( $s['subtitle'] ) : ?>
						<p class="nk-hero__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $s['features'] ) ) : ?>
						<ul class="nk-hero__features">
							<?php foreach ( $s['features'] as $feature ) : ?>
								<li><?php echo nakliye_icon( 'check', 16 ); // phpcs:ignore ?> <?php echo esc_html( $feature['text'] ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<div class="nk-hero__actions">
						<?php if ( $s['btn1_text'] ) : ?>
							<a class="nk-btn nk-btn--primary nk-btn--lg" <?php echo $this->link_attrs( $s['btn1_link'] ); // phpcs:ignore ?>><?php echo esc_html( $s['btn1_text'] ); ?> <?php echo nakliye_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
						<?php endif; ?>
						<?php if ( $s['btn2_text'] ) : ?>
							<a class="nk-btn nk-btn--ghost nk-btn--lg" <?php echo $this->link_attrs( $s['btn2_link'] ); // phpcs:ignore ?>><?php echo esc_html( $s['btn2_text'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( 'yes' === $s['show_phone'] && nakliye_option( 'phone' ) ) : ?>
						<a class="nk-hero__phone" href="<?php echo esc_attr( nakliye_tel( nakliye_option( 'phone' ) ) ); ?>">
							<span><?php echo nakliye_icon( 'phone', 22 ); // phpcs:ignore ?></span>
							<span><small><?php esc_html_e( '7/24 Çağrı Merkezi', 'nakliye' ); ?></small><strong><?php echo esc_html( nakliye_option( 'phone' ) ); ?></strong></span>
						</a>
					<?php endif; ?>
				</div>
				<?php if ( 'yes' === $s['show_form'] ) : ?>
					<div class="nk-hero__form">
						<h3><?php echo esc_html( $s['form_title'] ); ?></h3>
						<?php nakliye_render_quote_form( array( 'compact' => true, 'button' => __( 'Teklif İste', 'nakliye' ) ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
