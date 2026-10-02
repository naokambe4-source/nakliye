<?php
/**
 * Özel içerik türleri: Hizmetler, Filo, Müşteri Yorumları, Teklif Talepleri.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'nakliye_register_post_types' );
/**
 * İçerik türlerini kaydeder.
 */
function nakliye_register_post_types() {
	register_post_type(
		'nakliye_hizmet',
		array(
			'labels'       => nakliye_cpt_labels( __( 'Hizmet', 'nakliye' ), __( 'Hizmetler', 'nakliye' ) ),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'hizmetler', 'with_front' => false ),
			'menu_icon'    => 'dashicons-car',
			'menu_position'=> 25,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'elementor' ),
		)
	);

	register_taxonomy(
		'nakliye_hizmet_kat',
		'nakliye_hizmet',
		array(
			'labels'            => array( 'name' => __( 'Hizmet Kategorileri', 'nakliye' ), 'singular_name' => __( 'Hizmet Kategorisi', 'nakliye' ) ),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'hizmet-kategori' ),
		)
	);

	register_post_type(
		'nakliye_filo',
		array(
			'labels'       => nakliye_cpt_labels( __( 'Araç', 'nakliye' ), __( 'Araç Filosu', 'nakliye' ) ),
			'public'       => true,
			'has_archive'  => false,
			'rewrite'      => array( 'slug' => 'filo' ),
			'menu_icon'    => 'dashicons-performance',
			'menu_position'=> 26,
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'nakliye_yorum',
		array(
			'labels'              => nakliye_cpt_labels( __( 'Müşteri Yorumu', 'nakliye' ), __( 'Müşteri Yorumları', 'nakliye' ) ),
			'public'              => false,
			'show_ui'             => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-format-quote',
			'menu_position'       => 27,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
		)
	);

	register_post_type(
		'nakliye_teklif',
		array(
			'labels'              => nakliye_cpt_labels( __( 'Teklif Talebi', 'nakliye' ), __( 'Teklif Talepleri', 'nakliye' ) ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'nakliye',
			'exclude_from_search' => true,
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
		)
	);
}

/**
 * @param string $singular Tekil.
 * @param string $plural   Çoğul.
 * @return array
 */
function nakliye_cpt_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		/* translators: %s: içerik türü */
		'add_new_item'       => sprintf( __( 'Yeni %s Ekle', 'nakliye' ), $singular ),
		'add_new'            => __( 'Yeni Ekle', 'nakliye' ),
		/* translators: %s: içerik türü */
		'edit_item'          => sprintf( __( '%s Düzenle', 'nakliye' ), $singular ),
		/* translators: %s: içerik türü */
		'view_item'          => sprintf( __( '%s Görüntüle', 'nakliye' ), $singular ),
		'all_items'          => $plural,
		'search_items'       => __( 'Ara', 'nakliye' ),
		'not_found'          => __( 'Kayıt bulunamadı.', 'nakliye' ),
		'not_found_in_trash' => __( 'Çöp kutusunda kayıt yok.', 'nakliye' ),
	);
}

/**
 * Basit meta alan tanımları.
 *
 * @return array
 */
function nakliye_meta_fields() {
	return array(
		'nakliye_hizmet' => array(
			'_nk_icon'       => array( 'label' => __( 'İkon (emoji veya dashicon sınıfı)', 'nakliye' ), 'type' => 'text', 'placeholder' => '🚚' ),
			'_nk_price_from' => array( 'label' => __( 'Başlangıç fiyatı', 'nakliye' ), 'type' => 'text', 'placeholder' => '4.500 ₺' ),
			'_nk_features'   => array( 'label' => __( 'Öne çıkan özellikler (her satıra bir)', 'nakliye' ), 'type' => 'textarea' ),
		),
		'nakliye_filo'   => array(
			'_nk_vehicle_type' => array( 'label' => __( 'Araç tipi', 'nakliye' ), 'type' => 'text', 'placeholder' => 'Kamyon' ),
			'_nk_capacity'     => array( 'label' => __( 'Taşıma kapasitesi', 'nakliye' ), 'type' => 'text', 'placeholder' => '12 ton' ),
			'_nk_volume'       => array( 'label' => __( 'Hacim', 'nakliye' ), 'type' => 'text', 'placeholder' => '45 m³' ),
			'_nk_suitable'     => array( 'label' => __( 'Uygun olduğu taşıma', 'nakliye' ), 'type' => 'text', 'placeholder' => '3+1 / 4+1 ev' ),
		),
		'nakliye_yorum'  => array(
			'_nk_role'   => array( 'label' => __( 'Ünvan / Şehir', 'nakliye' ), 'type' => 'text', 'placeholder' => 'İstanbul → Ankara' ),
			'_nk_rating' => array( 'label' => __( 'Puan (1-5)', 'nakliye' ), 'type' => 'number' ),
		),
	);
}

add_action( 'add_meta_boxes', 'nakliye_add_meta_boxes' );
/**
 * Meta kutuları.
 */
function nakliye_add_meta_boxes() {
	foreach ( nakliye_meta_fields() as $post_type => $fields ) {
		add_meta_box( 'nakliye_meta', __( 'Nakliye Pro Detayları', 'nakliye' ), 'nakliye_render_meta_box', $post_type, 'normal', 'high', $fields );
	}
}

/**
 * @param WP_Post $post Yazı.
 * @param array   $box  Kutu.
 */
function nakliye_render_meta_box( $post, $box ) {
	wp_nonce_field( 'nakliye_meta', 'nakliye_meta_nonce' );
	echo '<div class="nk-metabox">';
	foreach ( $box['args'] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br>';
		if ( 'textarea' === $field['type'] ) {
			echo '<textarea class="widefat" rows="4" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
		} else {
			printf(
				'<input class="widefat" type="%1$s" id="%2$s" name="%2$s" value="%3$s" placeholder="%4$s"%5$s>',
				esc_attr( $field['type'] ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ),
				'number' === $field['type'] ? ' min="1" max="5"' : ''
			);
		}
		echo '</p>';
	}
	echo '</div>';
}

add_action( 'save_post', 'nakliye_save_meta', 10, 2 );
/**
 * @param int     $post_id Yazı kimliği.
 * @param WP_Post $post    Yazı.
 */
function nakliye_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['nakliye_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nakliye_meta_nonce'] ), 'nakliye_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = nakliye_meta_fields();
	if ( empty( $fields[ $post->post_type ] ) ) {
		return;
	}
	foreach ( $fields[ $post->post_type ] as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw   = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$value = 'textarea' === $field['type'] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( 'number' === $field['type'] ) {
			$value = max( 1, min( 5, absint( $value ) ) );
		}
		update_post_meta( $post_id, $key, $value );
	}
}

add_action( 'after_switch_theme', 'nakliye_flush_rewrites' );
/**
 * Kalıcı bağlantıları yeniler.
 */
function nakliye_flush_rewrites() {
	nakliye_register_post_types();
	flush_rewrite_rules();
}
