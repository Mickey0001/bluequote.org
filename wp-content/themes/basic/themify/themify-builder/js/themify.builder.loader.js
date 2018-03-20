/*! Themify Builder - Asynchronous Script and Styles Loader */
(function ($, window, document) {
    'use strict';
    $(document).ready(function () {
        function remove_tinemce() {
            if (tinymce !== undefined && tinyMCE) {
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['wp_autoresize_on'] = false;
                var content_css = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'].split(',');
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'] = content_css[1] !== undefined ? content_css[1] : content_css[0];
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['plugins'] = 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wpdialogs,wptextpattern,wpview,wplink';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['indent'] = 'simple';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['ie7_compat'] = false;
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['root_name'] = 'div';
                tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['relative_urls'] = true;
                tinyMCE.execCommand('mceRemoveEditor', true, 'tb_lb_hidden_editor');
                $('#wp-tb_lb_hidden_editor-editor-container,#wp-tb_lb_hidden_editor-editor-tools').remove();
            }
        }
        var builderLoader = false,
            $body = $('body');
        if (wp === undefined || wp.customize === undefined) {
            var builder = document.querySelectorAll('.themify_builder_content:not(.not_editable_builder)');
            for(var i=0,len=builder.length;i<len;++i){
                builder[i].insertAdjacentHTML('afterEnd','<a class="themify_builder_turn_on js-turn-on-builder" href="javascript:void(0);"><span class="dashicons dashicons-edit" data-id="' + builder[i].dataset.postid + '"></span>' + tbLoaderVars.turnOnBuilder + '</a>');
            }
            builder = null;
        }
        
        var responsiveSrc = window.location.href.indexOf('?') > 0 ? '&' : '?';
        responsiveSrc = window.location.href.replace(window.location.hash, '').replace('#', '') + responsiveSrc + 'tb-preview=1&ver=' + tbLocalScript.version;
        function init(){
            $body.one('click.tbloader', '.toggle_tb_builder a:first, a.js-turn-on-builder', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var is_locked = $body.hasClass('tb-restriction');
                    Themify.LoadAsync(tbLocalScript.builder_url+'/js/themify-ticks.js', function(){
                    if(is_locked){
                        TB_Ticks.init(tbLocalScript.ticks).show();
                        init(); 
                    }
                    },null,null,function(){
                        return typeof TB_Ticks!=='undefined';
                    });
                if(is_locked){
                    return;
                }
                var post_id = $( this ).find( '> span' ).data('id');
                setTimeout(remove_tinemce, 1);
                //remove unused the css/js to make faster switch mode/window resize
                var $children = $body.children(),
                    css = Array.prototype.slice.call(document.head.getElementsByTagName('link')),
                    js_styles = Array.prototype.slice.call(document.head.getElementsByTagName('script')).concat(Array.prototype.slice.call(document.head.getElementsByTagName('style')));
                $body[0].insertAdjacentHTML('beforeend', '<div class="themify_builder_workspace_container"><iframe src="' + responsiveSrc + '" id="themify_builder_site_canvas_iframe" name="themify_builder_site_canvas_iframe" class="themify_builder_site_canvas_iframe"></iframe></div>');
                if (!builderLoader) {
                    setTimeout(function () {
                        for (var i = 0, len = tbLoaderVars.styles.length; i < len; ++i) {
                            Themify.LoadCss(tbLoaderVars.styles[i]);
                        }
                        for (var i = 0, len = tbLoaderVars.js.length; i < len; ++i) {
                            if (tbLoaderVars.js[i].external) {
                                var s = document.createElement('script');
                                s.type = 'text/javascript';
                                s.text = tbLoaderVars.js[i].external;
                                var t = document.getElementsByTagName('script')[0];
                                t.parentNode.insertBefore(s, t);
                            }
                            Themify.LoadAsync(tbLoaderVars.js[i].src, null, tbLoaderVars.js[i].ver);
                        }
                        builderLoader = $('<div/>', {
                            id: 'themify_builder_alert',
                            class: 'tb_busy'
                        });
                        $body[0].insertAdjacentHTML('afterbegin', '<div class="themify_builder_fixed_scroll" id="themify_builder_fixed_bottom_scroll"></div>');
                        $body.append(builderLoader);
                        // Change text to indicate it's loading
                        $('.themify_builder_front_icon').length>0 && $('.themify_builder_front_icon').parent()[0].insertAdjacentHTML('beforeend', tbLoaderVars.progress);
                    }, 1);
                }
                $('#themify_builder_site_canvas_iframe').one('load', function () {
                    var scrollPos = $(document).scrollTop(),
                        contentWindow = this.contentWindow,
                        b = contentWindow.jQuery('body');
                    $body.one('themify_builder_ready', function (e) {
                        builderLoader.fadeOut(100, function () {
                            $(this).removeClass('tb_busy');
                        });
                        $('.themify_builder_workspace_container').show();
                        $children.hide();
                        for (var i = 0, len = js_styles.length; i < len; ++i) {
                            if (js_styles[i] && js_styles[i].parentNode) {
                                js_styles[i].parentNode.removeChild(js_styles[i]);
                            }
                        }
                        js_styles = null;
                        for (var i = 0, len = css.length; i < len; ++i) {
                            if (css[i] && css[i].parentNode && css[i].getAttribute('id') !== 'dashicons-css' && css[i].getAttribute('href').indexOf('tinymce/skins') === -1) {
                                css[i].parentNode.removeChild(css[i]);
                            }
                        }
                        css = null;
                        $('.themify_builder_content,#wpadminbar,header').remove();
                        $children.filter('ul,a,video,audio').remove();
                        $(window).off();
                        $body.off('scroll');
                        $(document).off();
                        $('html').removeAttr('style class');
                        $body.prop('class', 'themify_builder_active builder-breakpoint-desktop').removeAttr('style');
                        tbLoaderVars = null;
                        contentWindow.scrollTo(0, scrollPos);
                        if(!b.hasClass('tb-restriction')){
                            setTimeout(function(){
                                TB_Ticks.init(tbLocalScript.ticks,contentWindow).ticks();
                            },5000);
                        }
                        else{
                            setTimeout(function(){
                                document.body.appendChild(b.find('#tmpl-builder-restriction')[0]);
                                TB_Ticks.init(tbLocalScript.ticks,contentWindow).show();
                            },1000);
                        }
                    });
                    contentWindow.themifyBuilder.post_ID = post_id;
                    b.trigger('builderiframeloaded.themify', this);
                });
            });
        }
        init();
        if(!$body.hasClass('tb-restriction')){  
            if (window.location.hash === '#builder_active') {
                $(document).on('tinymce-editor-init', function (event, editor) {
                    if (editor.id === 'tb_lb_hidden_editor') {
                        $('.js-turn-on-builder').first().trigger('click');
                        window.location.hash = '';
                    }
                });
            }
            else {
                //cache iframe content in background and tincymce content_css
                var link = '<link href="' + responsiveSrc + '" rel="prerender prefetch"/>',
                    $tinemce = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['content_css'].split(','),
                    cache_suffix = tinyMCEPreInit.mceInit['tb_lb_hidden_editor']['cache_suffix'];
                for (var i = 0, len = $tinemce.length; i < len; ++i) {
                    $tinemce[i] += ($tinemce[i].indexOf('?') > -1 ? '&' : '?') + cache_suffix;
                    link += '<link href="' + $tinemce[i] + '" rel="prefetch"/>';
                }
                document.getElementsByTagName('head')[0].insertAdjacentHTML('beforeend', link);
            }
        }
    });
})(jQuery, window, document);