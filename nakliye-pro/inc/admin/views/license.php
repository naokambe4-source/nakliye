<?php
/**
 * Lisans görünümü.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

$nk_status = Nakliye_License::instance()->status();
$nk_ok     = 'active' === $nk_status['code'];
Nakliye_Admin::flash();
?>
<div class="nk-cards nk-cards--2-1">
	<div class="nk-card nk-license <?php echo $nk_ok ? 'is-active' : ''; ?>">
		<div class="nk-license__icon"><span class="dashicons dashicons-<?php echo $nk_ok ? 'unlock' : 'lock'; ?>"></span></div>
		<h2><?php echo $nk_ok ? esc_html__( 'Lisansınız aktif', 'nakliye' ) : esc_html__( 'Lisansınızı etkinleştirin', 'nakliye' ); ?></h2>
		<p class="nk-muted"><?php esc_html_e( 'Lisans anahtarınız satın alma e-postanızda yer alır. Her lisans belirli sayıda alan adında kullanılabilir.', 'nakliye' ); ?></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="nk-license__form">
			<?php wp_nonce_field( 'nakliye_license' ); ?>
			<input type="hidden" name="action" value="nakliye_license">
			<?php if ( $nk_ok ) : ?>
				<input type="text" value="<?php echo esc_attr( $nk_status['key'] ); ?>" disabled class="nk-input-lg">
				<div class="nk-actions">
					<button class="nk-abtn" name="license_action" value="check"><?php esc_html_e( 'Şimdi doğrula', 'nakliye' ); ?></button>
					<button class="nk-abtn nk-abtn--danger" name="license_action" value="deactivate" data-confirm><?php esc_html_e( 'Bu siteden kaldır', 'nakliye' ); ?></button>
				</div>
			<?php else : ?>
				<input type="text" name="license_key" class="nk-input-lg" placeholder="NKP-XXXX-XXXX-XXXX-XXXX" autocomplete="off" required>
				<div class="nk-actions">
					<button class="nk-abtn nk-abtn--primary" name="license_action" value="activate"><?php esc_html_e( 'Lisansı etkinleştir', 'nakliye' ); ?></button>
					<a class="nk-abtn" href="<?php echo esc_url( NAKLIYE_PURCHASE_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Lisans satın al', 'nakliye' ); ?></a>
					<?php if ( ! empty( $nk_status['key'] ) ) : ?>
						<button class="nk-abtn" name="license_action" value="check"><?php esc_html_e( 'Yeniden doğrula', 'nakliye' ); ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</form>
	</div>

	<div class="nk-card">
		<div class="nk-card__head"><h2><?php esc_html_e( 'Lisans bilgileri', 'nakliye' ); ?></h2></div>
		<table class="nk-kv">
			<tr><th><?php esc_html_e( 'Durum', 'nakliye' ); ?></th><td><span class="nk-pill nk-pill--<?php echo in_array( $nk_status['code'], array( 'active', 'dev' ), true ) ? 'ok' : 'bad'; ?>"><?php echo esc_html( $nk_status['label'] ); ?></span></td></tr>
			<tr><th><?php esc_html_e( 'Alan adı', 'nakliye' ); ?></th><td><code><?php echo esc_html( $nk_status['domain'] ); ?></code></td></tr>
			<?php if ( $nk_status['customer'] ) : ?>
				<tr><th><?php esc_html_e( 'Lisans sahibi', 'nakliye' ); ?></th><td><?php echo esc_html( $nk_status['customer'] ); ?></td></tr>
			<?php endif; ?>
			<?php if ( $nk_status['sites'] ) : ?>
				<tr><th><?php esc_html_e( 'Site kullanımı', 'nakliye' ); ?></th><td><?php echo esc_html( $nk_status['sites'] ); ?></td></tr>
			<?php endif; ?>
			<tr><th><?php esc_html_e( 'Bitiş', 'nakliye' ); ?></th><td><?php echo $nk_status['expires'] ? esc_html( wp_date( 'd.m.Y', $nk_status['expires'] ) ) : esc_html__( 'Süresiz', 'nakliye' ); ?></td></tr>
			<tr><th><?php esc_html_e( 'Son doğrulama', 'nakliye' ); ?></th><td><?php echo $nk_status['last_check'] ? esc_html( wp_date( 'd.m.Y H:i', $nk_status['last_check'] ) ) : '—'; ?></td></tr>
			<tr><th><?php esc_html_e( 'Dosya bütünlüğü', 'nakliye' ); ?></th><td><?php echo esc_html( Nakliye_Integrity::label( $nk_status['integrity']['status'] ) ); ?></td></tr>
		</table>
		<?php if ( $nk_status['last_error'] ) : ?>
			<div class="nk-alert nk-alert--error"><?php echo esc_html( $nk_status['last_error'] ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $nk_status['integrity']['files'] ) ) : ?>
			<details class="nk-details"><summary><?php esc_html_e( 'Değiştirilen dosyalar', 'nakliye' ); ?></summary><ul>
				<?php foreach ( $nk_status['integrity']['files'] as $nk_file ) : ?>
					<li><code><?php echo esc_html( $nk_file ); ?></code></li>
				<?php endforeach; ?>
			</ul><p class="nk-muted"><?php esc_html_e( 'Temayı orijinal paketten yeniden yükleyin. Özelleştirmeler için alt tema (child theme) kullanın.', 'nakliye' ); ?></p></details>
		<?php endif; ?>
		<?php if ( 'dev' === $nk_status['code'] ) : ?>
			<div class="nk-alert nk-alert--info"><?php esc_html_e( 'Yerel/geliştirme ortamında tüm özellikler lisanssız açıktır. Canlı alan adına taşıdığınızda lisansı etkinleştirmeniz gerekir.', 'nakliye' ); ?></div>
		<?php endif; ?>
	</div>
</div>

<div class="nk-cards nk-cards--3">
	<div class="nk-card nk-feature"><span class="dashicons dashicons-shield"></span><h3><?php esc_html_e( 'İmzalı lisans', 'nakliye' ); ?></h3><p class="nk-muted"><?php esc_html_e( 'Lisans jetonu RSA-2048 ile imzalanır ve alan adınıza bağlanır.', 'nakliye' ); ?></p></div>
	<div class="nk-card nk-feature"><span class="dashicons dashicons-update"></span><h3><?php esc_html_e( 'Otomatik doğrulama', 'nakliye' ); ?></h3><p class="nk-muted"><?php esc_html_e( 'Lisans günde bir kez arka planda doğrulanır; geçici bağlantı sorunlarında 14 gün tolerans vardır.', 'nakliye' ); ?></p></div>
	<div class="nk-card nk-feature"><span class="dashicons dashicons-admin-site-alt3"></span><h3><?php esc_html_e( 'Alan adı taşıma', 'nakliye' ); ?></h3><p class="nk-muted"><?php esc_html_e( 'Siteyi taşırken önce "Bu siteden kaldır" deyin, ardından yeni alan adında etkinleştirin.', 'nakliye' ); ?></p></div>
</div>
