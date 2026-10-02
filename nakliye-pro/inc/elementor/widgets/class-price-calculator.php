<?php
/**
 * Taşıma fiyat hesaplayıcı.
 *
 * Fiyat parametreleri Tema Ayarları → Fiyat Hesaplama ekranından gelir;
 * bileşen bazında da ezilebilir.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class Nakliye_Widget_Price_Calculator extends Nakliye_Widget_Base {

	public function get_name() {
		return 'nakliye-price-calculator';
	}

	public function get_title() {
		return __( 'Nakliye Fiyat Hesaplayıcı', 'nakliye' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	protected function register_controls() {
		$this->add_heading_controls(
			array(
				'eyebrow' => __( 'Fiyat Hesapla', 'nakliye' ),
				'title'   => __( 'Taşınma maliyetinizi saniyeler içinde öğrenin', 'nakliye' ),
			)
		);

		$this->start_controls_section( 'section_calc', array( 'label' => __( 'Hesaplayıcı', 'nakliye' ) ) );
		$this->add_control( 'override', array( 'label' => __( 'Tema ayarları yerine özel fiyatlar', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => '' ) );
		foreach ( array( '1+1', '2+1', '3+1', '4+1' ) as $type ) {
			$key = 'base_' . str_replace( '+', '_', $type );
			$this->add_control( $key, array( 'label' => $type . ' ' . __( 'taban', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'condition' => array( 'override' => 'yes' ) ) );
		}
		$this->add_control( 'per_km', array( 'label' => __( 'Km başı', 'nakliye' ), 'type' => Controls_Manager::NUMBER, 'condition' => array( 'override' => 'yes' ) ) );
		$this->add_control( 'show_quote', array( 'label' => __( 'Sonuçtan sonra teklif formu', 'nakliye' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before' ) );
		$this->add_control( 'note', array( 'label' => __( 'Alt not', 'nakliye' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_calc', array( 'label' => __( 'Hesaplayıcı', 'nakliye' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'calc_bg', array( 'label' => __( 'Arka plan', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-calc' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'result_bg', array( 'label' => __( 'Sonuç kutusu', 'nakliye' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .nk-calc__result' => 'background-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render_widget() {
		$s      = $this->get_settings_for_display();
		$config = nakliye_pricing_config();
		if ( 'yes' === $s['override'] ) {
			foreach ( array( '1+1', '2+1', '3+1', '4+1' ) as $type ) {
				$key = 'base_' . str_replace( '+', '_', $type );
				if ( is_numeric( $s[ $key ] ) ) {
					$config['base'][ $type ] = (float) $s[ $key ];
				}
			}
			if ( is_numeric( $s['per_km'] ) ) {
				$config['perKm'] = (float) $s['per_km'];
			}
		}
		$note = $s['note'] ? $s['note'] : nakliye_option( 'price_note' );
		$uid  = wp_unique_id( 'nkc-' );
		?>
		<div class="nk-calc-wrap">
			<?php $this->render_heading( $s ); ?>
			<div class="nk-calc" data-nk-calc="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
				<form class="nk-calc__form" onsubmit="return false">
					<div class="nk-field">
						<label for="<?php echo esc_attr( $uid ); ?>-type"><?php esc_html_e( 'Ev büyüklüğü', 'nakliye' ); ?></label>
						<div class="nk-segment" role="radiogroup" id="<?php echo esc_attr( $uid ); ?>-type">
							<?php foreach ( array_keys( $config['base'] ) as $i => $type ) : ?>
								<label><input type="radio" name="type" value="<?php echo esc_attr( $type ); ?>" <?php checked( 1, $i ); ?>><span><?php echo esc_html( $type ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="nk-form-grid">
						<div class="nk-field">
							<label for="<?php echo esc_attr( $uid ); ?>-scope"><?php esc_html_e( 'Taşıma türü', 'nakliye' ); ?></label>
							<select id="<?php echo esc_attr( $uid ); ?>-scope" name="scope">
								<option value="city"><?php esc_html_e( 'Şehir içi', 'nakliye' ); ?></option>
								<option value="intercity"><?php esc_html_e( 'Şehirler arası', 'nakliye' ); ?></option>
							</select>
						</div>
						<div class="nk-field" data-show-if="intercity">
							<label for="<?php echo esc_attr( $uid ); ?>-km"><?php esc_html_e( 'Mesafe (km)', 'nakliye' ); ?></label>
							<input id="<?php echo esc_attr( $uid ); ?>-km" name="km" type="number" min="0" step="10" value="450">
						</div>
						<div class="nk-field">
							<label for="<?php echo esc_attr( $uid ); ?>-floor-from"><?php esc_html_e( 'Çıkış katı', 'nakliye' ); ?></label>
							<input id="<?php echo esc_attr( $uid ); ?>-floor-from" name="floor_from" type="number" min="0" max="40" value="2">
						</div>
						<div class="nk-field">
							<label for="<?php echo esc_attr( $uid ); ?>-floor-to"><?php esc_html_e( 'Varış katı', 'nakliye' ); ?></label>
							<input id="<?php echo esc_attr( $uid ); ?>-floor-to" name="floor_to" type="number" min="0" max="40" value="3">
						</div>
					</div>
					<div class="nk-checks">
						<label><input type="checkbox" name="elevator" checked> <?php esc_html_e( 'Binalarda asansör var', 'nakliye' ); ?></label>
						<label><input type="checkbox" name="lift"> <?php esc_html_e( 'Dış cephe asansörü', 'nakliye' ); ?></label>
						<label><input type="checkbox" name="packing" checked> <?php esc_html_e( 'Profesyonel paketleme', 'nakliye' ); ?></label>
						<label><input type="checkbox" name="insurance" checked> <?php esc_html_e( 'Taşıma sigortası', 'nakliye' ); ?></label>
					</div>
				</form>
				<div class="nk-calc__result">
					<span class="nk-calc__label"><?php esc_html_e( 'Tahmini tutar', 'nakliye' ); ?></span>
					<strong class="nk-calc__total" data-total>—</strong>
					<ul class="nk-calc__breakdown" data-breakdown></ul>
					<?php if ( $note ) : ?>
						<p class="nk-calc__note"><?php echo esc_html( $note ); ?></p>
					<?php endif; ?>
					<?php if ( 'yes' === $s['show_quote'] ) : ?>
						<button type="button" class="nk-btn nk-btn--primary nk-btn--block" data-calc-quote><?php esc_html_e( 'Bu fiyatla teklif iste', 'nakliye' ); ?></button>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( 'yes' === $s['show_quote'] ) : ?>
				<div class="nk-calc__quote" hidden>
					<?php nakliye_render_quote_form( array( 'compact' => true, 'button' => __( 'Teklifimi Gönder', 'nakliye' ) ) ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
