<?php
/**
 * Demo içerik yükleyici.
 *
 * Sayfalar Elementor verisi (_elementor_data) ile oluşturulur; böylece demo
 * yüklendikten sonra her bölüm Elementor'da sürükle-bırak düzenlenebilir.
 *
 * @package Nakliye
 */

defined( 'ABSPATH' ) || exit;

final class Nakliye_Demo_Importer {

	/** @var array Oluşturulan sayfa kimlikleri. */
	private $pages = array();

	/**
	 * Demo içeriği yükler.
	 *
	 * @param array $parts Yüklenecek parçalar.
	 * @return array Günlük.
	 */
	public function run( array $parts ) {
		$log = array();

		if ( in_array( 'content', $parts, true ) ) {
			$log[] = sprintf( __( '%d hizmet oluşturuldu.', 'nakliye' ), $this->services() );
			$log[] = sprintf( __( '%d araç oluşturuldu.', 'nakliye' ), $this->fleet() );
			$log[] = sprintf( __( '%d müşteri yorumu oluşturuldu.', 'nakliye' ), $this->testimonials() );
			$log[] = sprintf( __( '%d blog yazısı oluşturuldu.', 'nakliye' ), $this->posts() );
		}
		if ( in_array( 'pages', $parts, true ) ) {
			$log[] = sprintf( __( '%d Elementor sayfası oluşturuldu.', 'nakliye' ), $this->pages() );
		}
		if ( in_array( 'menus', $parts, true ) ) {
			$this->menus();
			$log[] = __( 'Menüler oluşturuldu ve atandı.', 'nakliye' );
		}
		if ( in_array( 'widgets', $parts, true ) ) {
			$this->widgets();
			$log[] = __( 'Alt alan widgetları yerleştirildi.', 'nakliye' );
		}

		update_option( 'nakliye_demo_imported', time() );
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		flush_rewrite_rules();

		return $log;
	}

	/* --------------------------------------------------------------------- */
	/* İçerik türleri                                                        */
	/* --------------------------------------------------------------------- */

	/**
	 * @return int
	 */
	private function services() {
		$items = array(
			array( 'Evden Eve Nakliyat', 'truck', '4.500 ₺', 'Eşyalarınızı profesyonel ekip ve özel ambalaj malzemeleriyle paketleyip yeni evinize sigortalı olarak taşıyoruz.', "Ücretsiz ekspertiz\nÖzel ambalaj malzemeleri\nMobilya söküm & montaj\nSigortalı taşıma" ),
			array( 'Şehirler Arası Nakliyat', 'globe', '12.000 ₺', 'Türkiye’nin 81 iline GPS takipli araçlarımızla planlı ve güvenli şehirler arası taşımacılık.', "81 ile hizmet\nGPS ile canlı takip\nParsiyel ve komple araç\nKapıdan kapıya teslim" ),
			array( 'Ofis & İşyeri Taşıma', 'building', '8.000 ₺', 'Arşiv, elektronik cihaz ve mobilyalarınızı iş akışınızı aksatmadan hafta sonu taşıyoruz.', "Hafta sonu & gece taşıma\nArşiv etiketleme\nIT ekipmanı paketleme\nProje yöneticisi" ),
			array( 'Asansörlü Taşımacılık', 'lift', '2.500 ₺', 'Dış cephe mobil asansörlerimizle yüksek katlara hızlı, hasarsız ve merdiven yormadan taşıma.', "32 kata kadar erişim\nDar sokaklara uygun\nEşya hasarı riskini azaltır\nZaman tasarrufu" ),
			array( 'Eşya Depolama', 'warehouse', '1.500 ₺/ay', 'Kameralı, alarmlı ve nem kontrollü depolarımızda eşyalarınızı kısa veya uzun süreli güvenle saklıyoruz.', "7/24 kamera\nNem kontrolü\nSigortalı depolama\nEsnek süre" ),
			array( 'Piyano & Hassas Eşya', 'piano', '3.000 ₺', 'Piyano, kasa, sanat eseri ve antika gibi özel eşyalar için uzman ekip ve özel ekipmanla taşıma.', "Özel taşıma ekipmanı\nAhşap sandık\nUzman ekip\nTam sigorta" ),
		);
		$n = 0;
		foreach ( $items as $i => $item ) {
			if ( get_page_by_path( sanitize_title( $item[0] ), OBJECT, 'nakliye_hizmet' ) ) {
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'nakliye_hizmet',
					'post_status'  => 'publish',
					'post_title'   => $item[0],
					'post_excerpt' => $item[3],
					'post_content' => '<p>' . $item[3] . '</p><h2>Neden bizi seçmelisiniz?</h2><p>20 yılı aşkın tecrübemiz, eğitimli personelimiz ve modern araç filomuzla her taşımayı kendi eşyamızmış gibi özenle planlıyoruz. Ücretsiz ekspertiz sonrası net fiyat verir, sürpriz maliyet çıkarmayız.</p>',
					'menu_order'   => $i,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_nk_icon', $item[1] );
				update_post_meta( $id, '_nk_price_from', $item[2] );
				update_post_meta( $id, '_nk_features', $item[4] );
				++$n;
			}
		}
		return $n;
	}

	/**
	 * @return int
	 */
	private function fleet() {
		$items = array(
			array( 'Panelvan', 'Hafif ticari', '1,5 ton', '12 m³', '1+0 / 1+1 ev, parça eşya' ),
			array( 'Kamyonet', 'Kamyonet', '3,5 ton', '25 m³', '2+1 ev, küçük ofis' ),
			array( 'Kamyon', 'Kapalı kasa kamyon', '12 ton', '45 m³', '3+1 / 4+1 ev, şehirler arası' ),
		);
		$n = 0;
		foreach ( $items as $i => $item ) {
			if ( get_page_by_path( sanitize_title( $item[0] ), OBJECT, 'nakliye_filo' ) ) {
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => 'nakliye_filo', 'post_status' => 'publish', 'post_title' => $item[0], 'menu_order' => $i ) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_nk_vehicle_type', $item[1] );
				update_post_meta( $id, '_nk_capacity', $item[2] );
				update_post_meta( $id, '_nk_volume', $item[3] );
				update_post_meta( $id, '_nk_suitable', $item[4] );
				++$n;
			}
		}
		return $n;
	}

	/**
	 * @return int
	 */
	private function testimonials() {
		$items = array(
			array( 'Ayşe K.', 'İstanbul → Ankara', 'Ekspertizden teslimata kadar her şey planlandığı gibi gitti. Tek bir bardak bile kırılmadı, ekibe çok teşekkürler!' ),
			array( 'Mehmet D.', 'Kadıköy → Ataşehir', 'Asansörlü taşıma sayesinde 9. kattaki evimizi 4 saatte boşalttılar. Fiyat da verilen teklifle birebir aynıydı.' ),
			array( 'Zeynep A.', 'Ofis taşıma — İzmir', 'Ofisimizi hafta sonu taşıdılar, pazartesi sabahı her şey çalışır durumdaydı. Kesinlikle tavsiye ederim.' ),
			array( 'Burak Y.', 'Bursa → Antalya', 'Online takip sistemi çok işime yaradı, eşyalarımın nerede olduğunu her an bildim.' ),
			array( 'Elif S.', 'Beşiktaş → Sarıyer', 'Paketlemeden montaja kadar profesyonel bir ekip. Yeni evde mobilyaları bile yerleştirdiler.' ),
			array( 'Hakan T.', 'Piyano taşıma', 'Kuyruklu piyanomuzu özel sandıkla hasarsız taşıdılar. Çok özenliler.' ),
		);
		$n = 0;
		foreach ( $items as $item ) {
			if ( nakliye_get_post_by_title( $item[0], 'nakliye_yorum' ) ) {
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => 'nakliye_yorum', 'post_status' => 'publish', 'post_title' => $item[0], 'post_content' => $item[2] ) );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_nk_role', $item[1] );
				update_post_meta( $id, '_nk_rating', 5 );
				++$n;
			}
		}
		return $n;
	}

	/**
	 * @return int
	 */
	private function posts() {
		$items = array(
			array( 'Taşınmadan önce yapılacaklar listesi', 'Taşınma gününden 4 hafta önce başlayan adım adım kontrol listemizle hiçbir detayı atlamayın. Abonelik iptalleri, adres değişikliği, eşya ayıklama ve paketleme sırası...' ),
			array( 'Eşyalar nasıl doğru paketlenir?', 'Cam ve porselen eşyalar için balonlu naylon, kitaplar için küçük koliler, elektronik cihazlar için orijinal kutular... Profesyonel ekiplerimizin paketleme ipuçları.' ),
		);
		$n = 0;
		foreach ( $items as $item ) {
			if ( get_page_by_path( sanitize_title( $item[0] ), OBJECT, 'post' ) ) {
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $item[0], 'post_content' => '<p>' . $item[1] . '</p>' ) );
			if ( $id && ! is_wp_error( $id ) ) {
				++$n;
			}
		}
		return $n;
	}

	/* --------------------------------------------------------------------- */
	/* Elementor sayfaları                                                   */
	/* --------------------------------------------------------------------- */

	/**
	 * @return int
	 */
	private function pages() {
		$alt = nakliye_option( 'color_bg_alt' );

		$definitions = array(
			'home'     => array(
				'title' => 'Ana Sayfa',
				'slug'  => 'ana-sayfa',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-hero' ) ), 'full' ),
					$this->section( array( $this->widget( 'nakliye-services', array( 'source' => 'posts', 'limit' => 6 ) ) ) ),
					$this->section( array( $this->widget( 'nakliye-counters' ) ), 'full' ),
					$this->section( array( $this->widget( 'nakliye-process' ) ), 'boxed', $alt ),
					$this->section( array( $this->widget( 'nakliye-price-calculator' ) ), 'boxed', '', 'fiyat-hesapla' ),
					$this->section( array( $this->widget( 'nakliye-fleet' ) ), 'boxed', $alt ),
					$this->section( array( $this->widget( 'nakliye-testimonials' ) ) ),
					$this->section( array( $this->widget( 'nakliye-pricing' ) ), 'boxed', $alt ),
					$this->section( array( $this->widget( 'nakliye-quote-form' ) ), 'boxed', '', 'teklif' ),
					$this->section( array( $this->widget( 'nakliye-faq' ) ), 'boxed', $alt ),
				),
			),
			'about'    => array(
				'title' => 'Hakkımızda',
				'slug'  => 'hakkimizda',
				'data'  => array(
					$this->section(
						array(
							$this->widget(
								'nakliye-hero',
								array(
									'badge'     => '2004’ten beri',
									'title'     => '20 yıldır [güvenle] taşıyoruz',
									'subtitle'  => 'Küçük bir kamyonla başladığımız yolculuğa bugün 45 araçlık filomuz ve 120 kişilik ekibimizle devam ediyoruz.',
									'show_form' => '',
									'btn2_text' => '',
								)
							),
						),
						'full'
					),
					$this->section(
						array(
							$this->core_widget( 'heading', array( 'title' => 'Hikâyemiz', 'header_size' => 'h2' ) ),
							$this->core_widget( 'text-editor', array( 'editor' => '<p>Kurulduğumuz günden bu yana tek bir ilkemiz var: Müşterimizin eşyasına kendi eşyamız gibi davranmak. Eğitimli ekiplerimiz, sigortalı taşıma politikamız ve şeffaf fiyatlandırmamızla on binlerce ailenin ve yüzlerce kurumun taşınma sürecini kolaylaştırdık.</p><p>Bugün İstanbul, Ankara ve İzmir’deki şubelerimiz ve 81 ile uzanan hizmet ağımızla Türkiye’nin her noktasına kapıdan kapıya taşımacılık hizmeti sunuyoruz.</p>' ) ),
						)
					),
					$this->section( array( $this->widget( 'nakliye-counters' ) ), 'full' ),
					$this->section( array( $this->widget( 'nakliye-process' ) ), 'boxed', $alt ),
					$this->section( array( $this->widget( 'nakliye-cta' ) ) ),
				),
			),
			'services' => array(
				'title' => 'Hizmetlerimiz',
				'slug'  => 'hizmetlerimiz',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-services', array( 'source' => 'posts', 'limit' => 12, 'style' => 'boxed' ) ) ) ),
					$this->section( array( $this->widget( 'nakliye-pricing' ) ), 'boxed', $alt ),
					$this->section( array( $this->widget( 'nakliye-cta' ) ) ),
				),
			),
			'calc'     => array(
				'title' => 'Fiyat Hesapla',
				'slug'  => 'fiyat-hesapla',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-price-calculator' ) ) ),
					$this->section( array( $this->widget( 'nakliye-faq' ) ), 'boxed', $alt ),
				),
			),
			'quote'    => array(
				'title' => 'Teklif Al',
				'slug'  => 'teklif-al',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-quote-form' ) ) ),
					$this->section( array( $this->widget( 'nakliye-process' ) ), 'boxed', $alt ),
				),
			),
			'tracking' => array(
				'title' => 'Taşıma Takibi',
				'slug'  => 'tasima-takibi',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-tracking' ) ) ),
				),
			),
			'contact'  => array(
				'title' => 'İletişim',
				'slug'  => 'iletisim',
				'data'  => array(
					$this->section( array( $this->widget( 'nakliye-quote-form', array( 'eyebrow' => 'İletişim', 'title' => 'Bize ulaşın' ) ) ) ),
				),
			),
		);

		$n = 0;
		foreach ( $definitions as $key => $def ) {
			$existing = get_page_by_path( $def['slug'] );
			if ( $existing ) {
				$this->pages[ $key ] = $existing->ID;
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $def['title'],
					'post_name'    => $def['slug'],
					'post_content' => '',
				)
			);
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, '_wp_page_template', 'page-templates/template-fullwidth.php' );
			update_post_meta( $id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $def['data'] ) ) );
			$this->pages[ $key ] = $id;
			++$n;
		}

		// Blog sayfası.
		$blog = get_page_by_path( 'blog' );
		$blog = $blog ? $blog->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blog', 'post_name' => 'blog' ) );
		$this->pages['blog'] = $blog;

		if ( ! empty( $this->pages['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $this->pages['home'] );
			update_option( 'page_for_posts', $blog );
		}

		// Menü butonu teklif sayfasına yönlensin.
		if ( ! empty( $this->pages['quote'] ) ) {
			$opts                   = get_option( 'nakliye_options', array() );
			$opts                   = is_array( $opts ) ? $opts : array();
			$opts['header_cta_url'] = get_permalink( $this->pages['quote'] );
			update_option( 'nakliye_options', $opts );
		}

		return $n;
	}

	/**
	 * Elementor bölümü (section → column → widgets).
	 *
	 * @param array  $widgets Bileşenler.
	 * @param string $layout  boxed|full.
	 * @param string $bg      Arka plan rengi.
	 * @param string $anchor  Bağlantı çapası (#id).
	 * @return array
	 */
	private function section( array $widgets, $layout = 'boxed', $bg = '', $anchor = '' ) {
		$settings = array();
		if ( 'full' === $layout ) {
			$settings['layout']  = 'full_width';
			$settings['gap']     = 'no';
			$settings['padding'] = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
		} else {
			$settings['padding'] = array( 'unit' => 'px', 'top' => '90', 'right' => '20', 'bottom' => '90', 'left' => '20', 'isLinked' => false );
			$settings['padding_mobile'] = array( 'unit' => 'px', 'top' => '60', 'right' => '16', 'bottom' => '60', 'left' => '16', 'isLinked' => false );
		}
		if ( $bg ) {
			$settings['background_background'] = 'classic';
			$settings['background_color']      = $bg;
		}
		if ( $anchor ) {
			$settings['_element_id'] = $anchor;
		}

		return array(
			'id'       => $this->id(),
			'elType'   => 'section',
			'settings' => $settings,
			'elements' => array(
				array(
					'id'       => $this->id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100, '_inline_size' => null ),
					'elements' => $widgets,
				),
			),
			'isInner'  => false,
		);
	}

	/**
	 * Nakliye bileşeni. Boş ayarlar Elementor'da varsayılan değerlere düşer.
	 *
	 * @param string $type     Bileşen adı.
	 * @param array  $settings Ayarlar.
	 * @return array
	 */
	private function widget( $type, array $settings = array() ) {
		return array(
			'id'         => $this->id(),
			'elType'     => 'widget',
			'settings'   => (object) $settings,
			'elements'   => array(),
			'widgetType' => $type,
		);
	}

	/**
	 * Elementor çekirdek bileşeni.
	 *
	 * @param string $type     Bileşen.
	 * @param array  $settings Ayarlar.
	 * @return array
	 */
	private function core_widget( $type, array $settings ) {
		return $this->widget( $type, $settings );
	}

	/**
	 * @return string
	 */
	private function id() {
		return substr( md5( wp_generate_uuid4() ), 0, 7 );
	}

	/* --------------------------------------------------------------------- */
	/* Menüler & widgetlar                                                   */
	/* --------------------------------------------------------------------- */

	private function menus() {
		$locations = get_theme_mod( 'nav_menu_locations', array() );

		$primary = $this->menu(
			'Ana Menü',
			array(
				array( 'home', null ),
				array( 'about', null ),
				array( 'services', 'services_children' ),
				array( 'calc', null ),
				array( 'tracking', null ),
				array( 'blog', null ),
				array( 'contact', null ),
			)
		);
		$footer  = $this->menu( 'Alt Menü', array( array( 'about', null ), array( 'quote', null ), array( 'tracking', null ), array( 'blog', null ), array( 'contact', null ) ) );

		if ( $primary ) {
			$locations['primary'] = $primary;
		}
		if ( $footer ) {
			$locations['footer'] = $footer;
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * @param string $name  Menü adı.
	 * @param array  $items Öğeler.
	 * @return int
	 */
	private function menu( $name, array $items ) {
		$existing = wp_get_nav_menu_object( $name );
		if ( $existing ) {
			return (int) $existing->term_id;
		}
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return 0;
		}
		foreach ( $items as $item ) {
			list( $key, $children ) = $item;
			if ( empty( $this->pages[ $key ] ) ) {
				$page = $this->find_page( $key );
				if ( ! $page ) {
					continue;
				}
				$this->pages[ $key ] = $page;
			}
			$parent = wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-object-id' => $this->pages[ $key ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
			if ( 'services_children' === $children && ! is_wp_error( $parent ) ) {
				foreach ( get_posts( array( 'post_type' => 'nakliye_hizmet', 'numberposts' => 8, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $service ) {
					wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object-id' => $service->ID,
							'menu-item-object'    => 'nakliye_hizmet',
							'menu-item-type'      => 'post_type',
							'menu-item-parent-id' => $parent,
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
		return (int) $menu_id;
	}

	/**
	 * @param string $key Sayfa anahtarı.
	 * @return int
	 */
	private function find_page( $key ) {
		$slugs = array( 'home' => 'ana-sayfa', 'about' => 'hakkimizda', 'services' => 'hizmetlerimiz', 'calc' => 'fiyat-hesapla', 'quote' => 'teklif-al', 'tracking' => 'tasima-takibi', 'contact' => 'iletisim', 'blog' => 'blog' );
		$page  = isset( $slugs[ $key ] ) ? get_page_by_path( $slugs[ $key ] ) : null;
		return $page ? $page->ID : 0;
	}

	/**
	 * Alt alan widgetları: boş sütunlar temanın akıllı varsayılanlarını kullanır,
	 * burada yalnızca 3. sütuna "Son yazılar" yerleştirilir.
	 */
	private function widgets() {
		$sidebars = get_option( 'sidebars_widgets', array() );
		if ( ! empty( $sidebars['sidebar-1'] ) ) {
			return;
		}
		$search             = get_option( 'widget_search', array() );
		$search[2]          = array( 'title' => '' );
		$recent             = get_option( 'widget_recent-posts', array() );
		$recent[2]          = array( 'title' => 'Son Yazılar', 'number' => 5 );
		update_option( 'widget_search', $search );
		update_option( 'widget_recent-posts', $recent );
		$sidebars['sidebar-1'] = array( 'search-2', 'recent-posts-2' );
		update_option( 'sidebars_widgets', $sidebars );
	}
}

/**
 * get_page_by_title() WP 6.2'de kullanımdan kalktı; güvenli alternatif.
 *
 * @param string $title     Başlık.
 * @param string $post_type Tür.
 * @return int
 */
function nakliye_get_post_by_title( $title, $post_type ) {
	$posts = get_posts(
		array(
			'post_type'   => $post_type,
			'title'       => $title,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	return $posts ? (int) $posts[0] : 0;
}
