var TB_Ticks;
(function ($) {
    'use strict';
    TB_Ticks = {
        interval: null,
        time: 20000,
        $el: null,
        options: null,
        request: null,
        iframe:null,
        init: function (options,contentWindow) {
            var self = TB_Ticks;
            self.options = options;
            self.time = parseInt(options.tick / 2) * 1000;
            self.$el = $('#tmpl-builder-restriction');
            self.iframe = contentWindow;
            return self;
        },
        ticks: function () {
            var self = TB_Ticks;
            function callback(data) {
                if (data && Number( data ) !== 1) {
                    self.request.abort();
                    clearInterval(self.interval);
                    document.body.insertAdjacentHTML('beforeend', data);
                    self.$el = $('#tmpl-builder-restriction');
                    self.show();
                    self.iframe.ThemifyBuilderCommon.showLoader('show');
                    var api =  self.iframe.themifybuilderapp;
                    api.Utils.saveBuilder(function () {
                        api.Views.Toolbar.prototype.Revisions.ajax({action: 'tb_save_revision', rev_comment: self.$el.find('.tb-locked-revision').text()}, function () {
                            self.iframe.ThemifyBuilderCommon.showLoader('hide');
                            api.Models.Registry.destroy();
                        });
                    }, 'main', 0, true);
                }
            }
            if(self.interval!==null){
                clearInterval(self.interval);
            }
            self.interval = setInterval(function () {
                self.ajax(callback, null);
            }, self.time);
            $(document).off( 'heartbeat-tick',callback).on( 'heartbeat-tick', function callback( e, data ) {
                 if(typeof data['wp-refresh-post-lock']['lock_error']!=='undefined'){
                     self.request.abort();
                     clearInterval(self.interval);
                     $(document).off( 'heartbeat-tick',callback)
                 }
            });
            return self;
        },
        ajax: function (callback, take) {
            var self = TB_Ticks;
            self.request = $.ajax({
                type: 'POST',
                url: self.options.ajaxurl,
                data: {
                    action: 'tb_update_tick',
                    postID: self.options.postID,
                    take: take
                },
                success: callback
            });
        },
        isEditing: function () {
            return document.body.classList.contains('tb-restriction');
        },
        show: function () {
            var self = TB_Ticks;
            self.$el.show();
            self.close();
            self.takeOver();
        },
        hide: function () {
            TB_Ticks.$el.hide();
        },
        takeOver: function () {
            var self = TB_Ticks;
            $('body').one('click', '.tb-locked-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.ajax(function () {
                    var $body = $('body');
                    if($body.hasClass('themify_builder_active') || $body.hasClass('wp-admin')){
                        self.ticks();
                    }
                    else{
                        $body.removeClass('tb-restriction');
                        $('.js-turn-on-builder').first().trigger('click');
                    }
                    self.$el.remove();
                }, 1);
            });
        },
        close: function () {
            $('.tb-locked-close').one('click', this.hide);
        }
    };
    $(document).ready(function () {

    });

})(jQuery);