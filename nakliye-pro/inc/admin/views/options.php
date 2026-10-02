<?php
/**
 * Tema ayarları görünümü.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

$nk_schema   = nakliye_options_schema();
$nk_licensed = Nakliye_License::instance()->is_active();
$nk_tab      = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification
Nakliye_Admin::flash();
if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	echo '<div class="nk-alert nk-alert--success">' . esc_html__( 'Ayarlar kaydedildi.', 'nakliye' ) . '</div>';
}
?>
<div class="nk-options" data-nk-tabs>
	<aside class="nk-options__nav">
		<?php foreach ( $nk_schema as $nk_id => $nk_section ) : ?>
			<a href="#<?php echo esc_attr( $nk_id ); ?>" data-tab="<?php echo esc_attr( $nk_id ); ?>" class="<?php echo $nk_tab === $nk_id ? 'is-active' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $nk_section['icon'] ); ?>"></span> <?php echo esc_html( $nk_section['label'] ); ?>
				<?php if ( ! empty( $nk_section['premium'] ) && ! $nk_licensed ) : ?><span class="dashicons dashicons-lock nk-lock"></span><?php endif; ?>
			</a>
		<?php endforeach; ?>
		<a href="#tools" data-tab="tools" class="<?php echo 'tools' === $nk_tab ? 'is-active' : ''; ?>"><span class="dashicons dashicons-database-export"></span> <?php esc_html_e( 'Yedekle / Sıfırla', 'nakliye' ); ?></a>
	</aside>

	<div class="nk-options__main">
		<form method="post" action="options.php" class="nk-card nk-options__form">
			<?php settings_fields( 'nakliye_options_group' ); ?>
			<?php foreach ( $nk_schema as $nk_id => $nk_section ) : $nk_locked = ! empty( $nk_section['premium'] ) && ! $nk_licensed; ?>
				<section class="nk-options__panel" data-panel="<?php echo esc_attr( $nk_id ); ?>" <?php echo $nk_tab === $nk_id ? '' : 'hidden'; ?>>
					<h2><?php echo esc_html( $nk_section['label'] ); ?></h2>
					<?php if ( $nk_locked ) : ?>
						<div class="nk-alert nk-alert--warning"><?php echo wp_kses_post( nakliye_locked_message() ); ?></div>
					<?php endif; ?>
					<?php
					foreach ( $nk_section['fields'] as $nk_field_id => $nk_field ) {
						Nakliye_Admin::render_field( $nk_field_id, $nk_field, $nk_locked );
					}
					?>
				</section>
			<?php endforeach; ?>
			<div class="nk-options__save" data-save-bar>
				<?php submit_button( __( 'Değişiklikleri Kaydet', 'nakliye' ), 'primary nk-abtn nk-abtn--primary', 'submit', false ); ?>
			</div>
		</form>

		<section class="nk-card nk-options__panel" data-panel="tools" <?php echo 'tools' === $nk_tab ? '' : 'hidden'; ?>>
			<h2><?php esc_html_e( 'Yedekle / Sıfırla', 'nakliye' ); ?></h2>
			<div class="nk-tools">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'nakliye_export' ); ?>
					<input type="hidden" name="action" value="nakliye_export">
					<h3><?php esc_html_e( 'Dışa aktar', 'nakliye' ); ?></h3>
					<p class="nk-muted"><?php esc_html_e( 'Tüm tema ayarlarını JSON dosyası olarak indirin.', 'nakliye' ); ?></p>
					<button class="nk-abtn"><?php esc_html_e( 'Ayarları indir', 'nakliye' ); ?></button>
				</form>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'nakliye_import' ); ?>
					<input type="hidden" name="action" value="nakliye_import">
					<h3><?php esc_html_e( 'İçe aktar', 'nakliye' ); ?></h3>
					<p><input type="file" name="nakliye_import" accept="application/json"></p>
					<button class="nk-abtn"><?php esc_html_e( 'Yükle', 'nakliye' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'nakliye_reset_options' ); ?>
					<input type="hidden" name="action" value="nakliye_reset_options">
					<h3><?php esc_html_e( 'Sıfırla', 'nakliye' ); ?></h3>
					<p class="nk-muted"><?php esc_html_e( 'Tüm tema ayarları varsayılan değerlere döner.', 'nakliye' ); ?></p>
					<button class="nk-abtn nk-abtn--danger" data-confirm><?php esc_html_e( 'Varsayılana döndür', 'nakliye' ); ?></button>
				</form>
			</div>
		</section>
	</div>
</div>
