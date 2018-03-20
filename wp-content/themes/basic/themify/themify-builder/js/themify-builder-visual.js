(function ($) {
    'use strict';

    var api = themifybuilderapp,
            tb_shorcodes = [],
            module_cache = [],
            ThemifyLiveStyling;

    api.mode = 'visual';
    api.iframe = '';
    api.VisualCache = [];
    api.id = '';

    api.Mixins.Frontend = {
        render_visual: function () {
            // collect all jobs
            var constructData = [],
                    style_data = [],
                    batch = this.el.querySelectorAll('[data-cid]'),
                    batch = Array.prototype.slice.call(batch);
            batch.unshift(this.el);
            for (var i = 0, len = batch.length; i < len; ++i) {
                var model = api.Models.Registry.lookup(batch[i].getAttribute('data-cid'));
                if (model) {
                    var type = model.get('elType'),
                            key = type === 'module' ? 'mod_settings' : 'styling',
                            styles = model.get(key),
                            cid = model.cid;
                    constructData.push({jobID: cid, data: model.toJSON()});
                    if (styles && Object.keys(styles).length > 0) {
                        style_data.push({'type': type === 'module' ? model.get('mod_name') : type, 'cid': cid, 'data': styles});
                    }

                }
            }
            api.liveStylingInstance.setNewRules(style_data);
            return api.render_element(constructData);
        },
        change_callback: function () {
            var el = this.$el;
            el[0].insertAdjacentHTML('afterbegin', '<span class="sp-preloader tb-preview-component"></span>');
            this.render_visual().done(function () {
                el.find('.tb-preview-component').remove();
                api.Utils.setCompactMode(el[0].getElementsByClassName('module_column'));
                var cid = api.eventName === 'row' ? el.data('cid') : api.beforeEvent.data('cid');
                api.vent.trigger('dom:change', cid, api.beforeEvent, el, api.eventName);
                api.Mixins.Builder.update(el);
                if (api.eventName === 'row') {
                    api.vent.trigger('dom:builder:change');
                }
            });
        },
        createEl: function (markup) {
            var type = this.model.get('elType'),
                    $html = $(markup);
            if (type !== 'column') {
                $html = $html.filter('.module_' + type);
            }
            var cover = $html.children('.builder_row_cover'),
                    slider = $html.children('.' + type + '-slider'),
                    dataAttr = $html.data(),
                    style = $html[0].getAttribute('style');
            this.$el.addClass($html[0].getAttribute('class'));
            for (var i in dataAttr) {
                this.el.dataset[i] = typeof dataAttr[i] === 'object' ? JSON.stringify(dataAttr[i]) : dataAttr[i];
            }
            if (style) {
                this.el.setAttribute('style', style);
            }
            if (cover.length > 0) {
                var _cover = this.$el.children('.builder_row_cover');
                if (_cover.length > 0) {
                    _cover.replaceWith(cover);
                } else {
                    this.$el.prepend(cover);
                }
            }
            if (slider.length > 0) {
                var _slider = this.$el.children('.' + type + '-slider');
                if (_slider.length > 0) {
                    _slider.replaceWith(slider);
                } else {
                    this.$el.prepend(slider);
                }
            }
        },
        restoreHtml: function (rememberedEl) {
            var $currentEl = api.activeModel.get('elType') === 'module' ? $('.tb_element_cid_' + api.activeModel.cid) : api.liveStylingInstance.$liveStyledElmt;
            if ($currentEl.length > 0) {
                var batch = rememberedEl[0].querySelectorAll('[data-cid]');
                batch = Array.prototype.slice.call(batch);
                batch.unshift(rememberedEl[0]);
                for (var i = 0, len = batch.length; i < len; ++i) {
                    var model = api.Models.Registry.lookup(batch[i].getAttribute('data-cid'));
                    if (model) {
                        model.trigger('change:view', batch[i]);
                    }
                }
                $currentEl.replaceWith(rememberedEl);
                api.Mixins.Builder.update(rememberedEl);
            }
        }
    };

    api.previewVisibility = function () {
        var $el = 'row' === this.model.get('elType') ? this.$el : this.$el.find('.module'),
                visible = 'row' === this.model.get('elType') ? this.model.get('styling') : this.model.get('mod_settings');

        if (api.isPreview) {
            if ('hide_all' === visible['visibility_all']) {
                $el.addClass('hide-all');
            }
            else {
                if ('hide' === visible['visibility_desktop']) {
                    $el.addClass('hide-desktop');
                }

                if ('hide' === visible['visibility_tablet']) {
                    $el.addClass('hide-tablet');
                }

                if ('hide' === visible['visibility_mobile']) {
                    $el.addClass('hide-mobile');
                }
            }

            // Rellax initiation
            var init_rellax = false;
            if (!_.isEmpty(visible['custom_parallax_scroll_speed'])) {
                init_rellax = true;
                $el[0].dataset.parallaxElementSpeed = parseInt(visible['custom_parallax_scroll_speed']);
            }

            if (!_.isEmpty(visible['custom_parallax_scroll_reverse'])) {
                $el[0].dataset.parallaxElementReverse = true;
            }

            if (!_.isEmpty(visible['custom_parallax_scroll_fade'])) {
                $el[0].dataset.parallaxFade = true;
            }

            if (!_.isEmpty(visible['custom_parallax_scroll_zindex'])) {
                $el[0].style['zIndex'] = visible['custom_parallax_scroll_zindex'];
            }

            if (init_rellax) {
                ThemifyBuilderModuleJs.parallaxScrollingInit($el, true);
            }

        } else {
            $el.removeClass('hide-desktop hide-tablet hide-mobile hide-all');
            if (undefined !== typeof Rellax && !_.isEmpty(visible['custom_parallax_scroll_speed'])) {
                Rellax.destroy($el[0].dataset.rellaxIndex);
            }
        }
    };

    _.extend(api.Views.BaseElement.prototype, api.Mixins.Frontend);

    api.Views.register_row('visual', {
        initialize: function () {
            this.listenTo(this.model, 'create:element', this.createEl);
            this.listenTo(this.model, 'visual:change', this.change_callback);
            this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);

            api.vent.on('dom:preview', api.previewVisibility.bind(this));
        }
    });

    api.Views.register_subrow('visual', {
        initialize: function () {
            this.listenTo(this.model, 'create:element', this.createEl);
            this.listenTo(this.model, 'visual:change', this.change_callback);
            this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
        }
    });

    api.Views.register_column('visual', {
        initialize: function () {
            this.listenTo(this.model, 'create:element', this.createEl);
            this.listenTo(this.model, 'visual:change', this.change_callback);
            this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
        }
    });

    api.Views.register_module('visual', {
        template: wp.template('builder_visual_module_item'),
        is_ajax_call: false,
        _jqueryXhr: false,
        templateVisual: function (settings) {
            var tpl = wp.template('builder-' + this.model.get('mod_name') + '-content');
            return tpl(settings);
        },
        attributes: function () {
            var visible = this.model.get('mod_settings'),
                    cl = visible['visibility_all'] === 'hide_all' || visible['visibility_desktop'] === 'hide' || visible['visibility_tablet'] === 'hide' || visible['visibility_mobile'] === 'hide' ? ' tb_visibility_hidden' : '';

            return {
                'class': 'themify_builder_module_front module-' + this.model.get('mod_name') + ' active_module clearfix tb_element_cid_' + this.model.cid + cl,
                'data-cid': this.model.cid
            };
        },
        initialize: function () {
            this.listenTo(this.model, 'create:element', this.createEl);
            this.listenTo(this.model, 'visual:change', this.change_callback);
            this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
            this.listenTo(this.model, 'custom:preview:live', this.previewLive);
            this.listenTo(this.model, 'custom:preview:refresh', this.previewReload);

            api.vent.on('dom:preview', api.previewVisibility.bind(this));
        },
        createEl: function (markup) {
            var module = this.$('.module'),
                    cl = module[0].getAttribute('class');
            module.remove();
            this.el.insertAdjacentHTML('beforeend', markup);
            this.$el.find('.module').first().addClass(cl);
        },
        shortcodeToHTML: function (content) {
            var self = this;
            function previewShortcode(shorcodes) {
                if (self._shortcodeXhr !== undefined && 4 !== self._shortcodeXhr) {
                    self._shortcodeXhr.abort();
                }
                self._shortcodeXhr = $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'tb_render_element_shortcode',
                        shortcode_data: JSON.stringify(shorcodes),
                        tb_load_nonce: themifyBuilder.tb_load_nonce
                    },
                    success: function (data) {
						if ( data.success ) {
							var shortcodes = data.data.shortcodes,
								styles = data.data.styles;
							if( styles ) {
                                                            for (var i = 0, len = styles.length; i < len; ++i) {
                                                                    Themify.LoadCss(styles[i].s,styles[i].v,null,styles[i].m);
                                                            }
							}	
							for (var i = 0, len = shortcodes.length; i < len; ++i) {
                                                            var k = Themify.hash( shortcodes[i].key );
                                                            self.$el.find( '.tmp' + k ).replaceWith( shortcodes[i].html );
                                                            tb_shorcodes[k] = shortcodes[i].html;
                                                            api.Utils.loadContentJs( self.$el, 'module' );
							}

						}
                    }
                });
            }
            var found = [],
                    is_shortcode = false,
                    shorcode_list = themifyBuilder.available_shortcodes;
            for (var i = 0, len = shorcode_list.length; i < len; ++i) {
                content = wp.shortcode.replace(shorcode_list[i], content, function (atts) {
                    var sc_string = wp.shortcode.string(atts),
                            k = Themify.hash(sc_string),
                            replace = '';
                    if (tb_shorcodes[k] === undefined) {
                        found.push(sc_string);
                        replace = '<span class="tmp' + k + '">[loading shortcode...]</span>'
                    }
                    else {
                        replace = tb_shorcodes[k];
                    }
                    is_shortcode = true;
                    return replace;
                });
            }
            if (is_shortcode && found.length > 0) {
                previewShortcode(found);
            }
            return  {'content': content, 'found': is_shortcode};
        },
        previewLive: function (data, is_shortcode, cid, selector, value, el) {
            this.is_ajax_call = false;
            if (this._jqueryXhr && 4 !== this._jqueryXhr) {
                this._jqueryXhr.abort();
            }
            var is_selector = selector !== undefined && api.activeModel.cid && value,
                tmpl,
                timer = 300;
            if (is_selector) {
                selector = this.$el.find(selector);
                if (selector.length === 0) {
                    is_selector = false;
                }
                else if (el.data('control-repeater')) {
                    selector = selector.eq( el.closest('.tb_repeatable_field').index());
                }
            }
            data['cid'] = cid ? cid : api.activeModel.cid;
            if (!is_selector || is_shortcode === true) {
                tmpl = this.templateVisual(data);
                if (this.is_ajax_call) {//if previewReload is calling from visual template 
                    return;
                }
                if (is_shortcode === true) {
                    var shr = this.shortcodeToHTML(tmpl);
                    if (shr.found) {
                        timer = 1000;
                        tmpl = shr.content;
                        is_selector = false;
                    }
                }
            }
            if (is_selector) {
                selector.html(value);
            }
            else {
                var module = this.$('.module');
                module.replaceWith(tmpl);
                this.$el.children('style').remove(); // temporary fix unwanted inline style
                module = this.$('.module');
                if (api.VisualCache[data['cid']]) {
                    module.addClass(api.VisualCache[data['cid']]);
                }
                if (!cid) {
                    api.liveStylingInstance.$liveStyledElmt = module;
                    if (this.timeout) {
                        clearTimeout(this.timeout);
                    }
                    this.timeout = setTimeout(function () {
                        api.Utils.loadContentJs(module, 'module');
                    }, timer);
                }
            }
        },
        previewReload: function (settings, selector, value, el) {
            if (selector !== undefined && api.activeModel.cid && value) {
                selector = this.$el.find(selector);
                if (selector.length > 0) {
                    if (el.data('control-repeater')) {
                        selector = selector.eq(el.closest('.tb_repeatable_field').index());
                    }
                    selector.html(value);
                    return;
                }
            }
            var that = this;
            if (this._jqueryXhr && 4 !== this._jqueryXhr) {
                this._jqueryXhr.abort();
            }
            this.is_ajax_call = true;
            function callback(data) {
                var module = that.$el.find('.module').first();
                module.replaceWith(data);
                module = that.$el.find('.module').first();
                if (api.VisualCache[that.model.cid]) {
                    module.addClass(api.VisualCache[that.model.cid]);
                }
                api.liveStylingInstance.$liveStyledElmt = module;
                api.Utils.loadContentJs(module, 'module');
                that.$el.find('.tb-preview-component').remove();
            }
            that.el.insertAdjacentHTML('afterbegin', '<span class="tb-preview-component sp-preloader"></span>');
            var name = this.model.get('mod_name');
            delete settings['cid'];
            settings = api.Utils.clear(settings);
            settings['module_' + name + '_slug'] = name; //unique settings
            settings = JSON.stringify(settings);
            var key = Themify.hash(settings);

            if (module_cache[key] !== undefined && module_cache[key]['data'] !== undefined) {
                var old_cid = module_cache[key]['cid'];
                if (this.model.cid !== old_cid) {
                    var replace = name + '-' + old_cid + '-' + old_cid,
                            new_id = name + '-' + this.model.cid + '-' + this.model.cid,
                            re = new RegExp(replace, 'g');
                    module_cache[key]['data'] = module_cache[key]['data'].replace(re, new_id);
                    module_cache[key]['cid'] = this.model.cid;
                }
                callback(module_cache[key]['data']);
                return;
            }
            else {
                module_cache[key] = {};
            }
            this._jqueryXhr = $.ajax({
                type: 'POST',
                url: themifyBuilder.ajaxurl,
                data: {
                    action: 'tb_load_module_partial',
                    tb_post_id: this.$el.closest('.themify_builder_content').data('postid'),
                    tb_cid: this.model.cid,
                    tb_module_slug: name,
                    tb_module_data: settings,
                    tb_load_nonce: themifyBuilder.tb_load_nonce
                },
                success: function (data) {
                    module_cache[key]['data'] = data;
                    module_cache[key]['cid'] = that.model.cid;
                    callback(data);
                },
                error: function () {
                    that.$el.removeClass('tb-preview-loading');
                }
            });

            return this;
        }
    });

    api.bootstrap = function (settings, callback) {
        // collect all jobs
        var jobs = [],
                set_rules = settings ? true : false,
                style_data = [];
        if (!settings) {
            settings = api.Models.Registry.items;
        }
        for (var cid in settings) {
            var model = api.Models.Registry.items[cid],
                    data = model.toJSON(),
                    type = data.elType,
                    key = type === 'module' ? 'mod_settings' : 'styling',
                    styles = data[key];
            if (styles && Object.keys(styles).length > 0) {
                if (set_rules === true) {
                    style_data.push({'type': type === 'module' ? data['mod_name'] : type, 'cid': cid, 'data': styles});
                }
            }
            else if ('module' !== type) {
                continue;
            }
            if ('module' === type && 'tile' !== data['mod_name'] && themifyBuilder.modules[data['mod_name']].type !== 'ajax') {
                var model = api.Models.Registry.lookup(cid),
                        is_shortcode = 'accordion' === data['mod_name'] || 'box' === data['mod_name'] || 'feature' === data['mod_name'] || 'tab' === data['mod_name'] || 'text' === data['mod_name'] || 'plain-text' === data['mod_name'] || 'pointers' === data['mod_name'] || 'pro-image' === data['mod_name'] || 'countdown' === data['mod_name'] || 'button' === data['mod_name'] || 'pro-slider' === data['mod_name'] || 'timeline' === data['mod_name'];

                model.trigger('custom:preview:live', data['mod_settings'], is_shortcode, cid);
                continue;
            }
            if ('column' === type) {
                delete data.modules;
            }
            else if ('row' === type || 'module' === type || type === 'subrow') {
                delete data.cols;
            }
            jobs.push({jobID: cid, data: data});

        }
        if (set_rules === true) {
            api.liveStylingInstance.setNewRules(style_data);
        }
        this.batch_rendering(jobs, 0, 360, callback);
    };

    api.batch_rendering = function (jobs, current, size, callback) {
        if (current >= jobs.length) {
            // load callback
            if ($.isFunction(callback)) {
                callback.call(this);
            }
            return;
        } else {
            var smallerJobs = jobs.slice(current, current + size);
            this.render_element(smallerJobs).done(function () {
                api.batch_rendering(jobs, current += size, size, callback);
            });
        }
    };

    api.render_element = function (constructData) {
        // send json data
        return $.ajax({
            type: "POST",
            url: themifyBuilder.ajaxurl,
            dataType: 'json',
            data: {
                action: 'tb_render_element',
                batch: JSON.stringify(constructData),
                tb_load_nonce: themifyBuilder.tb_load_nonce
            },
            success: function (data) {
                for (var cid in data) {
                    var model = api.Models.Registry.lookup(cid);
                    model.trigger('create:element', data[cid]);
                }
            }
        });
    };

    api.get_visual_templates = function (callback) {
        var key = 'tb_visual_templates';
        function getData() {
            if (themifyBuilder.debug) {
                return false;
            }
            try {
                var record = localStorage.getItem(key),
                        m = '';
                if (!record) {
                    return false;
                }
                record = JSON.parse(record);
                for (var s in themifyBuilder.modules) {
                    m += s;
                }
                if (record.ver.toString() !== tbLocalScript.version.toString() || record.h !== Themify.hash(m)) {
                    return false;
                }
                return record.val;
            }
            catch (e) {
                return false;
            }
            return false;
        }
        function setData(value) {
            try {
                var m = '';
                for (var s in themifyBuilder.modules) {
                    m += s;
                }
                var record = {val: value, ver: tbLocalScript.version, h: Themify.hash(m)};
                localStorage.setItem(key, JSON.stringify(record));
                return true;
            }
            catch (e) {
                return false;
            }
        }

        function insert(data) {
            var insert = '';
            for (var i in data) {
                insert += data[i];
            }
            document.getElementsByTagName('body')[0].insertAdjacentHTML('beforeend', insert);
            if (callback) {
                callback();
            }
        }
        var data = getData();
        if (data) {//cache visual templates)
            insert(data);
            return;
        }
        $.ajax({
            type: 'POST',
            url: themifyBuilder.ajaxurl,
            dataType: 'json',
            data: {
                action: 'tb_load_visual_templates',
                tb_load_nonce: themifyBuilder.tb_load_nonce
            },
            success: function (resp) {
                if (resp) {
                    insert(resp);
                    setData(resp);
                }
            }
        });
    };

    api.render = function () {
        api.get_visual_templates(function () {
            var builder = [document.getElementById('themify_builder_content-' + themifyBuilder.post_ID)];
            for (var i = 0, len = 1; i < len; ++i) {
                var id = builder[i].dataset.postid,
                        input = builder[i].getElementsByTagName('script')[0];
                var data = window['builderdata' + '_' + id] ? window['builderdata' + '_' + id].data : [];
                if (data.length === 0) {
                    data = {};
                }
                window['builderdata' + '_' + id] = null;
                if (input) {
                    input.parentNode.removeChild(input);
                }
                api.id = id;
                api.Instances.Builder[i] = new api.Views.Builder({el: '#themify_builder_content-' + id, collection: new api.Collections.Rows(data), type: 'visual'});
                api.Instances.Builder[i].render();
                api.bootstrap(null, function () {
                    api.toolbar.el.style.display = 'block';
                    window.top.jQuery('body').trigger('themify_builder_ready');
                    api.Utils.loadContentJs();
                    top_iframe.body.insertAdjacentHTML('beforeend', themifyBuilder.data);
                    themifyBuilder.data = null;
                    Themify.is_builder_loaded = true;
                    api.vent.trigger('dom:builder:init', true);
                });
            }
            api.id = false;
        });
    };
    // Initialize Builder
    $('body').one('builderiframeloaded.themify', function (e, iframe) {
        api.iframe = iframe;
        if (tbLocalScript.isAnimationActive) {
            setTimeout(function () {
                Themify.LoadCss(tbLocalScript.builder_url + '/css/animate.min.css'); // load it anyway for animation live preview
            }, 1);
        }
        api.toolbar = new api.Views.Toolbar({el: '#tb_toolbar'});
        setTimeout(function () {
            api.liveStylingInstance = new ThemifyLiveStyling();
            api.liveStylingInstance.getRules();
        }, 50);
        setTimeout(function () {
            api.render();
        }, 1);
        setTimeout(function () {
            api.Views.bindEvents();
        }, 1);

        api.Forms.bindEvents();

    });

    ThemifyLiveStyling = (function ($) {

        function ThemifyLiveStyling() {
            this.$context = $('#themify_builder_lightbox_parent', top_iframe);
            this.isInit = false;
            this.prefix = false;
            this.type = false;
            this.group = false;
            this.styleName = 'themify-builder-component-customize';
            this.style_tab = '#themify_builder_options_styling';
            this.generatedPrefix = [];
            this.generatedCache = [];
            this.module_rules = [];
            this.rulesCache = [];
            this.tempData = [];
            this.prevData = [];
            this.currentSheet = null;
            var self = this;
            setTimeout(function () {
                self.InitInlineStyles();
                self.initModChange();
                self.before_close();
                self.bindLightboxForm();
            }, 1);
        }

        ThemifyLiveStyling.prototype.InitInlineStyles = function () {
            var styles = '<style type="text/css" id="' + this.styleName + '-desktop"></style>';
            for (var key in themifyBuilder.breakpoints) {
                var w = api.Utils.getBPWidth(key) + 'px';
                styles += '<style media="screen and (max-width:' + w + ')" type="text/css" id="' + this.styleName + '-' + key + '"></style>';
            }
            document.getElementsByTagName('head')[0].insertAdjacentHTML('beforeend', styles);
        };
        ThemifyLiveStyling.prototype.init = function (currentStyleObj) {
            this.type = api.activeModel.get('elType');
            this.$liveStyledElmt = $('.tb_element_cid_' + api.activeModel.cid);
            this.prefix = '.themify_builder .tb_element_cid_' + api.activeModel.cid;
            if (this.type === 'module') {
                this.$liveStyledElmt = this.$liveStyledElmt.children('.module');
                this.prefix += ' ';
                this.group = api.activeModel.get('mod_name');
            }
            else {
                if (this.type !== 'row' && api.activeModel.get('component_name') !== 'sub-column') {
                    this.prefix = '.themify_builder_content' + this.prefix;
                }
                this.group = this.type;
            }
            this.currentStyleObj = typeof currentStyleObj === 'object' ? currentStyleObj : {};
            this.tempData = [];
            this.tempData[api.activeBreakPoint] = [];
            if (!this.rulesCache[api.activeBreakPoint]) {
                this.rulesCache[api.activeBreakPoint] = [];
            }
            this.currentSheet = this.getSheet(api.activeBreakPoint);
            this.isInit = true;
        };

        ThemifyLiveStyling.prototype.getRules = function () {
            var key = 'tb_rules';
            function getData() {
                try {
                    var record = localStorage.getItem(key),
                            m = '';
                    if (!record) {
                        return false;
                    }
                    record = JSON.parse(record);
                    for (var s in themifyBuilder.modules) {
                        m += s;
                    }
                    if (record.ver.toString() !== tbLocalScript.version.toString() || record.h !== Themify.hash(m)) {
                        return false;
                    }
                    return JSON.parse(record.val);
                }
                catch (e) {
                    return false;
                }
                return false;
            }
            function setData(value) {
                try {
                    var m = '';
                    for (var s in themifyBuilder.modules) {
                        m += s;
                    }
                    var record = {val: JSON.stringify(value), ver: tbLocalScript.version, h: Themify.hash(m)};
                    localStorage.setItem(key, JSON.stringify(record));
                    return true;
                }
                catch (e) {
                    return false;
                }
            }
            var rules = getData();
            if (rules) {//cache rules
                this.module_rules = rules;
                return;
            }
            var self = this;
            $.ajax({
                type: 'POST',
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tb_load_rules',
                    tb_load_nonce: themifyBuilder.tb_load_nonce
                },
                success: function (resp) {
                    if (resp) {
                        self.module_rules = resp;
                        setData(resp);
                    }
                }
            });
        };

        ThemifyLiveStyling.prototype.setNewRules = function (data) {
            var self = this;
            return $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    data: JSON.stringify(data),
                    action: 'tb_set_newrules',
                    tb_load_nonce: themifyBuilder.tb_load_nonce
                },
                success: function (resp) {
                    if (resp) {
                        for (var bp in resp) {

                            var sheet = self.getSheet(bp),
                                    r = sheet.cssRules ? sheet.cssRules : sheet.rules;
                            for (var i = 0, len = resp[bp].length; i < len; ++i) {
                                resp[bp][i] = $.trim(resp[bp][i]);
                                if (resp[bp][i]) {
                                    sheet.insertRule(resp[bp][i], r.length);
                                }
                            }

                        }
                    }
                }
            });
        };
        /**
         * Apply CSS rules to the live styled element.
         *
         * @param {string} containing CSS rules for the live styled element.
         * @param {mixed) 
         * @param {Array} selectors List of selectors to apply the newStyleObj to (e.g., ['', 'h1', 'h2']).
         */
        ThemifyLiveStyling.prototype.setLiveStyle = function (prop, val, selectors) {
            function findIndex(rules, selector) {
                for (var i = 0, len = rules.length; i < len; ++i) {
                    if (selector === rules[i].selectorText) {
                        return i;
                    }
                }
                return false;
            }
            if (!selectors) {
                selectors = [''];
            }
            var fullSelector = '',
                    tmpProp = prop,
                    rules = this.currentSheet.cssRules ? this.currentSheet.cssRules : this.currentSheet.rules;

            for (var i = 0, len = selectors.length; i < len; ++i) {
                fullSelector += this.prefix + selectors[i];
                if (i !== (len - 1)) {
                    fullSelector += ',';
                }
            }
            if (prop.indexOf('-') !== -1) {
                var temp = prop.toLowerCase().split('-');
                tmpProp = temp[0] + temp[1].charAt(0).toUpperCase() + temp[1].slice(1);
                if (temp[2]) {
                    tmpProp += temp[2].charAt(0).toUpperCase() + temp[2].slice(1);
                }
            }
            fullSelector = fullSelector.replace(/\s{2,}/g, ' ').replace(/\s*>\s*/g, '>');
            var hkey = Themify.hash(fullSelector),
                    orig_v = val,
                    index = this.rulesCache[api.activeBreakPoint][hkey] !== undefined ? this.rulesCache[api.activeBreakPoint][hkey] : findIndex(rules, fullSelector);
            if (val === false) {
                val = '';
            }
            if (index === false || !rules[index]) {
                index = rules.length;
                this.currentSheet.insertRule(fullSelector + '{' + prop + ':' + val + ';}', index);
            }
            else {
                rules[index].style[tmpProp] = val;
            }
            this.rulesCache[api.activeBreakPoint][hkey] = index;
            if (this.tempData[api.activeBreakPoint][index] === undefined) {
                this.tempData[api.activeBreakPoint][index] = [];
            }
            this.tempData[api.activeBreakPoint][index][tmpProp] = val;
            if (orig_v === '') {
                this.removeGenerated(fullSelector, tmpProp, index);
            }
        };


        ThemifyLiveStyling.prototype.initModChange = function () {
            var self = this;
            $('body').on('themify_builder_change_mode', function (e, prevbreakpoint, breakpoint) {
                if (self.isInit) {
                    if (self.tempData[breakpoint] === undefined) {
                        self.tempData[breakpoint] = [];
                    }
                    if (self.rulesCache[breakpoint] === undefined) {
                        self.rulesCache[breakpoint] = [];
                    }
                    self.currentSheet = self.getSheet(breakpoint);
                }
            });
        };

        ThemifyLiveStyling.prototype.revertRules = function () {
            var remembered = this.prevData[api.activeModel.cid] !== undefined ? this.prevData[api.activeModel.cid] : false;
            for (var points in this.tempData) {
                var stylesheet = this.getSheet(points),
                        rules = stylesheet.cssRules ? stylesheet.cssRules : stylesheet.rules;
                for (var i in this.tempData[points]) {
                    if (rules[i]) {
                        for (var j in this.tempData[points][i]) {
                            var v = '';
                            if (remembered && remembered[points] !== undefined && remembered[points][i] !== undefined && remembered[points][i][j] !== undefined) {
                                v = remembered[points][i][j];
                            }
                            rules[i].style[j] = v;
                        }
                    }
                }
            }
            this.tempData = [];
        };

        ThemifyLiveStyling.prototype.removeGenerated = function (fullSelector, prop, key) {
            var cid = api.activeModel.cid;
            if (api.VisualCache[cid] !== undefined) {
                var post_id = this.$liveStyledElmt.closest('.themify_builder_content').data('postid'),
                        rules = document.getElementById('themify-builder-' + post_id + '-generated-css');
                if (rules !== null) {
                    rules = rules.sheet;
                    rules = rules.cssRules ? rules.cssRules : rules.rules;
                    if (this.generatedPrefix[cid] === undefined) {
                        var prefix = '';
                        if (this.type === 'column' && api.activeModel.get('component_name') !== 'sub-column') {
                            var rowCid = this.$liveStyledElmt.closest('.module_row').data('cid');
                            if (api.VisualCache[rowCid] !== undefined) {
                                prefix = api.VisualCache[rowCid].split(' ')[1];
                            }
                        }
                        else if (this.type === 'subrow') {
                            var col = this.$liveStyledElmt.closest('.module_column');
                            if (api.VisualCache[col.data('cid')] !== undefined) {
                                prefix = api.VisualCache[col.data('cid')].split(' ')[0];
                                var rowCid = col.closest('.module_row').data('cid');
                                if (api.VisualCache[rowCid] !== undefined) {
                                    prefix = api.VisualCache[rowCid].split(' ')[1] + ' .' + prefix;
                                }
                                else {
                                    prefix = '';
                                }
                            }
                        }
                        else if (this.type === 'module') {
                            prefix = 'themify_builder';
                        }
                        if (prefix !== '') {
                            prefix = '.' + prefix + ' ';
                        }
                        prefix = prefix + '.' + api.VisualCache[cid].replace(' ', '.');
                        var re = new RegExp(this.prefix, 'ig');
                        this.generatedPrefix[cid] = fullSelector.replace(re, prefix).replace(/\s{2,}/g, ' ').replace(/\s*\,\s*/g, ',').replace(/\s*>\s*/g, '>');

                    }

                }
                else {
                    delete api.VisualCache[cid], this.generatedPrefix[cid];
                    return;
                }
            }
            var self = this;
            function parseMedia(rules, type, w) {
                for (var i = 0, len = rules.length; i < len; ++i) {
                    if (type === rules[i].type) {
                        if ((w !== false && rules[i].conditionText.indexOf(w) !== -1)) {
                            var mrules = rules[i].cssRules ? rules[i].cssRules : rules[i].rules,
                                    j = parseMedia(mrules, CSSRule.STYLE_RULE, false);
                            if (j !== undefined) {
                                return [i, j];
                            }
                        }
                        else if (w === false && self.generatedPrefix[cid] === rules[i].selectorText.replace(/\s{2,}/g, ' ').replace(/\s*\,\s*/g, ',').replace(/\s*>\s*/g, '>')) {
                            return i;
                        }
                    }
                }
            }
            if (this.generatedPrefix[cid] !== undefined) {
                if (this.generatedCache[api.activeBreakPoint] === undefined) {
                    this.generatedCache[api.activeBreakPoint] = [];
                }
                var type = CSSRule.STYLE_RULE,
                        hkey = Themify.hash(this.generatedPrefix[cid]),
                        index = this.generatedCache[api.activeBreakPoint][hkey],
                        w = false;
                if (index === undefined) {
                    if (api.activeBreakPoint !== 'desktop') {
                        w = api.Utils.getBPWidth(api.activeBreakPoint) + 'px';
                        type = CSSRule.MEDIA_RULE;
                    }
                    this.generatedCache[api.activeBreakPoint][hkey] = parseMedia(rules, type, w);
                    index = this.generatedCache[api.activeBreakPoint][hkey];
                }
                if (index !== undefined) {
                    var v = '';
                    if (index[1] !== undefined) {
                        var msheet = rules[index[0]].cssRules ? rules[index[0]].cssRules : rules[index[0]].rules;
                        v = msheet[index[1]].style[prop];
                        msheet[index[1]].style[prop] = '';
                    }
                    else if (api.activeBreakPoint === 'desktop') {
                        v = rules[index].style[prop];
                        rules[index].style[prop] = '';
                    }
                    if (v !== '') {
                        if (this.prevData[cid] === undefined) {
                            this.prevData[cid] = [];
                        }
                        if (this.prevData[cid][api.activeBreakPoint] === undefined) {
                            this.prevData[cid][api.activeBreakPoint] = [];
                        }
                        if (this.prevData[cid][api.activeBreakPoint][key] === undefined) {
                            this.prevData[cid][api.activeBreakPoint][key] = [];
                        }
                        this.prevData[cid][api.activeBreakPoint][key][prop] = v;
                    }
                }

            }
        };

        ThemifyLiveStyling.prototype.remember = function (cid) {
            this.prevData[cid] = $.extend(true, {}, this.tempData);
        };

        ThemifyLiveStyling.prototype.getRememberedStyles = function () {
            return this.tempData;
        };

        ThemifyLiveStyling.prototype.getSheet = function (breakpoint) {
            return  document.getElementById(this.styleName + '-' + breakpoint).sheet;
        };

        ThemifyLiveStyling.prototype.reset = function () {
            var breakpoints = Object.keys(themifyBuilder.breakpoints);
            breakpoints.push('desktop');
            for (var key in breakpoints) {
                $('link#' + this.styleName + '-' + key).remove();
            }
            this.InitInlineStyles();
            this.rulesCache = [];
            this.tempData = [];
            this.prevData = [];
            this.generatedPrefix = [];
            this.generatedCache = [];
        };

        ThemifyLiveStyling.prototype.doUndo = function (styles, is_first) {
            for (var bp in styles) {
                var stylesheet = this.getSheet(bp),
                        rules = stylesheet.cssRules ? stylesheet.cssRules : stylesheet.rules;
                for (var i in styles[bp]) {
                    if (rules[i]) {
                        for (var j in styles[bp][i]) {
                            rules[i].style[j] = is_first ? '' : styles[bp][i][j];
                        }
                    }

                }
            }
        };


        //closing lightbox
        ThemifyLiveStyling.prototype.before_close = function () {
            var self = this;
            $('body').on('themify_builder_lightbox_before_close', function () {
                self.isInit = false;

                if (api.activeModel !== null) {
                    self.$liveStyledElmt.removeClass('animated');
                    if (!api.saving) {
                        self.revertRules();
                        if (self.type !== 'module') {
                            var styling = api.activeModel.get('styling');
                            if (styling['background_type'] === 'slider' && styling['background_slider'] && api.hasChanged) {
                                self.bindBackgroundSlider();
                            }
                        }
                    }
                }
            });
        };

        ThemifyLiveStyling.prototype.bindColors = function () {
            var self = this;
            $('body').on('themify_builder_color_picker_change', function (e, id, el, rgbaString) {
                if (self.isInit) {
                    if (id === 'cover_color' || id === 'cover_color_hover') {
                        self.addOrRemoveComponentOverlay(id, rgbaString);
                    } else {
                        if (el.hasClass('border_color')) {
                            self.bindMultiFields(el);
                            return;
                        }
                        var $data = self.getValue(id);
                        if ($data) {
                            self.setLiveStyle($data.prop, rgbaString, $data.selector);
                        }
                    }
                }
            });
        };


        ThemifyLiveStyling.prototype.overlayType = function ($id, val) {
            var is_color = val === 'color' || val === 'hover_color';
            $id = is_color ? $id.replace('-type', '') : ($id === 'cover_color_hover-type' ? 'cover_gradient_' + val.replace('_', '-') + '-type' : $id.replace('color', 'gradient-gradient'));
            var el = this.$context.find('#' + $id);
            if (is_color) {
                var v = el.val();
                if (v) {
                    if (el.data('minicolors-initialized')) {
                        v = el.minicolors('rgbaString');
                    }
                    else {
                        var opacity = el.data('opacity');
                        if (opacity !== '' && opacity !== undefined && opacity != '1' && opacity != '0.99') {
                            v = api.Utils.toRGBA(v + '_' + opacity);
                        }
                    }

                }
                $('body').trigger('themify_builder_color_picker_change', [$id, el, v]);
            }
            else {
                el.trigger('change');
            }

        };
        ThemifyLiveStyling.prototype.bindBackgroundGradient = function ($id, $val) {
            if (this.isInit) {
                var $data = this.getValue($id);
                if ($data) {
                    if ($id === 'cover_gradient' || $id === 'cover_gradient_hover') {
                        this.setLiveStyle('background-color', false, $data.selector);
                    }
                    this.setLiveStyle($data.prop, $val, $data.selector);
                }
            }
        };
        ThemifyLiveStyling.prototype.addOrRemoveComponentOverlay = function ($id, rgbaString) {
            var $overlayElmt = this.getComponentBgOverlay(),
                    $isset = $overlayElmt.length !== 0;
            if (!$isset) {
                $overlayElmt = $('<div/>', {
                    class: 'builder_row_cover'
                });
            }
            if ($id !== 'cover_gradient' && $id !== 'cover_gradient_hover') {
                var $data = this.getValue($id);
                this.setLiveStyle('background-image', 'none', $data.selector);
                this.setLiveStyle($data.prop, rgbaString, $data.selector);
            }
            else {
                this.bindBackgroundGradient($id, rgbaString)
            }
            if ($isset) {
                return;
            }
            var $elmtToInsertBefore = this.getComponentBgSlider();

            if (!$elmtToInsertBefore.length > 0) {
                var selector = this.type !== 'column' ? '.' + this.type + '_inner' : '.themify_module_holder';
                $elmtToInsertBefore = this.$liveStyledElmt.children(selector);
            }
            $overlayElmt.insertBefore($elmtToInsertBefore);
        };

        ThemifyLiveStyling.prototype.bindMultiFields = function ($this, isTrigger) {
            var self = this;

            function setFullWidth(val, prop) {
                if (!is_border && self.type === 'row' && tbLocalScript.fullwidth_support === '' && ((is_checked && (prop === 'padding' || prop === 'margin')) || prop === 'padding-left' || prop === 'padding-right' || prop === 'margin-left' || prop === 'margin-right')) {
                    var type = prop.split('-'),
                            k = api.activeBreakPoint + '-' + type[0];
                    if (is_checked) {
                        val = val + ',' + val;
                    }
                    else {
                        var old_val = self.$liveStyledElmt.data(k);
                        if (!old_val) {
                            old_val = [];
                        }
                        else {
                            old_val = old_val.split(',');
                        }
                        if (type[1] === 'left') {
                            old_val[0] = val;
                        }
                        else {
                            old_val[1] = val;
                        }
                        val = old_val.join(',');
                    }
                    self.$liveStyledElmt.attr('data-' + k, val).data(k, val);
                    ThemifyBuilderModuleJs.setupFullwidthRows(self.$liveStyledElmt);
                }
            }
            function getCssValue(el) {
                var v = $.trim(el.val());
                if (v !== '') {
                    v = parseFloat(v);
                    if (isNaN(v)) {
                        v = '';
                    }
                    else if (v !== 0) {
                        v += self.$context.find('#' + el.prop('id') + '_unit').val();
                    }
                }
                return v;
            }
            function getBorderValue(el) {
                var parent = el.closest('li'),
                        width = parseFloat($.trim(parent.find('.border_width').val())),
                        style = parent.find('.border_style').val(),
                        v = '',
                        color = parent.find('.minicolors-input');
                var color_val = $.trim(color.val());
                color = color_val && color.data('minicolors-initialized') ? color.minicolors('rgbaString') : color_val;
                if (style === 'none') {
                    v = style;
                }
                else if (isNaN(width) || width === '' || color === '') {
                    v = '';
                }
                else {
                    if (color.indexOf('rgb') === -1 && color.indexOf('#') === -1) {
                        color = '#' + color;
                    }
                    v = width + 'px ' + style + ' ' + color;
                }
                return v;
            }

            var prop_id = $this.prop('id'),
                    $data = self.getValue(prop_id);
            if ($data) {
                var parent = $this.closest('.tb_seperate_items'),
                        prop = $data.prop.split('-')[0],
                        is_border = parent.hasClass('tb_borders'),
                        is_checked = parent.attr('data-checked') === '1',
                        val;
                if (is_checked) {
                    val = is_border ? getBorderValue($this) : getCssValue($this);
                    self.setLiveStyle(prop, val, $data.selector);
                    setFullWidth(val, prop);
                }
                else {
                    if (isTrigger) {
                        self.setLiveStyle(prop, '', $data.selector);
                        parent.find('.tb_multi_field').each(function () {
                            val = is_border ? getBorderValue($(this)) : getCssValue($(this));
                            var prop = self.getValue($(this).prop('id')).prop;
                            self.setLiveStyle(prop, val, $data.selector);
                            setFullWidth(val, prop);
                        });
                    }
                    else {
                        val = is_border ? getBorderValue($this) : getCssValue($this);
                        self.setLiveStyle($data.prop, val, $data.selector);
                        setFullWidth(val, $data.prop);
                    }
                }

            }
        };

        ThemifyLiveStyling.prototype.bindRowWidthHeight = function () {
            var self = this;
            this.$context.on('change', 'input[name="row_height"]', function () {
                var val = $(this).val();
                if (val === 'fullheight') {
                    self.$liveStyledElmt.addClass(val);
                }
                else {
                    self.$liveStyledElmt.removeClass('fullheight');
                }
                $(window).trigger('tfsmartresize.tfVideo');
            })
            .on('change', 'input[name="row_width"]', function () {
                        var val = $(this).val(),
                                builderJs = ThemifyBuilderModuleJs;
                        if (val === 'fullwidth') {
                            self.$liveStyledElmt.removeClass('fullwidth').addClass('fullwidth_row_container');
                            builderJs.setupFullwidthRows(self.$liveStyledElmt);
                        } else if (val === 'fullwidth-content') {
                            self.$liveStyledElmt.removeClass('fullwidth_row_container').addClass('fullwidth');
                            builderJs.setupFullwidthRows(self.$liveStyledElmt);
                        } else {
                            self.$liveStyledElmt.removeClass('fullwidth fullwidth_row_container')
                                    .css({
                                        'margin-left': '',
                                        'margin-right': '',
                                        'padding-left': '',
                                        'padding-right': '',
                                        'width': ''
                                    });
                        }
                        $(window).trigger('tfsmartresize.tfVideo');
                    });
        };
        ThemifyLiveStyling.prototype.bindParralax = function () {
            var self = this;
            if (false && tbLocalScript.isParallaxScrollActive) {
                this.$context.on('change', '.tb_parrallax input,.tb_parrallax select', function () {
                    var container = self.$context.find('#themify_builder_options_animation'),
                            speed = container.find('#custom_parallax_scroll_speed').val();
                    Rellax.destroy(self.$liveStyledElmt[0].dataset.rellaxIndex);
                    if (speed) {
                        if (container.find('#custom_parallax_scroll_reverse_reverse').is(':checked')) {
                            speed = -speed;
                        }
                        fade = container.find('#custom_parallax_scroll_fade_fade').is(':checked') ? 1 : '';
                        self.$liveStyledElmt[0].dataset.rellaxFade = fade;
                        self.$liveStyledElmt.attr({'data-rellax-fade': fade, 'data-rellax-speed': speed});
                        ThemifyBuilderModuleJs.parallaxScrollingInit(self.$liveStyledElmt, true);
                    }
                });
            }
            this.$context.on('keyup', '#custom_parallax_scroll_zindex', function () {
                var zindex = parseInt($(this).val());
                if (isNaN(zindex)) {
                    zindex = '';
                }
                self.$liveStyledElmt[0].style['zIndex'] = zindex;
            });
        };

        ThemifyLiveStyling.prototype.bindAnimation = function () {
            var self = this;
            this.$context.on('change', '#animation_effect,#animation_effect_delay,#animation_effect_repeat', function () {
                var animationEffect = self.getStylingVal('animation_effect');
                if (animationEffect) {
                    self.$liveStyledElmt.removeClass(animationEffect + ' wow animated').css({'animation-name': '', 'animation-delay': '', 'animation-iteration-count': ''});
                }
                var effect = self.$context.find('#animation_effect').val();
                self.setStylingVal('animation_effect', effect);
                if (effect) {
                    var delay = parseFloat(self.$context.find('#animation_effect_delay').val()),
                            repeat = parseInt(self.$context.find('#animation_effect_repeat').val());
                    self.$liveStyledElmt.css({'animation-delay': delay > 0 && !isNaN(delay) ? delay + 's' : '', 'animation-iteration-count': repeat > 0 && !isNaN(repeat) ? repeat : ''});
                    setTimeout(function () {
                        self.$liveStyledElmt.addClass(effect + ' animated');
                    }, 1);
                }
            });
        };

        ThemifyLiveStyling.prototype.bindAdditionalCSSClass = function () {
            var self = this;
            this.$context.on('keyup', '#custom_css_row, #custom_css_column', function () {
                var id = this.id,
                        className = self.getStylingVal(id);

                self.$liveStyledElmt.removeClass(className);

                className = $(this).val();

                self.setStylingVal(id, className);
                self.$liveStyledElmt.addClass(className);
            });
        };

        ThemifyLiveStyling.prototype.bindRowAnchor = function () {
            var self = this;
            this.$context.on('keyup', '#row_anchor', function () {
                var rowAnchor = self.getStylingVal('row_anchor');
                self.$liveStyledElmt.removeClass(self.getRowAnchorClass(rowAnchor));
                rowAnchor = $.trim($(this).val());
                self.setStylingVal('row_anchor', rowAnchor);
                if (rowAnchor !== '') {
                    self.$liveStyledElmt.addClass(self.getRowAnchorClass(rowAnchor));
                }
            });
        };

        ThemifyLiveStyling.prototype.getRowAnchorClass = function (rowAnchor) {
            return rowAnchor.length > 0 ? 'tb_section-' + rowAnchor : '';
        };

        ThemifyLiveStyling.prototype.getStylingVal = function (stylingKey) {
            return this.currentStyleObj[stylingKey] !== undefined ? this.currentStyleObj[stylingKey] : '';
        };

        ThemifyLiveStyling.prototype.setStylingVal = function (stylingKey, val) {
            this.currentStyleObj[stylingKey] = val;
        };

        ThemifyLiveStyling.prototype.bindBackgroundMode = function (val) {
            var previousVal = this.getStylingVal('background_repeat');
            if (val.length > 0) {
                if (previousVal.length > 0) {
                    this.$liveStyledElmt.removeClass(previousVal);
                }
                this.setStylingVal('background_repeat', val);
                this.$liveStyledElmt.addClass(val);
            } else {
                this.$liveStyledElmt.removeClass(previousVal);
            }
            this.$liveStyledElmt.css({'background-size': '', 'background-position': ''});
        };

        ThemifyLiveStyling.prototype.bindBackgroundPosition = function (val) {
            var previousVal = this.getStylingVal('background_position');

            if (val && val.length > 0) {
                this.setStylingVal('background_position', val);

                var $data = this.getValue('background_position');
                if ($data) {
                    this.setLiveStyle($data.prop, val.replace('-', ' '), $data.selector);
                }
            }
        };

        ThemifyLiveStyling.prototype.bindBackgroundSlider = function () {
            var self = this,
                    images = $.trim(self.$context.find('#background_slider').val());
            self.removeBgSlider();

            function callback(slider) {
                var $bgSlider = $(slider),
                        bgCover = self.getComponentBgOverlay();
                if (bgCover.length > 0) {
                    bgCover.after($bgSlider);
                } else {
                    if (self.type === 'row') {
                        self.$liveStyledElmt.children('.themify_builder_row_top').after($bgSlider);
                    } else {
                        self.$liveStyledElmt.prepend($bgSlider);
                    }
                }
                ThemifyBuilderModuleJs.backgroundSlider($($bgSlider[0]));
            }
            if (images) {

                if (self.cahce === undefined) {
                    self.cahce = {};
                }

                var options = {
                    shortcode: encodeURIComponent(images),
                    mode: self.$context.find('#background_slider_mode').val(),
                    size: self.$context.find('#background_slider_size').val()
                },
                hkey = '';
                for (var i in options) {
                    hkey += Themify.hash(i + options[i]);
                }
                if (this.cahce[hkey] !== undefined) {
                    callback(this.cahce[hkey]);
                    return;
                }
                options['type'] = self.type;
                options['order'] = api.activeModel.cid;

                $.post(
                        themifyBuilder.ajaxurl,
                        {
                            nonce: themifyBuilder.tb_load_nonce,
                            action: 'tb_slider_live_styling',
                            tb_background_slider_data: options
                        },
                function (slider) {
                    if (slider.length < 10) {
                        return;
                    }
                    self.cahce[hkey] = slider;
                    callback(slider);
                }
                );
            }
        };
        ThemifyLiveStyling.prototype.VideoOptions = function () {
            var self = this;
            this.$context.on('change', 'input[name="background_video_options"]', function () {
                var video = self.$liveStyledElmt.find('.big-video-wrap').first(),
                        el = '',
                        val = $(this).val(),
                        is_checked = $(this).is(':checked'),
                        type = '';
                if (video.hasClass('themify_ytb_wrapper')) {
                    el = self.$liveStyledElmt;
                    type = 'ytb';
                }
                else if (video.hasClass('themify-video-vmieo')) {
                    el = $f(video.children('iframe')[0]);
                    if (el) {
                        type = 'vimeo';
                    }
                }
                else {
                    el = self.$liveStyledElmt.data('plugin_ThemifyBgVideo');
                    type = 'local';
                }

                if (val === 'mute') {
                    if (is_checked) {
                        if (type === 'ytb') {
                            el.ThemifyYTBMute();
                        }
                        else if (type === 'vimeo') {
                            el.api('setVolume', 0);
                        }
                        else if (type === 'local') {
                            el.muted(true);
                        }
                        self.$liveStyledElmt.data('mutevideo', 'mute');
                    }
                    else {
                        if (type === 'ytb') {
                            el.ThemifyYTBUnmute();
                        }
                        else if (type === 'vimeo') {
                            el.api('setVolume', 1);
                        }
                        else if (type === 'local') {
                            el.muted(false);
                        }
                        self.$liveStyledElmt.data('mutevideo', '');
                    }
                }
                else if (val === 'unloop') {
                    if (is_checked) {
                        if (type === 'vimeo') {
                            el.api('setLoop', 0);
                        }
                        else if (type === 'local') {
                            el.loop(false);
                        }
                        self.$liveStyledElmt.data('unloopvideo', '');
                    }
                    else {
                        if (type === 'vimeo') {
                            el.api('setLoop', 1);
                        }
                        else if (type === 'local') {
                            el.loop(true);
                        }
                        self.$liveStyledElmt.data('unloopvideo', 'loop');

                    }
                }
            });

        };
        ThemifyLiveStyling.prototype.bindBackgroundTypeRadio = function (bgType) {
            if (bgType === 'image' || bgType === 'gradient') {
                this.removeBgSlider();
                this.removeBgVideo();
                if (bgType === 'image') {
                    this.setLiveStyle('background-image', 'none');
                }
                else {
                    bgType = this.type === 'module' ? 'image-gradient-angle' : bgType + '-gradient-angle';
                }
            }
            else if (bgType === 'video') {
                this.removeBgSlider();
            } else {
                // remove bg image
                this.removeBgVideo();
            }
            this.$context.find('#background_' + bgType).trigger('change');
        };

        ThemifyLiveStyling.prototype.setData = function ($id, prop, $val) {
            var $data = this.getValue($id);
            if ($data) {
                if (prop === '') {
                    prop = $data.prop;
                }
                this.setLiveStyle(prop, $val, $data.selector);
            }
        };

        ThemifyLiveStyling.prototype.bindChangesEvent = function () {
            var self = this;
            self.$context.on('change', self.style_tab + ' select,' + self.style_tab + ' textarea,' + self.style_tab + ' input.themify-builder-uploader-input,' + self.style_tab + ' input[type="radio"]', function (e) {

                if (!self.isInit || $(this).hasClass('minicolors-input') || $(this).hasClass('color_opacity') || $(this).hasClass('themify-gradient-type')) {
                    return;
                }
                var $val = $.trim($(this).val()),
                        is_radio = $(this).is(':radio'),
                        is_select = !is_radio && $(this).is('select'),
                        $id = !is_select && is_radio ? $(this).parent('.tb_lb_option').prop('id') : $(this).prop('id');
                if (is_select) {
                    if ($(this).hasClass('font-family-select')) {
                        if ($val !== 'default' && ThemifyBuilderCommon.safe_fonts[$val] === undefined) {
                            ThemifyBuilderCommon.loadGoogleFonts([$val.split(' ').join('+') + ':400,700:latin,latin-ext']);
                        } else if ($val === 'default') {
                            $val = '';
                        }
                    }
                    else if ($(this).hasClass('tb_unit')) {
                        self.$context.find('#' + $id.replace('_unit', '')).trigger('keyup');
                    }
                    else if ($(this).hasClass('border_style')) {
                        self.bindMultiFields($(this), e.isTrigger);
                        return;
                    }
                    else if ($id === 'background_repeat') {
                        self.bindBackgroundMode($val);
                        return;
                    }
                    else if ($id === 'background_position') {
                        self.bindBackgroundPosition($val);
                        return;
                    }
                    else if (self.type !== 'module' && ($id === 'background_slider_size' || $id === 'background_slider_mode')) {
                        self.bindBackgroundSlider();
                        return;
                    }
                }
                else if (is_radio) {
                    if (!$(this).is(':checked')) {
                        $val = '';
                    }
                    if ($id === 'background_type' || $id === 'background_image-type') {
                        self.bindBackgroundTypeRadio($val);
                        return;
                    }
                    else if ($id === 'cover_color-type' || $id === 'cover_color_hover-type') {
                        self.overlayType($id, $val);
                        return;
                    }
                }
                else if ($(this).hasClass('themify-builder-uploader-input')) {
                    if ($id === 'background_video') {
                        if ($val.length > 0) {
                            self.$liveStyledElmt.data('fullwidthvideo', $val);
                            if (_.isEmpty(self.$liveStyledElmt.data('mutevideo')) && self.$context.find('#background_video_options_mute').is(':checked')) {
                                self.$liveStyledElmt.data('mutevideo', 'mute');
                            }
                            ThemifyBuilderModuleJs.fullwidthVideo(self.$liveStyledElmt);
                        } else {
                            self.removeBgVideo();
                        }
                        return false;
                    }
                    else {
                        $val = $val ? 'url(' + $val + ')' : 'none';
                    }
                }
                else if (self.type !== 'module' && $id === 'background_slider') {
                    self.bindBackgroundSlider();
                    return;
                }
                self.setData($id, '', $val);

            }).on('keyup', self.style_tab + ' input[type="text"]', function (e) {
                if ($(this).hasClass('minicolors-input') || $(this).hasClass('color_opacity')) {
                    return;
                }
                var $val = $.trim($(this).val()),
                        $id = $(this).prop('id');
                if ($(this).hasClass('border_width') || $(this).hasClass('tb_multi_field')) {
                    self.bindMultiFields($(this), e.isTrigger);
                    return;
                } else if ($(this).is('.column_gap, .column_rule_width')) {
                    $val += 'px';
                } else if ($val) {
                    var unit = self.$context.find(self.style_tab + ' #' + $id + '_unit');
                    if (unit.length > 0) {
                        $val += unit.val() ? unit.val() : 'px';
                    }
                }
                self.setData($id, '', $val);
            });
        };

        ThemifyLiveStyling.prototype.getValue = function ($id) {
            if (this.isInit) {
                return this.module_rules[this.group] !== undefined && this.module_rules[this.group][$id] !== undefined ? this.module_rules[this.group][$id] : false;
            }
            return false;
        };


        ThemifyLiveStyling.prototype.bindLightboxForm = function () {
            // "Styling" tab live styling
            this.bindChangesEvent();
            this.bindColors();
            this.bindAnimation();
            this.VideoOptions();
            this.bindRowAnchor();
            this.bindRowWidthHeight();
            this.bindParralax();
            this.bindAdditionalCSSClass();
        };


        /**
         * Returns component's background cover element wrapped in jQuery.
         *
         * @param {jQuery} $component
         * @returns {jQuery}
         */
        ThemifyLiveStyling.prototype.getComponentBgOverlay = function () {
            return this.$liveStyledElmt.children('.builder_row_cover');
        };

        /**
         * Returns component's background slider element wrapped in jQuery.
         *
         * @param {jQuery} $component
         * @returns {jQuery}
         */
        ThemifyLiveStyling.prototype.getComponentBgSlider = function () {
            var type = this.type === 'colum' && api.activeModel.get('component_name') === 'sub-column' ? 'sub-col' : (this.type === 'colum' ? 'col' : this.type);
            return this.$liveStyledElmt.children('.' + type + '-slider');
        };

        /**
         * Removes background slider if there is any in $component.
         *
         * @param {jQuery} $component
         */
        ThemifyLiveStyling.prototype.removeBgSlider = function () {
            this.getComponentBgSlider().add(this.$liveStyledElmt.children('.tb_backstretch')).remove();
            this.$liveStyledElmt.css({
                'position': '',
                'background': '',
                'z-index': ''
            });
        };




        /**
         * Removes background video if there is any in $component.
         *
         * @param {jQuery} $component
         */
        ThemifyLiveStyling.prototype.removeBgVideo = function () {
            this.$liveStyledElmt.removeAttr('data-fullwidthvideo').data('fullwidthvideo', '').children('.big-video-wrap').remove();
        };

        return ThemifyLiveStyling;
    })(jQuery);

})(jQuery);
