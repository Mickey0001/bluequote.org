/**
 * Themify - Drag and Drop(iframe to iframe)
 */
;
(function ($) {
    'use strict';
    var pluginName = 'ThemifyDraggable',
            mode = 'desktop',
            scrollDir,
            doScroll = false,
            fixedHeight,
            inIframe = true,
            placeHolderIframe = false,
            draggedEl,
            droppedEl,
            topFixed,
            bottomFixed,
            placeHolderBody,
            placeHolder,
            doc = $(window.top.document),
            currentBody,
            defaults = {
                append: true,
                dropitems: null,
                elements:null,
                onDragStart: null,
                onDrag: null,
                onDragEnter: null,
                onDragLeave: null,
                onDragEnd: null,
                onDrop: null
            };
    function Plugin(element, options) {
        this.element = $(element);
        this.options = $.extend({}, defaults, options);
        this.init();
    }
    Plugin.prototype = {
        init: function () {
            if (placeHolderIframe === false) {
                //Init PlaceHolder
                placeHolderBody = $('<div/>', {
                    id: 'themify_builder_placeholder_body',
                    class: 'themify_builder_placeholder_helper'
                });

                placeHolderIframe = $('<div/>', {
                    id: 'themify_builder_placeholder_iframe',
                    class: 'themify_builder_placeholder_helper'
                });
                currentBody = $('body');
                currentBody.prepend(placeHolderIframe).on('themify_builder_change_mode', this.changeMode);

                //Init Scroll items
                topFixed = $('#tb_toolbar', doc);
                bottomFixed = $('#themify_builder_fixed_bottom_scroll', doc);
                $('body', doc).append(placeHolderBody);
            }
            this.element.on('mousedown touchstart', this.mouseDown.bind(this));
        },
        elpos: {},
        size: {w: '', h: ''},
        SetSide: function (e) {
            var side = e.pageY > (droppedEl.offset().top + (droppedEl.outerHeight() / 2)) ? 'bottom' : 'top';
            if (droppedEl[0].dataset.pos !== side) {
                droppedEl.attr('data-pos', side);
            }
        },
        mouseDown: function (e) {
            if (e.which === 1 && ! e.target.classList.contains( 'themify_module_favorite' ) && ! e.target.classList.contains( 'add_module_btn' ) ) {

                e.preventDefault();
                doScroll = false;
                draggedEl = this.element;
                this.elpos = draggedEl.offset();
                inIframe = false;
                this.setCurrentPlaceHolder(e);
                var module = draggedEl[0].outerHTML;
                placeHolderIframe.removeClass('drop_animate').hide().html(module);
                placeHolderBody.removeClass('drop_animate').hide().html(module);
                placeHolder.show();
                this.size.w = (placeHolder.outerWidth() / 2);
                this.size.h = (placeHolder.outerHeight());
                // Init Events
                $(document)
                        .on('mousemove.tb_visual', this.mouseMove.bind(this))
                        .on('mouseup.tb_visual', this.mouseUp.bind(this))
                        .on('mouseenter.tb_visual', this.iframeEnter.bind(this))
                        .on('mouseleave.tb_visual', this.iframeLeave.bind(this))

                        // Init Droppable zones
                        .on('mouseenter.tb_visual', this.options.dropitems, this.mouseEnter.bind(this))
                        .on('mouseleave.tb_visual', this.options.dropitems, this.mouseLeave.bind(this));
                doc
                        .on('mousemove.tb_visual', this.mouseMove.bind(this))
                        .on('mouseup.tb_visual', this.mouseUp.bind(this));

                bottomFixed
                        .on('mouseenter.tb_visual', this.scroll.bind(this))
                        .on('mouseleave.tb_visual', this.scrollUp.bind(this));
                topFixed
                        .on('mouseenter.tb_visual', this.scroll.bind(this))
                        .on('mouseleave.tb_visual', this.scrollUp.bind(this));

                this.mouseMove(e);
                var $body = $('body', doc);
                $body = $body.add($('body'));
                $body.addClass('themify_builder_drag_start');
                if ($.isFunction(this.options.onDragStart)) {
                    this.options.onDragStart.call(this, e, draggedEl);
                }
            }
            else {
                draggedEl = placeHolder = droppedEl = null;
            }
        },
        mouseMove: function (e) {
            e.stopPropagation();
            if (draggedEl && placeHolder) {
                if (doScroll) {
                    var self = this,
                        scrollEl = currentBody.add(currentBody.closest('html'));
                    scrollEl.stop().animate({
                        scrollTop: doScroll
                    },
                    {
                        duration: 800,
                        step: function (scroll) {
                            if (doScroll) {
                                var top = scrollDir === 'down' ? bottomFixed.offset().top - fixedHeight : topFixed.offset().top + fixedHeight;
                                placeHolder.css('top', top);
                            }
                            else {
                                scrollEl.stop();
                            }
                            if ($.isFunction(self.options.onDrag)) {
                                self.options.onDrag.call(self, e, draggedEl, droppedEl);
                            }
                        },
                        complete: function () {
                            if (!self.checkScrollEnd()) {
                                scrollEl.stop();
                                doScroll = scrollDir = false;
                                self.setPlaceHolder(e);
                            }
                        }
                    });

                }
                else {
                    this.setPlaceHolder(e);
                }
            }
        },
        setCurrentPlaceHolder: function () {
            placeHolder = inIframe ? placeHolderIframe : placeHolderBody;
        },
        setPlaceHolder: function (e) {
            var w = this.size.w;
            placeHolder.css({top: e.originalEvent.pageY - this.size.h - 12, left: e.originalEvent.pageX - w});
            if (droppedEl) {
                this.SetSide(e);
            }
            if ($.isFunction(this.options.onDrag)) {
                this.options.onDrag.call(this, e, draggedEl, droppedEl);
            }
        },
        removeAttr:function(){
            var el = document.querySelectorAll('[data-pos]');
            for(var i=0,len=el.length;i<len;++i){
                el[i].removeAttribute('data-pos');
            }
        },
        mouseUp: function (e) {
            // Remove Events
            $(document)
                    .off('mousemove.tb_visual')
                    .off('mouseup.tb_visual')
                    .off('mouseenter.tb_visual')
                    .off('mouseleave.tb_visual')

                    // Init Droppable zones
                    .off('mouseenter.tb_visual')
                    .off('mouseleave.tb_visual');
            doc
                    .off('mousemove.tb_visual')
                    .off('mouseup.tb_visual');

            bottomFixed
                    .off('mouseenter.tb_visual')
                    .off('mouseleave.tb_visual');

            topFixed
                    .off('mouseenter.tb_visual')
                    .off('mouseleave.tb_visual');

            if (draggedEl && placeHolder && !e.isTrigger) {

                e.stopPropagation();
                var pos = {},
                        drag = draggedEl.clone();
                if (droppedEl) {
                    pos.top = droppedEl.offset().top;
                    pos.left = droppedEl.offset().left;
                    if (droppedEl[0].dataset.pos === 'bottom') {
                        pos.top += droppedEl.outerHeight();
                    }
                }
                else {
                    pos.top = this.elpos.top;
                    pos.left = this.elpos.left;
                }
                doScroll = draggedEl = null;
                var self = this;
                placeHolder.addClass('drop_animate').css(pos).one(themifybuilderapp.Utils.transitionPrefix(), function (e) {
                    var $body = $('body', doc);
                    $body = $body.add('body');
                    $body.removeClass('themify_builder_drag_start');
                    if (droppedEl && droppedEl[0].dataset.pos) {
                        drag.hide();
                        if (droppedEl[0].dataset.pos === 'bottom') {
                            if (self.options.append && !droppedEl[0].classList.contains(self.options.elements.replace('.',''))) {
                                droppedEl.append(drag);
                            }
                            else {
                                droppedEl.after(drag);
                            }
                        }
                        else {
                            if (self.options.append && !droppedEl[0].classList.contains(self.options.elements.replace('.',''))) {
                                droppedEl.prepend(drag);
                            }
                            else {
                                droppedEl.before(drag);
                            }
                        }
                        self.removeAttr();
                        if ($.isFunction(self.options.onDrop)) {
                            self.options.onDrop.call(self, e, drag, droppedEl);
                        }
                        droppedEl = null;
                    }
                    else if ($.isFunction(self.options.onDragEnd)) {
                        self.options.onDragEnd.call(self, e, drag);
                    }
                    placeHolderIframe.empty().removeAttr('style');
                    placeHolderBody.empty().removeAttr('style');
                });
            }

        },
        mouseEnter: function (e) {
            if (draggedEl && placeHolder) {
                droppedEl = $(e.currentTarget);
                var self = this,
                    child = droppedEl.find(this.options.dropitems);
                if (child.length > 0) {
                    child.each(function () {
                        if (self.CheckIntersect(e, $(this))) {
                            droppedEl = $(this);
                            return false;
                        }
                    });
                }
                if(this.options.elements){
                    droppedEl.find(this.options.elements).each(function(){
                        if (self.CheckIntersect(e, $(this))) {
                            droppedEl = $(this);
                            return false;
                        }
                    });
                }
                self.removeAttr();
                this.SetSide(e);
                if ($.isFunction(this.options.onDragEnter)) {
                    this.options.onDragEnter.call(this, e, draggedEl, droppedEl);
                }
            }
        },
        mouseLeave: function (e) {
            var el = $(e.currentTarget);
            if (draggedEl && droppedEl && !this.CheckIntersect(e, el)) {
                this.removeAttr();
                droppedEl = false;
                if ($.isFunction(this.options.onDragLeave)) {
                    this.options.onDragLeave.call(this, e, draggedEl, el);
                }
            }
        },
        scroll: function (e) {
            if (draggedEl && placeHolder) {
                var step = parseInt((currentBody.height() - $(window.parent).height()) / 5),
                        el = $(e.currentTarget);
                if (el.prop('id') === 'themify_builder_fixed_bottom_scroll') {
                    doScroll = '+=' + step + 'px';
                    scrollDir = 'down';
                }
                else {
                    doScroll = '-=' + step + 'px';
                    scrollDir = 'up';
                }
                if (step > 0) {
                    fixedHeight = el.height();
                }
                else {
                    doScroll = false;
                }
            }
        },
        checkScrollEnd: function () {
            var top = currentBody.scrollTop();
            return (scrollDir === 'up' && top !== 0) || (scrollDir === 'down' && ($(window.parent).height() + top) !== currentBody.height());
        },
        CheckIntersect: function (e, item) {
            var offset = item.offset();
            return (e.pageX >= offset.left && e.pageX <= (offset.left + item.outerWidth())) && (e.pageY >= offset.top && e.pageY <= (offset.top + item.outerHeight()));
        },
        scrollUp: function (e) {
            if (draggedEl && placeHolder) {
                doScroll = scrollDir = false;
                var scrollEl = currentBody.add(currentBody.closest('html'));
                scrollEl.stop();
            }
        },
        iframeEnter: function (e) {
            if (draggedEl && placeHolder) {
                e.stopPropagation();
                inIframe = true;
                placeHolderBody.hide();
                placeHolderIframe.show();
                this.setCurrentPlaceHolder();
            }
        },
        iframeLeave: function (e) {
            if (draggedEl && placeHolder) {
                e.stopPropagation();
                var self = this;
                setTimeout(function () {
                    inIframe = scrollDir ? ((mode === 'desktop' || placeHolderIframe.css('display') === 'block') && self.checkScrollEnd()) : false;
                    if (!inIframe) {
                        placeHolderIframe.hide();
                        placeHolderBody.show();
                        self.setCurrentPlaceHolder();
                    }
                }, 5);
            }
        },
        changeMode: function (e, prev, breakpoint) {
            mode = breakpoint;
            currentBody = mode === 'desktop' ? $('body') : $('body', doc);
        }
    };
    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
        });
    };
})(jQuery);
