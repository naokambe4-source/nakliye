<?php
/**
 * Sistem durumu görünümü.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

$nk_memory = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
$nk_checks = array(
	array( __( 'PHP sürümü', 'nakliye' ), PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ), __( 'En az 7.4 (8.1+ önerilir)', 'nakliye' ) ),
	array( __( 'WordPress sürümü', 'nakliye' ), get_bloginfo( 'version' ), version_compare( get_bloginfo( 'version' ), '6.0', '>=' ), __( 'En az 6.0', 'nakliye' ) ),
	array( 'Elementor', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : __( 'Yok', 'nakliye' ), defined( 'ELEMENTOR_VERSION' ), __( 'Sayfa düzenleme için gerekli', 'nakliye' ) ),
	array( 'Elementor Pro', defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : __( 'Yok (isteğe bağlı)', 'nakliye' ), true, __( 'Header/Footer tasarımı için isteğe bağlı', 'nakliye' ) ),
	array( __( 'Bellek limiti', 'nakliye' ), size_format( $nk_memory ), $nk_memory >= 128 * MB_IN_BYTES, __( 'En az 128 MB (Elementor için 256 MB önerilir)', 'nakliye' ) ),
	array( __( 'Maks. çalışma süresi', 'nakliye' ), ini_get( 'max_execution_time' ) . ' sn', (int) ini_get( 'max_execution_time' ) >= 60 || 0 === (int) ini_get( 'max_execution_time' ), __( 'Demo yükleme için en az 60 sn', 'nakliye' ) ),
	array( __( 'Maks. yükleme boyutu', 'nakliye' ), size_format( wp_max_upload_size() ), wp_max_upload_size() >= 8 * MB_IN_BYTES, __( 'En az 8 MB', 'nakliye' ) ),
	array( 'OpenSSL', OPENSSL_VERSION_TEXT, function_exists( 'openssl_verify' ), __( 'Lisans doğrulaması için gerekli', 'nakliye' ) ),
	array( 'cURL', function_exists( 'curl_version' ) ? curl_version()['version'] : __( 'Yok', 'nakliye' ), function_exists( 'curl_version' ), __( 'Lisans sunucusu bağlantısı', 'nakliye' ) ),
	array( __( 'Kalıcı bağlantılar', 'nakliye' ), get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Düz', 'nakliye' ), (bool) get_option( 'permalink_structure' ), __( '"Yazı adı" önerilir', 'nakliye' ) ),
	array( 'WP Cron', defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? __( 'Kapalı', 'nakliye' ) : __( 'Açık', 'nakliye' ), ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ), __( 'Günlük lisans doğrulaması için', 'nakliye' ) ),
	array( __( 'Lisans sunucusu', 'nakliye' ), NAKLIYE_LICENSE_SERVER, nakliye_public_key_ready(), __( 'Açık anahtar yapılandırılmış olmalı', 'nakliye' ) ),
);
?>
<div class="nk-card">
	<div class="nk-card__head"><h2><?php esc_html_e( 'Sunucu ortamı', 'nakliye' ); ?></h2></div>
	<table class="nk-table nk-table--status">
		<thead><tr><th><?php esc_html_e( 'Kontrol', 'nakliye' ); ?></th><th><?php esc_html_e( 'Değer', 'nakliye' ); ?></th><th><?php esc_html_e( 'Gereksinim', 'nakliye' ); ?></th><th></th></tr></thead>
		<tbody>
			<?php foreach ( $nk_checks as $nk_row ) : ?>
				<tr>
					<td><?php echo esc_html( $nk_row[0] ); ?></td>
					<td><code><?php echo esc_html( $nk_row[1] ); ?></code></td>
					<td class="nk-muted"><?php echo esc_html( $nk_row[3] ); ?></td>
					<td><span class="dashicons dashicons-<?php echo $nk_row[2] ? 'yes-alt nk-ok' : 'warning nk-bad'; ?>"></span></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
