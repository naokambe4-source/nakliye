/**
 * Nakliye Pro — yönetim paneli ve kurulum sihirbazı.
 */
( function ( $ ) {
	'use strict';

	var A = window.NakliyeAdmin || { i18n: {} };

	$( function () {
		// Renk seçici.
		if ( $.fn.wpColorPicker ) {
			$( '.nk-color' ).wpColorPicker();
		}

		// Ayar sekmeleri.
		var $tabs = $( '[data-nk-tabs]' );
		if ( $tabs.length ) {
			var show = function ( id ) {
				$tabs.find( '.nk-options__nav a' ).removeClass( 'is-active' ).filter( '[data-tab="' + id + '"]' ).addClass( 'is-active' );
				$tabs.find( '[data-panel]' ).attr( 'hidden', true ).filter( '[data-panel="' + id + '"]' ).removeAttr( 'hidden' );
				$tabs.find( '.nk-options__form' ).toggle( 'tools' !== id );
				try { window.localStorage.setItem( 'nakliyeTab', id ); } catch ( e ) {}
			};
			$tabs.on( 'click', '.nk-options__nav a', function ( e ) {
				e.preventDefault();
				show( $( this ).data( 'tab' ) );
			} );
			var initial = ( window.location.hash || '' ).replace( '#', '' );
			if ( ! initial && -1 === window.location.search.indexOf( 'tab=' ) ) {
				try { initial = window.localStorage.getItem( 'nakliyeTab' ); } catch ( e ) {}
			}
			if ( initial && $tabs.find( '[data-panel="' + initial + '"]' ).length ) {
				show( initial );
			} else {
				show( $tabs.find( '.nk-options__nav a.is-active' ).data( 'tab' ) || 'general' );
			}
		}

		// Medya seçici.
		$( document ).on( 'click', '[data-nk-media-select]', function ( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '[data-nk-media]' );
			var frame = wp.media( { title: A.i18n.choose, button: { text: A.i18n.use }, library: { type: 'image' }, multiple: false } );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
				$wrap.find( 'input[type=hidden]' ).val( att.id );
				$wrap.find( '.nk-media__preview' ).html( '<img src="' + url + '" alt="">' );
			} );
			frame.open();
		} );
		$( document ).on( 'click', '[data-nk-media-remove]', function ( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '[data-nk-media]' );
			$wrap.find( 'input[type=hidden]' ).val( '' );
			$wrap.find( '.nk-media__preview' ).empty();
		} );

		// Onay isteyen butonlar.
		$( document ).on( 'click', '[data-confirm]', function ( e ) {
			if ( ! window.confirm( A.i18n.confirm ) ) {
				e.preventDefault();
			}
		} );

		// Sihirbaz: eklenti kurulumu (sırayla).
		$( '[data-install-plugins]' ).on( 'click', function ( e ) {
			e.preventDefault();
			var $btn = $( this ).prop( 'disabled', true );
			var queue = $( '[data-plugin]:checked:not(:disabled)' ).map( function () { return $( this ).data( 'plugin' ); } ).get();
			var failed = false;

			var next = function () {
				if ( ! queue.length ) {
					if ( ! failed ) {
						window.location = $btn.data( 'next' );
					} else {
						$btn.prop( 'disabled', false );
					}
					return;
				}
				var slug = queue.shift();
				var $state = $( '[data-state="' + slug + '"]' ).removeClass( 'is-error' ).addClass( 'is-busy' ).text( A.i18n.installing );
				$.post( A.ajaxUrl, { action: 'nakliye_install_plugin', nonce: A.nonce, slug: slug } )
					.done( function ( res ) {
						if ( res && res.success ) {
							$state.removeClass( 'is-busy' ).text( A.i18n.installed );
							$( '[data-plugin="' + slug + '"]' ).prop( 'disabled', true );
						} else {
							failed = true;
							$state.removeClass( 'is-busy' ).addClass( 'is-error' ).text( res && res.data ? res.data.message : 'Hata' );
						}
					} )
					.fail( function () {
						failed = true;
						$state.removeClass( 'is-busy' ).addClass( 'is-error' ).text( 'Hata' );
					} )
					.always( next );
			};
			next();
		} );

		// Sihirbaz: demo içerik.
		$( '[data-import-demo]' ).on( 'click', function ( e ) {
			e.preventDefault();
			var $btn = $( this ).prop( 'disabled', true );
			var $log = $( '[data-demo-log]' ).removeAttr( 'hidden' ).text( A.i18n.importing );
			var parts = $( '[data-demo-parts] input:checked' ).map( function () { return this.value; } ).get();

			$.post( A.ajaxUrl, { action: 'nakliye_import_demo', nonce: A.nonce, parts: parts } )
				.done( function ( res ) {
					if ( res && res.success ) {
						$log.html( res.data.log.map( function ( l ) { return '✓ ' + $( '<span>' ).text( l ).html(); } ).join( '<br>' ) );
						setTimeout( function () { window.location = $btn.data( 'next' ); }, 1500 );
					} else {
						$log.css( 'color', '#fca5a5' ).text( res && res.data ? res.data.message : 'Hata' );
						$btn.prop( 'disabled', false );
					}
				} )
				.fail( function ( xhr ) {
					$log.css( 'color', '#fca5a5' ).text( 'Sunucu hatası (' + xhr.status + ')' );
					$btn.prop( 'disabled', false );
				} );
		} );
	} );
}( jQuery ) );
