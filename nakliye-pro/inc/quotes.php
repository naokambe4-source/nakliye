<?php
/**
 * Teklif talepleri ve gönderi takip sistemi.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

/**
 * Talep / taşıma durumları.
 *
 * @return array
 */
function nakliye_quote_statuses() {
	return array(
		'yeni'      => __( 'Yeni talep', 'nakliye' ),
		'ekspertiz' => __( 'Ekspertiz planlandı', 'nakliye' ),
		'teklif'    => __( 'Teklif iletildi', 'nakliye' ),
		'onay'      => __( 'Onaylandı', 'nakliye' ),
		'paket'     => __( 'Paketleniyor', 'nakliye' ),
		'yolda'     => __( 'Yolda', 'nakliye' ),
		'teslim'    => __( 'Teslim edildi', 'nakliye' ),
		'iptal'     => __( 'İptal', 'nakliye' ),
	);
}

/**
 * Formdaki alanlar.
 *
 * @return array
 */
function nakliye_quote_fields() {
	return array(
		'name'      => array( 'label' => __( 'Ad Soyad', 'nakliye' ), 'required' => true ),
		'phone'     => array( 'label' => __( 'Telefon', 'nakliye' ), 'required' => true ),
		'email'     => array( 'label' => __( 'E-posta', 'nakliye' ), 'required' => false ),
		'service'   => array( 'label' => __( 'Hizmet', 'nakliye' ), 'required' => false ),
		'from'      => array( 'label' => __( 'Nereden', 'nakliye' ), 'required' => true ),
		'to'        => array( 'label' => __( 'Nereye', 'nakliye' ), 'required' => true ),
		'date'      => array( 'label' => __( 'Taşınma tarihi', 'nakliye' ), 'required' => false ),
		'home_type' => array( 'label' => __( 'Ev tipi', 'nakliye' ), 'required' => false ),
		'floor'     => array( 'label' => __( 'Kat', 'nakliye' ), 'required' => false ),
		'elevator'  => array( 'label' => __( 'Asansör', 'nakliye' ), 'required' => false ),
		'estimate'  => array( 'label' => __( 'Tahmini tutar', 'nakliye' ), 'required' => false ),
		'message'   => array( 'label' => __( 'Not', 'nakliye' ), 'required' => false ),
	);
}

add_action( 'wp_ajax_nakliye_quote', 'nakliye_ajax_quote' );
add_action( 'wp_ajax_nopriv_nakliye_quote', 'nakliye_ajax_quote' );
/**
 * Teklif formu gönderimi.
 */
function nakliye_ajax_quote() {
	check_ajax_referer( 'nakliye_front', 'nonce' );

	if ( ! Nakliye_License::instance()->is_active() ) {
		wp_send_json_error( array( 'message' => __( 'Form şu anda kullanılamıyor.', 'nakliye' ) ), 403 );
	}

	// Bal küpü: botlar gizli alanı doldurur.
	if ( ! empty( $_POST['nk_website'] ) ) {
		wp_send_json_success( array( 'message' => __( 'Teşekkürler!', 'nakliye' ) ) );
	}

	// IP başına 10 dakikada en fazla 3 başarılı talep.
	$ip_key = 'nk_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 3 ) {
		wp_send_json_error( array( 'message' => __( 'Çok fazla deneme yaptınız. Lütfen biraz sonra tekrar deneyin.', 'nakliye' ) ), 429 );
	}

	$data = array();
	foreach ( nakliye_quote_fields() as $key => $field ) {
		$raw          = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$data[ $key ] = 'message' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( $field['required'] && '' === $data[ $key ] ) {
			/* translators: %s: alan adı */
			wp_send_json_error( array( 'message' => sprintf( __( '"%s" alanı zorunludur.', 'nakliye' ), $field['label'] ) ), 422 );
		}
	}

	if ( $data['email'] && ! is_email( $data['email'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Geçerli bir e-posta adresi girin.', 'nakliye' ) ), 422 );
	}
	if ( strlen( preg_replace( '/\D/', '', $data['phone'] ) ) < 10 ) {
		wp_send_json_error( array( 'message' => __( 'Geçerli bir telefon numarası girin.', 'nakliye' ) ), 422 );
	}

	set_transient( $ip_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$code    = nakliye_generate_tracking_code();
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'nakliye_teklif',
			'post_status' => 'publish',
			/* translators: 1: takip kodu 2: isim 3: nereden 4: nereye */
			'post_title'  => sprintf( '%1$s — %2$s (%3$s → %4$s)', $code, $data['name'], $data['from'], $data['to'] ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Talebiniz kaydedilemedi.', 'nakliye' ) ), 500 );
	}

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, '_nk_q_' . $key, $value );
	}
	update_post_meta( $post_id, '_nk_tracking', $code );
	update_post_meta( $post_id, '_nk_status', 'yeni' );
	update_post_meta( $post_id, '_nk_history', array( array( 'status' => 'yeni', 'time' => time(), 'note' => '' ) ) );

	nakliye_quote_notify( $post_id, $data, $code );

	wp_send_json_success(
		array(
			'message'  => sprintf(
				/* translators: %s: takip kodu */
				__( 'Talebiniz alındı! Takip numaranız: %s. Müşteri temsilcimiz en kısa sürede sizi arayacak.', 'nakliye' ),
				$code
			),
			'tracking' => $code,
		)
	);
}

/**
 * Benzersiz takip kodu: NK-YYMM-XXXXX.
 *
 * @return string
 */
function nakliye_generate_tracking_code() {
	do {
		$code   = 'NK-' . gmdate( 'ym' ) . '-' . strtoupper( wp_generate_password( 5, false, false ) );
		$exists = get_posts(
			array(
				'post_type'   => 'nakliye_teklif',
				'meta_key'    => '_nk_tracking', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => $code, // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
	} while ( $exists );
	return $code;
}

/**
 * E-posta bildirimleri.
 *
 * @param int    $post_id Kayıt.
 * @param array  $data    Veri.
 * @param string $code    Takip kodu.
 */
function nakliye_quote_notify( $post_id, array $data, $code ) {
	$to = nakliye_option( 'quote_email' ) ? nakliye_option( 'quote_email' ) : get_option( 'admin_email' );

	$lines = array();
	foreach ( nakliye_quote_fields() as $key => $field ) {
		if ( '' !== $data[ $key ] ) {
			$lines[] = $field['label'] . ': ' . $data[ $key ];
		}
	}
	$lines[] = '';
	$lines[] = __( 'Yönet:', 'nakliye' ) . ' ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	/* translators: %s: takip kodu */
	wp_mail( $to, sprintf( __( 'Yeni teklif talebi: %s', 'nakliye' ), $code ), implode( "\n", $lines ) );

	if ( $data['email'] ) {
		wp_mail(
			$data['email'],
			/* translators: %s: firma adı */
			sprintf( __( '%s — Talebiniz alındı', 'nakliye' ), nakliye_option( 'company_name' ) ),
			sprintf(
				/* translators: 1: isim 2: takip kodu 3: telefon */
				__( "Merhaba %1\$s,\n\nTaşıma talebiniz bize ulaştı. Takip numaranız: %2\$s\n\nSorularınız için: %3\$s\n\nİyi günler dileriz.", 'nakliye' ),
				$data['name'],
				$code,
				nakliye_option( 'phone' )
			)
		);
	}
}

add_action( 'wp_ajax_nakliye_track', 'nakliye_ajax_track' );
add_action( 'wp_ajax_nopriv_nakliye_track', 'nakliye_ajax_track' );
/**
 * Gönderi takibi. Gizlilik için takip kodu + telefonun son 4 hanesi istenir.
 */
function nakliye_ajax_track() {
	check_ajax_referer( 'nakliye_front', 'nonce' );

	$code  = isset( $_POST['code'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['code'] ) ) ) : '';
	$last4 = isset( $_POST['last4'] ) ? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['last4'] ) ) ) : '';

	if ( ! $code || strlen( $last4 ) !== 4 ) {
		wp_send_json_error( array( 'message' => __( 'Takip numarası ve telefonunuzun son 4 hanesini girin.', 'nakliye' ) ), 422 );
	}

	$posts = get_posts(
		array(
			'post_type'   => 'nakliye_teklif',
			'meta_key'    => '_nk_tracking', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $code, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
		)
	);

	$phone = $posts ? preg_replace( '/\D/', '', get_post_meta( $posts[0]->ID, '_nk_q_phone', true ) ) : '';
	if ( ! $posts || substr( $phone, -4 ) !== $last4 ) {
		wp_send_json_error( array( 'message' => __( 'Bu bilgilerle eşleşen kayıt bulunamadı.', 'nakliye' ) ), 404 );
	}

	$id       = $posts[0]->ID;
	$statuses = nakliye_quote_statuses();
	$history  = (array) get_post_meta( $id, '_nk_history', true );
	$steps    = array();
	foreach ( $history as $item ) {
		if ( empty( $item['status'] ) ) {
			continue;
		}
		$steps[] = array(
			'label' => isset( $statuses[ $item['status'] ] ) ? $statuses[ $item['status'] ] : $item['status'],
			'time'  => wp_date( get_option( 'date_format' ) . ' H:i', (int) $item['time'] ),
			'note'  => isset( $item['note'] ) ? $item['note'] : '',
		);
	}

	$current = get_post_meta( $id, '_nk_status', true );
	wp_send_json_success(
		array(
			'code'    => $code,
			'status'  => isset( $statuses[ $current ] ) ? $statuses[ $current ] : $current,
			'key'     => $current,
			'route'   => get_post_meta( $id, '_nk_q_from', true ) . ' → ' . get_post_meta( $id, '_nk_q_to', true ),
			'date'    => get_post_meta( $id, '_nk_q_date', true ),
			'steps'   => $steps,
			'order'   => array_keys( $statuses ),
		)
	);
}

/* ------------------------------------------------------------------------- */
/* Yönetim ekranı                                                            */
/* ------------------------------------------------------------------------- */

add_action( 'add_meta_boxes_nakliye_teklif', 'nakliye_quote_meta_boxes' );
/**
 * Teklif kutuları.
 */
function nakliye_quote_meta_boxes() {
	add_meta_box( 'nakliye_quote_details', __( 'Talep Detayları', 'nakliye' ), 'nakliye_quote_details_box', 'nakliye_teklif', 'normal', 'high' );
	add_meta_box( 'nakliye_quote_status', __( 'Durum & Takip', 'nakliye' ), 'nakliye_quote_status_box', 'nakliye_teklif', 'side', 'high' );
}

/**
 * @param WP_Post $post Kayıt.
 */
function nakliye_quote_details_box( $post ) {
	echo '<table class="widefat striped nk-quote-table"><tbody>';
	foreach ( nakliye_quote_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, '_nk_q_' . $key, true );
		if ( 'phone' === $key && $value ) {
			$value = '<a href="tel:' . esc_attr( preg_replace( '/[^\d+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a> · <a target="_blank" rel="noopener" href="https://wa.me/' . esc_attr( preg_replace( '/\D/', '', $value ) ) . '">WhatsApp</a>';
		} elseif ( 'email' === $key && $value ) {
			$value = '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
		} else {
			$value = nl2br( esc_html( $value ) );
		}
		echo '<tr><th style="width:180px">' . esc_html( $field['label'] ) . '</th><td>' . wp_kses_post( $value ? $value : '—' ) . '</td></tr>';
	}
	echo '</tbody></table>';

	$history = (array) get_post_meta( $post->ID, '_nk_history', true );
	$labels  = nakliye_quote_statuses();
	echo '<h4>' . esc_html__( 'Durum geçmişi', 'nakliye' ) . '</h4><ol class="nk-history">';
	foreach ( array_reverse( $history ) as $item ) {
		if ( empty( $item['status'] ) ) {
			continue;
		}
		printf(
			'<li><strong>%1$s</strong> — %2$s %3$s</li>',
			esc_html( isset( $labels[ $item['status'] ] ) ? $labels[ $item['status'] ] : $item['status'] ),
			esc_html( wp_date( 'd.m.Y H:i', (int) $item['time'] ) ),
			! empty( $item['note'] ) ? '<em>(' . esc_html( $item['note'] ) . ')</em>' : ''
		);
	}
	echo '</ol>';
}

/**
 * @param WP_Post $post Kayıt.
 */
function nakliye_quote_status_box( $post ) {
	wp_nonce_field( 'nakliye_quote_status', 'nakliye_quote_nonce' );
	$current = get_post_meta( $post->ID, '_nk_status', true );
	echo '<p><strong>' . esc_html__( 'Takip No:', 'nakliye' ) . '</strong> <code>' . esc_html( get_post_meta( $post->ID, '_nk_tracking', true ) ) . '</code></p>';
	echo '<p><label for="nk_status">' . esc_html__( 'Durum', 'nakliye' ) . '</label><select class="widefat" name="nk_status" id="nk_status">';
	foreach ( nakliye_quote_statuses() as $key => $label ) {
		echo '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select></p>';
	echo '<p><label for="nk_status_note">' . esc_html__( 'Müşteriye görünen not', 'nakliye' ) . '</label><input class="widefat" type="text" name="nk_status_note" id="nk_status_note"></p>';
	echo '<p class="description">' . esc_html__( 'Durum değiştiğinde müşteri takip ekranında görünür.', 'nakliye' ) . '</p>';
}

add_action( 'save_post_nakliye_teklif', 'nakliye_save_quote_status' );
/**
 * @param int $post_id Kayıt.
 */
function nakliye_save_quote_status( $post_id ) {
	if ( ! isset( $_POST['nakliye_quote_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nakliye_quote_nonce'] ), 'nakliye_quote_status' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = isset( $_POST['nk_status'] ) ? sanitize_key( $_POST['nk_status'] ) : '';
	$note   = isset( $_POST['nk_status_note'] ) ? sanitize_text_field( wp_unslash( $_POST['nk_status_note'] ) ) : '';
	if ( ! isset( nakliye_quote_statuses()[ $status ] ) ) {
		return;
	}
	$current = get_post_meta( $post_id, '_nk_status', true );
	if ( $status !== $current || $note ) {
		$history   = (array) get_post_meta( $post_id, '_nk_history', true );
		$history[] = array( 'status' => $status, 'time' => time(), 'note' => $note );
		update_post_meta( $post_id, '_nk_history', array_values( array_filter( $history ) ) );
		update_post_meta( $post_id, '_nk_status', $status );
	}
}

add_filter( 'manage_nakliye_teklif_posts_columns', 'nakliye_quote_columns' );
/**
 * @param array $columns Sütunlar.
 * @return array
 */
function nakliye_quote_columns( $columns ) {
	return array(
		'cb'        => $columns['cb'],
		'title'     => __( 'Talep', 'nakliye' ),
		'nk_phone'  => __( 'Telefon', 'nakliye' ),
		'nk_date'   => __( 'Taşınma', 'nakliye' ),
		'nk_est'    => __( 'Tahmin', 'nakliye' ),
		'nk_status' => __( 'Durum', 'nakliye' ),
		'date'      => __( 'Oluşturma', 'nakliye' ),
	);
}

add_action( 'manage_nakliye_teklif_posts_custom_column', 'nakliye_quote_column_content', 10, 2 );
/**
 * @param string $column  Sütun.
 * @param int    $post_id Kayıt.
 */
function nakliye_quote_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'nk_phone':
			echo esc_html( get_post_meta( $post_id, '_nk_q_phone', true ) );
			break;
		case 'nk_date':
			echo esc_html( get_post_meta( $post_id, '_nk_q_date', true ) );
			break;
		case 'nk_est':
			echo esc_html( get_post_meta( $post_id, '_nk_q_estimate', true ) );
			break;
		case 'nk_status':
			$status = get_post_meta( $post_id, '_nk_status', true );
			$labels = nakliye_quote_statuses();
			echo '<span class="nk-badge nk-badge--' . esc_attr( $status ) . '">' . esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status ) . '</span>';
			break;
	}
}

/**
 * Durumlara göre talep sayıları (pano için).
 *
 * @return array
 */
function nakliye_quote_counts() {
	global $wpdb;
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT pm.meta_value AS status, COUNT(*) AS total FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_nk_status' AND p.post_type = 'nakliye_teklif' AND p.post_status = 'publish'
		GROUP BY pm.meta_value",
		OBJECT_K
	);
	$out = array();
	foreach ( nakliye_quote_statuses() as $key => $label ) {
		$out[ $key ] = isset( $rows[ $key ] ) ? (int) $rows[ $key ]->total : 0;
	}
	return $out;
}

/* ------------------------------------------------------------------------- */
/* Ön yüz formu                                                              */
/* ------------------------------------------------------------------------- */

/**
 * Teklif formunu basar (Elementor bileşeni, hero ve kısa kod ortak kullanır).
 *
 * @param array $args Seçenekler.
 */
function nakliye_render_quote_form( array $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'compact'  => false,
			'button'   => __( 'Ücretsiz Teklif Al', 'nakliye' ),
			'services' => array(),
			'success'  => '',
		)
	);

	$uid      = wp_unique_id( 'nkq-' );
	$services = $args['services'];
	if ( empty( $services ) ) {
		$services = wp_list_pluck( get_posts( array( 'post_type' => 'nakliye_hizmet', 'numberposts' => 20, 'orderby' => 'menu_order', 'order' => 'ASC' ) ), 'post_title' );
	}
	$home_types = array( '1+0', '1+1', '2+1', '3+1', '4+1', '5+1 ve üzeri', __( 'Ofis / İşyeri', 'nakliye' ), __( 'Parça eşya', 'nakliye' ) );
	?>
	<form class="nk-quote-form<?php echo $args['compact'] ? ' nk-quote-form--compact' : ''; ?>" data-nk-quote data-success="<?php echo esc_attr( $args['success'] ); ?>" novalidate>
		<div class="nk-form-grid">
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Ad Soyad', 'nakliye' ); ?> *</label>
				<input id="<?php echo esc_attr( $uid ); ?>-name" name="name" type="text" required autocomplete="name">
			</div>
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Telefon', 'nakliye' ); ?> *</label>
				<input id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" type="tel" required autocomplete="tel" placeholder="05xx xxx xx xx">
			</div>
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-from"><?php esc_html_e( 'Nereden', 'nakliye' ); ?> *</label>
				<input id="<?php echo esc_attr( $uid ); ?>-from" name="from" type="text" required placeholder="<?php esc_attr_e( 'İl / İlçe', 'nakliye' ); ?>">
			</div>
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-to"><?php esc_html_e( 'Nereye', 'nakliye' ); ?> *</label>
				<input id="<?php echo esc_attr( $uid ); ?>-to" name="to" type="text" required placeholder="<?php esc_attr_e( 'İl / İlçe', 'nakliye' ); ?>">
			</div>
			<?php if ( ! $args['compact'] ) : ?>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'E-posta', 'nakliye' ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email">
				</div>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-service"><?php esc_html_e( 'Hizmet', 'nakliye' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-service" name="service">
						<option value=""><?php esc_html_e( 'Seçiniz', 'nakliye' ); ?></option>
						<?php foreach ( $services as $service ) : ?>
							<option><?php echo esc_html( $service ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-date"><?php esc_html_e( 'Taşınma tarihi', 'nakliye' ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-date" name="date" type="date" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
				</div>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-home"><?php esc_html_e( 'Ev tipi', 'nakliye' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-home" name="home_type">
						<?php foreach ( $home_types as $type ) : ?>
							<option><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-floor"><?php esc_html_e( 'Kat', 'nakliye' ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-floor" name="floor" type="number" min="-3" max="60" value="0">
				</div>
				<div class="nk-field">
					<label for="<?php echo esc_attr( $uid ); ?>-elevator"><?php esc_html_e( 'Binada asansör', 'nakliye' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-elevator" name="elevator">
						<option><?php esc_html_e( 'Var', 'nakliye' ); ?></option>
						<option><?php esc_html_e( 'Yok', 'nakliye' ); ?></option>
					</select>
				</div>
				<div class="nk-field nk-field--full">
					<label for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'Eklemek istedikleriniz', 'nakliye' ); ?></label>
					<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="3"></textarea>
				</div>
			<?php endif; ?>
		</div>
		<input type="hidden" name="estimate" value="">
		<div class="nk-hp" aria-hidden="true"><input type="text" name="nk_website" tabindex="-1" autocomplete="off"></div>
		<button class="nk-btn nk-btn--primary nk-btn--block" type="submit"><?php echo esc_html( $args['button'] ); ?></button>
		<p class="nk-form-note"><?php echo nakliye_icon( 'shield', 14 ); // phpcs:ignore ?> <?php esc_html_e( 'Bilgileriniz KVKK kapsamında korunur, üçüncü kişilerle paylaşılmaz.', 'nakliye' ); ?></p>
		<div class="nk-form-message" role="status" aria-live="polite"></div>
	</form>
	<?php
}

/**
 * Takip formu.
 */
function nakliye_render_tracking_form() {
	$uid = wp_unique_id( 'nkt-' );
	?>
	<div class="nk-tracking" data-nk-tracking>
		<form class="nk-tracking__form">
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-code"><?php esc_html_e( 'Takip numarası', 'nakliye' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-code" name="code" type="text" placeholder="NK-2410-AB12C" required>
			</div>
			<div class="nk-field">
				<label for="<?php echo esc_attr( $uid ); ?>-last4"><?php esc_html_e( 'Telefonun son 4 hanesi', 'nakliye' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-last4" name="last4" type="text" inputmode="numeric" maxlength="4" pattern="\d{4}" required>
			</div>
			<button class="nk-btn nk-btn--primary" type="submit"><?php echo nakliye_icon( 'search', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Sorgula', 'nakliye' ); ?></button>
		</form>
		<div class="nk-tracking__result" aria-live="polite"></div>
	</div>
	<?php
}

/**
 * Kısa kodlar: Elementor kullanmayanlar için.
 */
add_shortcode(
	'nakliye_teklif_formu',
	static function ( $atts ) {
		if ( ! Nakliye_License::instance()->is_active() ) {
			return current_user_can( 'edit_posts' ) ? '<div class="nk-locked">' . wp_kses_post( nakliye_locked_message() ) . '</div>' : '';
		}
		$atts = shortcode_atts( array( 'kompakt' => 'hayir' ), $atts );
		ob_start();
		nakliye_render_quote_form( array( 'compact' => 'evet' === $atts['kompakt'] ) );
		return ob_get_clean();
	}
);

add_shortcode(
	'nakliye_takip',
	static function () {
		if ( ! Nakliye_License::instance()->is_active() ) {
			return current_user_can( 'edit_posts' ) ? '<div class="nk-locked">' . wp_kses_post( nakliye_locked_message() ) . '</div>' : '';
		}
		ob_start();
		nakliye_render_tracking_form();
		return ob_get_clean();
	}
);
