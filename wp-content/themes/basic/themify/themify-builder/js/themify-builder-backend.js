(function ($) {

    'use strict';

    if ($('#page-builder.themify_write_panel').length === 0)
        return;
    var api = themifybuilderapp,
    $body = $('body'),
    saved = false;
    api.mode = false;
    api.render = function () {
       
        $body[0].insertAdjacentHTML('afterbegin', '<div class="themify_builder_fixed_scroll" id="themify_builder_fixed_bottom_scroll"></div>');
        $body.append($('<div/>', {id: 'themify_builder_alert'}));
        if (themifyBuilder.builder_data.length === 0) {
            themifyBuilder.builder_data = {};
        }
        this.toolbar = new api.Views.Toolbar({el: '#tb_toolbar'});
        api.Instances.Builder[0] = new api.Views.Builder({el: '#themify_builder_row_wrapper', collection: new api.Collections.Rows(themifyBuilder.builder_data)});
        api.Instances.Builder[0].render();
        /* hook save to publish button */
        $('input#publish,input#save-post').one('click', function (e) {
            if (!saved) {
                api.Utils.saveBuilder(function(){
                    // Clear undo history
                    api.toolbar.undoManager.reset();
                    $(e.currentTarget).trigger('click');
                });
                e.preventDefault();
            }
        });
        // switch frontend
        $('<a href="#" id="themify_builder_switch_frontend_button" class="button themify_builder_switch_frontend">' + themifyBuilder.i18n.switchToFrontendLabel + '</a>')
                .on('click', function (e) {
                    e.preventDefault();
                    $('#themify_builder_switch_frontend').trigger('click');
                }).appendTo('#postdivrich #wp-content-media-buttons');

        $('input[name*="builder_switch_frontend"]').closest('.themify_field_row').remove(); // hide the switch to frontend field check

        api.Views.bindEvents();

        api.Forms.bindEvents();

        api.vent.trigger('dom:builder:init', true);
    };

    api._backendSwitchFrontend = function(){
        $('#builder_switch_frontend_noncename').val('ok');
        saved = true;
        if ( 'publish' === $('#original_post_status').val() ) {
            $('#publish').trigger('click');
        } else {
            $('#save-post').trigger('click');
        }
    };
    api._backendBuilderFocus = function(){
        $( '#page-buildert' ).trigger( 'click' );
        $( 'html, body' ).animate( {
                scrollTop: $( '#tb_toolbar' ).offset().top - $( '#wpadminbar' ).height()
        }, 2000 );
    };

    $(function () {
        if ( $body.hasClass( 'post-php' ) && $( '#post-lock-dialog' ).length ) {
			if ( ! $( '#post-lock-dialog' ).hasClass( 'hidden' ) ) {
				return;
			}
			Themify.LoadAsync( themifyBuilder.builder_url + '/js/themify-ticks.js', function() {
				if ( $body.hasClass( 'tb-restriction' ) ) {
					TB_Ticks.init( themifyBuilder.ticks, window ).show();
				} else {
					TB_Ticks.init( themifyBuilder.ticks, window ).ticks();
				}
			}, null, null, function() {
				return typeof TB_Ticks !== 'undefined';
			} );
        }
        var _original_icl_copy_from_original;

        api.render();

        // WPML compat
        if (typeof window.icl_copy_from_original === 'function') {
            _original_icl_copy_from_original = window.icl_copy_from_original;
            // override "copy content" button action to load Builder modules as well
            window.icl_copy_from_original = function (lang, id) {
                _original_icl_copy_from_original(lang, id);
                jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: 'themify_builder_icl_copy_from_original',
                        source_page_id: id,
                        source_page_lang: lang
                    },
                    success: function (data) {
                        if (data != '-1') {
                            jQuery('#page-builder .themify_builder.themify_builder_admin').empty().append(data).contents().unwrap();

                            // redo module events
                            //ThemifyPageBuilder.moduleEvents();
                        }
                    }
                });
            };
        }

    });

    // Run on WINDOW load
    $(window).load(function () {

        var $panel = $('#tb_toolbar'),
			$module_tmp_helper = $('#themify_builder_module_tmp'),
			$scroll_anchor = $('#tb_scroll_anchor'),
			$top = 0,
			$left = 0,
			$scrollTimer = null,
			$panel_top = 0,
			$wpadminbar = $('#wpadminbar'),
			$wpadminbarHeight = $wpadminbar.css( 'position' ) === 'fixed' ? $wpadminbar.outerHeight(true) : 0;
        if ($panel.length > 0) {
            if ($panel.is(':visible')) {
                themify_sticky_pos();
            }
            else {
                $('.themify-meta-box-tabs a').one('click', function () {
                    if ($(this).attr('id') === 'page-buildert') {
                        themify_sticky_pos();
                    }
                });
            }
        }

		function isStickyBar() {
			var $bottom = $panel_top + $('#page-builder').height(),
				$scroll = $( window ).scrollTop();

			return $scroll > $top && $scroll < $bottom;
		}

		function themify_sticky_pos() {
			$panel.width( $panel.width() );
			$top = $scroll_anchor.offset().top;
			$left = $scroll_anchor.offset().left;
			$panel_top = Math.round( $('#page-builder').offset().top );
			$module_tmp_helper.height( $panel.outerHeight(true) );

			$(window).scroll(function () {
				$scrollTimer && clearTimeout( $scrollTimer );
				$scrollTimer = setTimeout(handleScroll, 15);
			}).resize(function () {
				$top = $scroll_anchor.offset().top;
				$left = $scroll_anchor.offset().left;
				$wpadminbarHeight = $wpadminbar.css( 'position' ) === 'fixed' ? $wpadminbar.outerHeight(true) : 0;
				$panel.width( $('#page-builder .themify_builder_admin' ).width() )
					.css( 'top', isStickyBar() ? $wpadminbarHeight : '' );
				$module_tmp_helper.height( $panel.outerHeight(true) );
			});
		}

		function handleScroll() {
			$scrollTimer = null;
			
			if ( isStickyBar() ) {
				$panel.addClass('tb_toolbar_fixed').css({
					top: $wpadminbarHeight,
					left: $left
				});
				$module_tmp_helper.css('display', 'block');
			} else {
				$panel.removeClass('tb_toolbar_fixed').css({
					top: 0,
					left: 0
				});
				$module_tmp_helper.css('display', 'none');
			}
		}
		if( sessionStorage.getItem( 'focusBackendEditor' ) ) {
			api._backendBuilderFocus();
			sessionStorage.removeItem( 'focusBackendEditor' );
		}
    });
})(jQuery);