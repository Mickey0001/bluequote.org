;(function($) {
    "use strict";


	/*----------------------------------------------------*/
	/*  Preloader
	/*----------------------------------------------------*/
    if( $('.preloader').length ){
    	$('.preloader' ).delay( 300 ).addClass('preloader-shrink');
    }

	/*----------------------------------------------------*/
	/*  Bootstrap Multilevel Dropdown
	/*----------------------------------------------------*/
    $('.auth-social-nav .dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open')
    });

    $('.off-canvas .dropdown > a.dropdown-toggle').on('click', function(event) {
        if ( $(window).width() <= 767 ){
          event.preventDefault();
          event.stopPropagation();
        }
        $(this).parent().siblings().removeClass('open');
        $(this).parent().toggleClass('open')
    });

	/*----------------------------------------------------*/
	/*  Top Search
	/*----------------------------------------------------*/
	$('.search-top').on('click','a',function(e){
		e.preventDefault();
		$('.top-search-form').slideToggle();

		$(this).find('i').toggleClass('fa-times');
	});

	/*----------------------------------------------------*/
	/*  Breaking News
	/*----------------------------------------------------*/
	if( $('.breaking-news').length ){
		$('.bnews-ticker').each(function(){
			$('.bnews-ticker').owlCarousel({
				loop:true,
				margin:10,
				items: 1,
				autoplay: 1,
				animateIn: 'fadeIn',
				animateOut: 'fadeOut'
			})
		})
	}

	/*----------------------------------------------------*/
	/*  Product Carousel
	/*----------------------------------------------------*/
	if( $('.product-images-carousel').length ){
		$('.product-images-carousel').each(function(){
			$('.product-images-carousel').owlCarousel({
				loop: true,
				margin: 0,
				items: 1,
				autoplay: 1,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				animateIn: 'fadeIn',
				animateOut: 'fadeOut'
			})
		})
	}

	/*----------------------------------------------------*/
	/*  Editor Choice Carousel
	/*----------------------------------------------------*/
	if ( $('.editor-choice-post-carousel').length ){
		$('.editor-choice-post-carousel').each(function(){
			$('.editor-choice-post-carousel').owlCarousel({
				margin: 0,
				loop: 0,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				navContainer: '.editor-choice-nav',
				autoplayHoverPause: true,
				responsive: {
					0:{
						items:1
					},
					768:{
						items:2
					},
					1100:{
						items:3
					},
					1600:{
						items:4
					}
				}
			})
		})
	}

	/*----------------------------------------------------*/
	/*  Sticky Posts Carousel
	/*----------------------------------------------------*/
	if ( $('.sticky-posts-carousel').length ){
		$('.sticky-posts-carousel').each(function(){
			$('.sticky-posts-carousel').owlCarousel({
				margin: 0,
				loop: 1,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				autoplayHoverPause: true,
				center: true,
				responsive: {
					0:{
						items:1
					},
					600:{
						items:2
					},
					900:{
						items:3
					},
					1100:{
						items:4
					},
					1600:{
						items:5
					}
				}
			})
		})
	}

	/*----------------------------------------------------*/
	/*  Highlights Posts Carousel
	/*----------------------------------------------------*/
	if ( $('.box-posts-carousel').length ){
		$('.box-posts-carousel').each(function(){
			$('.box-posts-carousel').owlCarousel({
				margin: 0,
				loop: 1,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				autoplayHoverPause: true,
				center: true,
				responsive: {
					0:{
						items:1
					},
					768:{
						items:2
					},
					992:{
						items:3
					}
				}
			})
		})
	}

	/*----------------------------------------------------*/
	/*  Off Canvas
	/*----------------------------------------------------*/
	if($('.off-canvas').length){

		$('.off-canvas-trigger').on('click', function(){
			$('.off-canvas,.off-close.outer').addClass('open')
		});

		$('.off-close').on('click', function(){
			$('.off-canvas,.off-close.outer').removeClass('open')
		})
	}

	/*----------------------------------------------------*/
	/*  Go To
	/*----------------------------------------------------*/
	if( $('.toTop').length ){
		$('.toTop').on('click', function(event) {

			var target = $( $(this).attr('href') );

			if( target.length ) {
				event.preventDefault();
				$('html, body').animate({
					scrollTop: target.offset().top
				}, 1000)
			}

		});
	}

	/*----------------------------------------------------*/
	/*  Body Background Image
	/*----------------------------------------------------*/
	if( $('[data-bodyimg]').length ){

		var $bodyBg = $('[data-bodyimg]').data('bodyimg');

		$('[data-bodyimg]').css(
			"background-image", "url("+ $bodyBg +")"
		)

	}

	/*----------------------------------------------------*/
	/*  Post By Category
	/*----------------------------------------------------*/
	if( $('.post-by-cats .pbc-carousel').length ){

		$('.post-by-cats .pbc-carousel').each(function(){
			var divs = $(this).find('.col-sm-6');
			for(var i = 0; i < divs.length; i+=6) {
				divs.slice( i, i+6 ).wrapAll('<div class="item"><div class="row"></div></div>');
			}
		});

		$('.post-by-cats.style2 .pbc-carousel, .post-by-cats.multiple-cats .pbc-carousel').each(function(){
			var divs = $(this).find('.this-cat-post');
			for(var i = 0; i < divs.length; i+=3) {
				divs.slice( i, i+3 ).wrapAll('<div class="item"></div>');
			}
		});

		$('.post-by-cats .pbc-carousel').each(function(){
			$('.post-by-cats .pbc-carousel').owlCarousel({
				items: 1,
				margin: 0,
				loop: false,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				dots: false
			});
		});

	}
	if( $('.post-by-cats .pbc-carousel2').length ){

		$('.post-by-cats .pbc-carousel2').each(function(){
			var divs = $(this).find('.this-cat-post');
			for(var i = 0; i < divs.length; i+=3) {
				divs.slice( i, i+3 ).wrapAll('<div class="item"></div>');
			}
		});

		$('.post-by-cats .pbc-carousel2').each(function(){
			$('.post-by-cats .pbc-carousel2').owlCarousel({
				margin: 0,
				loop: false,
				nav: true,
				navText: ['<i class="fa fa-angle-left"></i>','<i class="fa fa-angle-right"></i>'],
				dots: false,
				responsive: {
					0: {
						items: 1
					},
					768: {
						items: 2,
						margin: 30
					}
				}
			});
		});

	}

	/*----------------------------------------------------*/
	/*  Featured Posts Lists
	/*----------------------------------------------------*/
	if( $('.main-navigation').length ){

		$(window).on('scroll', function(){
			var $topG = $('.main-navigation').offset().top;

			if ( $(window).scrollTop() > $topG + 70 ) $('.main-navigation').addClass('affix-coming');
			else $('.main-navigation').removeClass('affix-coming');

			$('.main-navigation').affix({
				offset: {
					top: $topG + 150
				}
			})
		})

	}

	/*----------------------------------------------------*/
	/*  Isotope Gallery
	/*----------------------------------------------------*/

	if ( $('.layout-masonry').length ){
		$( document ).ready(function() {
			$(".layout-masonry").imagesLoaded(function(){
				$(".layout-masonry").isotope({
					itemSelector: ".hentry",
					layoutMode: 'masonry',
					masonry: {
						columnWidth: '.grid-sizer'
					}
				})
			});
		});
	}

	$(document).ready(setIsotopeLayout);
	$(window).on('resize', setIsotopeLayout);

	function setIsotopeLayout() {
		if ( $('.isotope-gallery').length ){
			$(".isotope-gallery").imagesLoaded(function(){
				$(".isotope-gallery").isotope({
					itemSelector: ".featured-item",
					layoutMode: 'masonry',
					transitionDuration: 0,
					masonry: {
						columnWidth: '.grid-sizer'
					}
				})
			});
		}
	}


	/*----------------------------------------------------*/
	/*  Product Count Spinner
	/*----------------------------------------------------*/
	if ( $('.number-spinner').length ){

		var action, btn, input;
		$(".number-spinner button").mousedown(function () {
			btn = $(this);
			input = btn.closest('.number-spinner').find('input');
			btn.closest('.number-spinner').find('button').prop("disabled", false);

			if (btn.attr('data-dir') == 'up') {
				action = setInterval(function(){
					if ( input.attr('max') == undefined || parseInt(input.val()) < parseInt(input.attr('max')) ) {
						input.val(parseInt(input.val())+1)
					}else{
						btn.prop("disabled", true);
						clearInterval(action)
					}
				}, 50)
			} else {
				action = setInterval(function(){
					if ( input.attr('min') == undefined || parseInt(input.val()) > parseInt(input.attr('min')) ) {
						input.val(parseInt(input.val())-1)
					}else{
						btn.prop("disabled", true);
						clearInterval(action)
					}
				}, 50)
			}
		}).mouseup(function(){
			clearInterval(action)
		})
	}

	/*----------------------------------------------------*/
	/*  Fitvids
	/*----------------------------------------------------*/
    $("body").fitVids();


})(jQuery)
