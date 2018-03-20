/* Author: Simon */
var animating = false;
var player = null;

var carouselItems = 0;

var releasePages = 0;

$(document).ready(function(){

/*	if($('.li-shop > a').is(':visible')){
		
		$('.li-shop').mouseenter(function(e){
		
			$('#shop_menu').fadeIn(200);
		});
		$('.li-shop').mouseleave(function(e){
			$('#shop_menu').fadeOut(200);
		});
	}*/

	//$('.artist-releases').jScrollPane();
	if ($('.artist-releases').length){
		// Sort out the release paging.
		$i = 0;
		$('.artist-releases').each(function(){
			if ($i !== 0){
				$(this).hide();
			}
			else{
				$(this).addClass('current');
			}
			$(this).attr('data-page', $i+1);

			$i++;
		});

		$('.right-col-releases').append('<div class="release-navigation" data-pages="'+$i+'"></div>');
		if ($i > 1){
			releasePages = $i;

			$('.right-col-releases').find('.release-navigation').append('<a href="#" class="a-release-left ir disabled" data-page="">Left</a>');
			$('.right-col-releases').find('.release-navigation').append('<a href="#" class="a-release-right ir" data-page="2">Right</a>');

			$('.a-release-left').click(function(event){
				event.preventDefault();

				if (!animating && !$(this).hasClass('disabled')){
					console.log('blah1');
					animating = true;
					var page = parseInt($(this).attr('data-page'), 10);

					$('.artist-releases.current').fadeOut(function(){
						$('.a-release-right').removeClass('disabled').show().attr('data-page', page+1);
						if (page == 1){
							$('.a-release-left').addClass('disabled').hide();
						}
						else{
							$('.a-release-left').attr('data-page', page-1);
						}

						$('.artist-releases[data-page="'+page+'"]').fadeIn(function(){
							animating = false;
						}).addClass('current');
					}).removeClass('current');
				}
			}).hide();

			$('.a-release-right').click(function(event){
				event.preventDefault();

				if (!animating && !$(this).hasClass('disabled')){
					console.log('blah2');
					animating = true;
					var page = parseInt($(this).attr('data-page'), 10);

					$('.artist-releases.current').fadeOut(function(){
						$('.a-release-left').removeClass('disabled').show().attr('data-page', page-1);
						if (page == releasePages){
							$('.a-release-right').addClass('disabled').hide();
						}
						else{
							$('.a-release-right').attr('data-page', page+1);
						}

						$('.artist-releases[data-page="'+page+'"]').fadeIn(function(){
							animating = false;
						}).addClass('current');
					}).removeClass('current');
				}
			});
		}

		$('.release-height').css({
			minHeight: ($('.right-col-releases').height()+90)+'px'
		});

	}
	$('.release-links .a-release-link').live('click', function(event){
		console.log('here');
		$(this).closest('.release-links').addClass('show');
	});

	if (typeof EMIBuyButton != 'undefined'){
		$('body').bind('click', function(event) { EMIBuyButton.hideAllMenus(event); $('.release-links.show').removeClass('show'); });
	}

	/*
	Needs to be tested

	$('body').click(function(event){
		if ($(window).width() < 800 && $('.menu').position().left === 0){
			$('.menu').css('left', '-280px');
		}
	}); */

	$('.menu').click(function(event){
		event.stopPropagation();
	});

	// Artist carousel.
	$('.a-carousel-left').click(function(event){
		event.preventDefault();

		if (!animating){
			animating = true;
			$('.artist-carousel-container').animate({
				scrollLeft: $('.artist-carousel-container').scrollLeft() - $('.central-col').width()
			}, 400, function(){
				animating = false;
			});
		}
	});

	$('.a-carousel-right').click(function(event){
		event.preventDefault();

		if (!animating){
			animating = true;
			$('.artist-carousel-container').animate({
				scrollLeft: $('.artist-carousel-container').scrollLeft() + $('.central-col').width()
			}, 400, function(){
				animating = false;
			});
		}
	});

	$('.menu-link, .h2-menu-close').click(function(event){
		event.preventDefault();

		if ($('.menu').position().left === 0){
			$('.menu').css('left', '-280px');
		}
		else{
			$('.menu').css('left', 0);
		}
	});

	$(".menu").hammer({
		swipe_time: 500,
		drag: false,
		tap: false,
		transform: false
	}).bind("swipe", function(ev) {
		if (ev.direction == "left" && $(window).width() < 800) {
			$('.menu').css('left', '-280px');
		}
	});

	$('.a-shop-carousel-right').click(function(event){
		event.preventDefault();

		if (!animating){
			animating = true;
			$('.shop-carousel-container').animate({
				scrollLeft: '123px'
			}, 200, function(){
				$('.shop-carousel-inner a:first-child').appendTo($('.shop-carousel-inner'));
				$('.shop-carousel-container').scrollLeft(0);
				animating = false;
			});
		}
	});

	if ($(window).width() < 800){
		$('.menu').height($(window).height()+100);
	}
	$(window).resize(function(){
		$('.artist-carousel-container').scrollLeft(0);
		if ($(window).width() < 800){
			$('.menu').height($(window).height());
		}
		else{
			$('.menu').height('auto');
		}
	});

	// Subscribe form
	$('.form-subscribe').submit(function(event){
		var error = false;
		var dob = new Date(parseInt($('select[name="year"]').val(), 10), parseInt($('select[name="month"]').val(), 10), parseInt($('select[name="day"]').val(), 10));
		var thriteenYears = new Date();
		thriteenYears.setTime(thriteenYears.valueOf() - 13 * 365 * 24 * 60 * 60 * 1000);
		if ($('input[name="email"]').val() === '' || !isEmail($('input[name="email"]').val())){
			error = true;
			$('input[name="email"]').focus();
			showFormError('You must enter a valid email.');
		}
		else if ($('input[name="fname"]').val() === ''){
			error = true;
			$('input[name="fname"]').focus();
			showFormError('You must enter your name.');
		}
		else if ($('select[name="country"]').val() === ''){
			error = true;
			showFormError('You must select your country.');
		}
		else if (isNaN(dob.getTime())){
			error = true;
			showFormError('You must enter a valid birthday.');
		}
		else if (dob > thriteenYears){
			error = true;
			showFormError('You must be older than 13 years old to subscribe.');
		}

		if (error){
			event.preventDefault();
		}
	});

	// Full width carousel.
	if ($('.full-width-carousel').length){
		var i = 0;
		var vids = 0;
		carouselItems = $('.full-width-carousel-item').length;
		$('.full-width-carousel-item:not(.clone)').each(function(){
			// Don't move the first.
			if (i > 0){
				$(this).css({
					left: (i*100)+'%'
				});
			}

			var clone = null;
			if (carouselItems > 1){
				// Create a duplicate and place it on the left.
				clone = $(this).clone().removeClass('current').addClass('clone').css({
					left: ((i-carouselItems)*100)+'%'
				});
				clone.appendTo('.full-width-carousel-dummy');
			}

			if ($(this).hasClass('featured-video')){
				$(this).find('.responsive_video').attr('id', 'video-'+vids).attr('data-vid', vids);
				vids++;
				if (clone !== null){
					clone.find('.responsive_video').attr('id', 'video-'+vids).attr('data-vid', vids);
					vids++;
				}
			}

			i++;

			if (i == carouselItems){
				$('.full-width-carousel-item').click(function(event){
					if (!animating && !$(this).hasClass('current')){
						event.preventDefault();

						animating = true;

						var left = $(this).position().left;
						var width = $(this).width();
						var move = left / width;
						var item = $(this);
						$('.full-width-carousel-dummy').animate({
							left: -(width*move)+'px'
						}, 400, function(){
							$('.full-width-carousel-dummy').css({
								left: ''
							});
							$('.full-width-carousel-item').each(function(){
								var leftItem = $(this).position().left;
								var pc = leftItem / width;
								$(this).css({
									left: ((pc-move)*100)+'%'
								});
							});

							$('.full-width-carousel-item.current').removeClass('current');
							item.addClass('current');

							// Move the last element to the other side.
							var itemIdx = item.attr('data-i');
							var clone = null;
							if (move > 0){
								itemIdx--;
								var isClone = item.hasClass('clone');
								if (itemIdx < 0){
									itemIdx = carouselItems-1;
									isClone = !isClone;
								}
								if (isClone){
									clone = $('.full-width-carousel-item[data-i="'+itemIdx+'"]:not(.clone)');
								}
								else{
									clone = $('.full-width-carousel-item[data-i="'+itemIdx+'"].clone');
								}
								clone.css({
									left: ((carouselItems-1)*100)+'%'
								});
							}
							else{
								if (item.hasClass('clone')){
									clone = $('.full-width-carousel-item[data-i="'+itemIdx+'"]:not(.clone)');
								}
								else{
									clone = $('.full-width-carousel-item[data-i="'+itemIdx+'"].clone');
								}
								clone.css({
									left: (-carouselItems*100)+'%'
								});
							}

							animating = false;
						});
					}
					else if ($(this).hasClass('current') && !animating && $('body#home').length && $(this).attr('href') != '#'){
						if ($(this).hasClass('featured-video')){
							event.preventDefault();

							$(this).closest('.full-width-carousel-item').find('.featured-carousel-bg, .full-width-carousel-item-inner').fadeOut(200);

							//$(this).find('video').addClass('show');

							var idx = parseInt($(this).find('.responsive_video').attr('data-vid'), 10);
							player = _V_("video-"+idx, {"techOrder":["html5","flash","youtube"],"ytcontrols":false});
							player.play();
						}
					}
					else{
						event.preventDefault();
					}
				});
			}
		});

		$('.a-full-width-carousel-left').click(function(event){
			event.preventDefault();

			// Find the one left of the current item.
			var current = $('.full-width-carousel-item.current');
			var isClone = current.hasClass('clone');
			var idx = parseInt(current.attr('data-i'), 10)-1;
			var item = null;
			if (idx >= 0 && isClone){
				item = $('.full-width-carousel-item[data-i="'+idx+'"].clone');
			}
			else if (idx >= 0){
				item = $('.full-width-carousel-item[data-i="'+idx+'"]:not(.clone)');
			}
			else if (isClone){
				item = $('.full-width-carousel-item[data-i="'+(carouselItems-1)+'"]:not(.clone)');
			}
			else{
				item = $('.full-width-carousel-item[data-i="'+(carouselItems-1)+'"].clone');
			}
			item.trigger('click');
		});

		$('.a-full-width-carousel-right').click(function(event){
			event.preventDefault();

			// Find the one left of the current item.
			var current = $('.full-width-carousel-item.current');
			var isClone = current.hasClass('clone');
			var idx = parseInt(current.attr('data-i'), 10)+1;
			var item = null;
			if (idx < carouselItems && isClone){
				item = $('.full-width-carousel-item[data-i="'+idx+'"].clone');
			}
			else if (idx < carouselItems){
				item = $('.full-width-carousel-item[data-i="'+idx+'"]:not(.clone)');
			}
			else if (isClone){
				item = $('.full-width-carousel-item[data-i="0"]:not(.clone)');
			}
			else{
				item = $('.full-width-carousel-item[data-i="0"].clone');
			}
			item.trigger('click');
		});

		var hammer = new Hammer($(".full-width-carousel").get(0));
		hammer.ondragend = function(ev) {
			// if we moved the slide 100px then navigate
			if(Math.abs(ev.distance) > 100) {
				if(ev.direction == 'right') {
					$('.a-full-width-carousel-left').trigger('click');
				} else if(ev.direction == 'left') {
					$('.a-full-width-carousel-right').trigger('click');
				}
			}
		};
		// Swipes.
		/*$(".full-width-carousel").hammer({
			swipe_time: 500,
			drag: false,
			tap: false,
			tap_double: false,
			transform: false,
			hold: false,
			prevent_default: false
		}).bind("swipe", function(ev) {
			if (ev.direction == "right") {
				$('.a-full-width-carousel-left').trigger('click');
			} else if (ev.direction == "left") {
				$('.a-full-width-carousel-right').trigger('click');
			}
		});*/
	}

	if ($('#tv-video').length){
		player = _V_("tv-video", {"techOrder":["html5","flash","youtube"],"ytcontrols":false});
	}

	setTimeout(function(){
		$('.featured-video .responsive_video').each(function(){
			var idx = parseInt($(this).attr('data-vid'), 10);
		});
	}, 1000);

	// External Links
	$('a[rel="external"]').click( function(event) {
		event.stopPropagation();
		window.open( $(this).attr('href') );
		return false;
	});
});

function facebookInit(){
	//put any code here that should wait for the Facebook SDK to load and FB.init() to finish
	if (console.log){
		console.log('Facebook ready');
	}
}

function showFormError(error){
	alert(error);
}

function isEmail(email){
	return /^([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x22([^\x0d\x22\x5c\x80-\xff]|\x5c[\x00-\x7f])*\x22))*\x40([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d)(\x2e([^\x00-\x20\x22\x28\x29\x2c\x2e\x3a-\x3c\x3e\x40\x5b-\x5d\x7f-\xff]+|\x5b([^\x0d\x5b-\x5d\x80-\xff]|\x5c[\x00-\x7f])*\x5d))*$/.test( email );
}

function reloadStylesheets() {
    var queryString = '?reload=' + new Date().getTime();
    $('link[rel="stylesheet"]').each(function () {
        this.href = this.href.replace(/\?.*|$/, queryString);
    });
}