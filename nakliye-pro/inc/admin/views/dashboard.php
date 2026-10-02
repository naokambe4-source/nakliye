<?php
/**
 * Pano görünümü.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

$nk_license = Nakliye_License::instance()->status();
$nk_counts  = nakliye_quote_counts();
$nk_labels  = nakliye_quote_statuses();
$nk_total   = array_sum( $nk_counts );
$nk_recent  = get_posts( array( 'post_type' => 'nakliye_teklif', 'numberposts' => 6 ) );
$nk_elem    = did_action( 'elementor/loaded' );
?>
<section class="nk-hero-card">
	<div>
		<h1><?php esc_html_e( 'Hoş geldiniz 👋', 'nakliye' ); ?></h1>
		<p><?php esc_html_e( 'Nakliye Pro ile sitenizi birkaç dakikada yayına hazırlayın. Tüm sayfalar Elementor ile sürükle-bırak düzenlenebilir.', 'nakliye' ); ?></p>
		<div class="nk-actions">
			<a class="nk-abtn nk-abtn--primary" href="<?php echo esc_url( admin_url( 'admin.php?page=nakliye-setup' ) ); ?>"><?php esc_html_e( 'Kurulum sihirbazını aç', 'nakliye' ); ?></a>
			<a class="nk-abtn" href="<?php echo esc_url( admin_url( 'admin.php?page=nakliye-options' ) ); ?>"><?php esc_html_e( 'Tema ayarları', 'nakliye' ); ?></a>
			<a class="nk-abtn" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'Siteyi görüntüle', 'nakliye' ); ?></a>
		</div>
	</div>
	<span class="nk-hero-card__art"><?php echo nakliye_icon( 'truck', 120 ); // phpcs:ignore ?></span>
</section>

<div class="nk-cards nk-cards--4">
	<div class="nk-stat">
		<span class="nk-stat__label"><?php esc_html_e( 'Toplam talep', 'nakliye' ); ?></span>
		<strong class="nk-stat__value"><?php echo esc_html( number_format_i18n( $nk_total ) ); ?></strong>
	</div>
	<div class="nk-stat nk-stat--accent">
		<span class="nk-stat__label"><?php esc_html_e( 'Yeni talepler', 'nakliye' ); ?></span>
		<strong class="nk-stat__value"><?php echo esc_html( number_format_i18n( $nk_counts['yeni'] ) ); ?></strong>
	</div>
	<div class="nk-stat">
		<span class="nk-stat__label"><?php esc_html_e( 'Yoldaki taşımalar', 'nakliye' ); ?></span>
		<strong class="nk-stat__value"><?php echo esc_html( number_format_i18n( $nk_counts['yolda'] + $nk_counts['paket'] ) ); ?></strong>
	</div>
	<div class="nk-stat">
		<span class="nk-stat__label"><?php esc_html_e( 'Tamamlanan', 'nakliye' ); ?></span>
		<strong class="nk-stat__value"><?php echo esc_html( number_format_i18n( $nk_counts['teslim'] ) ); ?></strong>
	</div>
</div>

<div class="nk-cards nk-cards--2-1">
	<div class="nk-card">
		<div class="nk-card__head">
			<h2><?php esc_html_e( 'Son teklif talepleri', 'nakliye' ); ?></h2>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nakliye_teklif' ) ); ?>"><?php esc_html_e( 'Tümü', 'nakliye' ); ?> →</a>
		</div>
		<?php if ( $nk_recent ) : ?>
			<table class="nk-table">
				<thead><tr><th><?php esc_html_e( 'Talep', 'nakliye' ); ?></th><th><?php esc_html_e( 'Telefon', 'nakliye' ); ?></th><th><?php esc_html_e( 'Durum', 'nakliye' ); ?></th><th><?php esc_html_e( 'Tarih', 'nakliye' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $nk_recent as $nk_post ) : $nk_status = get_post_meta( $nk_post->ID, '_nk_status', true ); ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $nk_post->ID ) ); ?>"><?php echo esc_html( $nk_post->post_title ); ?></a></td>
						<td><?php echo esc_html( get_post_meta( $nk_post->ID, '_nk_q_phone', true ) ); ?></td>
						<td><span class="nk-badge nk-badge--<?php echo esc_attr( $nk_status ); ?>"><?php echo esc_html( isset( $nk_labels[ $nk_status ] ) ? $nk_labels[ $nk_status ] : $nk_status ); ?></span></td>
						<td><?php echo esc_html( get_the_date( 'd.m.Y H:i', $nk_post ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p class="nk-muted"><?php esc_html_e( 'Henüz teklif talebi yok. Teklif formu bileşenini bir sayfaya eklediğinizde talepler burada listelenir.', 'nakliye' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="nk-card">
		<div class="nk-card__head"><h2><?php esc_html_e( 'Durum', 'nakliye' ); ?></h2></div>
		<ul class="nk-checks-list">
			<li class="<?php echo in_array( $nk_license['code'], array( 'active', 'dev' ), true ) ? 'ok' : 'bad'; ?>">
				<?php esc_html_e( 'Lisans', 'nakliye' ); ?>: <strong><?php echo esc_html( $nk_license['label'] ); ?></strong>
			</li>
			<li class="<?php echo Nakliye_Integrity::is_intact() || nakliye_is_dev_domain() ? 'ok' : 'bad'; ?>">
				<?php esc_html_e( 'Dosya bütünlüğü', 'nakliye' ); ?>: <strong><?php echo esc_html( Nakliye_Integrity::label( $nk_license['integrity']['status'] ) ); ?></strong>
			</li>
			<li class="<?php echo $nk_elem ? 'ok' : 'bad'; ?>">
				Elementor: <strong><?php echo $nk_elem ? esc_html( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'Etkin', 'nakliye' ) ) : esc_html__( 'Kurulu değil', 'nakliye' ); ?></strong>
			</li>
			<li class="<?php echo get_option( 'nakliye_demo_imported' ) ? 'ok' : 'warn'; ?>">
				<?php esc_html_e( 'Demo içerik', 'nakliye' ); ?>: <strong><?php echo get_option( 'nakliye_demo_imported' ) ? esc_html__( 'Yüklendi', 'nakliye' ) : esc_html__( 'Yüklenmedi', 'nakliye' ); ?></strong>
			</li>
		</ul>
		<h3><?php esc_html_e( 'Hızlı bağlantılar', 'nakliye' ); ?></h3>
		<div class="nk-quick">
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nakliye_hizmet' ) ); ?>"><span class="dashicons dashicons-car"></span><?php esc_html_e( 'Hizmetler', 'nakliye' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nakliye_filo' ) ); ?>"><span class="dashicons dashicons-performance"></span><?php esc_html_e( 'Filo', 'nakliye' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=nakliye_yorum' ) ); ?>"><span class="dashicons dashicons-format-quote"></span><?php esc_html_e( 'Yorumlar', 'nakliye' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><span class="dashicons dashicons-menu"></span><?php esc_html_e( 'Menüler', 'nakliye' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><span class="dashicons dashicons-screenoptions"></span><?php esc_html_e( 'Widgetlar', 'nakliye' ); ?></a>
			<?php if ( $nk_elem ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=elementor' ) ); ?>"><span class="dashicons dashicons-edit-page"></span>Elementor</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="nk-card">
	<div class="nk-card__head"><h2><?php esc_html_e( 'Talep dağılımı', 'nakliye' ); ?></h2></div>
	<div class="nk-bars">
		<?php foreach ( $nk_counts as $nk_key => $nk_count ) : ?>
			<div class="nk-bars__row">
				<span><?php echo esc_html( $nk_labels[ $nk_key ] ); ?></span>
				<span class="nk-bars__track"><span class="nk-bars__fill nk-badge--<?php echo esc_attr( $nk_key ); ?>" style="width:<?php echo esc_attr( $nk_total ? round( $nk_count / $nk_total * 100 ) : 0 ); ?>%"></span></span>
				<strong><?php echo esc_html( $nk_count ); ?></strong>
			</div>
		<?php endforeach; ?>
	</div>
</div>
