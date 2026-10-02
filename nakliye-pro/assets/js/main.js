/**
 * Nakliye Pro — ön yüz betikleri (bağımlılıksız).
 */
( function () {
	'use strict';

	var D = window.NakliyeData || { ajaxUrl: '', nonce: '', currency: '₺', i18n: {} };
	var i18n = D.i18n || {};

	function $( sel, ctx ) { return ( ctx || document ).querySelector( sel ); }
	function $$( sel, ctx ) { return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) ); }

	function money( value ) {
		var rounded = Math.round( value / 50 ) * 50;
		return rounded.toLocaleString( 'tr-TR' ) + ' ' + ( D.currency || '₺' );
	}

	function post( action, data ) {
		var body = new FormData();
		body.append( 'action', action );
		body.append( 'nonce', D.nonce );
		Object.keys( data ).forEach( function ( k ) { body.append( k, data[ k ] ); } );
		return fetch( D.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json(); } );
	}

	function escapeHtml( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Genel site davranışları (bir kez)                                  */
	/* ------------------------------------------------------------------ */

	function initSite() {
		// Preloader.
		var pre = $( '.nk-preloader' );
		if ( pre ) {
			var hide = function () { pre.classList.add( 'is-hidden' ); };
			window.addEventListener( 'load', hide );
			setTimeout( hide, 2500 );
		}

		// Yapışkan üst alan + yukarı çık.
		var header = $( '#nk-header' );
		var top = $( '.nk-float__btn--top' );
		var onScroll = function () {
			var y = window.scrollY;
			if ( header ) { header.classList.toggle( 'is-scrolled', y > 80 ); }
			if ( top ) { top.classList.toggle( 'is-visible', y > 600 ); }
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
		if ( top ) {
			top.addEventListener( 'click', function () { window.scrollTo( { top: 0, behavior: 'smooth' } ); } );
		}

		// Mobil menü.
		var burger = $( '.nk-burger' );
		var nav = $( '#nk-nav' );
		if ( burger && nav ) {
			var toggle = function ( open ) {
				nav.classList.toggle( 'is-open', open );
				document.body.classList.toggle( 'nk-menu-open', open );
				burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			};
			burger.addEventListener( 'click', function () { toggle( ! nav.classList.contains( 'is-open' ) ); } );
			document.addEventListener( 'keydown', function ( e ) { if ( 'Escape' === e.key ) { toggle( false ); } } );
			document.addEventListener( 'click', function ( e ) {
				if ( nav.classList.contains( 'is-open' ) && ! nav.contains( e.target ) && ! burger.contains( e.target ) ) { toggle( false ); }
			} );
			$$( '.menu-item-has-children > a', nav ).forEach( function ( a ) {
				a.addEventListener( 'click', function ( e ) {
					if ( window.innerWidth > 1024 ) { return; }
					var li = a.parentNode;
					if ( ! li.classList.contains( 'is-open' ) ) {
						e.preventDefault();
						li.classList.add( 'is-open' );
					}
				} );
			} );
			$$( 'a[href^="#"]', nav ).forEach( function ( a ) { a.addEventListener( 'click', function () { toggle( false ); } ); } );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Bileşenler (Elementor önizlemesinde tekrar çağrılır)               */
	/* ------------------------------------------------------------------ */

	function initReveal( scope ) {
		var items = $$( '.nk-feature-card, .nk-step, .nk-vehicle, .nk-plan, .nk-service-card, .nk-post-card, .nk-heading', scope );
		if ( ! ( 'IntersectionObserver' in window ) || document.body.classList.contains( 'elementor-editor-active' ) ) { return; }
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					io.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.12 } );
		items.forEach( function ( el, i ) {
			if ( el.dataset.nkReveal ) { return; }
			el.dataset.nkReveal = '1';
			el.classList.add( 'nk-reveal' );
			el.style.transitionDelay = ( i % 4 ) * 80 + 'ms';
			io.observe( el );
		} );
	}

	function initCounters( scope ) {
		var els = $$( '[data-nk-count]', scope );
		if ( ! els.length ) { return; }
		var run = function ( el ) {
			var target = parseFloat( el.getAttribute( 'data-nk-count' ) ) || 0;
			var start = null;
			var duration = 1800;
			var step = function ( ts ) {
				if ( ! start ) { start = ts; }
				var p = Math.min( ( ts - start ) / duration, 1 );
				var eased = 1 - Math.pow( 1 - p, 3 );
				el.textContent = Math.round( target * eased ).toLocaleString( 'tr-TR' );
				if ( p < 1 ) { requestAnimationFrame( step ); }
			};
			requestAnimationFrame( step );
		};
		if ( ! ( 'IntersectionObserver' in window ) ) { els.forEach( run ); return; }
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) { run( entry.target ); io.unobserve( entry.target ); }
			} );
		}, { threshold: 0.4 } );
		els.forEach( function ( el ) { io.observe( el ); } );
	}

	function initSliders( scope ) {
		$$( '[data-nk-slider]', scope ).forEach( function ( slider ) {
			if ( slider.dataset.ready ) { return; }
			slider.dataset.ready = '1';
			var track = $( '.nk-slider__track', slider );
			var slides = $$( '.nk-slider__slide', slider );
			var dotsWrap = $( '[data-dots]', slider );
			var index = 0;
			var timer;

			var perView = function () {
				return parseInt( getComputedStyle( slider ).getPropertyValue( '--per-view' ), 10 ) || 1;
			};
			var maxIndex = function () { return Math.max( 0, slides.length - perView() ); };

			var renderDots = function () {
				dotsWrap.innerHTML = '';
				for ( var i = 0; i <= maxIndex(); i++ ) {
					var b = document.createElement( 'button' );
					b.type = 'button';
					b.setAttribute( 'aria-label', String( i + 1 ) );
					b.addEventListener( 'click', go.bind( null, i ) );
					dotsWrap.appendChild( b );
				}
			};
			var go = function ( i ) {
				var max = maxIndex();
				index = i > max ? 0 : ( i < 0 ? max : i );
				var gap = parseFloat( getComputedStyle( track ).columnGap || getComputedStyle( track ).gap ) || 0;
				var w = slides[ 0 ] ? slides[ 0 ].getBoundingClientRect().width + gap : 0;
				track.style.transform = 'translateX(' + ( -index * w ) + 'px)';
				$$( 'button', dotsWrap ).forEach( function ( d, n ) { d.classList.toggle( 'is-active', n === index ); } );
			};
			var play = function () {
				if ( '1' !== slider.getAttribute( 'data-autoplay' ) ) { return; }
				clearInterval( timer );
				timer = setInterval( function () { go( index + 1 ); }, 5000 );
			};

			$( '[data-prev]', slider ).addEventListener( 'click', function () { go( index - 1 ); play(); } );
			$( '[data-next]', slider ).addEventListener( 'click', function () { go( index + 1 ); play(); } );
			slider.addEventListener( 'mouseenter', function () { clearInterval( timer ); } );
			slider.addEventListener( 'mouseleave', play );

			// Dokunmatik kaydırma.
			var sx = null;
			track.addEventListener( 'touchstart', function ( e ) { sx = e.touches[ 0 ].clientX; }, { passive: true } );
			track.addEventListener( 'touchend', function ( e ) {
				if ( null === sx ) { return; }
				var dx = e.changedTouches[ 0 ].clientX - sx;
				if ( Math.abs( dx ) > 40 ) { go( index + ( dx < 0 ? 1 : -1 ) ); play(); }
				sx = null;
			} );

			window.addEventListener( 'resize', function () { renderDots(); go( Math.min( index, maxIndex() ) ); } );
			renderDots();
			go( 0 );
			play();
		} );
	}

	function initQuoteForms( scope ) {
		$$( '[data-nk-quote]', scope ).forEach( function ( form ) {
			if ( form.dataset.ready ) { return; }
			form.dataset.ready = '1';
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var msg = $( '.nk-form-message', form );
				var btn = $( 'button[type="submit"]', form );
				var ok = true;

				$$( '[required]', form ).forEach( function ( input ) {
					var field = input.closest( '.nk-field' );
					var bad = ! input.value.trim();
					if ( field ) { field.classList.toggle( 'has-error', bad ); }
					if ( bad ) { ok = false; }
				} );
				if ( ! ok ) {
					msg.className = 'nk-form-message is-error';
					msg.textContent = i18n.required;
					return;
				}

				var data = {};
				new FormData( form ).forEach( function ( v, k ) { data[ k ] = v; } );
				var label = btn.innerHTML;
				btn.classList.add( 'is-loading' );
				btn.textContent = i18n.sending;

				post( 'nakliye_quote', data ).then( function ( res ) {
					msg.className = 'nk-form-message ' + ( res.success ? 'is-success' : 'is-error' );
					msg.textContent = res.success && form.dataset.success ? form.dataset.success + ' (' + res.data.tracking + ')' : ( res.data && res.data.message ) || i18n.error;
					if ( res.success ) {
						form.reset();
						form.dispatchEvent( new CustomEvent( 'nakliye:quote-sent', { bubbles: true, detail: res.data } ) );
					}
				} ).catch( function () {
					msg.className = 'nk-form-message is-error';
					msg.textContent = i18n.error;
				} ).finally( function () {
					btn.classList.remove( 'is-loading' );
					btn.innerHTML = label;
				} );
			} );
		} );
	}

	function initCalculators( scope ) {
		$$( '[data-nk-calc]', scope ).forEach( function ( calc ) {
			if ( calc.dataset.ready ) { return; }
			calc.dataset.ready = '1';
			var cfg;
			try { cfg = JSON.parse( calc.getAttribute( 'data-nk-calc' ) ); } catch ( err ) { cfg = D.pricing; }
			var form = $( '.nk-calc__form', calc );
			var totalEl = $( '[data-total]', calc );
			var listEl = $( '[data-breakdown]', calc );
			var wrap = calc.closest( '.nk-calc-wrap' );
			var last = 0;

			var compute = function () {
				var f = form.elements;
				var type = ( $( 'input[name="type"]:checked', form ) || {} ).value || '2+1';
				var intercity = 'intercity' === f.scope.value;
				var base = cfg.base[ type ] || 0;
				var rows = [ [ type + ' taşıma', base ] ];
				var sub = base;

				$$( '[data-show-if]', form ).forEach( function ( el ) { el.hidden = el.getAttribute( 'data-show-if' ) !== f.scope.value; } );

				if ( intercity ) {
					var km = Math.max( 0, parseFloat( f.km.value ) || 0 );
					rows.push( [ km + ' km yol', km * cfg.perKm ] );
					sub += km * cfg.perKm;
				}
				if ( ! f.elevator.checked && ! f.lift.checked ) {
					var floors = Math.max( 0, parseInt( f.floor_from.value, 10 ) || 0 ) + Math.max( 0, parseInt( f.floor_to.value, 10 ) || 0 );
					if ( floors ) {
						rows.push( [ floors + ' kat merdiven', floors * cfg.perFloor ] );
						sub += floors * cfg.perFloor;
					}
				}
				if ( f.lift.checked ) {
					rows.push( [ 'Dış cephe asansörü', cfg.lift ] );
					sub += cfg.lift;
				}
				var total = sub;
				if ( f.packing.checked ) {
					rows.push( [ 'Paketleme (%' + cfg.packing + ')', sub * cfg.packing / 100 ] );
					total += sub * cfg.packing / 100;
				}
				if ( f.insurance.checked ) {
					rows.push( [ 'Sigorta (%' + cfg.insurance + ')', sub * cfg.insurance / 100 ] );
					total += sub * cfg.insurance / 100;
				}

				totalEl.textContent = money( total );
				if ( total !== last ) {
					totalEl.classList.add( 'is-bump' );
					setTimeout( function () { totalEl.classList.remove( 'is-bump' ); }, 200 );
					last = total;
				}
				listEl.innerHTML = rows.map( function ( r ) {
					return '<li><span>' + escapeHtml( r[ 0 ] ) + '</span><strong>' + money( r[ 1 ] ) + '</strong></li>';
				} ).join( '' );

				calc.dataset.summary = type + ', ' + ( intercity ? 'şehirler arası' : 'şehir içi' ) + ' — ' + money( total );
			};

			form.addEventListener( 'input', compute );
			form.addEventListener( 'change', compute );
			compute();

			var btn = $( '[data-calc-quote]', calc );
			var quote = wrap ? $( '.nk-calc__quote', wrap ) : null;
			if ( btn && quote ) {
				btn.addEventListener( 'click', function () {
					quote.hidden = false;
					var est = $( 'input[name="estimate"]', quote );
					if ( est ) { est.value = calc.dataset.summary; }
					quote.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					var first = $( 'input[name="name"]', quote );
					if ( first ) { setTimeout( function () { first.focus(); }, 500 ); }
				} );
			}
		} );
	}

	function initTracking( scope ) {
		$$( '[data-nk-tracking]', scope ).forEach( function ( box ) {
			if ( box.dataset.ready ) { return; }
			box.dataset.ready = '1';
			var form = $( 'form', box );
			var out = $( '.nk-tracking__result', box );
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var btn = $( 'button', form );
				btn.classList.add( 'is-loading' );
				post( 'nakliye_track', { code: form.elements.code.value, last4: form.elements.last4.value } ).then( function ( res ) {
					if ( ! res.success ) {
						out.innerHTML = '<div class="nk-form-message is-error">' + escapeHtml( ( res.data && res.data.message ) || i18n.notFound ) + '</div>';
						return;
					}
					var d = res.data;
					var flow = [ 'yeni', 'onay', 'paket', 'yolda', 'teslim' ];
					var names = { yeni: 'Talep', onay: 'Onay', paket: 'Paketleme', yolda: 'Yolda', teslim: 'Teslim' };
					var pos = flow.indexOf( d.key );
					if ( pos < 0 ) { pos = 'iptal' === d.key ? -1 : ( [ 'ekspertiz', 'teklif' ].indexOf( d.key ) > -1 ? 0 : 0 ); }
					var pct = pos <= 0 ? 0 : ( pos / ( flow.length - 1 ) ) * 100;

					var html = '<div class="nk-track-card">';
					html += '<div class="nk-track-card__head"><div><small>Takip No</small><br><strong>' + escapeHtml( d.code ) + '</strong></div>';
					html += '<div><small>Güzergâh</small><br><strong>' + escapeHtml( d.route ) + '</strong></div>';
					html += '<div><span class="nk-track-status">' + escapeHtml( d.status ) + '</span></div></div>';
					html += '<div class="nk-track-progress"><span class="nk-track-progress__bar" style="width:0"></span>';
					flow.forEach( function ( k, i ) {
						html += '<div class="nk-track-progress__step' + ( i <= pos ? ' is-done' : '' ) + '"><span>' + ( i <= pos ? '✓' : i + 1 ) + '</span><small>' + names[ k ] + '</small></div>';
					} );
					html += '</div><ol class="nk-timeline">';
					d.steps.slice().reverse().forEach( function ( s ) {
						html += '<li><strong>' + escapeHtml( s.label ) + '</strong><small>' + escapeHtml( s.time ) + '</small>' + ( s.note ? '<div>' + escapeHtml( s.note ) + '</div>' : '' ) + '</li>';
					} );
					html += '</ol></div>';
					out.innerHTML = html;
					requestAnimationFrame( function () {
						var bar = $( '.nk-track-progress__bar', out );
						if ( bar ) { bar.style.width = pct + '%'; }
					} );
				} ).catch( function () {
					out.innerHTML = '<div class="nk-form-message is-error">' + escapeHtml( i18n.error ) + '</div>';
				} ).finally( function () { btn.classList.remove( 'is-loading' ); } );
			} );
		} );
	}

	function initScope( scope ) {
		scope = scope || document;
		initReveal( scope );
		initCounters( scope );
		initSliders( scope );
		initQuoteForms( scope );
		initCalculators( scope );
		initTracking( scope );
	}

	window.NakliyeInit = initScope;

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { initSite(); initScope( document ); } );
	} else {
		initSite();
		initScope( document );
	}

	// Elementor editör önizlemesi: bileşen her yeniden çizildiğinde başlat.
	var hookElementor = function () {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) { return; }
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
			var el = $scope && $scope[ 0 ] ? $scope[ 0 ] : null;
			if ( el && el.querySelector( '[class*="nk-"]' ) ) { initScope( el ); }
		} );
	};
	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		hookElementor();
	} else if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}
}() );
