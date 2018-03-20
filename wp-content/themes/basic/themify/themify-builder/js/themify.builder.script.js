/**
 * Tabify
 */
;
(function ($) {

    'use strict';

    $.fn.tabify = function () {
        return this.each(function () {
            var tabs = $(this);
            if (!tabs.data('tabify')) {
                tabs.data('tabify', true);
                $('ul.tab-nav:first li:first', tabs).addClass('current');
                var tabLinks = $('ul.tab-nav:first li', tabs);
                $(tabLinks).click(function () {
                    $(this).addClass('current').attr('aria-expanded', 'true').siblings().removeClass('current').attr('aria-expanded', 'false');
                    $('ul.tab-nav:first', tabs).siblings('.tab-content').attr('aria-hidden', 'true');
                    var activeTab = $(this).find('a').attr('href');
                    $(activeTab).attr('aria-hidden', 'false').trigger('resize');
                    $('body').trigger('tb_tabs_switch', [activeTab, tabs]);
                    Themify.triggerEvent(window, 'resize');
                    return false;
                });
                $('ul.tab-nav:first', tabs).siblings('.tab-content').find('a[href^="#tab-"]').on('click', function (event) {
                    event.preventDefault();
                    var dest = $(this).prop('hash').replace('#tab-', ''),
                            contentID = $('ul.tab-nav:first', tabs).siblings('.tab-content').eq(dest - 1).prop('id');
                    if ($('a[href^="#' + contentID + '"]').length > 0) {
                        $('a[href^="#' + contentID + '"]').trigger('click');
                    }
                });
            }
        });
    };
	$( 'body' ).on( 'click', 'a[href*="#tab-"]', function(e) {
		var hash = this.hash;
		if ( $( this ).closest( '.tab-nav' ).length )
			return;
		if ( $( hash ).length && $( hash ).closest( '.module-tab' ).length ) {
			$( hash ).closest( '.module-tab' ).find( '.tab-nav a[href="' + hash +'"]' ).click();
			e.preventDefault();
		}
	} );

    // $('img.photo',this).themifyBuilderImagesLoaded(myFunction)
    // execute a callback when all images have loaded.
    // needed because .load() doesn't work on cached images
    if (!$.fn.themifyBuilderImagesLoaded) {
        $.fn.themifyBuilderImagesLoaded = function (callback) {
            var elems = this.filter('img'),
                    len = elems.length,
                    blank = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

            elems.bind('load.imgloaded', function () {
                if (--len <= 0 && this.src !== blank) {
                    elems.unbind('load.imgloaded');
                    callback.call(elems, this);
                }
            }).each(function () {
                // cached images don't fire load sometimes, so we reset src.
                if (this.complete || this.complete === undefined) {
                    var src = this.src;
                    // webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
                    // data uri bypasses webkit log warning (thx doug jones)
                    this.src = blank;
                    this.src = src;
                }
            });

            return this;
        };
    }
})(jQuery);

/*
 * Parallax Scrolling Builder
 */
(function ($, window) {

    'use strict';

    var $window = $(window),
            wH = null,
            is_mobile = false,
            isInitialized = false,
            className = 'builder-parallax-scrolling',
            defaults = {
                xpos: '50%',
                speedFactor: 0.1
            };
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    }
    Plugin.prototype = {
        items: [],
        top: 0,
        index: 0,
        init: function () {
            this.top = this.element.offset().top;
            this.items.push(this);
            if (!isInitialized) {
                wH = $window.height();
                is_mobile = ThemifyBuilderModuleJs._isMobile();
                $window.on('tfsmartresize.builderParallax', this.resize.bind(this))
                        .on('scroll.builderParallax', function () {
                            for (var i in this.items) {
                                this.items[i].update(i);
                            }
                        }.bind(this));
                isInitialized = true;
            }
            this.update();
        },
        resize: function () {
            wH = $window.height();
            for (var i in this.items) {
                this.items[i].top = this.items[i].element.offset().top;
                this.items[i].update(i);
            }
        },
        destroy: function (index) {
            if (this.items[index] !== undefined) {
                this.items.splice(index, 1);
                if (this.items.length === 0) {
                    $window.off('scroll.builderParallax').off('tfsmartresize.builderParallax');
                    isInitialized = false;
                }
            }
        },
        update: function (i) {
            if (document.body.contains(this.element[0]) === false || this.element[0].className.indexOf(className) === -1) {
                this.destroy(i);
                return;
            }
            var pos = $window.scrollTop(),
                    top = this.element.offset().top,
                    outerHeight = this.element.outerHeight(true);
            // Check if totally above or totally below viewport
            if ((top + outerHeight) < pos || top > (pos + wH)) {
                return;
            }
            if (is_mobile) {
                /* #3699 = for mobile devices increase background-size-y in 30% (minimum 400px) and decrease background-position-y in 15% (minimum 200px) */
                var outerWidth = this.element.outerWidth(true),
                        dynamicDifference = outerHeight > outerWidth ? outerHeight : outerWidth;
                dynamicDifference = Math.round(dynamicDifference * 0.15);
                if (dynamicDifference < 200) {
                    dynamicDifference = 200;
                }
                this.element.css({
                    backgroundSize: 'auto ' + Math.round(outerHeight + (dynamicDifference * 2)) + 'px',
                    'background-position-y': Math.round(((this.top - pos) * this.options.speedFactor) - dynamicDifference) + 'px'
                });
            }
            else {
                this.element.css('background-position-y', Math.round((this.top - pos) * this.options.speedFactor) + 'px');
            }
        }
    };
    $.fn['builderParallax'] = function (options) {
        return this.each(function () {
            $.data(this, 'plugin_builderParallax', new Plugin($(this), options));

        });
    };
})(jQuery, window);


var ThemifyBuilderModuleJs;
(function ($, window, document, undefined) {

    'use strict';

    ThemifyBuilderModuleJs = {
        wow: null,
        is_mobile: null,
        fwvideos: [], // make it accessible to public
        init: function () {
            this.bindEvents();
        },
        bindEvents: function () {
            if ('complete' !== document.readyState) {
                $(document).ready(this.document_ready);
            } else {
                this.document_ready();
            }
            if (window.loaded) {
                this.window_load();
            } else {
                $(window).load(this.window_load);
            }

        },
        /**
         * Executed on jQuery's document.ready() event.
         */
        document_ready: function () {
            var self = ThemifyBuilderModuleJs;
            self.setupBodyClasses();
            $.event.trigger('themify_builder_loaded');
            if (tbLocalScript.fullwidth_support === '') {
                $(window).on('tfsmartresize.tbfullwidth', function (e) {
                    self.setupFullwidthRows();
                });
            }
            if (!Themify.is_builder_active) {
                if (tbLocalScript.fullwidth_support === '') {
                    self.setupFullwidthRows();
                }
                self.GridBreakPoint();
                if (tbLocalScript.isAnimationActive) {
                    self.wowInit();
                }
                self.carousel();
                self.InitScrollHighlight();
                self.accordion();
                self.touchdropdown();
                self.tabs();
                self.onInfScr();
                self.menuModuleMobileStuff();
                self.playFocusedVideoBg();
                self.showcaseGallery();
                self.galleryPagination();
				self.readMoreLink();
                $(window).on('hashchange', this.tabsDeepLink);
            }
            $(window).on('tfsmartresize.tblink', function () {
                self.menuModuleMobileStuff(true);
            });
        },
        /**
         * Executed on JavaScript 'load' window event.
         */
        window_load: function () {
            var self = ThemifyBuilderModuleJs;
            window.loaded = true;
            if (!Themify.is_builder_active) {
                self.parallaxScrollingInit();
                self.charts();
                self.fullwidthVideo();
                self.backgroundSlider();
                self.backgroundZoom();
                self.backgroundZooming();
                if (tbLocalScript.isParallaxActive) {
                    self.backgroundScrolling();
                }
                self.tabsDeepLink();
            }
        },
        wowInit: function (callback, resync) {
            var self = ThemifyBuilderModuleJs;
            if (resync && self.wow) {
                self.wow.doSync();
                self.wow.sync();
                return;
            }
            function wowCallback() {
                function wowDuckPunch() {
                    // duck-punching WOW to get delay and iteration from classnames
                    if (typeof self.wow.__proto__ !== 'undefined') {
                        self.wow.__proto__.applyStyle = function (box, hidden) {
                            var duration = box.getAttribute('data-wow-duration'),
                                    iteration = box.getAttribute('class').match(/animation_effect_repeat_(\d*)/),
                                    delay = box.getAttribute('class').match(/animation_effect_delay_((?:\d+\.?\d*|\.\d+))/);
                            if (null !== delay) {
                                delay = delay[1] + 's';
                            }
                            if (null !== iteration)
                                iteration = iteration[1];
                            return this.animate((function (_this) {
                                return function () {
                                    return _this.customStyle(box, hidden, duration, delay, iteration);
                                };
                            })(this));
                        };
                    }
                }

				function wowApplyOnHover() {
					$(document).on({
						mouseenter: function () {
							var hoverAnimation = this.getAttribute('class').match(/hover-animation-(\w*)/),
								animation = this.style.animationName;
							if( '' != animation ){
								$(this).css('animation-name','').removeClass( animation );
							}
							$(this).addClass('animated ' + hoverAnimation[1]);
						},
						mouseleave: function () {
							var animation = this.getAttribute('class').match(/hover-animation-(\w*)/);
							$(this).removeClass('animated ' + animation[1]);
						}
					}, '.hover-wow')
				}

                if (themify_vars.TB) {
                    ThemifyBuilderModuleJs.animationOnScroll(resync);
                }
                self.wow = new WOW({
                    live: true,
                    offset: typeof tbLocalScript !== 'undefined' && tbLocalScript ? parseInt(tbLocalScript.animationOffset) : 100
                });
                self.wow.init();
                wowDuckPunch();
				wowApplyOnHover();
            }
            callback = callback || wowCallback;
			if (typeof tbLocalScript !== 'undefined'
				&& typeof tbLocalScript.animationInviewSelectors !== 'undefined'
				&& ( $(tbLocalScript.animationInviewSelectors.toString()).length || $('.hover-wow').length ) ){
				if (!ThemifyBuilderModuleJs.wow) {
                    Themify.LoadCss(tbLocalScript.builder_url + '/css/animate.min.css', null, null, null, function () {
                        Themify.LoadAsync(themify_vars.url + '/js/wow.min.js', callback, null, null, function () {
                            return (ThemifyBuilderModuleJs.wow);
                        });
                    });
                }
                else {
                    callback();
                    return (ThemifyBuilderModuleJs.wow);
                }
            }
        },
        setupFullwidthRows: function (el) {
            if (tbLocalScript.fullwidth_support !== '') {
                return;
            }
            if (!el) {
                if (!Themify.is_builder_active && this.rows !== undefined) {
                    el = this.rows;
                }
                else {
                    el = document.querySelectorAll('.themify_builder_content .module_row.fullwidth,.themify_builder_content .module_row.fullwidth_row_container');
                    if (!Themify.is_builder_active) {
                        this.rows = el;
                    }
                }
                if (el.length === 0) {
                    return;
                }
            }
            else if (!el.hasClass('fullwidth') && !el.hasClass('fullwidth_row_container')) {
                return;
            }
            else {
                el = el.get();
            }
            var container = $(tbLocalScript.fullwidth_container),
                    outherWith = container.outerWidth(),
                    outherLeft = container.offset().left;
            if (outherWith === 0) {
                return;
            }
            var styleId = 'tb-fulllwidth-styles',
                    style = '',
                    tablet = tbLocalScript.breakpoints.tablet,
                    tablet_landscape = tbLocalScript.breakpoints.tablet_landscape,
                    mobile = tbLocalScript.breakpoints.mobile,
                    arr = ['mobile', 'tablet', 'tablet_landscape', 'desktop'],
                    width = $(window).width(),
                    type = 'desktop';
            if (width <= mobile) {
                type = 'mobile';
            }
            else if (width <= tablet[1]) {
                type = 'tablet';
            }
            else if (width <= tablet_landscape[1]) {
                type = 'tablet_landscape';
            }
            function getCurrentValue(prop) {
                var val = $this.data(type + '-' + prop);
                if (val === undefined) {
                    if (type !== 'destop') {
                        for (var i = arr.indexOf(type) + 1; i < 4; ++i) {
                            if (arr[i] !== undefined) {
                                val = $this.data(arr[i] + '-' + prop);
                                if (val !== undefined) {
                                    $this.data(type + '-' + prop, val);
                                    break;
                                }
                            }
                        }
                    }
                }
                return val !== undefined ? val.split(',') : [];
            }
            for (var i = 0, len = el.length; i < len; ++i) {
                var $this = $(el[i]),
                        row = $this.closest('.themify_builder_content'),
                        left = row.offset().left - outherLeft,
                        right = outherWith - left - row.outerWidth();

                // set to zero when zoom is enabled
                if (row.hasClass('themify_builder_zooming_50') || row.hasClass('themify_builder_zooming_75')) {
                    left = 0;
                    right = 0;
                }
                if (!Themify.is_builder_active) {
                    var index = $this.attr('class').match(/module_row_(\d+)/)[1];
                    style += '.themify_builder.themify_builder_content .themify_builder_' + row.data('postid') + '_row.module_row_' + index + '.module_row{';
                }
                if (el[i].classList.contains('fullwidth')) {
                    var margin = getCurrentValue('margin'),
                            sum = '';
                    if (margin[0]) {
                        sum = margin[0];
                        style += 'margin-left:calc(' + margin[0] + ' - ' + Math.abs(left) + 'px);';
                    }
                    else {
                        style += 'margin-left:' + (-left) + 'px;';
                    }
                    if (margin[1]) {
                        if (sum !== '') {
                            sum += ' + ';
                        }
                        sum += margin[1];
                        style += 'margin-right:calc(' + margin[1] + ' - ' + Math.abs(right) + 'px);';
                    }
                    else {
                        style += 'margin-right:' + (-right) + 'px;';
                    }
                    style += sum !== '' ? 'width:calc(' + outherWith + 'px - (' + sum + '));' : 'width:' + outherWith + 'px;';
                }
                else {
                    style += 'margin-left:' + (-left) + 'px;margin-right:' + (-right) + 'px;width:' + outherWith + 'px;';
                    if (left || right) {
                        var padding = getCurrentValue('padding'),
                                sign = '+';
                        if (left) {
                            if (padding[0]) {
                                if (left < 0) {
                                    sign = '-';
                                }
                                style += 'padding-left:calc(' + padding[0] + ' ' + sign + ' ' + Math.abs(left) + 'px);';
                            }
                            else {
                                style += 'padding-left:' + Math.abs(left) + 'px;';
                            }
                        }
                        if (right) {
                            if (padding[1]) {
                                sign = right > 0 ? '+' : '-';
                                style += 'padding-right:calc(' + padding[1] + ' ' + sign + ' ' + Math.abs(right) + 'px);';
                            }
                            else {
                                style += 'padding-right:' + Math.abs(right) + 'px;';
                            }
                        }
                    }
                }

                if (Themify.is_builder_active) {
                    el[i].style['paddingRight'] = el[i].style['paddingLeft'] = el[i].style['marginRight'] = el[i].style['marginLeft'] = '';
                    el[i].style.cssText += style;
                    style = '';
                }
                else {
                    style += '}';
                }
            }
            if (!Themify.is_builder_active) {
                style = '<style id="' + styleId + '" type="text/css">' + style + '</style>';
                $('#' + styleId).remove();
                document.getElementsByTagName('head')[0].insertAdjacentHTML('beforeend', style);
            }
        },
        addQueryArg: function (e, n, l) {
            l = l || window.location.href;
            var r, f = new RegExp("([?&])" + e + "=.*?(&|#|$)(.*)", "gi");
            if (f.test(l))
                return 'undefined' !== typeof n && null !== n ? l.replace(f, "$1" + e + "=" + n + "$2$3") : (r = l.split("#"), l = r[0].replace(f, "$1$3").replace(/(&|\?)$/, ""), 'undefined' !== typeof r[1] && null !== r[1] && (l += "#" + r[1]), l);
            if ('undefined' !== typeof n && null !== n) {
                var i = -1 !== l.indexOf("?") ? "&" : "?";
                return r = l.split("#"), l = r[0] + i + e + "=" + n, 'undefined' !== typeof r[1] && null !== r[1] && (l += "#" + r[1]), l
            }
            return l
        },
        onInfScr: function () {
            var self = ThemifyBuilderModuleJs;
            $(document).ajaxSend(function (e, request, settings) {
                var page = settings.url.replace(/^(.*?)(\/page\/\d+\/)/i, '$2'),
                        regex = /^\/page\/\d+\//i,
                        match;

                if ((match = regex.exec(page)) !== null) {
                    if (match.index === regex.lastIndex) {
                        regex.lastIndex++;
                    }
                }

                if (null !== match) {
                    settings.url = self.addQueryArg('themify_builder_infinite_scroll', 'yes', settings.url);
                }
            });
        },
        InitScrollHighlight: function () {
            if (tbLocalScript.loadScrollHighlight == true && $('div[class*=tb_section-]').length > 0) {
                Themify.LoadAsync(tbLocalScript.builder_url + '/js/themify.scroll-highlight.js', this.ScrollHighlightCallBack, null, null, function () {
                    return('undefined' !== typeof $.fn.themifyScrollHighlight);
                });
            }
        },
        ScrollHighlightCallBack: function () {
            $('body').themifyScrollHighlight( tbScrollHighlight ? tbScrollHighlight : {});
        },
        // Row, col, sub-col, sub_row: Background Slider
        backgroundSlider: function ($bgSlider) {
            $bgSlider = $bgSlider || $('.row-slider, .column-slider, .subrow-slider');
            function callBack() {
                var themifySectionVars = {
                    autoplay: tbLocalScript.backgroundSlider.autoplay,
                    speed: tbLocalScript.backgroundSlider.speed
                };
                // Parse injected vars
                themifySectionVars.autoplay = parseInt(themifySectionVars.autoplay, 10);
                if (themifySectionVars.autoplay <= 10) {
                    themifySectionVars.autoplay *= 1000;
                }
                themifySectionVars.speed = parseInt(themifySectionVars.speed, 10);
                // Initialize slider
                $bgSlider.each(function () {
                    var $thisRowSlider = $(this),
                            $backel = $thisRowSlider.parent(),
                            rsImages = [],
                            rsImagesAlt = [],
                            bgMode = $thisRowSlider.data('bgmode');

                    // Initialize images array with URLs
                    $thisRowSlider.find('li').each(function () {
                        rsImages.push($(this).attr('data-bg'));
                        rsImagesAlt.push($(this).attr('data-bg-alt'));
                    });

                    // Call backstretch for the first time
                    $backel.tb_backstretch(rsImages, {
                        speed: themifySectionVars.speed,
                        duration: themifySectionVars.autoplay,
                        mode: bgMode
                    });
                    rsImages = null;

                    // Cache Backstretch object
                    var thisBGS = $backel.data('tb_backstretch');

                    // Previous and Next arrows
                    $thisRowSlider.find('.row-slider-prev,.row-slider-next').on('click', function (e) {
                        e.preventDefault();
                        if ($(this).hasClass('row-slider-prev')) {
                            thisBGS.prev();
                        }
                        else {
                            thisBGS.next();
                        }
                    });

                    // Dots
                    $thisRowSlider.find('.row-slider-dot').on('click', function () {
                        thisBGS.show($(this).data('index'));
                    });

                    // Add alt tag
                    $(window).on('backstretch.before backstretch.show', function (e, instance, index) {
                        // Needed for col styling icon and row grid menu to be above row and sub-row top bars.
                        if (Themify.is_builder_active) {
                            $backel.css('zIndex', 0);
                        }
                        if (rsImagesAlt[ index ] !== undefined) {
                            setTimeout(function () {
                                instance.$wrap.find('img:not(.deleteable)').attr('alt', rsImagesAlt[ index ]);
                            }, 1);
                        }
                    });
                });
            }
            if ($bgSlider.length > 0) {
                Themify.LoadAsync(
                        themify_vars.url + '/js/backstretch.themify-version.js',
                        callBack,
                        null,
                        null,
                        function () {
                            return ('undefined' !== typeof $.fn.tb_backstretch);
                        }
                );
            }
        },
        // Row: Fullwidth video background
        fullwidthVideo: function ($videoElm, parent) {
            if (this._isMobile()) {
                $('.big-video-wrap').remove();
                return;
            }
            if (!parent) {
                parent = $('.themify_builder');
            }
            $videoElm = $videoElm || $('[data-fullwidthvideo]', parent);
            if ($videoElm.length > 0) {
                var self = this,
                        $is_youtube = [],
                        $is_vimeo = [],
                        $is_local = [];

                $videoElm.each(function (i) {
                    var $video = $(this),
                            url = $video.data('fullwidthvideo');
                    if (!url) {
                        return true;
                    }
                    $video.children('.big-video-wrap').remove();
                    var provider = Themify.parseVideo(url);
                    if (provider.type === 'youtube') {
                        if (provider.id) {
                            $is_youtube.push({'el': $video, 'id': provider.id});
                        }
                    }
                    else if (provider.type === 'vimeo') {
                        if (provider.id) {
                            $is_vimeo.push({'el': $video, 'id': provider.id});
                        }
                    }
                    else {
                        $is_local.push($video);
                    }
                });

                if ($is_local.length > 0) {
                    Themify.LoadAsync(
                            themify_vars.url + '/js/bigvideo.js',
                            function () {
                                self.fullwidthVideoCallBack($is_local);
                            },
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $.fn.ThemifyBgVideo);
                            }
                    );
                }

                if ($is_vimeo.length > 0) {
                    Themify.LoadAsync(
                            tbLocalScript.builder_url + '/js/froogaloop.min.js',
                            function () {
                                self.fullwidthVimeoCallBack($is_vimeo);
                            },
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $f);
                            }
                    );
                }
                if ($is_youtube.length > 0) {
                    if (!$.fn.ThemifyYTBPlayer) {
                        Themify.LoadAsync(
                                tbLocalScript.builder_url + '/js/themify-youtube-bg.js',
                                function () {
                                    self.fullwidthYoutobeCallBack($is_youtube);
                                },
                                null,
                                null,
                                function () {
                                    return typeof $.fn.ThemifyYTBPlayer !== 'undefined';
                                }
                        );
                    }
                    else {
                        self.fullwidthYoutobeCallBack($is_youtube);
                    }

                }
            }

        },
        videoParams: function ($el) {
            var mute = 'mute' === $el.data('mutevideo'),
                    loop = 'undefined' !== typeof $el.data('unloopvideo') ? 'loop' === $el.data('unloopvideo') : 'yes' === tbLocalScript.backgroundVideoLoop;

            return {'mute': mute, 'loop': loop};
        },
        // Row: Fullwidth video background
        fullwidthVideoCallBack: function ($videos) {
            var self = ThemifyBuilderModuleJs;
            $.each($videos, function (i, elm) {
                var $video = $(elm),
                        videoURL = $video.data('fullwidthvideo'),
                        hash = Themify.hash(i + '-' + videoURL);
                var params = self.videoParams($video);
                $video.ThemifyBgVideo({
                    url: videoURL,
                    doLoop: params.loop,
                    ambient: params.mute,
                    id: hash
                });
            });
        },
        fullwidthYoutobeCallBack: function ($videos) {
            var self = this;
            if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
                Themify.LoadAsync(
                        "//www.youtube.com/iframe_api",
                        function () {
                            window.onYouTubePlayerAPIReady = _each;
                        },
                        null,
                        null,
                        function () {
                            return typeof YT !== 'undefined' && typeof YT.Player !== 'undefined';
                        });

            }
            else {
                _each();
            }
            function _each() {
                $.each($videos, function (i, elm) {
                    var params = self.videoParams(elm.el);
                    elm.el.ThemifyYTBPlayer({
                        videoID: elm.id,
                        id: elm.el.closest('.themify_builder_content').data('postid') + '_' + i,
                        mute: params.mute,
                        loop: params.loop,
                        mobileFallbackImage: tbLocalScript.videoPoster
                    });
                });
            }

        },
        fullwidthVimeoCallBack: function ($videos) {
            var self = this;
            if (typeof self.fullwidthVimeoCallBack.counter === 'undefined') {
                self.fullwidthVimeoCallBack.counter = 1;
                $(window).on('tfsmartresize', function (e) {
                    if (!e.isTrigger) {
                        $.each($videos, function (i, elm) {
                            VimeoVideo(elm.el.children('.themify-video-vmieo'));
                        });
                    }
                });

            }
            function VimeoVideo($video) {
                var width = $video.outerWidth(true),
                        height = $video.outerHeight(true),
                        pHeight = Math.ceil(width / 1.7), //1.7 ~ 16/9 aspectratio
                        iframe = $video.children('iframe');
                iframe.width(width).height(pHeight).css({
                    left: 0,
                    top: (height - pHeight) / 2
                });
            }
            $.each($videos, function (i, elm) {
                var $video = elm.el,
                        params = self.videoParams($video),
                        $iframe = $('<div class="big-video-wrap themify-video-vmieo"><iframe id="themify-vimeo-' + i + '" src="//player.vimeo.com/video/' + elm.id + '?api=1&portrait=0&title=0&title=0&badge=0&player_id=themify-vimeo-' + i + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>');
                $video.prepend($iframe);
                var player = $f($('#themify-vimeo-' + i)[0]);
                player.addEvent('ready', function () {
                    player.api('setLoop', params.loop);
                    player.api('setVolume', params.mute ? 0 : 1);
                    player.api('fullscreen', 0);
                    if ($video.children('.big-video-wrap').length > 1) {
                        $video.children('.big-video-wrap').slice(1).remove();
                    }
                    VimeoVideo($iframe);
                    player.api('play');
                });
            });
        },
        playFocusedVideoBg: function () {
            var self = ThemifyBuilderModuleJs,
                    playOnFocus = function () {
                        if (!self.fwvideos.length > 0)
                            return;
                        var h = window.innerHeight;
                        for (var i in self.fwvideos) {
                            var el = self.fwvideos[i].getPlayer();
                            if (el.isPlaying || !el.source) {
                                return;
                            }
                            var rect = el.P.getBoundingClientRect();
                            if (rect.bottom >= 0 && rect.top <= h) {
                                el.show(el.source);
                                el.isPlaying = true;
                            }
                        }
                    };
            $(window).on('scroll mouseenter keydown assignVideo', playOnFocus);
        },
        charts: function (el) {
            var elements = $('.module-feature-chart-html5', el),
                    self = this;
            if (elements.length > 0) {
                if (this.charts_data === undefined) {
                    this.charts_data = {};
                }
                Themify.LoadAsync(themify_vars.url + '/js/waypoints.min.js', callback, null, null, function () {
                    return ('undefined' !== typeof $.fn.waypoint);
                });
            }
            function callback() {

                function chartsCSS(charts) {
                    var styleId = 'chart-html5-styles',
                            styleHTML = '<style id="' + styleId + '">',
                            prefix = Themify.getVendorPrefix();
                    for (var i in charts) {
                        styleHTML += '.module-feature-chart-html5[data-progress="' + i + '"] .chart-html5-full,' +
                                '.module-feature-chart-html5[data-progress="' + i + '"] .chart-html5-fill {' + prefix + 'transform: rotate(' + charts[i] + 'deg);transform:rotate(' + charts[i] + 'deg);}';
                    }
                    styleHTML += '</style>';
                    $('#' + styleId).remove();
                    document.getElementsByTagName('head')[0].insertAdjacentHTML('beforeend', styleHTML);
                }

                // this mess adjusts the size of the chart, to make it responsive
                var setChartSize = function ($this) {
                    var width = Math.min($this.data('size'), $this.closest('.module-feature').width()),
                            halfw = Math.ceil(width / 2);
                    $this.css( {width: width, height: width} ).find( '.chart-html5-mask' ).css( {
						borderRadius: '0 ' + halfw + 'px ' + halfw + 'px 0',
						clip: 'rect(0px, ' + width + 'px, ' + width + 'px, ' + halfw + 'px)'
					} );
					
					$this.find( '.chart-html5-fill' ).addClass('chart-loaded').css( {
						borderRadius: halfw + 'px 0 0 ' + halfw + 'px',
						clip: 'rect(0px, ' + halfw + 'px, ' + width + 'px, 0px)'
					} );
                };
                var deg = parseFloat(180 / 100).toFixed(2),
                        reinit = false;
                elements.each(function () {
                    var progress = $(this).data('progress-end');
                    if (progress === undefined) {
                        progress = 100;
                    }
                    if (self.charts_data [progress] === undefined) {
                        self.charts_data [progress] = parseFloat(deg * progress).toFixed(2) - 0.1;
                        reinit = true;
                    }
                    setChartSize($(this));
                });
                if (reinit === true) {
                    chartsCSS(self.charts_data);
                }
                elements.each(function () {
                    var $this = $(this);
                    $this.waypoint(function () {
                        $this.attr('data-progress', $this.data('progress-end'));
                    }, {
                        offset: '100%',
                        triggerOnce: true
                    });
                });
                $(window).on('tfsmartresize.charts', function () {
                    elements.each(function () {
                        setChartSize($(this));
                    });
                });
            }
        },
        carousel: function (el) {
            if ($('.themify_builder_slider', el).length > 0) {
                var $self = this;
                Themify.LoadAsync(themify_vars.includesURL + 'js/imagesloaded.min.js', function () {
                    if ('undefined' === typeof $.fn.carouFredSel) {
                        Themify.LoadAsync(themify_vars.url + '/js/carousel.min.js', function () {
                            $self.carouselCalback(el);
                        }, null, null, function () {
                            return ('undefined' !== typeof $.fn.carouFredSel);
                        });
                    }
                    else {
                        $self.carouselCalback(el);
                    }
                }, null, null, function () {
                    return ('undefined' !== typeof $.fn.imagesLoaded);
                });
            }

        },
        videoSliderAutoHeight: function ($this) {
            // Get all the possible height values from the slides
            var heights = $this.children().map(function () {
                return $(this).height();
            });
            // Find the max height and set it
            $this.parent().height(Math.max.apply(null, heights));
        },
        carouselCalback: function (el) {
            var self = this;
            $('.themify_builder_slider', el).each(function () {
                if ($(this).closest('.caroufredsel_wrapper').length > 0) {
                    return;
                }
                var $this = $(this),
                        img_length = $this.find('img').length,
                        $height = (typeof $this.data('height') === 'undefined') ? 'variable' : $this.data('height'),
                        $args = {
                            responsive: true,
                            circular: true,
                            infinite: true,
                            height: $height,
                            items: {
                                visible: {min: 1, max: $this.data('visible')},
                                width: 150,
                                height: 'variable'
                            },
                            onCreate: function (items) {
                                $('.themify_builder_slider_wrap').css({'visibility': 'visible', 'height': 'auto'});
                                $this.trigger('updateSizes');
                                $('.themify_builder_slider_loader').remove();

                                // Fix bug video height with auto height settings.
                                if ('auto' === $height && 'video' === $this.data('type')) {
                                    ThemifyBuilderModuleJs.videoSliderAutoHeight($this);
                                }
                            }
                        };

                // fix the one slide problem
                if ($this.children().length < 2) {
                    $('.themify_builder_slider_wrap').css({'visibility': 'visible', 'height': 'auto'});
                    $('.themify_builder_slider_loader').remove();
                    $(window).resize();
                    return;
                }

                // Auto
                if (parseInt($this.data('auto-scroll')) > 0) {
                    $args.auto = {
                        play: true,
                        timeoutDuration: parseInt($this.data('auto-scroll') * 1000)
                    };
                }
                else if ($this.data('effect') !== 'continuously' && (typeof $this.data('auto-scroll') !== 'undefined' || parseInt($this.data('auto-scroll')) === 0)) {
                    $args.auto = false;
                }

                // Scroll
                if ($this.data('effect') === 'continuously') {
                    var speed = $this.data('speed'), duration;
                    if (speed == .5) {
                        duration = 0.10;
                    } else if (speed == 4) {
                        duration = 0.04;
                    } else {
                        duration = 0.07;
                    }
                    $args.auto = {timeoutDuration: 0};
                    $args.align = false;
                    $args.scroll = {
                        delay: 1000,
                        easing: 'linear',
                        items: $this.data('scroll'),
                        duration: duration,
                        pauseOnHover: $this.data('pause-on-hover')
                    };
                } else {
                    $args.scroll = {
                        items: $this.data('scroll'),
                        pauseOnHover: $this.data('pause-on-hover'),
                        duration: parseInt($this.data('speed') * 1000),
                        fx: $this.data('effect')
                    }
                }

                if ($this.data('arrow') === 'yes') {
                    $args.prev = '#' + $this.data('id') + ' .carousel-prev';
                    $args.next = '#' + $this.data('id') + ' .carousel-next';
                }

                if ($this.data('pagination') === 'yes') {
                    $args.pagination = {
                        container: '#' + $this.data('id') + ' .carousel-pager',
                        items: $this.data('visible')
                    };
                }

                if ($this.data('wrap') === 'no') {
                    $args.circular = false;
                    $args.infinite = false;
                }


                if (img_length > 0) {
                    $this.imagesLoaded().always(function () {
                        self.carouselInitSwipe($this, $args);
                    });
                } else {
                    self.carouselInitSwipe($this, $args);
                }

                $('.mejs__video').on('resize', function (e) {
                    e.stopPropagation();
                });
                $(window).on('tfsmartresize', function () {
                    $('.mejs__video').resize();
                    $this.trigger('updateSizes');

                    // Fix bug video height with auto height settings.
                    if ('auto' === $height && 'video' === $this.data('type')) {
                        ThemifyBuilderModuleJs.videoSliderAutoHeight($this);
                    }
                });

            });
        },
        carouselInitSwipe: function ($this, $args) {
            $this.carouFredSel($args);
            $this.swipe({
                excludedElements: 'label, button, input, select, textarea, .noSwipe',
                swipeLeft: function () {
                    $this.trigger('next', true);
                },
                swipeRight: function () {
                    $this.trigger('prev', true);
                },
                tap: function (event, target) {
                    // in case of an image wrapped by a link click on image will fire parent link
                    $(target).parent().trigger('click');
                }
            });

			if( $args.auto && $args.auto.timeoutDuration === 0 && $args.next && $args.prev && $args.scroll ) {
				$( 'body' ).on( 'click', [$args.next, $args.prev].join(), function() {
					$this.trigger( 'finish' );
					$this.trigger( $( this ).is( $args.next ) ? 'next' : 'prev', [{duration: $args.scroll.duration * 2}]);
				} );
			}
        },
        loadOnAjax: function (el, type) {
            var self = ThemifyBuilderModuleJs;
            if (type === 'row') {
                self.setupFullwidthRows(el);
            }
            self.touchdropdown(el);
            self.tabs(el);
            self.carousel(el);
            self.charts(el);
            self.fullwidthVideo(el, null);
            if (!el) {
                self.backgroundSlider();
            }
            var zoomScrolling = null,
                    zoom = null,
                    bgscrolling = null;
            if (el) {
                zoomScrolling = el.find('.builder-zoom-scrolling');
                if (el.hasClass('builder-zoom-scrolling')) {
                    zoomScrolling = zoomScrolling.add(el);
                }
                zoom = el.find('.builder-zooming');
                if (el.hasClass('builder-zooming')) {
                    zoom = zoom.add(el);
                }
                if (tbLocalScript.isParallaxActive) {
                    bgscrolling = el.find('.builder-parallax-scrolling');
                    if (el.hasClass('builder-parallax-scrolling')) {
                        bgscrolling = bgscrolling.add(el);
                    }

                }
            }
            if (zoomScrolling === null || zoomScrolling.length > 0) {
                self.backgroundZoom(zoomScrolling);
            }
            if (zoom === null || zoom.length > 0) {
                self.backgroundZooming(zoom);
            }
            if (tbLocalScript.isParallaxActive && (bgscrolling === null || bgscrolling.length > 0)) {
                self.backgroundScrolling(bgscrolling);
            }
            self.menuModuleMobileStuff(false, el);
            if (tbLocalScript.isAnimationActive) {
                self.wowInit(null, el);
            }
        },
        touchdropdown: function (el) {
            if (tbLocalScript.isTouch) {
                if (!$.fn.themifyDropdown) {
                    Themify.LoadAsync(themify_vars.url + '/js/themify.dropdown.js', function () {
                        $('.module-menu .nav', el).themifyDropdown();
                    },
                            null,
                            null,
                            function () {
                                return ('undefined' !== typeof $.fn.themifyDropdown);
                            });
                }
                else {
                    $('.module-menu .nav', el).themifyDropdown();
                }
            }
        },
        accordion: function () {
            $('body').off('click.themify', '.accordion-title').on('click.themify', '.accordion-title', function (e) {
                var $this = $(this),
                        $panel = $this.next(),
                        $item = $this.closest('li'),
                        type = $this.closest('.module.module-accordion').data('behavior'),
                        def = $item.toggleClass('current').siblings().removeClass('current'); /* keep "current" classname for backward compatibility */

                if ('accordion' === type) {
                    def.find('.accordion-content').slideUp().attr('aria-expanded', 'false').closest('li').removeClass('builder-accordion-active');
                }
                if ($item.hasClass('builder-accordion-active')) {
                    $panel.slideUp();
                    $item.removeClass('builder-accordion-active');
                    $panel.attr('aria-expanded', 'false');
                } else {
                    $item.addClass('builder-accordion-active');
                    $panel.slideDown(function () {
                        if (type === 'accordion' && window.scrollY > $panel.offset().top) {
                            var $scroll = $('html,body');
                            $scroll.animate({
                                scrollTop: $this.offset().top
                            },
                            {duration: tbScrollHighlight.speed,
                                complete: function () {
                                    if (tbScrollHighlight.fixedHeaderSelector != '' && $(tbScrollHighlight.fixedHeaderSelector).length > 0) {
                                        var to = Math.ceil($this.offset().top - $(tbScrollHighlight.fixedHeaderSelector).outerHeight(true));
                                        $scroll.stop().animate({scrollTop: to}, 300);
                                    }
                                }
                            }
                            );
                        }
                    });
                    $panel.attr('aria-expanded', 'true');

                    // Show map marker properly in the center when tab is opened
                    var existing_maps = $($panel).hasClass('default-closed') ? $panel.find(".themify_map") : false;
                    if (existing_maps.length) {
                        for (var i = 0; i < existing_maps.length; i++) { // use loop for multiple map instances in one tab
                            var current_map = $(existing_maps[i]).data('gmap_object'); // get the existing map object from saved in node
                            if (typeof current_map.already_centered !== "undefined" && !current_map.already_centered)
                                current_map.already_centered = false;
                            if (!current_map.already_centered) { // prevent recentering
                                var currCenter = current_map.getCenter();
                                google.maps.event.trigger(current_map, 'resize');
                                current_map.setCenter(currCenter);
                                current_map.already_centered = true;
                            }
                        }
                    }
                }

                $('body').trigger('tb_accordion_switch', [$panel]);
                Themify.triggerEvent(window, 'resize');
                e.preventDefault();
            });
        },
        tabs: function (el) {
            $('.module.module-tab', el).each(function () {
                var $height = $('.tab-nav:first', this).outerHeight();
                if ($height > 200) {
                    $(".tab-nav:first", this).siblings(".tab-content").css('min-height', $height);
                }
            }).tabify();
        },
        tabsDeepLink: function () {
            var hash = decodeURIComponent( window.location.hash );
            hash = hash.replace('!/', ''); // fix conflict with section highlight
            if ('' != hash && '#' !== hash && $(hash + '.tab-content').length > 0) {
                var cons = 100,
                        $moduleTab = $(hash).closest('.module-tab');
                if ($moduleTab.length > 0) {
                    $('a[href="' + hash + '"]').click();
                    $('html, body').animate({scrollTop: $moduleTab.offset().top - cons}, 1000);
                }
            }
        },
        backgroundScrolling: function (el) {
            if (!el) {
                el = $('.builder-parallax-scrolling');
            }
            el.builderParallax();
        },
        backgroundZoom: function (el) {
            var selector = '.themify_builder .builder-zoom-scrolling';
            if (!el) {
                el = $(selector);
            }
            function doZoom(e) {
                if (e !== null) {
                    el = $(selector);
                }
                if (el.length > 0) {
                    var height = window.innerHeight;
                    el.each(function () {
                        var rect = this.getBoundingClientRect();
                        if (rect.bottom >= 0 && rect.top <= height) {
                            var zoom = 140 - (rect.top + this.offsetHeight) / (height + this.offsetHeight) * 40;
                            $(this).css('background-size', zoom + '%')
                        }
                    });
                }
                else {
                    $(window).off('scroll', doZoom);
                }
            }
            if (el.length > 0) {
                doZoom(null);
                $(window).off('scroll', doZoom).on('scroll', doZoom);
            }
        },
        backgroundZooming: function (el) {
            var selector = '.themify_builder .builder-zooming';
            if (!el) {
                el = $(selector);
            }
            function isZoomingElementInViewport(item, innerHeight, clientHeight, bclientHeight) {
                var rect = item.getBoundingClientRect();
                return (
                        rect.top + item.clientHeight >= (innerHeight || clientHeight || bclientHeight) / 2 &&
                        rect.bottom - item.clientHeight <= (innerHeight || clientHeight || bclientHeight) / 3
                        );
            }

            function doZooming(e) {
                var zoomingClass = 'active-zooming';
                if (e !== null) {
                    el = $(selector);
                }
                if (el.length > 0) {
                    var height = window.innerHeight,
                            clientHeight = document.documentElement.clientHeight,
                            bclientHeight = document.body.clientHeight;

                    el.each(function () {
                        if (!$(this).hasClass(zoomingClass) && isZoomingElementInViewport(this, height, clientHeight, bclientHeight)) {
                            $(this).addClass(zoomingClass);
                        }
                    });
                }
                else {
                    $(window).off('scroll', doZooming);
                }
            }
            if (el.length > 0) {
                doZooming(null);
                $(window).off('scroll', doZooming).on('scroll', doZooming);
            }
        },
        animationOnScroll: function (resync) {
            var self = ThemifyBuilderModuleJs,
                    selectors = tbLocalScript.animationInviewSelectors;
            function doAnimation() {
                resync = resync || false;
                // On scrolling animation
                var $body = $('body');
                if ($body.find(selectors).length > 0) {
                    if (!$body.hasClass('animation-running')) {
                        $body.addClass('animation-running');
                    }
                } else if ($body.hasClass('animation-running')) {
                    $body.removeClass('animation-running');
                }

                // Core Builder Animation
                $.each(selectors, function (i, selector) {
                    $(selector).addClass('wow');
                });

                if (resync) {
                    if (self.wow) {
                        self.wow.doSync();
                    }
                    else {
                        var wow = self.wowInit();
                        if (wow) {
                            wow.doSync();
                        }
                    }
                }
            }
            $('body').addClass('animation-on');
            doAnimation();
        },
        setupBodyClasses: function () {
            var classes = [];
            if (ThemifyBuilderModuleJs._isTouch()) {
                classes.push('builder-is-touch');
            }
            if (ThemifyBuilderModuleJs._isMobile()) {
                classes.push('builder-is-mobile');
            }
            if (tbLocalScript.isParallaxActive) {
                classes.push('builder-parallax-scrolling-active');
            }
            if (!Themify.is_builder_active) {
                $('.themify_builder_content').each(function () {
                    if ($(this).children(':not(.js-turn-on-builder)').length > 0) {
                        classes.push('has-builder');
                        return false;
                    }
                });
            }
            $('body').addClass(classes.join(' '));
        },
        _isTouch: function () {
            var isTouchDevice = this._isMobile(),
                    isTouch = isTouchDevice || (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints));
            return isTouch;
        },
        _isMobile: function () {
            if (this.is_mobile === null) {
                this.is_mobile = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/);
            }
            return this.is_mobile;
        },
        galleryPagination: function () {
            $('body').delegate('.builder_gallery_nav a', 'click', function (e) {
                e.preventDefault();
                var $wrap = $(this).closest('.module-gallery');
                $.ajax({
                    url: this,
                    beforeSend: function () {
                        $wrap.addClass('builder_gallery_load');
                    },
                    complete: function () {
                        $wrap.removeClass('builder_gallery_load');
                    },
                    success: function (data) {
                        if (data) {
                            var $id = $wrap.prop('id');
                            $wrap.html($(data).find('#' + $id).html());
                        }
                    }
                });
            });
        },
        showcaseGallery: function () {
            $('body').on('click', '.module-gallery.layout-showcase a', function (e) {
                e.preventDefault();
                var showcaseContainer = $(this).closest('.gallery').find('.gallery-showcase-image'),
                        titleBox = showcaseContainer.find('.gallery-showcase-title');

                titleBox.css({opacity: '', visibility: ''});
                showcaseContainer.find('img').prop('src', $(this).data('image'));
                showcaseContainer.find('#gallery-showcase-title').html($(this).prop('title'));
                showcaseContainer.find('#gallery-showcase-caption').html($(this).data('caption'));
                !$.trim(titleBox.text()) && titleBox.css({opacity: 0, visibility: 'hidden'});
            });
        },
        parallaxScrollingInit: function (el, is_live) {
            if (tbLocalScript.isParallaxScrollActive) {
                if (el) {
                    if (is_live) {
                        el = el.get();
                    }
                    else {
                        var is_rellax = el.data('parallax-element-speed'),
                                p = el.get(0);
                        el = Array.prototype.slice.call(p.querySelectorAll('[data-parallax-element-speed]'));
                        if (is_rellax) {
                            el.push(p);
                        }
                    }
                }
                else {
                    el = document.querySelectorAll('[data-parallax-element-speed]');
                }
                if (el.length > 0) {
                    if (typeof Rellax === 'undefined') {
                        Themify.LoadAsync(tbLocalScript.builder_url + '/js/premium/themify.parallaxit.js', parallaxScrollingCallback, false, false, function () {
                            return typeof Rellax !== 'undefined';
                        });
                    }
                    else {
                        parallaxScrollingCallback();
                    }
                }
            }
            function parallaxScrollingCallback() {
                function rellaxInit(items) {
                    new Rellax(items, {
                        round: false
                    });
                }
                rellaxInit(el);
                if (!Themify.is_builder_active) {
                    $(document).ajaxComplete(function () {
                        var elem = document.querySelectorAll('[data-parallax-element-speed]');
                        if (elem.length > 0) {
                            rellaxInit(elem);
                        }
                    });
                }
            }
        },
        menuModuleMobileStuff: function (is_resize, el) {
            var menuModules = $('.module.module-menu', el);

            if (menuModules.length > 0) {
                var windowWidth = window.innerWidth,
                        $body = $('body'),
                        closeMenu = function () {
                            $('.mobile-menu-module')
                                    .empty()
                                    .prop('class', 'mobile-menu-module')
                                    .next('.body-overlay')
                                    .removeClass('body-overlay-on');
                            $body.removeClass('menu-module-left menu-module-right');
                        };
                if ($body.find('.mobile-menu-module').length === 0) {
                    $body
                            .append('<div class="themify_builder"><div class="mobile-menu-module"></div><div class="body-overlay"></div></div>');
                }

                menuModules.each(function () {
                    var $this = $(this),
                            breakpoint = $this.data('menu-breakpoint');

                    if (breakpoint) {
                        var menuContainer = $this.find('div[class*="-container"]'),
                                menuBurger = $this.find('.menu-module-burger');

                        if (windowWidth >= breakpoint) {
                            menuContainer.show();
                            menuBurger.hide();
                        } else {
                            menuContainer.hide();
                            menuBurger.css('display', 'block');
                        }

                        if (!is_resize) {
                            if ($this.next('style').length > 0) {
                                var styleContent = $this.next('style').html().replace(/\.[^{]+/g, function (match) {
                                    return match + ', .mobile-menu-module' + match.replace(/\.themify_builder\s|\.module-menu/g, '');
                                });

                                $this.next('style').html(styleContent);
                            }
                            $this.append('<a class="menu-module-burger"></a>');
                        }
                    }
                });

                if (!is_resize && !Themify.is_builder_active) {
                    $body.on('click', '.menu-module-burger', function (e) {
                        e.preventDefault();

                        var menuDirection = $(this).parent().data('menu-direction'),
                                menuContent = $(this).parent().find('div[class*="-container"] > ul').clone(),
                                menuUI = menuContent.prop('class').replace(/nav|menu-bar|fullwidth|vertical/g, ''),
                                customStyle = $(this).parent().prop('class').match(/menu-[\d\-]+/g);

                        customStyle = customStyle ? customStyle[0] : '';
                        menuContent = menuContent.removeAttr('id').removeAttr('class').addClass('nav');
                        menuContent.find('ul').prev('a').append('<i class="toggle-menu fa fa-angle-down"></i>');
                        $body.addClass('menu-module-' + menuDirection);
                        $('.mobile-menu-module').addClass(menuDirection + ' ' + menuUI + ' ' + customStyle)
                                .html(menuContent)
                                .prepend('<a class="menu-close"></a>')
                                .next('.body-overlay')
                                .addClass('body-overlay-on');

                    })
                            .on('click', '.mobile-menu-module ul a', function (e) {
                                var $this = $(this),
                                        $linkIcon = $this.find('i.toggle-menu');
                                if ($this.has('i.toggle-menu').length) {
                                    e.preventDefault();
                                    $this.next('ul').toggle();
                                    if ($linkIcon.hasClass('fa-angle-down')) {
                                        $linkIcon.removeClass('fa-angle-down').addClass('menu-close');
                                    } else {
                                        $linkIcon.removeClass('menu-close').addClass('fa-angle-down');
                                    }
                                }
                            })
                            .on('click', '.mobile-menu-module > .menu-close, .mobile-menu-module + .body-overlay', closeMenu);
                } else {
                    closeMenu()
                }
            }
        },
        GridBreakPoint: function () {
            var tablet_landscape = tbLocalScript.breakpoints.tablet_landscape,
                tablet = tbLocalScript.breakpoints.tablet,
                mobile = tbLocalScript.breakpoints.mobile,
                rows = document.querySelectorAll('.row_inner,.subrow_inner'),
                prev = false;
            function Breakpoints() {
                var width = $(window).width(),
                        type = 'desktop';
                if (width <= mobile) {
                    type = 'mobile';
                }
                else if (width <= tablet[1]) {
                    type = 'tablet';
                }
                else if (width <= tablet_landscape[1]) {
                    type = 'tablet_landscape';
                }
                if (type !== prev) {
                    var is_desktop = type === 'desktop',
                            set_custom_width = is_desktop || prev === 'desktop';
                    if (is_desktop) {
                        $('body').removeClass('tb_responsive_mode');
                    }
                    else {
                        $('body').addClass('tb_responsive_mode');
                    }
                    for (var i = 0, len = rows.length; i < len; ++i) {
                            var columns = rows[i].children,
                                    grid = rows[i].dataset['col_' + type],
                                    first = columns[0],
                                    last = columns[columns.length - 1],
                                    base = rows[i].dataset['basecol'];
                            if (set_custom_width) {
                                for (var j = 0, clen = columns.length; j < clen; ++j) {
                                    var w = columns[j].dataset['w'];
                                    if (w) {
                                        if (is_desktop) {
                                            columns[j].style['width'] = w + '%';
                                        }
                                        else {
                                            columns[j].style['width'] = '';
                                        }
                                    }
                                }
                            }
                            var dir = rows[i].dataset[type + '_dir'];
                            if (dir === 'rtl') {
                                first.classList.remove('first');
                                first.classList.add('last');
                                last.classList.remove('last');
                                last.classList.add('first');
                                rows[i].classList.add('direction-rtl');
                            }
                            else {
                                first.classList.remove('last');
                                first.classList.add('first');
                                last.classList.remove('first');
                                last.classList.add('last');
                                rows[i].classList.remove('direction-rtl');
                            }
                            if(base && !is_desktop){
                                if (prev !== false && prev !== 'desktop') {
                                    rows[i].classList.remove('tb_3col');
                                    var prev_class = rows[i].dataset['col_' + prev];
                                    if (prev_class) {
                                        rows[i].classList.remove($.trim(prev_class.replace('tb_3col', '').replace('mobile', 'column').replace('tablet', 'column')));
                                    }
                                }

                                if (!grid || grid === '-auto') {
                                    rows[i].classList.remove('tb_grid_classes');
                                    rows[i].classList.remove('col-count-' + base);
                                }
                                else {
                                    var cl = rows[i].dataset['col_' + type];
                                    if (cl) {
                                        rows[i].classList.add('tb_grid_classes');
                                        rows[i].classList.add('col-count-' + base);
                                        cl = cl.split(' ');
                                        for (var c in cl) {
                                            rows[i].classList.add($.trim(cl[c].replace('mobile', 'column').replace('tablet', 'column')));
                                        }
                                    }
                                }
                        }
                    }
                    prev = type;
                }
            }
            Breakpoints();
            $(window).on('tfsmartresize.themify_grid', function (e) {
                if (!e.isTrigger) {
                    Breakpoints();
                }
            });
        },
		readMoreLink: function(){
			if( $( '.module-text-more' ).length ){
				$( '.module-text-more' ).parent().nextAll().each(function(){
					$( this ).toggle(0);
				});
				$( '.module-text-more' ).on('click',function(e){
					e.preventDefault();
					$( this ).parent().nextAll().each(function(){
						$( this ).toggle( 400 );
					});
					$( this ).parent().remove();
				})
			}
		}
    };

    // Initialize
    ThemifyBuilderModuleJs.init();

}(jQuery, window, document));
