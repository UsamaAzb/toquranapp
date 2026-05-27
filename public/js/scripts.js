(function($) {


    "use strict";


    /* ---------------------------------------------------------------------------
	 * Global vars
	 * --------------------------------------------------------------------------- */

    var scrollticker;	// Scroll Timer | don't need to set this in every scroll

    var rtl 			= $('body').hasClass('rtl');
    var simple			= $('body').hasClass('style-simple');

    var top_bar_top 	= '61px';
    var header_H 		= 0;

    var pretty 			= false;
	 var mobile_init_W 	=  1240;

"use strict";
jQuery(window).bind("debouncedresize", function() {
	iframesHeight();
	jQuery('.masonry.isotope').isotope();
	jQuery('.masonry.gallery').isotope('layout');
});

function iframeHeight(item, ratio) {
	var itemW = item.width();
	var itemH = itemW * ratio;
	if (itemH < 147) itemH = 147;
	item.height(itemH);
}

function iframesHeight() {
	iframeHeight(jQuery(".blog_wrapper .post-photo-wrapper .mfn-jplayer, .blog_wrapper .post-photo-wrapper iframe, .post-related .mfn-jplayer, .post-related iframe, .blog_slider_ul .mfn-jplayer, .blog_slider_ul iframe"), 0.78); // blog - list
	iframeHeight(jQuery(".single-post .single-photo-wrapper .mfn-jplayer, .single-post .single-photo-wrapper iframe"), 0.4); // blog - single
	iframeHeight(jQuery(".section-portfolio-header .mfn-jplayer, .section-portfolio-header iframe"), 0.4); // portfolio - single
}
iframesHeight();
var topBarTop = '61px';
var mfn_header_height = 0;
function mfn_stickyH() {
	if (jQuery('body').hasClass('header-below')) {
		mfn_header_height = jQuery('.mfn-main-slider').innerHeight() + jQuery('#Header').innerHeight();
	} else {
		mfn_header_height = jQuery('#Top_bar').innerHeight() + jQuery('#Action_bar').innerHeight();
	}
}
function mfn_sticky() {
// 	if (jQuery('body').hasClass('sticky-header')) {
// 		var start_y = mfn_header_height;
// 		var window_y = jQuery(window).scrollTop();
// 		if (window_y > start_y) {
// 			if (!(jQuery('#Top_bar').hasClass('is-sticky'))) {
// 				jQuery('.header-classic .header_placeholder').css('height', jQuery('#Top_bar').innerHeight() - jQuery('#Action_bar').innerHeight());
// 				jQuery('.header-stack   .header_placeholder').css('height', 71);
// 				jQuery('.header-below   .header_placeholder').css('height', jQuery('#Top_bar').innerHeight());
// 				jQuery('.minimalist-header .header_placeholder').css('height', jQuery('#Top_bar').innerHeight());
// 				jQuery('#Top_bar').addClass('is-sticky').css('top', -60).animate({
// 					'top': jQuery('#wpadminbar').innerHeight()
// 				}, 300);
// 			}
// 		} else {
// 			if (jQuery('#Top_bar').hasClass('is-sticky')) {
// 				jQuery('.header_placeholder').css('height', 0);
// 				jQuery('#Top_bar').removeClass('is-sticky').css('top', topBarTop);
// 			}
// 		}
// 	}
	
	
	

	
}
function mfn_equalH_wrap() {
	jQuery('.section.equal-height-wrap .section_wrapper').each(function() {
		var maxH = 0;
		jQuery('> .wrap', jQuery(this)).each(function() {
			jQuery(this).css('height', 'auto');
			if (jQuery(this).innerHeight() > maxH) {
				maxH = jQuery(this).innerHeight();
			}
		});
		jQuery('> .wrap', jQuery(this)).css('height', maxH + 'px');
	});
}

function mfn_footer() {
	if (jQuery('.footer-fixed #Footer, .footer-sliding #Footer').length) {
		var footerH = jQuery('#Footer').height();
		jQuery('#Content').css('margin-bottom', footerH + 'px');
	}
}

// function mfn_header() {
//     console.log('asdasd');
// 	var rightW = jQuery('.top_bar_right').innerWidth();
// 	var parentW = jQuery('#Top_bar .one').innerWidth() - 10;
// 	var leftW = parentW - rightW;
	
// 	var w_window=$(window).width();
// 	var new_width=w_window(-w_window*.10);
// // 	jQuery('.top_bar_left, .menu > li > ul.mfn-megamenu ').width(leftW);
// 	jQuery('.top_bar_left, .menu > li > ul.mfn-megamenu ').width(new_width);

// }
function mfn_sectionH() {
	var windowH = jQuery(window).height();
		var offset = 0;
		if( $( '.section.full-screen:not(.hide-desktop)' ).length > 1 ){
			offset = 5;
		}

		$( '.section.full-screen' ).each( function(){

			var section = $( this );
			var wrapper = $( '.section_wrapper', section );

			section
				.css( 'padding', 0 )
				.css( 'min-height', windowH + offset );

			var padding = ( windowH + offset - wrapper.height() ) / 2;

			if( padding < 50 ) padding = 50;

			wrapper
				.css( 'padding-top', padding + 10 )			// 20 = column margin-bottom / 2
				.css( 'padding-bottom', padding - 10 );
		});
}
function hashNav() {
	// # window.location.hash
	var hash = window.location.hash;
	if (hash && jQuery(hash).length) {
		var stickyH = jQuery('.sticky-header #Top_bar').innerHeight();
		var tabsHeaderH = jQuery(hash).siblings('.ui-tabs-nav').innerHeight();
		jQuery('html, body').animate({
			scrollTop: jQuery(hash).offset().top - stickyH - tabsHeaderH
		}, 500);
	}
}

	jQuery(window).load(function(){
		mfn_equalH_wrap();
	});

jQuery(document).ready(function() {
	topBarTop = parseInt(jQuery('#Top_bar').css('top'), 10);
	if (topBarTop < 0) topBarTop = 61;
	topBarTop = topBarTop + 'px';


		function retinaLogo(){
			if( window.devicePixelRatio > 1 ){

				var el 		= '';
				var src 	= '';
				var height 	= '';

				var parent 	= $( '#Top_bar #logo' );
				var parentH	= parent.data( 'height' );

				var maxH	= {
					sticky : {
						init 			: 35,
						no_padding		: 60,
						overflow 		: 110
					},
					mobile : {
						mini 			: 50,
						mini_no_padding	: 60
					},
					mobile_sticky : {
						init 			: 50,
						no_padding		: 60,
						overflow 		: 80
					}
				};

				$( '#Top_bar #logo img' ).each( function( index ){

					el 		= $( this );
					src 	= el.data( 'retina' );
					height 	= el.height();



					if( el.hasClass( 'logo-main' ) ){

						if( $( 'body' ).hasClass( 'logo-overflow' ) ){

							// do nothing

						} else if( height > parentH ){

							height = parentH;

						}

					}

					if( el.hasClass( 'logo-sticky' ) ){

						if( $( 'body' ).hasClass( 'logo-overflow' ) ){

							if( height > maxH.sticky.overflow ){
								height = maxH.sticky.overflow;
							}

						} else if( $( 'body' ).hasClass( 'logo-no-sticky-padding' ) ){

							if( height > maxH.sticky.no_padding ){
								height = maxH.sticky.no_padding;
							}

						} else if( height > maxH.sticky.init ){

							height = maxH.sticky.init;

						}

					}


					if( el.hasClass( 'logo-mobile' ) ){

						if( $( 'body' ).hasClass( 'mobile-header-mini' ) ){

							if( parent.data( 'padding' ) > 0 ){

								if( height > maxH.mobile.mini ){
									height = maxH.mobile.mini;
								}

							} else {

								if( height > maxH.mobile.mini_no_padding ){
									height = maxH.mobile.mini_no_padding;
								}

							}

						}

					}


					if( el.hasClass( 'logo-mobile-sticky' ) ){

						if( $( 'body' ).hasClass( 'logo-no-sticky-padding' ) ){

							if( height > maxH.mobile_sticky.no_padding ){
								height = maxH.mobile_sticky.no_padding;
							}

						} else if( height > maxH.mobile_sticky.init ){
							height = maxH.mobile_sticky.init;
						}

					}



					if( src ){
						el.parent().addClass( 'retina' );
						el.attr( 'src', src ).css( 'max-height', height + 'px' );
					}

				});

			}
		}
		retinaLogo();


	jQuery('.responsive-menu-toggle').click(function(e) {
		e.preventDefault();
		var el = jQuery(this)
		var menu = jQuery('#Top_bar #menu');
		var menuWrap = menu.closest('.menu_wrapper');
		el.toggleClass('active');
		if (el.hasClass('is-sticky') && el.hasClass('active')) {
			var top = 0;
			if (menuWrap.length) top = menuWrap.offset().top;
			jQuery('body,html').animate({
				scrollTop: top
			}, 200);
		}
		menu.stop(true, true).slideToggle(200);
	});


		function sideSlide(){

			var slide 				= $( '#Side_slide' );
			var overlay 			= $( '#body_overlay' );
			var ss_mobile_init_W 	= mobile_init_W;
			var pos 				= 'right';



			var constructor = function(){
				if( ! slide.hasClass( 'enabled' ) ){
					$( 'nav#menu' ).detach().appendTo( '#Side_slide .menu_wrapper' );
					slide.addClass( 'enabled' );
				}
			};


			var destructor = function(){
				if( slide.hasClass( 'enabled' ) ){
					close();
					$( 'nav#menu' ).detach().prependTo( '#Top_bar .menu_wrapper' );
					slide.removeClass( 'enabled' );
				}
			};

			var reload = function(){

				if( ( $(document).width() < ss_mobile_init_W ) ){
					constructor();
				} else {
					destructor();
				}
			};


			var init = function(){
				if( slide.hasClass( 'left' ) ){
					pos = 'left';
				}

				if( $( 'body' ).hasClass( 'header-simple' ) ){
					ss_mobile_init_W = 9999;
				}

				reload();
			};


			var reset = function( time ){

				$( '.lang-active.active', slide ).removeClass('active').children('i').attr('class','icon-down-open-mini');
				$( '.lang-wrapper', slide ).fadeOut(0);

				$( '.icon.search.active', slide ).removeClass('active');
				$( '.search-wrapper', slide ).fadeOut(0);

				$( '.menu_wrapper, .social', slide ).fadeIn( time );

			};

			var button = function(){

				// show
				if( pos == 'left' ){
					slide.animate({ 'left':0 },300);
					$('body').animate({ 'right':-125 },300);
				} else {
					slide.animate({ 'right':0 },300);
					$('body').animate({ 'left':-125 },300);
				}

				overlay.fadeIn(300);

				reset(0);

			};


			var close = function(){

				if( pos == 'left' ){
					slide.animate({ 'left':-250 },300);
					$('body').animate({ 'right':0 },300);
				} else {
					slide.animate({ 'right':-250 },300);
					$('body').animate({ 'left':0 },300);
				}

				overlay.fadeOut(300);
			};

			init();


			$( '.responsive-menu-toggle' ).off( 'click' );

			$( '.responsive-menu-toggle' ).on( 'click', function(e){
				e.preventDefault();
				button();
			});

			overlay.on( 'click', function(e){
				close();
			});

			$( '.close', slide ).on( 'click', function(e){
				e.preventDefault();
				close();
			});
			$( slide ).on( 'click', function(e){
				if( $( e.target ).is( slide ) ){
					reset(300);
				}
			});

			$( window ).on( 'debouncedresize', reload );


		}

		if( $( 'body' ).hasClass( 'mobile-side-slide' ) ){
			sideSlide();
		}





		function mainMenu(){

			var mm_mobile_init_W = mobile_init_W;

			if( $( 'body' ).hasClass( 'header-simple' ) || $( '#Header_creative.dropdown' ).length ){
				mm_mobile_init_W = 9999;
			}

			$( '#menu > ul.menu' ).mfnMenu({
				addLast		: true,
				arrows		: true,
				mobileInit	: mm_mobile_init_W,
			});

			$( '#secondary-menu > ul.secondary-menu' ).mfnMenu({
				mobileInit	: mm_mobile_init_W,
			});

		}

		mainMenu();

	mfn_stickyH()
	mfn_sticky();

	jQuery(".sliding-top-control").click(function(e) {
		e.preventDefault();
		jQuery('#Sliding-top .widgets_wrapper').slideToggle();
		jQuery('#Sliding-top').toggleClass('active');
	});

	jQuery('a.button_js').each(function() {
		var btn = jQuery(this);
		if (btn.find('.button_icon').length && btn.find('.button_label').length) {
			btn.addClass('kill_the_icon');
		}
	});

	jQuery('.fixed-nav').appendTo('body');

	jQuery('.feature_list ul li:nth-child(4n):not(:last-child)').after('<hr>');

	function checkIE() {
		// IE 9
		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");
		if (msie > 0 && parseInt(ua.substring(msie + 5, ua.indexOf(".", msie))) == 9) {
			jQuery("body").addClass("ie");
		}
	}
	checkIE();

// 	var ua = navigator.userAgent,
// 		isMobileWebkit = /WebKit/.test(ua) && /Mobile/.test(ua);
// 	if (!isMobileWebkit && jQuery(window).width() >= 768) {
// 		$.stellar({
// 			horizontalScrolling: false,
// 			responsive: true
// 		});
// 	}

	jQuery('li.scroll > a, a.scroll').click(function() {
		var url = jQuery(this).attr('href');
		var hash = '#' + url.split('#')[1];
		var stickyH = jQuery('.sticky-header #Top_bar').innerHeight();
		var tabsHeaderH = jQuery(hash).siblings('.ui-tabs-nav').innerHeight();
		if (hash && jQuery(hash).length) {
			jQuery('html, body').animate({
				scrollTop: jQuery(hash).offset().top - stickyH - tabsHeaderH
			}, 500);
		}
	});

	jQuery('#back_to_top').click(function() {
		jQuery('body,html').animate({
			scrollTop: 0
		}, 500);
		return false;
	});

	jQuery('.section .section-nav').click(function() {
		var el = jQuery(this);
		var section = el.closest('.section');
		if (el.hasClass('prev')) {
			// Previous Section -------------
			if (section.prev().length) {
				jQuery('html, body').animate({
					scrollTop: section.prev().offset().top
				}, 500);
			}
		} else {
			// Next Section -----------------
			if (section.next().length) {
				jQuery('html, body').animate({
					scrollTop: section.next().offset().top
				}, 500);
			}
		}
	});
	function iframeHeight(item, ratio) {
		var itemW = item.width();
		var itemH = itemW * ratio;
		if (itemH < 147) itemH = 147;
		item.height(itemH);
	}

	function iframesHeight() {
		iframeHeight(jQuery(".blog_wrapper .post-photo-wrapper .mfn-jplayer, .blog_wrapper .post-photo-wrapper iframe, .post-related .mfn-jplayer, .post-related iframe, .blog_slider_ul .mfn-jplayer, .blog_slider_ul iframe"), 0.78); // blog - list
		iframeHeight(jQuery(".single-post .single-photo-wrapper .mfn-jplayer, .single-post .single-photo-wrapper iframe"), 0.4); // blog - single
		iframeHeight(jQuery(".section-portfolio-header .mfn-jplayer, .section-portfolio-header iframe"), 0.4); // portfolio - single
	}
	iframesHeight();
	jQuery(window).bind("debouncedresize", function() {
		iframesHeight();
		jQuery('.masonry.isotope,.isotope').isotope();
		mfn_footer();
// 		mfn_header();
		mfn_sectionH();
	});

	function isotopeFilter(domEl, isoWrapper) {
		var filter = domEl.attr('data-rel');
		isoWrapper.isotope({
			filter: filter
		});
	}
	jQuery('.isotope-filters .filters_wrapper').find('li:not(.close) a').click(function(e) {
		e.preventDefault();
		var filters = jQuery(this).closest('.isotope-filters');
		var parent = filters.attr('data-parent');
		if (parent) {
			parent = filters.closest('.' + parent);
			var isoWrapper = parent.find('.isotope').first()
		} else {
			var isoWrapper = jQuery('.isotope');
		}
		filters.find('li').removeClass('current-cat');
		jQuery(this).closest('li').addClass('current-cat');
		isotopeFilter(jQuery(this), isoWrapper);
	});
	jQuery('.isotope-filters .filters_buttons').find('li.reset a').click(function(e) {
		e.preventDefault();
		jQuery('.isotope-filters .filters_wrapper').find('li').removeClass('current-cat');
		isotopeFilter(jQuery(this), jQuery('.isotope'));
	});
	mfn_footer();
// 	mfn_header();
	mfn_sectionH();
	hashNav();
});

jQuery(window).scroll(function() {
	mfn_stickyH();
	mfn_sticky();
});
jQuery(window).load(function() {

	mfn_footer();
// 	mfn_header();
	mfn_sectionH();
	hashNav();
});

window.mfn_sliders = {
	blog: 0,
	clients: 0,
	offer: 10000,
	portfolio: 0,
	shop: 0,
	slider: 6000,
	testimonials: 7000
};
jQuery(document).ready(function($) {
	jQuery('.masonry.isotope,.isotope').isotope();
});
})(jQuery);



jQuery(window).load(function(){
	jQuery('.isotope').isotope('layout');
// 	jQuery('.before_after.twentytwenty-container').twentytwenty();

});
