<?php
/**
 * Hizmetler ızgarası.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class Nakliye_Widget_Services extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-services';
	}

	public function get_title() {
		return __( 'Nakliye Hizmetler', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Hizmetlerimiz', 'nakliye' ),
				'title'   => __( 'Her ihtiyaca uygun taşıma çözümü', 'nakliye' ),
				'desc'    => __( 'Evden eve nakliyattan kurumsal lojistiğe kadar tüm süreci uzman ekibimizle yönetiyoruz.', 'nakliye' ),
			)
		);

		$this->start_controls_section( 'section_items', array( 'label' => __( 'Hizmetler', 'nakliye' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Kaynak', 'nakliye' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'posts',
				'options' => array(
					'posts'  => __( 'Hizmetler içerik türünden', 'nakliye' ),
					'manual' => __( 'Elle girilen', 'nakliye' ),
				),
			)
		);
		$this->add_control( 'limit', array( 'label' => __( 'Adet', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'default' => 6, 'condition' => array( 'source' => 'posts' ) ) );

		$rep = new Repeater();
		$rep->add_control( 'icon', array( 'label' => __( 'İkon', 'nakliye' ), 'type' => Controls_Manager::SELECT, 'default' => 'truck', 'options' => $this->icon_choices() ) );
		$rep->add_control( 'title', array( 'label' => __( 'Başlık', 'nakliye' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Evden Eve Nakliyat', 'nakliye' ) ) );
		$rep->add_control( 'text', array( 'label' => __( 'Açıklama', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'Paketlemeden kuruluma kadar anahtar teslim taşıma.', 'nakliye' ) ) );
		$rep->add_control( 'link', array( 'label' => __( 'Bağlantı', 'nakliye' ), 'type' => Controls_Manager::URL ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Hizmetler', 'nakliye' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'manual' ),
				'default'     => array(
					array( 'icon' => 'truck', 'title' => __( 'Evden Eve Nakliyat', 'nakliye' ) ),
					array( 'icon' => 'building', 'title' => __( 'Ofis Taşıma', 'nakliye' ), 'text' => __( 'İş akışınızı aksatmadan hafta sonu taşıma.', 'nakliye' ) ),
					array( 'icon' => 'warehouse', 'title' => __( 'Eşya Depolama', 'nakliye' ), 'text' => __( 'Kameralı, nem kontrollü güvenli depolar.', 'nakliye' ) ),
				),
			)
		);
		$this->add_columns_control( 3 );
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Kart stili', 'nakliye' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'card',
				'options' => array( 'card' => __( 'Kart', 'nakliye' ), 'boxed' => __( 'Çerçeveli', 'nakliye' ), 'image' => __( 'Görselli (içerik türü)', 'nakliye' ) ),
			)
		);
		$this->end_controls_section();

		$this->add_card_style_controls( '.nk-feature-card' );
	}

	protected function render_widget() {
		$s = $this->get_settings_for_display();
		echo '<div class="nk-services nk-services--' . esc_attr( $s['style'] ) . '">';
		$this->render_heading( $s );
		echo '<div class="nk-grid nk-grid--3">';

		if ( 'posts' === $s['source'] ) {
			$posts = get_posts( array( 'post_type' => 'nakliye_hizmet', 'numberposts' => max( 1, (int) $s['limit'] ), 'orderby' => 'menu_order', 'order' => 'ASC' ) );
			foreach ( $posts as $post ) {
				if ( 'image' === $s['style'] ) {
					nakliye_service_card( $post );
					continue;
				}
				$icon = get_post_meta( $post->ID, '_nk_icon', true );
				$this->card( $icon ? $icon : 'truck', get_the_title( $post ), get_the_excerpt( $post ), 'href="' . esc_url( get_permalink( $post ) ) . '"' );
			}
			if ( ! $posts && current_user_can( 'edit_posts' ) ) {
				echo '<p>' . esc_html__( 'Henüz hizmet eklenmemiş. Hizmetler → Yeni Ekle menüsünden ekleyin veya kurulum sihirbazından demo içerik yükleyin.', 'nakliye' ) . '</p>';
			}
		} else {
			foreach ( (array) $s['items'] as $item ) {
				$this->card( $item['icon'], $item['title'], $item['text'], ! empty( $item['link']['url'] ) ? $this->link_attrs( $item['link'] ) : '' );
			}
		}

		echo '</div></div>';
	}

	/**
	 * @param string $icon  İkon.
	 * @param string $title Başlık.
	 * @param string $text  Metin.
	 * @param string $link  Bağlantı öznitelikleri.
	 */
	private function card( $icon, $title, $text, $link ) {
		?>
		<article class="nk-feature-card">
			<span class="nk-feature-card__icon"><?php echo nakliye_render_icon( $icon, 30 ); // phpcs:ignore ?></span>
			<h3><?php echo esc_html( $title ); ?></h3>
			<p><?php echo esc_html( $text ); ?></p>
			<?php if ( $link ) : ?>
				<a class="nk-link-arrow" <?php echo $link; // phpcs:ignore ?>><?php esc_html_e( 'Detaylı bilgi', 'nakliye' ); ?> <?php echo nakliye_icon( 'arrow', 16 ); // phpcs:ignore ?></a>
			<?php endif; ?>
		</article>
		<?php
	}
}
