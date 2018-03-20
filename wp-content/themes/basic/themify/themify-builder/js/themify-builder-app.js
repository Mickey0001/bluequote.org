window.themifybuilderapp = window.themifybuilderapp || {};
(function ($) {

    'use strict';


    // extend jquery-ui sortable with beforeStart event
    var oldMouseStart = $.ui.sortable.prototype._mouseStart,
            is_fullSection = $('body').hasClass('full-section-scrolling');
    $.ui.sortable.prototype._mouseStart = function (event, overrideHandle, noActivation) {
        this._trigger('beforeStart', event, this._uiHash());
        oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
    };

    // Serialize Object Function
    if (undefined === $.fn.themifySerializeObject) {
        $.fn.themifySerializeObject = function () {
            var o = {};
            for (var i = 0, len = this.length; i < len; ++i) {
                var type = this[i].type;
                if ($(this[i]).hasClass('wp-editor-area') && tinyMCE !== undefined) {
                    var tiny = tinyMCE.get(this[i].id);
                    if (tiny) {
                        this[i].value = tiny.getContent();
                    }
                }
                if (this[i].value !== '' && this[i].name && (type === 'text' || type === 'radio' || type === 'checkbox' || type === 'textarea' || type === 'select-one' || type === 'hidden' || type === 'email' || type === 'select' || type === 'select-multiple' )) {
                    var name = this[i].name,
                            val = this[i].value;
                    //jQuery returns all selected values for select elements with multi option on
                    if( type === 'select-multiple' ) val = jQuery( this[i] ).val();

                    if (type === 'radio' || type === 'checkbox') {
                        val = this[i].checked && val;
                    }

                    if (o[name] !== undefined) {
                        !o[name].push && (o[name] = [o[name]]);
                        val && o[name].push(val);
                    } else {
                        val && (o[name] = val);
                    }
                }
            }
            return o;
        };
    }

    var api = themifybuilderapp = {
        activeModel: null,
        Models: {},
        Collections: {},
        Mixins: {},
        Views: {Modules: {}, Rows: {}, SubRows: {}, Columns: {}, Controls: {}},
        Forms: {},
        Utils: {},
        Instances: {Builder: {}},
        cache: {repeaterElements: {}}
    };
    var tempSettings = [];
    api.autoSaveCid = null;
    api.hasChanged = null;
    api.editing = false;
    api.init = false;
    api.scrollTo = false;
    api.eventName = false;
    api.beforeEvent = false;
    api.saving = false;
    api.rowStyling = [];
    api.saveCid = false;//for predessinged row styling
    api.activeBreakPoint = 'desktop';
    api.zoomMeta = {isActive: false, size: 100};
    api.isPreview = false;
    api.isComponentSaved = [];// for undo/redo change styling to detect the first saving
    api.Models.Module = Backbone.Model.extend({
        defaults: {
            elType: 'module',
            mod_name: '',
            mod_settings: {}
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        toRenderData: function () {
            return {
                slug: this.get('mod_name'),
                name: this.get('mod_name'),
                excerpt: this.getExcerpt()
            }
        },
        getExcerpt: function (settings) {
            var setting = settings || this.get('mod_settings'),
                    excerpt = setting.content_text || setting.content_box || setting.plain_text || '';
            return this.limitString(excerpt, 100);
        },
        limitString: function (str, limit) {
            var new_str = '';
            if (str !== '') {
                str = this.stripHtml(str).toString(); // strip html tags
                new_str = str.length > limit ? str.substr(0, limit) : str;
            }
            return new_str;
        },
        stripHtml: function (html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        },
        setData: function (data) {
            var model = api.Views.init_module(data, api.mode);
            model.model.trigger('custom:change', model);
        },
        backendLivePreview: function () {
            $('.tb_element_cid_' + this.cid).find('.module_excerpt').text(this.getExcerpt(tempSettings));
        },
        // for instant live preview
        getPreviewSettings: function () {
            return _.extend({cid: this.cid}, themifyBuilder.modules[ this.get('mod_name') ].defaults, tempSettings);
        }
    });

    api.Models.SubRow = Backbone.Model.extend({
        defaults: {
            elType: 'subrow',
            row_order: 0,
            gutter: 'gutter-default',
            column_alignment: 'col_align_top',
            background_video: '',
            mutevideo: '',
            unloopvideo: '',
            desktop_dir: 'ltr',
            tablet_dir: 'ltr',
			tablet_landscape_dir: 'ltr',
            mobile_dir: 'ltr',
            col_mobile: '-auto',
            col_tablet_landscape: '-auto',
            col_tablet: '-auto',
            cols: {},
            styling: {}
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            var model = api.Views.init_subrow(data, api.mode);
            model.model.trigger('custom:change', model);
        }
    });

    api.Models.Column = Backbone.Model.extend({
        defaults: {
            elType: 'column',
            column_order: '',
            grid_class: '',
            component_name: 'column',
            background_video: '',
            mutevideo: '',
            unloopvideo: '',
            modules: {},
            styling: {}
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            var model = api.Views.init_column(data, api.mode);
            model.model.trigger('custom:change', model);
        }
    });

    api.Models.Row = Backbone.Model.extend({
        defaults: {
            elType: 'row',
            row_order: 0,
            gutter: 'gutter-default',
            column_alignment: is_fullSection ? 'col_align_middle' : 'col_align_top',
            desktop_dir: 'ltr',
            tablet_dir: 'ltr',
			tablet_landscape_dir: 'ltr',
            mobile_dir: 'ltr',
            col_mobile: '-auto',
            col_tablet_landscape: '-auto',
            col_tablet: '-auto',
            background_video: '',
            mutevideo: '',
            unloopvideo: '',
            cols: {},
            styling: {}
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            var model = api.Views.init_row(data, api.mode);
            model.model.trigger('custom:change', model);
        }
    });

    api.Collections.Rows = Backbone.Collection.extend({
        model: api.Models.Row
    });

    api.Models.Registry = {
        items: {},
        register: function (id, object) {
            this.items[id] = object;
        },
        lookup: function (id) {
            return this.items[id] || null;
        },
        remove: function (id) {
            delete this.items[id];
        },
        destroy: function () {
            _.each(this.items, function (model, cid) {
                model.destroy();
            });
            this.items = {};
            console.log('destroy registry');
        }
    };

    api.Models.setValue = function (cid, data, silent) {
        silent = silent || false;
        var model = api.Models.Registry.lookup(cid);
        model.set(data, {silent: silent});
    };

    api.vent = _.extend({}, Backbone.Events);

    api.Views.register_module = function (type, args) {

        if ('default' !== type) {
            this.Modules[ type ] = this.Modules.default.extend(args);
        }

    };

    api.Views.init_module = function (args, type) {
        if (themifyBuilder.modules[args.mod_name] === undefined) {
            return false;
        }
        type = type || 'default';
        if (args.mod_settings === undefined && themifyBuilder.modules[ args.mod_name ].defaults !== undefined) {
            args.mod_settings = _.extend({}, themifyBuilder.modules[ args.mod_name ].defaults);
        }

        var model = args instanceof api.Models.Module ? args : new api.Models.Module(args),
                callback = this.get_module(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    };

    api.Views.get_module = function (type) {
        type = type || 'default';
        if (this.module_exists(type))
            return this.Modules[ type ];

        return this.Modules.default;
    };

    api.Views.unregister_module = function (type) {

        if ('default' !== type && this.module_exists(type))
            delete this.Modules[ type ];
    };

    api.Views.module_exists = function (type) {

        return this.Modules.hasOwnProperty(type);
    };

    // column
    api.Views.register_column = function (type, args) {

        if ('default' !== type)
            this.Columns[ type ] = this.Columns.default.extend(args);
    };

    api.Views.init_column = function (args, type) {
        type = type || 'default';
        var model = args instanceof api.Models.Column ? args : new api.Models.Column(args),
                callback = this.get_column(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    };

    api.Views.get_column = function (type) {
        type = type || 'default';
        if (this.column_exists(type))
            return this.Columns[ type ];

        return this.Columns.default;
    };

    api.Views.unregister_column = function (type) {

        if ('default' !== type && this.column_exists(type))
            delete this.Columns[ type ];
    };

    api.Views.column_exists = function (type) {

        return this.Columns.hasOwnProperty(type);
    };

    // sub-row
    api.Views.register_subrow = function (type, args) {

        if ('default' !== type)
            this.SubRows[ type ] = this.SubRows.default.extend(args);
    };

    api.Views.init_subrow = function (args, type) {
        type = type || 'default';
        var model = args instanceof api.Models.SubRow ? args : new api.Models.SubRow(args),
                callback = this.get_subrow(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    };

    api.Views.get_subrow = function (type) {
        type = type || 'default';
        if (this.subrow_exists(type))
            return this.SubRows[ type ];

        return this.SubRows.default;
    };

    api.Views.unregister_subrow = function (type) {

        if ('default' !== type && this.subrow_exists(type))
            delete this.SubRows[ type ];
    };

    api.Views.subrow_exists = function (type) {

        return this.SubRows.hasOwnProperty(type);
    };

    // Row
    api.Views.register_row = function (type, args) {

        if ('default' !== type)
            this.Rows[ type ] = this.Rows.default.extend(args);
    };

    api.Views.init_row = function (args, type) {
        var attr = args.attributes;
        if (attr === undefined || ((attr.cols !== undefined && (Object.keys(attr.cols) > 0 || attr.cols.length > 0)) || (attr.styling !== undefined && Object.keys(attr.styling).length > 0))) {
            type = type || 'default';
            var model = args instanceof api.Models.Row ? args : new api.Models.Row(args),
                    callback = this.get_row(type),
                    view = new callback({model: model, type: type});

            return {
                model: model,
                view: view
            };
        }
        else {
            return false;
        }
    };

    api.Views.get_row = function (type) {
        type = type || 'default';
        if (this.row_exists(type))
            return this.Rows[ type ];

        return this.Rows.default;
    };

    api.Views.unregister_row = function (type) {

        if ('default' !== type && this.row_exists(type))
            delete this.Rows[ type ];
    };

    api.Views.row_exists = function (type) {

        return this.Rows.hasOwnProperty(type);
    };

    api.Views.BaseElement = Backbone.View.extend({
        type: 'default',
        events: {
            'click .themify_builder_copy_component': 'copy',
            'click .themify_builder_paste_component': 'paste',
            'click .themify_builder_import_component': 'import',
            'click .themify_builder_export_component': 'export',
            'click .themify_duplicate': 'duplicate',
            'click .themify_delete': 'delete'
        },
        initialize: function (options) {
            _.extend(this, _.pick(options, 'type'));

            this.listenTo(this.model, 'custom:change', this.modelChange);
            this.listenTo(this.model, 'destroy', this.remove);
        },
        modelChange: function () {

            this.$el.attr(_.extend({}, _.result(this, 'attributes')));
            var el = this.render(),
                    type = this.model.get('elType'),
                    cid = api.beforeEvent.data('cid');
            $('.tb_element_cid_' + cid).replaceWith(el.el);
            if (api.mode === 'visual') {
                if (type !== 'subrow') {
                    api.Mixins.Builder.initGridMenu(el.el, type !== 'module');
                }
                this.model.trigger('visual:change');
            }
            else {
                if (api.eventName === 'row') {
                    cid = this.$el.data('cid');
                }
                api.vent.trigger('dom:change', cid, api.beforeEvent, this.$el, api.eventName);
                api.Mixins.Builder.update(this.$el);
                if (api.eventName === 'row') {
                    api.vent.trigger('dom:builder:change');
                }
            }
        },
        remove: function () {
            this.$el.remove();
        },
        copy: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(e.currentTarget),
                    $selected,
                    component = ThemifyBuilderCommon.detectBuilderComponent($this);

            switch (component) {
                case 'row':
                case 'subrow':
                    $selected = $this.closest('.module_' + component);
                    break;

                case 'module':
                    $selected = $this.closest('.active_module');
                    break;

                case 'column':
                case 'sub-column':
                    $selected = $this.closest('.module_column');
                    break;
            }
            var data = this.getData($selected, component);
            if (component === 'sub-column') {
                data['component_name'] = component;
            }
            ThemifyBuilderCommon.Clipboard.set(component, data);
        },
        paste: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $el = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($el),
                    data = ThemifyBuilderCommon.Clipboard.get(component);
            if (data === false) {
                ThemifyBuilderCommon.alertWrongPaste();
                return;
            }
            if (!ThemifyBuilderCommon.confirmDataPaste()) {
                return;
            }
            var current = $el.closest('[data-cid]'),
                    model = api.Models.Registry.lookup(current.data('cid'));
            api.eventName = 'row';
            if (component === 'column' || component === 'sub-column') {
                data['grid_class'] = api.Utils.filterClass(current.prop('class'));
                if (current.hasClass('first')) {
                    data['grid_class'] += ' first';
                }
                else if (current.hasClass('last')) {
                    data['grid_class'] += ' last';
                }
                var width = current[0].style['width'];
                if (width) {
                    data['grid_width'] = width.replace('%', '');
                }
                else {
                    delete data['grid_width'];
                }
            }
            api.beforeEvent = ThemifyBuilderCommon.Lightbox.clone(current);
            model.setData(data);
        },
        import: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $thisElem = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem),
                    el = $thisElem.closest('[data-cid]'),
                    model = api.Models.Registry.lookup(el.data('cid')),
                    options = {
                        data: {
                            action: 'tb_component_data',
                            component: component,
                            type: 'import'
                        }
                    };
            if (component === 'column' || component === 'sub-column') {
                var $selectedCol = $thisElem.closest('.module_column'),
                        $selectedRow = $selectedCol.closest('column' === component ? '.module_row' : '.module_subrow').index();
                options.data.indexData = {row: $selectedRow, col: $selectedCol.index()};
            }
            ThemifyBuilderCommon.Lightbox.open(options, null, function () {
                var $lightbox = this.$lightbox;
                $lightbox.find('#builder_submit_import_component_form').on('click', function (e) {
                    e.preventDefault();
                    var $dataField = $lightbox.find('#tb_data_field'),
                            dataPlainObject = JSON.parse($dataField.val());
                    if ((component === 'column' && dataPlainObject['component_name'] === 'sub-column') || (component === 'sub-column' && dataPlainObject['component_name'] === 'column')) {
                        dataPlainObject['component_name'] = component;
                    }
                    if (dataPlainObject['component_name'] === undefined || dataPlainObject['component_name'] !== component) {
                        ThemifyBuilderCommon.alertWrongPaste();
                        return;
                    }
                    dataPlainObject = api.Utils.clear(dataPlainObject, true);
                    if (component === 'column' || component === 'sub-column') {
                        dataPlainObject['column_order'] = $selectedCol.index();
                        dataPlainObject['grid_class'] = $selectedCol.prop('class');

                        if ('column' === component) {
                            dataPlainObject['row_order'] = $selectedRow;
                        } else {
                            dataPlainObject['sub_row_order'] = $selectedRow;
                            dataPlainObject['row_order'] = $selectedCol.closest('.module_row').index();
                            dataPlainObject['col_order'] = $selectedCol.parents('.module_column').index();
                        }
                    }
                    api.eventName = 'row';
                    api.beforeEvent = ThemifyBuilderCommon.Lightbox.clone(el);
                    model.setData(dataPlainObject);
                    ThemifyBuilderCommon.Lightbox.close();
                });
            });
        },
        getData: function (el, component) {
            var data = {},
                    type = component || ThemifyBuilderCommon.detectBuilderComponent(el);
            switch (type) {
                case 'row':
                case 'subrow':
                    var $selectedRow = el.closest('.module_' + type),
                            rowOrder = $selectedRow.index();
                    data = api.Utils._getRowSettings($selectedRow[0], rowOrder, type);
                    break;
                case 'module':
                    data = api.Models.Registry.lookup(el.closest('.active_module').data('cid')).attributes;
                    data = api.Utils.clear(data, true);
                    break;
                case 'column':
                case 'sub-column':
                    var $selectedCol = el.closest('.module_column'),
                            $selectedRow = $selectedCol.closest('column' === type ? '.module_row' : '.module_subrow'),
                            rowOrder = $selectedRow.index(),
                            rowData = api.Utils._getRowSettings($selectedRow[0], rowOrder, 'column' === type ? 'row' : 'subrow'),
                            data = rowData.cols[ $selectedCol.index() ];
                    break;
            }
            return data;
        },
        export: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $thisElem = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem),
                    data = this.getData($thisElem, component),
                    options = {
                        data: {
                            action: 'tb_component_data',
                            component: component,
                            type: 'export'
                        }
                    };

            data['component_name'] = component;
            data = JSON.stringify(data);
            ThemifyBuilderCommon.Lightbox.open(options, null, function () {
                this.$lightbox.find('#tb_data_field').val(data).on('click', function () {
                    $(this).trigger('focus').trigger('select');
                });
            });
        },
        duplicate: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var current = $(e.currentTarget).closest('[data-cid]'),
                    el = ThemifyBuilderCommon.Lightbox.clone(current),
                    model = api.Models.Registry.lookup(el.data('cid'));
            current.removeClass('tb_element_cid_' + model.cid);
            el.hide().insertAfter(current);

            var data = this.getData(el, model.get('elType'));
            api.eventName = 'duplicate';
            api.beforeEvent = el;
            model.setData(data);
            current.addClass('tb_element_cid_' + model.cid);
        },
        editComponent: function () {
            api.hasChanged = false;
            if (api.autoSaveCid === api.activeModel.cid) {
                return;
            }
            var component = api.activeModel.get('elType'),
                    template = component === 'module' ? api.activeModel.get('mod_name') : component;
            ThemifyBuilderCommon.Lightbox.open({loadMethod: 'inline', templateID: 'builder_form_' + template}, function (response) {
                api.Mixins.Common.editComponentCallback(response, component, false, false);
            }, function (response) {
                var lightbox = ThemifyBuilderCommon.Lightbox.$lightbox;
                if (api.activeModel.get('styleClicked')) {
                    lightbox.find('a[href="#themify_builder_options_styling"]').trigger('click');
                }
                else if (api.activeModel.get('visibileClicked')) {
                    lightbox.find('a[href="#themify_builder_options_visibility"]').trigger('click');
                }
                else {
                    var scroll = component === 'column' || component === 'subrow' ? 'themify_builder_options_styling' : 'themify_builder_options_setting';
                    new SimpleBar(lightbox.find('#' + scroll)[0]);
                }
                api.autoSaveCid = api.activeModel.cid;
            });
        },
        delete: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var current = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent(current);
            if (!confirm(themifyBuilder.i18n[component + 'DeleteConfirm'])) {
                return;
            }
            var item = current.closest('[data-cid]'),
                    cid = item.data('cid'),
                    model = api.Models.Registry.lookup(cid);
            if (model) {
                var before = item.closest('.module_row'),
                        type = 'row',
                        after = '',
                        data = {};
                if (model.get('elType') === 'row') {
                    data['pos_cid'] = before.next('.module_row');
                    data['pos'] = 'before';
                    if (data['pos_cid'].length === 0) {
                        data['pos'] = 'after';
                        data['pos_cid'] = before.prev('.module_row');
                    }
                    type = 'delete_row';
                    data['pos_cid'] = data['pos_cid'].data('cid');
                }
                else {
                    cid = before.data('cid');
                }
                before = ThemifyBuilderCommon.Lightbox.clone(before);
                model.destroy();
                if (model.get('elType') !== 'row') {
                    after = $('.tb_element_cid_' + cid);
                }
                else {
                    api.vent.trigger('dom:builder:change');
                }
                api.vent.trigger('dom:change', cid, before, after, type, data);
            }
        }

    });

    api.Views.BaseElement.extend = function (child) {
        var self = this,
                view = Backbone.View.extend.apply(this, arguments);
        view.prototype.events = _.extend({}, this.prototype.events, child.events);
        view.prototype.initialize = function () {
            if (_.isFunction(self.prototype.initialize))
                self.prototype.initialize.apply(this, arguments);
            if (_.isFunction(child.initialize))
                child.initialize.apply(this, arguments);
        }
        return view;
    };

    api.Views.Modules['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            return {
                'class': 'themify_builder_module module-' + this.model.get('mod_name') + ' active_module tb_element_cid_' + this.model.cid,
                'data-cid': this.model.cid
            };
        },
        template: wp.template('builder_module_item'),
        events: {
            'dblclick': 'edit',
            'click .themify_module_options': 'edit',
            'click .themify_builder_module_styling': 'edit',
            'click .tb_visibility_component ': 'edit'
        },
        initialize: function () {
            this.listenTo(this, 'edit', this.edit);
            this.listenTo(this.model, 'dom:module:unsaved', this.removeUnsaved);
            this.listenTo(this.model, 'change:view', this.setView);

        },
        removeUnsaved: function () {
            this.model.destroy();
        },
        render: function () {
            this.el.innerHTML = this.template(this.model.toRenderData());
            return this;
        },
        setView: function (node) {
            this.setElement(node);
        },
        edit: function (e) {
            if (api.isPreview)
                return true;
            if (e !== null) {
                e.preventDefault();
                e.stopPropagation();
                if (e.currentTarget.classList.contains('themify_builder_module_styling')) {
                    this.model.set({styleClicked: true}, {silent: true});
                }
                else if (e.currentTarget.classList.contains('tb_visibility_component')) {
                    this.model.set({visibileClicked: true}, {silent: true});
                }
            }

            // remove is_new from prev model before set new activeModel
            if (api.activeModel && api.activeModel.get('is_new') !== undefined) {
                api.activeModel.unset('is_new', {silent: true});
            }

            if (this.model.cid !== api.autoSaveCid && api.autoSaveCid !== null) {
                api.Forms.saveComponent(null);
            }
            api.activeModel = this.model;
            this.editComponent();
        }
    });

    api.Views.Columns['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            var classes = 'column' === this.model.get('component_name') ? '' : ' sub_column',
                    attr = {
                        'class': 'module_column tb-column tb_element_cid_' + this.model.cid + ' ' + this.model.get('grid_class') + classes,
                        'data-cid': this.model.cid
                    };
            if (this.model.get('grid_width')) {
                attr['style'] = 'width:' + this.model.get('grid_width') + '%';
            }
            return attr;
        },
        template: wp.template('builder_column_item'),
        events: {
            'click .themify_builder_option_column': 'edit',
            'click .js-tb_empty_row_btn': 'showModPanel'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:view', this.setView);
        },
        render: function (identify) {
            this.el.innerHTML = this.template({component_name: this.model.get('component_name')});
            var modules = this.model.get('modules');
            if (!api.id && api.saveCid) {
                api.rowStyling[this.model.cid] = 1;
            }
            // check if it has module
            if (modules) {
                var container = document.createDocumentFragment();
                for (var i in modules) {
                    if (modules[i]!== undefined && modules[i]!== null) {
                            var m = modules[i],
                                cidentify = identify ? identify + '-' + i : false,
                            moduleView = m.cols === undefined ? api.Views.init_module(m, this.type) : api.Views.init_subrow(m, this.type);
                            if(moduleView){
                                var el = moduleView.view.render(cidentify);
                                if (api.id && m.mod_name) {
                                    api.VisualCache[moduleView.model.cid] = m.mod_name + '-' + api.id + '-' + cidentify;
                                    el.$el.children('.module')[0].className += ' ' + api.VisualCache[moduleView.model.cid];
                                }
                                else if (!api.id && api.saveCid) {
                                    api.rowStyling[moduleView.model.cid] = 1;
                                }
                                container.appendChild(el.el);
                            }
                    }
                }
                this.el.getElementsByClassName('themify_module_holder')[0].appendChild(container);

            }
            return this;
        },
        edit: function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (this.model.cid !== api.autoSaveCid && api.autoSaveCid !== null) {
                api.Forms.saveComponent(null);
            }
            api.activeModel = this.model;
            this.editComponent();

        },
        setView: function (node) {
            this.setElement(node);
        },
        showModPanel: function (e) {
            e.preventDefault();
            api.toolbar.Panel.show();
        }
    });

    // SubRow view share same model as ModuleView
    api.Views.SubRows['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            var attr = {
                'class': 'themify_builder_sub_row module_subrow active_module clearfix tb_element_cid_' + this.model.cid,
                'data-cid': this.model.cid
            };
            return attr;
        },
        template: wp.template('builder_subrow_item'),
        events: {
            'click .themify_builder_style_subrow': 'edit'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:view', this.setView);
        },
        render: function (identify) {
            var cols = this.model.get('cols'),
                    len = Object.keys(cols).length;
            this.el.innerHTML = this.template();
            if (api.id) {
                api.VisualCache[this.model.cid] = 'sub_row_' + identify;
                this.el.className += ' ' + api.VisualCache[this.model.cid];
            }
            if (len > 0) {
                var container = document.createDocumentFragment();
                for (var i = 0; i <= len; ++i) {
                    if (cols[i] !== undefined) {
                        cols[i].component_name = 'sub-column';
                        var sidentify = identify + '-' + i,
                                columnView = api.Views.init_column(cols[i], this.type),
                                el = columnView.view.render('sub_row_' + sidentify).el;
                        if (api.id) {
                            api.VisualCache[columnView.model.cid] = 'sub_column_post_' + api.id + ' sub_column_' + sidentify;
                            el.className += ' ' + api.VisualCache[columnView.model.cid];
                        }
                        container.appendChild(el);
                    }
                }
                this.el.getElementsByClassName('subrow_inner')[0].appendChild(container);
            }
            if (api.init && !api.id) {
                api.Utils.selectedGridMenu(this.el);
            }
            return this;
        },
        setView: function (node) {
            this.setElement(node);
        },
        edit: function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (this.model.cid !== api.autoSaveCid && api.autoSaveCid !== null) {
                api.Forms.saveComponent(null);
            }
            api.activeModel = this.model;
            this.editComponent();

        }
    });

    api.Views.Rows['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            var attr = {
                'class': 'themify_builder_row module_row clearfix tb_element_cid_' + this.model.cid,
                'data-cid': this.model.cid
            };
            return attr;
        },
        template: wp.template('builder_row_item'),
        events: {
            'click .themify_builder_option_row': 'edit',
            'click .themify_builder_style_row': 'edit',
            'click .tb_visibility_component ': 'edit',
            'click .themify_builder_grid_list li a': '_gridMenuClicked',
            'click .themify_builder_grid_list_wrapper .grid_tabs li a': '_switchGridTabs',
            'click .themify_builder_column_alignment li a': '_columnAlignmentMenuClicked',
            'click .themify_builder_column_direction li a': '_columnDirectionMenuClicked',
            'change .gutter_select': '_gutterChange',
            'click .toggle_row': 'toggleRow',
            'click .themify_builder_toggle_row': 'toggleRow'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:view', this.setView);
        },
        render: function (row) {
            var cols = this.model.get('cols'),
                    len = Object.keys(cols).length;
            this.el.innerHTML = this.template();
            if (api.saveCid) {
                api.rowStyling[this.model.cid] = 1;
            }
            if (len > 0) {
                var container = document.createDocumentFragment(),
                        col_order,
                        identify = false;
                for (var i = 0; i <= len; ++i) {
                    if (cols[i] !== undefined) {
                        cols[i].component_name = 'column';
                        var columnView = api.Views.init_column(cols[i], this.type);
                        if (api.id) {
                            col_order = columnView.model.get('column_order');
                            if (col_order === undefined) {
                                col_order = i;
                            }
                            identify = row + '-' + col_order;
                        }
                        var el = columnView.view.render(identify).el;
                        if (api.id) {
                            api.VisualCache[columnView.model.cid] = 'module_column_' + col_order + ' tb_' + api.id + '_column'
                            el.className += ' ' + api.VisualCache[columnView.model.cid];
                        }
                        container.appendChild(el);
                    }
                }
                this.el.getElementsByClassName('row_inner')[0].appendChild(container);
            } else {
                // Add column
                api.Utils._addNewColumn({
                    newclass: 'col-full',
                    component: 'column',
                    type: this.type
                }, this.$el.find('.row_inner'));
            }
            api.Utils.selectedGridMenu(this.el);
            return this;
        },
        edit: function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (e.currentTarget.classList.contains('themify_builder_style_row')) {
                this.model.set({styleClicked: true}, {silent: true});
            }
            else if (e.currentTarget.classList.contains('tb_visibility_component')) {
                this.model.set({visibileClicked: true}, {silent: true});
            }
            if (this.model.cid !== api.autoSaveCid && api.autoSaveCid !== null) {
                api.Forms.saveComponent(null);
            }
            api.activeModel = this.model;
            this.editComponent();
        },
        _switchGridTabs: function (e) {
            api.scrollTo = $(e.currentTarget).closest('[data-cid]');
            api.Forms.lightbox_switcher(e);
        },
        _gridMenuClicked: function (e) {
            e.preventDefault();
            var $this = $(e.currentTarget),
                    set = $this.data('grid'),
                    handle = $this.data('handle'),
                    $base,
                    row,
                    is_sub_row = false,
                    type = $this.data('type'),
                    is_desktop = type === 'desktop';
            var before = ThemifyBuilderCommon.Lightbox.clone($this.closest('.module_row'));
            $this.parent().addClass('selected').siblings().removeClass('selected');
            if (handle === 'module') {
                if (set[0] !== '-full') {
                    is_sub_row = true;
                    var subRowDataPlainObject = {
                        cols: [{grid_class: 'col-full'}]
                    },
                    subRowView = api.Views.init_subrow(subRowDataPlainObject, api.mode),
                            $mod_ori = $this.closest('.active_module'),
                            $mod_clone = $mod_ori.clone();
                    $mod_clone.insertAfter($mod_ori);
                    $base = subRowView.view.render().$el
                            .find('.themify_module_holder')
                            .prepend($mod_ori)
                            .end()
                            .insertAfter($mod_clone)
                            .find('.' + $this.attr('class').replace(' ', '.'))
                            .closest('li')
                            .addClass('selected')
                            .siblings().removeClass('selected')
                            .end().end().end()
                            .find('.subrow_inner');
                    $mod_clone.remove();
                    row = $base.closest('.module_subrow');
                }
            }
            else {
                is_sub_row = handle === 'subrow';
                row = $this.closest('.module_' + handle);
                $base = row.find('.' + handle + '_inner').first();
            }
            if ($base.length === 0) {
                return;
            }
            if (is_desktop || handle === 'module') {
                var $both = $base,
                        col = $this.data('col');
                $both = $both.add(row);
                if (col === undefined) {
                    col = 1;
                    $this.data('col', col);
                }
                $both.removeClass('col-count-1 col-count-' + $base.attr('data-basecol')).addClass('col-count-' + col);
                $base.attr('data-basecol', col);
                if (is_desktop) {
                    $this.closest('.themify_builder_grid_list_wrapper').find('.themify_builder_grid_reposnive .themify_builder_grid_list').each(function () {
                        var selected = $(this).find('.selected'),
                                item = selected.find('a'),
                                mode = item.data('type'),
                                rcol = item.data('col');
                        if (rcol !== undefined && (rcol > col || (col === 4 && rcol === 3) || (col >= 4 && rcol >= 4 && col != rcol))) {
                            selected.removeClass('selected');
                            $base.removeClass('tb_grid_classes col-count-' + $base.attr('data-basecol') + ' ' + $base.attr('data-col_' + mode)).attr('data-col_' + mode, '');
                            $(this).closest('.themify_builder_grid_list').find('.' + mode + '-auto').parent().addClass('selected');
                        }
                    });
                }
            }
            else {
                if (set[0] !== '-auto') {
                    var cl = 'column' + set.join('-'),
                            col = $this.data('col');
                    if (col === 3 && $base.attr('data-basecol') > col) {
                        cl += ' tb_3col';
                    }
                    $base.removeClass($base.attr('data-col_tablet') + ' ' + $base.attr('data-col_tablet_landscape') + ' ' + $base.attr('data-col_mobile'))
                            .addClass(cl + ' tb_grid_classes col-count-' + $base.attr('data-basecol')).attr('data-col_' + type, cl);
                }
                else {
                    $base.removeClass('tb_grid_classes tb_3col col-count-' + $base.attr('data-basecol') + ' ' + $base.attr('data-col_' + type)).attr('data-col_' + type, '');
                }
                if (api.mode === 'visual') {
                    $('body', top_iframe).height(document.body.scrollHeight);
                }
                api.Utils.setCompactMode($base.children('.module_column'));
                return false;
            }

            var cols = $base.children('.module_column'),
                    set_length = set.length,
                    col_cl = 'module_column' + (is_sub_row ? ' sub_column' : '') + ' col';
            for (var i = 0; i < set_length; ++i) {
                var c = cols.eq(i);
                if (c.length > 0) {
                    c.removeClass(api.Utils.gridClass.join(' ')).addClass(col_cl + set[i]);
                } else {
                    // Add column
                    api.Utils._addNewColumn({
                        newclass: col_cl + set[i],
                        component: is_sub_row ? 'sub-column' : 'column',
                        type: api.mode
                    }, $base);
                }
            }

            // remove unused column
            if (set_length < $base.children().length) {
                $base.children('.module_column').eq(set_length - 1).nextAll().each(function () {
                    // relocate active_module
                    var modules = $(this).find('.themify_module_holder').first();
                    modules.children().appendTo($(this).prev().find('.themify_module_holder').first());
                    $(this).remove(); // finally remove it
                });
            }
            var $children = $base.children();
            $children.removeClass('first last');
            if ($base.hasClass('direction-rtl')) {
                $children.last().addClass('first');
                $children.first().addClass('last');
            }
            else {
                $children.first().addClass('first');
                $children.last().addClass('last');
            }
            // remove sub_row when fullwidth column
            if (is_sub_row && set[0] === '-full') {
                var subrow = $base.closest('.module_subrow'),
                        column = subrow.closest('.module_column'),
                        $move_modules = $base.find('.active_module');
                $move_modules.insertAfter(subrow);
                subrow.remove();
                api.Mixins.Builder.initGridMenu(column[0], true);
                $move_modules.find('.themify_builder_grid_list .grid-layout--full').parent().addClass('selected').siblings().removeClass('selected');
            }
            api.Utils.columnDrag($base, true);
            var row = $this.closest('.module_row');
            api.Mixins.Builder.columnSort(row);
            api.Mixins.Builder.updateModuleSort(row);
            api.vent.trigger('dom:change', row.data('cid'), before, row, 'row');
        },
        _columnAlignmentMenuClicked: function (e) {
            e.preventDefault();
            var $this = $(e.currentTarget),
                    handle = $this.data('handle'),
                    $row = null;
            if (handle === 'module' || $this.closest('li').hasClass('selected')) {
                return;
            }
            $this.closest('li').addClass('selected').siblings('li').removeClass('selected');
            $row = $this.closest('.module_' + handle);
            var alignment = $this.data('alignment'),
                    el = api.Models.Registry.lookup($row.data('cid')),
                    before = ThemifyBuilderCommon.Lightbox.clone($row);
            $row.find('.' + handle + '_inner').first().removeClass(el.get('column_alignment')).addClass(alignment);
            el.set({column_alignment: alignment}, {silent: true});
            api.vent.trigger('dom:change', before.data('cid'), before, $this.closest('.module_' + handle), 'row');

        },
        _columnDirectionMenuClicked: function (e) {
            e.preventDefault();
            var $this = $(e.currentTarget),
                    handle = $this.data('handle'),
                    dir = $this.data('dir'),
                    $row = null;
            if (handle === 'module' || $this.closest('li').hasClass('selected')) {
                return;
            }
            $this.closest('li').addClass('selected').siblings('li').removeClass('selected');
            $row = $this.closest('.module_' + handle);
            var inner = $row.find('.' + handle + '_inner').first(),
                columns = inner.children('.module_column'),
                first = columns.first(),
                last = columns.last();
            if (dir === 'rtl') {
                first.removeClass('first').addClass('last');
                last.removeClass('last').addClass('first');
                inner.addClass('direction-rtl');
            }
            else {
                first.removeClass('last').addClass('first');
                last.removeClass('first').addClass('last');
                inner.removeClass('direction-rtl');
            }

            inner.attr('data-' + api.activeBreakPoint + '_dir', dir);
        },
        _gutterChange: function (e) {
            var $this = $(e.currentTarget),
                    handle = $this.data('handle');
            if (handle === 'module') {
                return;
            }
            var val = $this.val();
            $this.find('option').removeAttr('selected').filter('[value="' + val + '"]').attr('selected', 'selected');//need for undo/redo
            var row = $this.closest('.module_' + handle),
                    before = ThemifyBuilderCommon.Lightbox.clone(row),
                    inner = row.find('.' + handle + '_inner').first(),
                    el = api.Models.Registry.lookup(row.data('cid'));
            before.find('.themify_builder_' + handle + '_top .gutter_select').val(row.data('gutter'));//need for undo/redo
            api.Utils.columnDrag(inner, false, el.get('gutter'), val);
            inner.removeClass(el.get('gutter')).addClass(val);
            el.set({gutter: val}, {silent: true});
            api.vent.trigger('dom:change', before.data('cid'), before, $this.closest('.module_' + handle), 'row');
        },
        setView: function (node) {
            this.setElement(node);
        },
        toggleRow: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var row = $(e.currentTarget).closest('.module_row');
            row.find('.row_inner').first().slideToggle('fast', function () {
                row.toggleClass('collapsed');
            });
        }
    });

    api.Views.Builder = Backbone.View.extend({
        type: 'default',
        events: {
            'click .tb-import-layout-button': 'importLayoutButton'
        },
        initialize: function (options) {
            _.extend(this, _.pick(options, 'type'));
            api.vent.on('dom:builder:change', this.tempEvents.bind(this));
            api.vent.on('dom:builder:init', this.init.bind(this));
        },
        init: function (init) {
            api.Forms.getFormTemplates();
            api.init = init;
            this.rowSort();
            this.initGridMenu(this.el, true);
            if (api.mode === 'visual') {
                this.updateModuleSort(this.$el);
                if (init) {
                    this.initModuleVisualDrag();
                }
                setTimeout(function () {
                    api.Utils._onResize(true);
                }, 1500);
            }
            else {
                if (init) {
                    this.initModuleDraggable(api.toolbar.$el);
                }
                this.updateModuleSort($('body'));

            }
            var self = this;
            setTimeout(function () {
                api.Utils.setCompactMode(self.el.getElementsByClassName('module_column'));
            }, 1000);
            api.vent.trigger('dom:builder:change');
            this.insertLayoutButton();
            api.init = true;
        },
        render: function () {

            var container = document.createDocumentFragment(),
                    rows = this.collection,
                    row = false;
            for (var i = 0, len = rows.length; i < len; ++i) {
                var rowView = api.Views.init_row(rows.models[i], this.type);
                if (rowView !== false) {
                    if (api.id) {
                        row = rowView.model.get('row_order');
                        if (row === undefined) {
                            row = i;
                        }
                    }
                    var el = rowView.view.render(row).el;
                    if (api.id) {
                        api.VisualCache[rowView.model.cid] = 'themify_builder_' + api.id + '_row module_row_' + row;
                        el.className += ' ' + api.VisualCache[rowView.model.cid];
                    }
                    container.appendChild(el);
                }
            }

            this.el.appendChild(container);
            api.Utils.columnDrag(false, false);
            return this;
        },
        tempEvents: function () {
            this.newRowAvailable();
        },
        insertLayoutButton: function () {
            this.$el.find('.tb-import-layout-button').remove();
            if (this.$('.module_row').length < 2) {
                var cl = themifyBuilder.is_premium ? '' : ' themify_builder_lite';
                this.el.insertAdjacentHTML('beforeend', '<a href="#" class="tb-import-layout-button' + cl + '">' + themifyBuilder.i18n.text_import_layout_button + '</a>');
            }
        },
        importLayoutButton: function (e) {
            e.preventDefault();
            api.Views.Toolbar.prototype.loadLayout(e);
        }
    });

    api.Mixins.Common = {
        styleData: {},
        doTheBinding: function ($this, val, context) {
            var logic = false,
                responsive = false,
                binding = $this.data('binding'),
                is_responsive = 'desktop' !== api.activeBreakPoint;
            if (!val && binding['empty'] !== undefined) {
                logic = binding['empty'];
            }
            else if (val && binding[val] !== undefined) {
                if ($this.attr('type') === 'radio') {
                    logic = $this.is(':checked') ? binding[val] : false;
                } else {
                    logic = binding[val];
                }
            }
            else if (val && binding['not_empty'] !== undefined) {
                logic = binding['not_empty'];
            }
            else if (binding['select'] !== undefined && val !== binding['select']['value']) {
                logic = binding['select'];
            }
            else if (binding['checked'] !== undefined && $this.is(':checked')) {
                logic = binding['checked'];
            }
            else if (binding['not_checked'] !== undefined && !$this.is(':checked')) {
                logic = binding['not_checked'];
            }
            
            if (binding['responsive'] !== undefined && $this.is('select') ) {
                responsive = binding['responsive'];
                if ( is_responsive && responsive['disabled'] !== undefined && this.styleData['breakpoint_desktop'][ $this.prop('id') ] !== undefined && _.contains( responsive['disabled'], this.styleData['breakpoint_desktop'][ $this.prop('id') ] ) ) {
                    logic = binding[ this.styleData.breakpoint_desktop[ $this.prop('id') ] ];
                }
            }
            if (logic) {
                var items = [];
                if (logic['show'] !== undefined) {
                    items = logic['show'];
                }
                if (logic['hide'] !== undefined) {
                    items = items.concat(logic['hide']);
                }
                if (context === undefined || context.length === 0) {
                    context = $('#themify_builder_lightbox_container', top_iframe);
                }
                for (var i = 0, len = items.length; i < len; ++i) {
                    if (logic['hide'] !== undefined && logic['hide'][i] !== undefined) {
                        $('.' + logic['hide'][i], context).addClass('_tf-hide').hide();
                    }
                    if (logic['show'] !== undefined && logic['show'][i] !== undefined) {
                        $('.' + logic['show'][i], context).removeClass('_tf-hide').show();
                    }
                }
                if (logic['responsive'] !== undefined) {
                    var items_disabled = [];
                    if (logic['responsive']['disabled'] !== undefined) {
                        items_disabled = items_disabled.concat(logic['responsive']['disabled']);
                    }

                    for (var i = 0, len = items_disabled.length; i < len; ++i) {
                        if (logic['responsive']['disabled'] !== undefined && logic['responsive']['disabled'][i] !== undefined) {
                            if ( is_responsive ) {
                                $('.' + logic['responsive']['disabled'][i], context).addClass('reponive_disable');
                            } else {
                                $('.' + logic['responsive']['disabled'][i], context).removeClass('reponive_disable');   
                            }
                        }
                    }
                }
            }
            if ( responsive ) {
                var options_el = [];

                if (responsive['disabled'] !== undefined) {
                    options_el = options_el.concat(responsive['disabled']);
                }

                for (var i = 0, len = options_el.length; i < len; ++i) {
                    if (responsive['disabled'] !== undefined && responsive['disabled'][i] !== undefined) {
                        $this.find('option[value="'+ responsive['disabled'][i] +'"]').prop('disabled', is_responsive);
                    }
                }
            }
        },
        moduleOptionsBinding: function () {
            var $this = this;
            $('#themify_builder_lightbox_container', top_iframe).on('change', '[data-binding]', function () {
                $this.doTheBinding($(this), $.trim($(this).val()), $(this).closest('.tb_repeatable_field_content'));
            }).on('click', '.themify-layout-icon[data-binding] a', function () {
                $this.doTheBinding($(this).parent(), $(this).prop('id'), $(this).closest('.tb_repeatable_field_content'));
            });
        },
        mode_change: function (component) {
            var isNewModule = component === 'module' && api.activeModel.get('is_new') !== undefined,
                    item = isNewModule ? $('.tb_element_cid_' + api.activeModel.cid).closest('.module_row') : $('.tb_element_cid_' + api.activeModel.cid);
            api.beforeEvent = ThemifyBuilderCommon.Lightbox.clone(item);
            var self = this,
                    key = component === 'module' ? 'mod_settings' : 'styling',
                    stylefields = null;
            self.styleData = {};
            self.styleData['breakpoint_desktop'] = $.extend(true, {}, api.activeModel.get(key));
            for (var k in themifyBuilder.breakpoints) {
                if (self.styleData['breakpoint_desktop']['breakpoint_' + k] !== undefined) {
                    self.styleData['breakpoint_' + k] = self.styleData['breakpoint_desktop']['breakpoint_' + k];
                    delete self.styleData['breakpoint_desktop']['breakpoint_' + k];
                }
            }

            function setData(breakpoint) {
                if (stylefields === null) {
                    stylefields = ThemifyBuilderCommon.Lightbox.$lightbox.find('#themify_builder_options_styling')[0];
                }
                self.styleData['breakpoint_' + breakpoint] = api.Forms.serialize('themify_builder_options_styling', self.styleData, breakpoint, false);
            }
            function changeCallback(e, prevbreakpoint, breakpoint) {
                setData(prevbreakpoint);
                self.editComponentCallback(stylefields, component, self.styleData, true);
            }

            $('body').off('themify_builder_change_mode', changeCallback)
                    .on('themify_builder_change_mode', changeCallback)
                    .one('themify_builder_lightbox_before_close', function () {
                        tempSettings = [];
                        if (api.saving) {
                            setData(api.activeBreakPoint);
                        }
                        $('body').off('themify_builder_change_mode', changeCallback);
                    });
        },
        editComponentCallback: function (response, component, settings, is_mod_change) {

            var key = 'styling',
                    self = api.Mixins.Common,
                    type = component,
                    editors = [],
                    rbuttons = [],
                    rcheckbox = [],
                    gradients = [],
                    repeater = [],
                    binding = [],
                    isNewModule = false,
                    breakpoints = api.activeBreakPoint !== 'desktop' ? Object.keys(themifyBuilder.breakpoints).reverse() : false;
            if (component === 'module') {
                type = api.activeModel.get('mod_name');
                key = 'mod_settings';
                isNewModule = api.activeModel.get('is_new') !== undefined;
            }
            if (breakpoints !== false) {
                var index = breakpoints.indexOf(api.activeBreakPoint);
                for (var i = 0; i <= index; ++i) {
                    breakpoints.shift();
                }
                breakpoints.push('desktop');
            }

            var parseSettings = function (options, data, all_settings, repeat) {
                var id = '';
                if (!repeat) {
                    id = options.getAttribute('name');
                    if (!id) {
                        id = options.getAttribute('id');
                    }
                }
                else {
                    id = options.getAttribute('data-input-id');
                }
                var val = data && data[id] !== undefined ? data[id] : false;
                if (!is_mod_change && (val === 'px' || val === 'pixels' || val === 'solid' || val === '|' || val === 'default')) {
                    return;
                }
                var $this_option = $(options),
                        cl = options.classList;
                if (!isNewModule && breakpoints !== false && !repeat && !val) {
                    if (!cl.contains('themify-checkbox')) {
                        for (var j = 0, blen = breakpoints.length; j < blen; ++j) {
                            if (all_settings['breakpoint_' + breakpoints[j]] !== undefined && all_settings['breakpoint_' + breakpoints[j]][id] !== undefined) {
                                val = all_settings['breakpoint_' + breakpoints[j]][id];
                                break;
                            }
                            else if (breakpoints[j] === 'desktop' && all_settings[id] !== undefined) {
                                val = all_settings[id];
                                break;
                            }
                        }
                    }
                    else if (all_settings[id] !== undefined && $this_option.closest('#themify_builder_options_styling').length === 0) {
                        val = all_settings[id];
                    }
                }
                if (cl.contains('themify-gradient')) {
                    gradients.push({'k': $this_option, 'v': val});
                    if (val) {
                        $this_option.val(val);
                    }
                }
                else if (cl.contains('themify-builder-uploader-input')) {

                    if (val) {
                        var img_thumb = $('<img/>', {src: val, width: 50, height: 50});
                        $this_option.val(val).parent().find('.img-placeholder').html(img_thumb);
                    }
                    else if (is_mod_change) {
                        $this_option.val('').parent().find('.img-placeholder').empty();
                    }
                }
                else if (cl.contains('themify-option-query-cat')) {
                    if (val) {
                        var parent = $this_option.parent(),
                                cat_val = val.split('|')[0];
                        parent.find('#' + id + '_dropdown').children("option[value='" + cat_val + "']").prop('selected', true);
                        parent.find('.query_category_multiple').val(cat_val);
                    }
                }
                else if (cl.contains('tb-radio-input-container')) {
                    var radio = null,
                            v = val ? val : ($this_option.data('default') !== undefined ? $this_option.data('default') : false);
                    if (v !== false && v !== '') {
                        radio = $this_option.find("input[value='" + v + "']");
                        if (radio.is(':disabled')) {
                            radio = null;
                        }
                        else {
                            radio.prop('checked', true);
                        }
                    }
                    else if (is_mod_change && cl.contains('tb-icon-radio')) {
                        $this_option.find('input').prop('checked', false);
                    }
                    if (radio === null) {
                        radio = $this_option.find('input:checked');
                    }
                    // has group element enable
                    if (radio.length > 0 && cl.contains('tb-option-radio-enable')) {
                        rbuttons.push(radio);
                    }
                }
                else if (cl.contains('themify-checkbox')) {
                    var cel = $this_option.find('.tb-checkbox');

                    if (!val && _.isEmpty(data)) {
                        val = cel.map(function () {
                            return ($(this).is(':checked')) ? $(this).val() : null;
                        }).get().join('|');
                    }

                    if (val) {
                        var cselected = val.split('|');
                        cel.each(function () {
                            if (cselected.indexOf($(this).val()) !== -1) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                    else if ((!isNewModule || is_mod_change)) {
                        var groupInput = cel.closest('.themify_builder_input').find('.tb_seperate_items input');

                        if (groupInput.length) {
                            var hasValue = false;

                            groupInput.each(function () {
                                if ($(this).val().length) {
                                    hasValue = true;
                                    return false;
                                }
                            });

                            cel.prop('checked', !hasValue);

                        } else {
                            cel.prop('checked', false);
                        }
                    }
                    if (cl.contains('tb-option-checkbox-enable')) {
                        rcheckbox.push(cel);
                    }

                } else if (cl.contains('themify-layout-icon')) {
                    if (val) {
                        $this_option.find('a').filter(function () {
                            $(this).removeClass('selected');
                            if (val === $(this).prop('id')) {
                                return $(this);
                            }
                        }).addClass('selected');
                    }
                    else {
                        var m_defaults = themifyBuilder.modules[ type ];
                        if (m_defaults !== undefined && m_defaults.defaults !== undefined && m_defaults.defaults[id]) {
                            $this_option.find('#' + m_defaults.defaults[id]).addClass('selected')
                        }
                        else {
                            $this_option.find('a').first().addClass('selected');
                        }
                    }
                }
                else if (options.tagName === 'SELECT') {
                    if (val) {
                        $this_option.val(val);
                        if (cl.contains('font-family-select')) {
                            $this_option.data('selected', val);
                        }
                    }
                    else if (is_mod_change) {
                        $this_option.find('option').prop('selected', false);
                    }

                    if (is_mod_change && cl.contains('font-family-select')) {

                        if ($this_option.prop('tabindex') === -1) {
                            $this_option.trigger('change.select');
                        }
                        else if (val) {
                            var $optgroup = $this_option.find('optgroup');
                            if (ThemifyBuilderCommon.safe_fonts[val] !== undefined) {
                                $optgroup.first().html('<option selected="selected" data-type="webfont" value="' + val + '">' + ThemifyBuilderCommon.safe_fonts[val] + '</option>');
                            }
                            else {
                                $optgroup.last().html('<option selected="selected" value="' + val + '">' + ThemifyBuilderCommon.google_fonts[val] + '</option>');
                            }
                        }
                    }
                }
                else if (options.tagName === 'TEXTAREA' || options.tagName === 'INPUT') {
                    var is_textarea = options.tagName === 'TEXTAREA';
                    if (val || is_mod_change) {
                        if (is_mod_change && !val) {
                            val = '';
                        }
                        $this_option.val(val);
                        if (cl.contains('minicolors-input')) {
                            var color = val,
                                    opacity = '';
                            if (val.indexOf('_') !== -1) {
                                color = api.Utils.toRGBA(val);
                                val = val.split('_');
                                opacity = val[1];
                                if (!opacity) {
                                    opacity = 1;
                                }
                                $this_option.val(val[0]);
                            }
                            else if (val) {
                                if (val.indexOf('#') === -1) {
                                    color = '#' + val;
                                }
                                opacity = 1;
                            }
                            $this_option.attr('data-opacity', opacity).next('.minicolors-swatch').find('span').css({'background': color, 'opacity': opacity}).closest('.minicolors').next('.color_opacity').val(opacity);
                        }
                        else if (is_textarea && !isNewModule && (cl.contains('tb-shortcode-input') || cl.contains('tb-thumbs-preview'))) {
                            self.getShortcodePreview($this_option, val);
                        }
                    }
                    if (is_textarea && cl.contains('tb_lb_wp_editor')) {
                        editors.push($this_option);
                    }
                }
                else if (cl.contains('themify_builder_row_js_wrapper')) {
                    var row_append = val ? val.length - 1 : 0,
                            items,
                            e = $.Event('click', {isTrigger: true, currentTarget: $this_option.next('.add_new').find('a').first()});
                    if (api.cache.repeaterElements[id] === undefined) {
                        items = options.getElementsByClassName('tb_repeatable_field');
                        api.cache.repeaterElements[id] = $(items[0]).clone();
                    }
                    for (var j = 0; j < row_append; ++j) {
                        api.Forms.moduleOptAddRow(e, null);
                    }
                    items = options.getElementsByClassName('tb_repeatable_field');
                    for (var j = 0, clen = items.length; j < clen; ++j) {
                        var items_child = items[j].getElementsByClassName('tb_lb_option_child'),
                            opt_val = val[j] !== undefined ? val[j] : false;
                        for (var k = 0, n = items_child.length; k < n; ++k) {
                            parseSettings(items_child[k], opt_val, all_settings, true);
                        }
                    }
                    repeater.push({el: $this_option, binding_type: $this_option.data('control-binding')});
                }
                // Hide conditional inputs
                if ($this_option.data('binding')) {
                    binding.push({el: $this_option, 'v': val});
                }
                if (!is_mod_change && $this_option.data('control-binding') && !cl.contains('tb_lb_wp_editor') && !cl.contains('minicolors-input') && !cl.contains('themify-gradient') && 'repeater' !== $this_option.data('control-type')) {

                    api.Views.init_control($this_option.data('control-type'), {el: $this_option, binding_type: $this_option.data('control-binding'), selector: $this_option.data('live-selector')});
                }
            };
            var all_settings = is_mod_change ? settings : api.activeModel.get(key),
                    el_settings = $.extend(true, {}, all_settings);
            if (api.activeBreakPoint !== 'desktop' || is_mod_change) {//closest styles
                if (el_settings['breakpoint_' + api.activeBreakPoint] !== undefined) {
                    el_settings = el_settings['breakpoint_' + api.activeBreakPoint];
                }
                else {
                    for (var j = 0, blen = breakpoints.length; j < blen; ++j) {
                        if (el_settings['breakpoint_' + breakpoints[j]] !== undefined) {
                            el_settings = el_settings['breakpoint_' + breakpoints[j]];
                            break;
                        }
                    }
                }
            }
            var options = response.getElementsByClassName('tb_lb_option');
            for (var i = 0, len = options.length; i < len; ++i) {
                parseSettings(options[i], el_settings, all_settings, false);
                if (!is_mod_change) {
                    options[i].addEventListener('change',function tb_change(e){
                        e.currentTarget.removeEventListener(e.type, tb_change);
                        api.hasChanged = true;
                    },{once:true},false);
                }
            }
            options = null;
            if (!is_mod_change) {
                if (repeater.length > 0) {
                    setTimeout(function () {
                        for (var i = 0, len = repeater.length; i < len; ++i) {
                            api.Views.init_control('repeater', repeater[i]);
                        }
                        repeater = null;

                    }, 1);
                }
                setTimeout(function () {
                    if (component === 'module') {
                        for (var i = 0, len = binding.length; i < len; ++i) {
                            self.doTheBinding(binding[i].el, binding[i].v);
                        }
                    }
                    binding = null;
                    // option binding setup
                    self.moduleOptionsBinding();
                }, 1);
            }
            setTimeout(function () {
                for (var i = 0, len = rbuttons.length; i < len; ++i) {
                    ThemifyBuilderCommon.Lightbox.clickRadioOption(null, rbuttons[i]);
                }
                rbuttons = null;
            }, 1);
            setTimeout(function () {
                for (var i = 0, len = rcheckbox.length; i < len; ++i) {
                    ThemifyBuilderCommon.Lightbox.clickCheckboxOption(null, rcheckbox[i]);
                }
                rcheckbox = null;
            }, 1);
            setTimeout(function () {
                self.applyAll_init(is_mod_change);
                var b = response.getElementsByClassName('border_style');
                for (var i = 0, len = b.length; i < len; ++i) {
                    ThemifyBuilderCommon.Lightbox.hideShowBorder(null, $(b[i]));
                }
                b = null;
            }, 1);
            if ($.fn.ThemifyGradient !== undefined) {
                setTimeout(function () {
                    for (var i = 0, len = gradients.length; i < len; ++i) {
                        api.Utils.createGradientPicker(gradients[i].k, gradients[i].v, is_mod_change);
                    }
                    gradients = null;
                }, 1);
            }
            if (!is_mod_change) {
                setTimeout(function () {
                    ThemifyBuilderCommon.fontPreview($('#themify_builder_lightbox_container', top_iframe));
                }, 300);
                if (isNewModule && 'gallery' === type) {
                    setTimeout(function () {
                        $('.tb-gallery-btn', response).trigger('click');
                    }, 1);
                }

                setTimeout(function () {
                    // plupload init
                    api.Utils.builderPlupload('normal');
                }, 1);
                // colorpicker
                setTimeout(function () {
                    api.Utils.setColorPicker(response);
                }, 1);
                if ('visual' === api.mode) {
                    setTimeout(function () {
                        tempSettings = component === 'module' ? api.Forms.serialize('themify_builder_options_setting') : el_settings;//cache exclude styling
                        api.liveStylingInstance.init(tempSettings);
                    }, 1);
                    if (!isNewModule) {
                        ThemifyBuilderCommon.Lightbox.rememberRow();
                    }
                }

                setTimeout(function () {
                    if (editors.length > 0) {
                        var initEditor = function () {
                            for (var i = 0, len = editors.length; i < len; ++i) {
                                
                                api.Views.init_control('wp_editor', {el: editors[i], binding_type: editors[i].data('control-binding'), selector: editors[i].data('live-selector')});
                            }
                            editors = null;
                        };
                        if (!api.activeModel.get('styleClicked') && !api.activeModel.get('visibileClicked')) {
                            initEditor();
                        }
                        else {
                            $('body').one('themify_builder_tabsactive', function (e, id, content) {
                                if (id === '#themify_builder_options_setting') {
                                    initEditor();
                                }
                            });
                        }
                    }
                    self.mode_change(component);
                }, 1);
                // Trigger event
                $('body').trigger('editing_' + component + '_option', [type, el_settings, response]);
                if (api.mode === 'visual') {
                    // Trigger parent iframe
                    window.top.jQuery('body').trigger('editing_' + component + '_option', [type, el_settings, response]);
                }
            }
        },
        // "Apply all" // apply all init
        applyAll_init: function (is_mod_change) {
            var items = $('.style_apply_all', top_iframe);
            items.off('change.tb_apply_all').on('change.tb_apply_all', function (e) {
                var parent = $(this).closest('.themify_builder_input').find('.tb_seperate_items'),
                        items = parent.find('li'),
                        init = !e.isTrigger;
                if ($(this).is(':checked')) {
                    if (init) {
                        items.not(':first-child').slideUp();
                    }
                    else {//works faster
                        items.not(':first-child').hide();
                    }
                    parent.attr('data-checked', 1);
                }
                else {
                    items.slideDown();
                    parent.removeAttr('data-checked');
                }
                if (init) {
                    items.first().find('select').trigger('change');
                }
            });
            if (!is_mod_change) {
                items = items.filter(':checked');
            }
            items.trigger('change.tb_apply_all');
        },
        getShortcodePreview: function ($input, value) {
            var self = this;
            if (self.galerry_cache === undefined) {
                self.galerry_cache = {};
            }
            function callback(data) {
                $input.next('.tb_shortcode_preview').remove();
                if (data) {
                    $input.after(data);
                }
            }
            if (self.galerry_cache[value] !== undefined) {
                callback(self.galerry_cache[value]);
                return;
            }
            $.ajax({
                type: 'POST',
                url: themifyBuilder.ajaxurl,
                data:
                        {
                            action: 'tb_load_shortcode_preview',
                            tb_load_nonce: themifyBuilder.tb_load_nonce,
                            shortcode: value
                        },
                success: function (data) {
                    callback(data);
                    self.galerry_cache[value] = data;
                }
            });
        }
    };

    api.Mixins.Builder = {
        rowSort: function () {

            var toggleCollapseRow = false,
                    self = this, startIndex, changeIndex, uiHeight,
                    rowSortable = {
                        items: '.module_row',
                        handle: '.themify_builder_row_top',
                        axis: 'y',
                        placeholder: 'themify_builder_ui_state_highlight',
                        containment: 'parent',
                        tolerance: 'pointer',
                        forceHelperSize: true,
                        forcePlaceholderSize: true,
                        scroll: false,
                        beforeStart: function (e, ui) {
                            self.dragScroll();
                            if ('visual' === api.mode && !ui.item.hasClass('collapsed')) {
                                toggleCollapseRow = true;
                                ui.item.addClass('collapsed ui-sortable-row');
                            }
                        },
                        start: function (e, ui) {
                            startIndex = ui.placeholder.index();
                            uiHeight = ui.item.outerHeight(true);//get offset incl margin
                            ui.item.nextAll('.module_row').css('transform', 'translateY(' + uiHeight + 'px)');
                        },
                        change: function (e, ui) {
                            changeIndex = ui.placeholder.index();
                            var slice = false;
                            if (startIndex > changeIndex) {
                                var rows = $('.module_row');
                                slice = rows.slice(changeIndex, rows.length);
                            } else if (startIndex < changeIndex) {
                                slice = $('.module_row').slice(startIndex, changeIndex);
                                uiHeight = 0;
                            }
                            if (slice !== false) {
                                slice.not('.ui-sortable-helper').each(function () {
                                    $(this).css('transform', 'translateY(' + uiHeight + 'px)');
                                });
                            }
                            startIndex = changeIndex;
                        },
                        stop: function (e, ui) {
                            if (toggleCollapseRow) {
                                ui.item.removeClass('collapsed ui-sortable-row');
                                toggleCollapseRow = false;
                            }
                            $('.ui-sortable-handle, .module_row').css('transform', '');
                            self.dragScroll(true);
                        },
                        update: function (e, ui) {
							if (ui.item.hasClass('predesigned_row')) {
								$(document).trigger('tb_setpredesignedrows', [ui.item.data('slug'), function (data) {
									self.rowDrop(data, ui.item);
								}]);
							}
							if (ui.item.hasClass('tb_row_grid')) {
								$(document).trigger('tb_setpremaderows', [ui.item.data('slug'), function (data) {
									self.rowDrop(data, ui.item);
								}]);
							}
                        }
                    };
            if ('visual' === api.mode) {
                rowSortable.helper = function () {
                    return $('<div class="themify_builder_sortable_helper"/>');
                };
            }
            this.$el.sortable(rowSortable)
            this.columnSort(this.$el);
        },
        columnSort: function (el) {
            var before,
                    body = $('body'),
                    colums;
            el.find('.row_inner, .subrow_inner').sortable({
                items: '> .module_column',
                handle: '> .themify_builder_column_action .themify_builder_column_dragger',
                axis: 'x',
                placeholder: 'themify_builder_ui_state_highlight',
                tolerance: 'pointer',
                cursorAt: {
                    top: 20,
                    left: 20
                },
                beforeStart: function (e, ui) {
                    body.addClass('themify_builder_drag_start');
                    before = ThemifyBuilderCommon.Lightbox.clone(ui.item.closest('.module_row'));
                    colums = ui.item.siblings();
                    colums.css('marginLeft', 0);
                },
                start: function (e, ui) {
                    $('.themify_builder_ui_state_highlight').width(ui.item.width());
                },
                stop: function (e, ui) {
                    body.removeClass('themify_builder_drag_start');
                    colums.css('marginLeft', '');
                },
                update: function (e, ui) {
                    var inner = ui.item.closest('.ui-sortable'),
                            children = inner.children('.module_column');
                    children.removeClass('first last');
                    if (inner.hasClass('direction-rtl')) {
                        children.last().addClass('first');
                        children.first().addClass('last');
                    }
                    else {
                        children.first().addClass('first');
                        children.last().addClass('last');
                    }
                    api.Utils.columnDrag(inner, false);
                    api.Utils.setCompactMode(children);
                    var row = inner.closest('.module_row');
                    api.vent.trigger('dom:change', row.data('cid'), before, row, 'row');
                }
            });
        },
        update: function (el) {
            var type = api.activeModel !== null ? api.activeModel.get('elType') : api.Models.Registry.lookup(el.data('cid')).get('elType');
            if (api.mode === 'visual') {
                api.Utils.loadContentJs(el, type);
            }
            api.Mixins.Builder.initGridMenu(el[0], type !== 'module');
            api.Mixins.Builder.columnSort(el);
            var row = el.closest('.module_row');
            api.Utils.columnDrag(row.find('.row_inner'), false);
            api.Utils.columnDrag(row.find('.subrow_inner'), false);
            this.updateModuleSort(row);
        },
        dragScroll: function (off) {
            var body = $('body', top_iframe);
            if (api.mode === 'visual') {
                body = body.add($('body'));
            }
            if (this.top === undefined) {
                this.top = api.toolbar.$el;
                this.top = this.top.add($('#themify_builder_fixed_bottom_scroll', top_iframe));
                if (api.mode !== 'visual') {
                    this.top = this.top.add('#wpadminbar');
                }
            }
            if (off === true) {
                this.top.off('mouseenter');
                body.removeClass('themify_builder_drag_start');
                return;
            }
            var scrollEl = api.activeBreakPoint === 'desktop' ? $('body,html') : $('body,html', top_iframe),
                    bh = '',
                    wh = 0;
            if (api.mode === 'visual') {
                bh = scrollEl.height(),
                        wh = $(window.parent).height();
            }
            else {
                bh = $('#page-builder').height();
            }
            function onDragScroll(e) {
                var step = parseInt((bh - wh) / 7),
                        scroll = false;
                if (step > 0) {
                    var id = $(this).prop('id');
                    scroll = id === 'tb_toolbar' || id === 'wpadminbar' ? '-' : '+';
                    scroll += '=' + step + 'px';
                    scrollEl.stop().animate({
                        scrollTop: scroll
                    },
                    800);
                }
                else {
                    scrollEl.stop();
                }
            }
            body.addClass('themify_builder_drag_start');
            this.top.off('mouseenter').on('mouseenter', onDragScroll);
        },
        updateModuleSort: function (context, disable) {
            var items = disable ? $('.themify_module_holder') : context.find('.themify_module_holder');
            if (disable) {
                items.sortable(disable);
                return false;
            }
            items.each(function () {
                $(this).data().uiSortable = null;
            });
            var toggleCollapseMod = false,
                    row = false,
                    self = this;
            var moduleHolderArgs = {
                placeholder: 'themify_builder_ui_state_highlight',
                items: '.active_module',
                connectWith: '.themify_module_holder',
                revert: 100,
                scroll: false,
                tolerance: 'pointer',
                cursorAt: {
                    top: 20,
                    left: 110
                },
                beforeStart: function (e, ui) {
                    if (row === false) {
                        row = ThemifyBuilderCommon.Lightbox.clone(ui.item.closest('.module_row'));
                        if ('visual' === api.mode) {
                            ui.item.css('height', 40);
                            toggleCollapseMod = true;
                        }
                        self.dragScroll();
                    }
                },
                stop: function (e, ui) {
                    self.dragScroll(true);
                    if (e.type !== 'dragstop') {
                        if (toggleCollapseMod) {
                            ui.item.css('height', '');
                            toggleCollapseMod = false;
                        }
                        var moved_row = ui.item.closest('.module_row');
                        api.vent.trigger('dom:change', ui.item.data('cid'), row, moved_row, 'sort', {'before': row.data('cid'), 'after': moved_row.data('cid')});
                    }
                    row = false;
                },
                update: function (e, ui) {
                    if (ui.sender) {
                        // Make sub_row only can nested one level
                        if (ui.item.hasClass('module_subrow') && ui.item.parents('.module_subrow').length > 0) {
                            var $clone_for_move = ui.item.find('.active_module').clone();
                            $clone_for_move.insertAfter(ui.item);
                            ui.item.remove();
                        }
                        else {
                            api.Mixins.Builder.initGridMenu(ui.item[0]);
                        }
                        api.vent.trigger('dom:builder:change');
                    }
                    else if (ui.item.hasClass('tb_module_dragging_helper')) {
                        tempSettings = [];
                        self.moduleDrop(ui.item, false);
                    }
                }
            };
            if ('visual' === api.mode) {
                moduleHolderArgs.helper = function () {
                    return $('<div class="themify_builder_sortable_helper"/>');
                };
                moduleHolderArgs.handle = '.themify_builder_module_front_overlay, .themify_builder_subrow_top';
            }
            items.sortable(moduleHolderArgs);
        },
        initModuleDraggable: function (parent) {
            var self = this;
            parent.find('.themify_builder_module').draggable({
                appendTo: 'body',
                helper: 'clone',
                revert: 'invalid',
                scroll: false,
                connectToSortable: '.themify_module_holder',
                containment: '#themify_builder_row_wrapper,.themify_builder_content',
                cursorAt: {
                    top: 10,
                    left: 40
                },
                start: function (e, ui) {
                    self.dragScroll();
                    ui.helper.addClass('tb_module_dragging_helper').removeClass('themify_builder_module');
                    api.toolbar.preDesignedRows.btn.hide();
                },
                stop: function (e, ui) {
                    self.dragScroll(true);
                }
            });
        },
        initModuleVisualDrag: function () {
            var self = this;
            api.toolbar.$el.find('.themify_builder_module').ThemifyDraggable({
                iframe: '#themify_builder_site_canvas_iframe',
                dropitems: '.themify_module_holder',
                elements: '.active_module',
                onDragStart: function (e, drag) {
                    api.toolbar.preDesignedRows.btn.hide();
                    // api.toolbar.Panel.hide();
                },
                onDragEnd: function (e, drag) {
                    // api.toolbar.Panel.resetPanel();
                },
                onDrop: function (e, drag, drop) {
                    self.moduleDrop(drag, false);
                }
            });
        },
        initRowDraggable: function (parent) {
            var self = this;
			parent.find('.predesigned_row').draggable({
                appendTo: 'body',
                helper: 'clone',
                revert: 'invalid',
                connectToSortable: '#themify_builder_row_wrapper,.themify_builder_content',
                cursorAt: {
                    top: 10,
                    left: 40
                },
                start: function (e, ui) {
                    self.dragScroll();
                    ui.helper.addClass('tb_module_dragging_helper').find('.tb_predesigned_rows_list_image').remove();
                    api.toolbar.preDesignedRows.btn.hide();
                },
                stop: function (e, ui) {
                    self.dragScroll(true);
                }
            });
        },
		initGridsDraggable: function(parent){
			var self = this;
			parent.find('.tb_premade_rows_grid .tb_row_grid').draggable({
				appendTo: 'body',
				helper: 'clone',
				revert: 'invalid',
				connectToSortable: '#themify_builder_row_wrapper,.themify_builder_content,.themify_module_holder',
				cursorAt: {
					top: 10,
					left: 40
				},
				start: function (e, ui) {
					self.dragScroll();
					ui.helper.addClass('tb_module_dragging_helper');
					api.toolbar.preDesignedRows.btn.hide();
				},
				stop: function (e, ui) {
					self.dragScroll(true);
				}
			});
        },
        initRowGridVisualDrag: function () {
			var self = this;
			api.toolbar.$el.find('.tb_row_grid').ThemifyDraggable({
				iframe: '#themify_builder_site_canvas_iframe',
				dropitems: '.themify_module_holder, .module_row',
				append: false,
				onDragStart: function (e, drag) {
					api.toolbar.preDesignedRows.btn.hide();
				},
				onDrop: function (e, drag, drop) {
					$(document).trigger('tb_setpremaderows', [drag.data('slug'), function (data) {
						if( drop.hasClass('themify_module_holder') ){
							self.subRowDrop(data, drag);
						}else{
							self.rowDrop(data, drag);
						}
					}]);
				}
			});
		},
        initRowVisualDrag: function () {
            var self = this;
            api.toolbar.$el.find('.predesigned_row').ThemifyDraggable({
                iframe: '#themify_builder_site_canvas_iframe',
                dropitems: '.module_row',
                append: false,
                onDragStart: function (e, drag) {
                    api.toolbar.preDesignedRows.btn.hide();
                    // api.toolbar.Panel.hide();
                },
                onDragEnd: function (e, drag) {
                    // api.toolbar.Panel.resetPanel();
                },
                onDrop: function (e, drag, drop) {
                    $(document).trigger('tb_setpredesignedrows', [drag.data('slug'), function (data) {
                        self.rowDrop(data, drag);
                    }]);
                }
            });
        },
        subRowDrop: function( data, drag ){
		    if( ! drag.closest('.sub_column').length ){
				var subRowView = api.Views.init_subrow( data, api.mode );
				if( ! drag.parent().hasClass('themify_module_holder') ) {
					if (drag.prev().hasClass('themify_module_holder')) {
						drag.parent().find('> .themify_module_holder').append(subRowView.view.render().$el);
					} else {
						drag.parent().find('> .themify_module_holder').prepend(subRowView.view.render().$el);
					}
				}else{
                    drag[0].parentNode.replaceChild(subRowView.view.render().el, drag[0]);
                }

				var set_length = data[0].cols.length,
					col_cl;
				for (var i = 0; i < set_length; ++i) {
					col_cl = '';
					if( i === 0 ){
						col_cl = 'first ';
					}
					if( i === set_length - 1 ){
						col_cl = 'last ';
					}
					// Add column
					api.Utils._addNewColumn({
						newclass: col_cl + data[0].cols[i].grid_class,
						component: 'sub-column',
						type: api.mode
					}, subRowView.view.$el.find('.subrow_inner'));
				}
            }else{
		        drag.closest('.module_subrow')
                    .find('.themify_builder_grid_list [data-col="'+ drag.data('slug') +'"]')
                    .first()
                    .trigger('click');
            }
			drag.remove();


		},
        rowDrop: function (data, drag) {
            function callback() {
                var prev_row_id = drag.prev('.module_row'),
                        bid = drag.closest('.themify_builder_content').data('postid');
                prev_row_id = prev_row_id.length === 0 ? false : prev_row_id.data('cid');
                drag[0].innerHTML = '';
                drag[0].parentNode.replaceChild(fragment, drag[0]);
                api.saveCid = false;
                api.rowStyling = [];
                api.vent.trigger('dom:change', '', '', '', 'predesign', {'prev': prev_row_id, 'rows': rows, 'bid': bid});
                for (var i = 0, len = rows.length; i < len; ++i) {
                    api.Mixins.Builder.update(rows[i]);
                }
                ThemifyBuilderCommon.showLoader('hide');
            }
            var fragment = document.createDocumentFragment(),
                    rows = [];
            api.saveCid = true;
            for (var i in data) {
                if ((data[i].cols && (Object.keys(data[i].cols).length > 0 || (data[i].cols[0].styling && Object.keys(data[i].cols[0].styling).length > 0))) || (data[i].styling && Object.keys(data[i].styling).length > 0)) {
                    var row = api.Views.init_row(data[i], api.mode);
                    if (row !== false) {
                        var el = row.view.render();
                        fragment.appendChild(el.el);
                        rows.push(el.$el);
                    }
                }
            }
            if (api.mode === 'visual') {
                api.bootstrap(api.rowStyling, callback);
            }
            else {
                callback();
            }
        },
        moduleDrop: function (drag, drop) {
            var self = this;
            if( drag.hasClass('tb_row_grid') ){
				$(document).trigger('tb_setpremaderows', [drag.data('slug'), function (data) {
					self.subRowDrop(data, drag);
				}]);
				return;
            }
            var moduleView = api.Views.init_module({mod_name: drag.data('module-slug')}, api.mode),
                    module = moduleView.view.render();
            moduleView.model.set({is_new: 1}, {silent: true});
            if (drop) {
                drop.append(module.el);
            }
            else {
                drag.replaceWith(module.el);
            }
            moduleView.view.trigger('edit', null);
            api.hasChanged = true;
            if (api.mode === 'visual') {
                var settings = moduleView.model.getPreviewSettings();
                if (Object.keys(settings).length > 1) {//excluding cid
                    if (drag.data('type') === 'ajax') {
                        moduleView.model.trigger('custom:preview:refresh', settings);
                    }
                    else {
                        moduleView.model.trigger('custom:preview:live', settings);
                    }
                }
            }
        },
        newRowAvailable: function () {
            var row = this.$el.children('.module_row');
            if (row.length === 0 || row.last().find('.active_module').length > 0) {
                var rowDataPlainObject = {
                    cols: [{grid_class: 'col-full'}]
                },
                rowView = api.Views.init_row(rowDataPlainObject, this.type),
                        $template = rowView.view.render().$el;
                $template.appendTo(this.$el);
                this.$el.sortable(this.$el.sortable('option'));
                api.Mixins.Builder.updateModuleSort($template);
                if (api.mode === 'visual' && api.activeBreakPoint !== 'desktop') {
                    $('body', top_iframe).height(document.body.scrollHeight);
                }
            }
        },
        initGridMenu: function (el, deep) {
            var modules = deep === true ? el.getElementsByClassName('active_module') : [el];
            if (this.grid_menu === undefined) {
                this.grid_menu = ThemifyBuilderCommon.templateCache.get('tmpl-builder_grid_menu');
                this.grid_menu = $(this.grid_menu);
                this.grid_menu.find('.grid-layout--full').closest('li').addClass('selected');
                this.grid_menu = this.grid_menu[0].outerHTML;
            }
            for (var i = 0, len = modules.length; i < len; ++i) {
                if (modules[i].className.indexOf('module_subrow') === -1 && modules[i].getElementsByClassName('grid_menu').length === 0 && $(modules[i]).closest('.sub_column').length === 0) {
                    modules[i].insertAdjacentHTML('afterbegin', this.grid_menu);
                }
            }
        },
        toJSON: function () {
            var option_data = {};
            // rows
            var rows = this.el.getElementsByClassName('module_row');
            for (var i = 0, len = rows.length; i < len; ++i) {
                option_data[i] = api.Utils._getRowSettings(rows[i], i);
            }
            return option_data;
        }
    };

    api.Forms = {
        Data: {},
        Validators: {},
        bindEvents: function () {
            var $body = $('body', top_iframe),
                    actionEvent = 'true' === themifyBuilder.isTouch ? 'touchend' : 'click';
            $body
                    .on(actionEvent, '.builder_save_button', this.saveComponent)

                    .on('click', '#themify_builder_lightbox_parent .add_new a', this.moduleOptAddRow)
                    .on('click', '#builder_submit_import_form', this.builderImportSubmit)
                    .on('click', '.tb-lightbox-switcher a', this.lightbox_switcher)
                    /* Layout Action */
                    .on('click', '.layout_preview img', this.templateSelected)
                    .on('click', '#builder_submit_layout_form', this.saveAsLayout);
            $('body').on('themify_builder_lightbox_close', this.clear);
            this.widget_actions();
        },
        getFormTemplates: function () {
            var key = 'tb_form_templates';
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
                if(typeof data==='string'){
                    insert = data;
                }
                else{//old version data, can be removed in the versions 13.02.2018
                    for (var i in data) {
                        insert += data[i];
                    }
                }
                document.getElementsByTagName('body')[0].insertAdjacentHTML('beforeend', insert);
                insert = data = null;
            }
            var data = getData();
            if (data) {//cache visual templates)
                insert(data);
                return;
            }
            $.ajax({
                type: 'POST',
                url: themifyBuilder.ajaxurl,
                data: {
                    action: 'tb_load_form_templates',
                    tb_load_nonce: themifyBuilder.tb_load_nonce
                },
                success: function (resp) {
                    if (resp) {
                        insert(resp);	
                        setData(resp.replace(/\s\s+/g, ' '));
                    }
                }
            });
        },
        parseSettings: function (item, is_style, breakpoint, repeat) {
            var value = false,
                    $this = $(item),
                    cl = item.classList,
                    option_id = '',
                    checked = true;
            if (repeat) {
                option_id = $this.data('input-id');
            }
            else {
                option_id = item.getAttribute('name');
                if (!option_id) {
                    option_id = item.getAttribute('id');
                }
            }
            if (cl.contains('tb_lb_wp_editor')) {
                if (tinyMCE !== undefined) {
                    var tid = item.getAttribute('id'),
                            tiny = tinyMCE.get(tid);
                    value = tiny !== null ? (tiny.hidden === false ? tiny.getContent() : switchEditors.wpautop(tinymce.DOM.get(tid).value)) : $this.val();
                } else {
                    value = $this.val();
                }
            }
            else if (cl.contains('themify-checkbox')) {
                var cselected = [];
                $this.find('.tb-checkbox:checked').each(function (i) {
                    cselected.push($(this).val());
                });
                value = cselected.length > 0 ? cselected.join('|') : false;
                cselected = null;
                checked = false;
            }
            else if (cl.contains('themify-layout-icon')) {
                value = $this.find('.selected').prop('id');
            }
            else if (cl.contains('themify-option-query-cat')) {
                var parent = $this.parent(),
                        single_cat = parent.find('.query_category_single'),
                        multiple_cat = parent.find('.query_category_multiple');
                value = multiple_cat.val() ? multiple_cat.val() + '|multiple' : single_cat.val() + '|single';
            }
            else if (cl.contains('themify_builder_row_js_wrapper')) {
                value = [];
                var repeats = item.getElementsByClassName('tb_repeatable_field_content');
                for (var i = 0, len = repeats.length; i < len; ++i) {
                    var childs = repeats[i].getElementsByClassName('tb_lb_option_child');
                    value[i] = {};
                    for (var j = 0, clen = childs.length; j < clen; ++j) {
                        var v = this.parseSettings(childs[j], is_style, breakpoint, true);
                        if (v && v['v'] !== 'px' && v['v'] !== 'pixels' && v['v'] !== 'solid' && v['v'] !== 'default' && v['v'] !== '|') {
                            value[i][v['id']] = v['v'];
                        }
                    }
                }

            }
            else if (cl.contains('tb-radio-input-container')) {
                var input = $this.find('input:checked');
                if (breakpoint === 'desktop' || !input.hasClass('reponive_disable')) {
                    value = input.val();
                }
                checked = false;
            }
            else if (cl.contains('module-widget-form-container')) {
                value = $this.find(':input').themifySerializeObject();
            }
            else {
                value = $this.val();
                var opacity = $this.attr('data-opacity');
                if (opacity !== undefined && opacity !== '' && opacity != 1 && opacity !== '0.99') {
                    value += '_' + opacity;
                }
            }
            if (value || !is_style) {
                if (!is_style || (is_style && value !== 'px' && value !== 'pixels' && value !== 'solid' && value !== 'linear' && value !== 'default' && value !== '|' && (breakpoint === 'desktop' || $this.closest('.reponive_disable').length === 0))) {
                    if (!is_style && value === false) {
                        value = '';
                    }
                    return {'id': option_id, 'v': value, 'checked': checked};
                }
            }
            return false;
        },
        serialize: function (id, styles, breakpoint) {
            breakpoint = breakpoint || api.activeBreakPoint;
            var is_style = styles !== undefined;
            if (this.breakpoints === undefined && breakpoint !== 'desktop' && is_style) {
                this.breakpoints = Object.keys(themifyBuilder.breakpoints).reverse();
            }
            var result = {},
                    el = top_iframe.getElementById(id),
                    options = el.getElementsByClassName('tb_lb_option'),
                    breakpoints = breakpoint !== 'desktop' && is_style && this.breakpoints !== undefined ? this.breakpoints : false;
            if (breakpoints !== false) {
                var index = breakpoints.indexOf(breakpoint);
                for (var i = 0; i <= index; ++i) {
                    breakpoints.shift();
                }
                breakpoints.push('desktop');//sorted from small width to large
            }
            for (var i = 0, len = options.length; i < len; ++i) {
                var v = this.parseSettings(options[i], is_style, breakpoint, false);
                if (v !== false) {
                    if (breakpoints !== false && v['checked'] === true && options[i].tagName !== 'SELECT') {//don't save the same parent styles
                        var found = false;
                        for (var j = 0, blen = breakpoints.length; j < blen; ++j) {
                            if (styles['breakpoint_' + breakpoints[j]] !== undefined && styles['breakpoint_' + breakpoints[j]][v['id']] !== undefined && styles['breakpoint_' + breakpoints[j]][v['id']] === v['v']) {

                                found = true;
                                break;
                            }
                            else if (breakpoints[j] === 'desktop' && styles[v['id']] !== undefined && styles[v['id']] === v['v']) {
                                found = true;
                                break;
                            }
                        }

                        if (found) {
                            continue;
                        }
                    }
                    result[v['id']] = v['v'];
                }
            }
            return result;
        },
        saveComponent: function (e) {
            var auto_save = e === null;
            if (!auto_save) {
                e.preventDefault();
            }
            if (!api.hasChanged) {
                ThemifyBuilderCommon.Lightbox.close(auto_save);
                return;
            }
            var self = api.Forms,
                    id = api.activeModel.get('elType'),
                    is_module = id === 'module';
            if (is_module && !self.isValidate($('#tb_module_settings', top_iframe))) {
                return;
            }

            $('body').trigger('themify_builder_save_component');
            if (api.mode === 'visual') {
                // Trigger parent iframe
                window.top.jQuery('body').trigger('themify_builder_save_component');
            }

            api.saving = true;
            var result = {},
                    animation = null,
                    is_new = false,
                    column = false, //for the new modules of undo/redo
                    visible = null,
                    options = null,
                    is_first = false, //need for undo change, on the first saving we need to remove selector not to change
                    k = 'styling',
                    elem = $('.tb_element_cid_' + api.activeModel.cid);
            if (is_module || id === 'row') {
                if (is_module) {
                    k = 'mod_settings';
                    is_new = api.activeModel.get('is_new');
                    api.activeModel.unset('is_new', {silent: true});
                }
                options = self.serialize('themify_builder_options_setting');
                animation = self.serialize('themify_builder_options_animation');
                visible = self.serialize('themify_builder_options_visibility');
                if (api.mode === 'visual') {
                    if (visible['visibility_all'] === 'hide_all' || visible['visibility_desktop'] === 'hide' || visible['visibility_tablet'] === 'hide' || visible['visibility_mobile'] === 'hide') {
                        elem.addClass('tb_visibility_hidden');
                    }
                    else {
                        elem.removeClass('tb_visibility_hidden');
                    }
                }
            }
            if (api.mode === 'visual') {
                api.liveStylingInstance.remember(api.activeModel.cid);
            }
            if (!auto_save) {
                ThemifyBuilderCommon.Lightbox.close(auto_save);
            }
            else{
                $('body').trigger('themify_builder_lightbox_before_close');
            }
            var data = $.extend(true, {}, api.Mixins.Common.styleData),
                    old_data = $.extend(true, {}, api.activeModel.get(k)),
                    styling = data['breakpoint_desktop'];
            delete data['breakpoint_desktop'];
            for (var i in data) {
                styling[i] = data[i];
            }
            result[k] = $.extend(true, styling, options, animation, visible);
            result[k] = api.Utils.clear(result[k]);
            api.activeModel.set(result, {silent: true});
            if (is_module) {
                if (is_new) {
                    column = ThemifyBuilderCommon.Lightbox.clone(elem.closest('.module_column'));
                    api.Mixins.Builder.initGridMenu(elem[0]);
                }
                api.vent.trigger('dom:builder:change');
                $('.tb-import-layout-button').remove();
            }
            var saved_styles;
            if (api.mode === 'visual') {
                saved_styles = api.liveStylingInstance.getRememberedStyles();
                if (api.isComponentSaved[api.activeModel.cid] === undefined) {
                    api.isComponentSaved[api.activeModel.cid] = 1;
                    is_first = true;
                }
            }
            api.vent.trigger('dom:change', api.activeModel.cid, api.beforeEvent, elem, 'save', {old: old_data, 'new': result[k], styles: saved_styles, 'column': column, first: is_first});
            api.beforeEvent = false;
            api.saving = false;
            if (auto_save) {
                ThemifyBuilderCommon.Lightbox.close(auto_save);
            }
        },
        moduleOptAddRow: function (e, values) {
            e.preventDefault();
            var parent = $(e.currentTarget).parent().prev(),
                    template = api.cache.repeaterElements[ parent.prop('id') ].clone(),
                    row_count = Math.random().toString(36).substr(2, 7),
                    editors = [],
                    editor_cache = false,
                    uploader = false,
                    is_not_trigger = !e.isTrigger || values;
            template.removeClass('collapsed').find('.row_inner').show();
            var items = template[0].getElementsByClassName('tb_lb_option_child');

            values = values ? $(values) : false;
            for (var i = 0, len = items.length; i < len; ++i) {
                var $child = $(items[i]),
                        input = values ? values.find('[data-input-id="' + $child.data('input-id') + '"]') : false,
                        cl = items[i].classList;
                if (cl.contains('tb_lb_wp_editor')) {

                    var orig_id = $child.data('input-id'),
                            repeated_id = $child.data('control-repeater'),
                            p = $child.closest('.wp-editor-wrap'),
                            new_id = orig_id + '_' + i + '_' + Math.random().toString(36).substr(2, 7);
                    if (editor_cache === false) {
                        editor_cache = p[0].innerHTML;
                    }
                    p[0].innerHTML  = editor_cache.replace(new RegExp(orig_id, 'g'), new_id);
                    $child = p.find('.tb_lb_wp_editor');
                    $child.attr({'name': orig_id, 'data-input-id': orig_id, 'data-control-repeater': repeated_id}).data({'input-id': orig_id, 'control-repeater': repeated_id});
                    if (input) {
                        var tid = input.prop('id'),
                            tiny = tinyMCE.get(tid),
                            value = tiny.hidden === false ? tiny.getContent() : switchEditors.wpautop(tinymce.DOM.get(tid).value);
                        $child.val(value);
                    }
                    else {
                        $child.val('');
                    }
                    editors.push($child);
                }
                else if (cl.contains('themify-layout-icon')) {
                    var layouts = $child.find('a');
                    layouts.removeClass('selected');
                    if (input) {
                        layouts.filter('#' + input.find('.selected').prop('id')).addClass('selected');
                    }
                    else {
                        var m_defaults = themifyBuilder.modules[ api.activeModel.get('mod_name') ];
                        if (m_defaults !== undefined && m_defaults.defaults !== undefined && m_defaults.defaults[orig_id]) {
                            layouts.filter('#' + m_defaults.defaults[orig_id]).addClass('selected');
                        }
                        else {
                            layouts.first().addClass('selected');
                        }
                    }
                }
                else if (cl.contains('themify-builder-uploader-input')) {
                    if (is_not_trigger) {
                        uploader = true;
                        input = input !== false ? input.val() : '';
                        var p = $child.val(input).parent(),
                                placeholder = p.find('.thumb_preview').find('.img-placeholder');
                        p.find('.tb-upload-btn').prop('id', 'pluploader_' + row_count + '_' + i + 'themify-builder-plupload-upload-ui').addClass('plupload-clone')
                                .find('.builder_button').prop('id', 'pluploader_' + row_count + '_' + i + 'themify-builder-plupload-browse-button');
                        if (input !== '') {
                            var img_thumb = $('<img/>', {src: input, width: 50, height: 50});
                            placeholder.html(img_thumb);
                        }
                    }
                }
                else if (cl.contains('tb-radio-input-container')) {
                    var childs = items[i].getElementsByClassName('themify-builder-radio-dnd'),
                            oriname = $child.data('input-id'),
                            val = input ? input.find(':checked').val() : false;
                    for (var j = 0, clen = childs.length; j < clen; ++j) {
                        var $self = $(childs[j]);
                        $self.prop({name: oriname + '_' + row_count, id: oriname + '_' + row_count + '_' + j, checked: false})
                                .next('label').prop('for', oriname + '_' + row_count + '_' + j);
                        if (val === $self.val() || (!val && $self.data('checked'))) {
                            $self.prop('checked', true);
                        }
                    }
                    if (cl.contains('tb-option-radio-enable')) {
                        ThemifyBuilderCommon.Lightbox.clickRadioOption(null, $child.find(':checked'));
                    }
                }
                else {
                    var val = input ? input.val() : '';
                    if (val === undefined) {
                        val = '';
                    }
                    $child.val(val);
                }
                if (is_not_trigger) {
                    // Hide conditional inputs
                    if ($child.data('binding')) {
                        api.Mixins.Common.doTheBinding($child, val, template);
                    }
                    if ($child.data('control-binding') && !cl.contains('tb_lb_wp_editor') && !cl.contains('minicolors-input') && !cl.contains('themify-gradient')) {
                        api.Views.init_control($child.data('control-type'), {el: $child, binding_type: $child.data('control-binding'), selector: $child.data('live-selector')});
                    }

                }
            }
            if (is_not_trigger) {
                setTimeout(function () {
                    api.Utils.setColorPicker(template);
                }, 1);
            }
            parent[0].appendChild(template[0]);
            if (is_not_trigger) {
                if (editors.length > 0) {
                    setTimeout(function () {
                        for (var i = 0, len = editors.length; i < len; ++i) {
                            
                            api.Views.init_control('wp_editor', {el: editors[i], binding_type: editors[i].data('control-binding'), selector: editors[i].data('live-selector')});
                        }
                        editors = null;
                    }, 1);
                }
                if (uploader) {
                    api.Utils.builderPlupload('new_elemn');
                }
            }
        },
        builderImportSubmit: function (e) {
            e.preventDefault();

            var $this = $(this),
                    options = {
                        buttons: {
                            no: {
                                label: 'Replace Existing Builder'
                            },
                            yes: {
                                label: 'Append Existing Builder'
                            }
                        }
                    };

            ThemifyBuilderCommon.LiteLightbox.confirm(themifyBuilder.i18n.dialog_import_page_post, function (response) {
                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    data:
                            {
                                action: 'builder_import_submit',
                                nonce: themifyBuilder.tb_load_nonce,
                                data: $this.closest('form').serialize(),
                                importType: 'no' === response ? 'replace' : 'append',
                                importTo: themifyBuilder.post_ID
                            },
                    beforeSend: function (xhr) {
                        ThemifyBuilderCommon.showLoader('show');
                    },
                    success: function (data) {
                        api.Forms.reLoad(data, themifyBuilder.post_ID);
                        ThemifyBuilderCommon.Lightbox.close();
                    }
                });

            }, options);
        },
        lightbox_switcher: function (e) {
            e.preventDefault();
            var $this = $(e.currentTarget),
                    id = $this.attr('href').replace('#', '');
            if (api.activeModel && api.mode === 'visual') {
                api.scrollTo = api.liveStylingInstance.$liveStyledElmt;
            }
            $('.tb_breakpoint_switcher.breakpoint-' + id, top_iframe).trigger('click');
        },
        reLoad: function (data, id) {
            function callback() {
                if (api.mode === 'visual') {
                    api.Utils.loadContentJs();
                    api.id = false;
                }
                api.vent.trigger('dom:builder:init');
                ThemifyBuilderCommon.showLoader('hide');
                if (api.mode === 'visual' && api.activeBreakPoint !== 'desktop') {
                    $('body', top_iframe).height(document.body.scrollHeight);
                    setTimeout(function () {
                        $('body', top_iframe).height(document.body.scrollHeight);
                    }, 2000);
                }
            }
            api.Models.Registry.destroy();
            api.Instances.Builder = {};
            var el = '';
            if (api.mode === 'visual') {
                var linkId = 'themify-builder-' + id + '-generated-css',
                        css = $('link#' + linkId);
                el = '#themify_builder_content-' + id;
                api.id = id;
                css.remove();
                if (data.css !== undefined && data.css.css_file !== undefined) {
                    var link = '<link id=' + linkId + ' type="text/css" rel="stylesheet" href="' + data.css.css_file + '?tmp=' + Date.now() + '" />';
                    document.getElementById('themify-builder-admin-ui-css').insertAdjacentHTML('afterend', link);
                }
                api.VisualCache = [];
                api.editing = false;
                api.isComponentSaved = [];
                setTimeout(function () {
                    api.liveStylingInstance.reset();
                }, 1);
                $('body').addClass('sidebar-none full_width');
            }
            else {
                el = '#themify_builder_row_wrapper';
            }
            api.Instances.Builder[0] = new api.Views.Builder({el: el, collection: new api.Collections.Rows(data.builder_data), type: 'visual'});
            api.Instances.Builder[0].render();
            api.toolbar.undoManager.reset();
            if (api.mode === 'visual') {
                api.bootstrap(null, callback);
            }
            else {
                callback();
            }
        },
        templateSelected: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(this).closest('.layout_preview'),
                    options = {
                        buttons: {
                            no: {
                                label: 'Replace Existing Layout'
                            },
                            yes: {
                                label: 'Append Existing Layout'
                            }
                        }
                    };

            ThemifyBuilderCommon.LiteLightbox.confirm(themifyBuilder.i18n.confirm_template_selected, function (response) {
                var id = themifyBuilder.post_ID,
                        args = {
                            type: "POST",
                            url: themifyBuilder.ajaxurl,
                            dataType: 'json',
                            data: {
                                action: 'tb_set_layout',
                                mode: 'no' === response ? 1 : 0,
                                nonce: themifyBuilder.tb_load_nonce,
                                layout_slug: $this.data('slug'),
                                id: id,
                                layout_group: $this.data('group')
                            },
                            beforeSend: function () {
                                if ('visual' === api.mode) {
                                    ThemifyBuilderCommon.showLoader('show');
                                }
                            },
                            success: function (data) {
                                ThemifyBuilderCommon.Lightbox.close();
                                if (data.status === 'success') {
                                    api.Forms.reLoad(data, id);
                                } else {
                                    ThemifyBuilderCommon.showLoader('error');
                                    alert(data.msg);
                                }
                            }
                        };
                if ($this.data('group') === 'pre-designed') {
                    ThemifyBuilderCommon.showLoader('show');
                    var slug = $this.data('slug'),
                            file = 'https://themify.me/themify-layouts/' + slug + '.txt',
                            done = function () {
                                args.data.builder_data = api.layouts_selected[slug];
                                $.ajax(args);
                            };
                    if (!api.layouts_selected) {
                        api.layouts_selected = {};
                    }
                    else if (api.layouts_selected[slug]) {
                        done();
                        return;
                    }
                    $.get(file, null, null, 'text')
                            .done(function (data) {
                                api.layouts_selected[slug] = data;
                                done();
                            })
                            .fail(function (jqxhr, textStatus, error) {
                                ThemifyBuilderCommon.LiteLightbox.alert('There was an error in loading layout, please try again later, or you can download this file: (' + file + ') and then import manually (http://themify.me/docs/builder#import-export).');
                            })
                            .always(function () {
                                ThemifyBuilderCommon.showLoader();
                            });
                } else {
                    $.ajax(args);
                }
            }, options);
        },
        saveAsLayout: function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tb_save_custom_layout',
                    nonce: themifyBuilder.tb_load_nonce,
                    form_data: $('#tb_save_layout_form', top_iframe).serialize()
                },
                success: function (data) {
                    if (data.status === 'success') {
                        ThemifyBuilderCommon.Lightbox.close();
                    } else {
                        alert(data.msg);
                    }
                }
            });
        },
        widget_actions: function () {
            var cache = {},
                    settings_instance = false,
                    textInit = false,
                    mediaInit = false;

            $('body', top_iframe).on('change', '#class_widget', function () {
                var val = $(this).val(),
                        base = $(this).find(' :selected').data('idbase'),
                        widget_id = settings_instance['widget-id'],
                        callback = function (base, data) {
                            var instance = $('#instance_widget', top_iframe),
                                    form = $(data.form),
                                    initjJS = function (base) {
                                        if (base === 'text') {
                                            if (!textInit) {
                                                textInit = true;
                                                wp.textWidgets.init();
                                            }
                                        } else if (!mediaInit) {
                                            wp.mediaWidgets.init();
                                            mediaInit = true;
                                        }

                                        $(document).trigger('widget-added', [instance]);
                                        base === 'text' && api.Views.init_control('wp_editor', {el: instance.find('.wp-editor-area'), binding_type: 'refresh'});
                                    },
                                    recurisveLoader = function (js, i, base) {
                                        var len = js.length,
                                                loadJS = function (src, callback, condition) {
                                                    Themify.LoadAsync(src, callback, null, null, condition);
                                                };

                                        loadJS(js[i], function () {
                                            ++i;
                                            i < len ? recurisveLoader(js, i, base) : initjJS(base);
                                        });
                                    };

                            if (settings_instance) {
                                for (var i in settings_instance) {
                                    form.find('[name="' + i + '"]').val(settings_instance[i]);
                                }
                            }

                            form.find('.widget-id').val(Math.random());
                            form.find('select').wrap('<span class="selectwrapper"/>');
                            instance.html(form.html());

                            if (cache[base] === undefined && data.template) {
                                document.getElementsByTagName('body')[0].insertAdjacentHTML('beforeend', data.template);
                                data.src.length > 0 && recurisveLoader(data.src, 0, base);
                            } else if (data.init) {
                                initjJS(base, data.form);
                            }

                            api.mode === 'visual' && val && instance.find(':input').first().trigger('change');
                        };

                tempSettings['class_widget'] = val;

                if (cache[base] !== undefined && widget_id === cache[base][ 'widgetID' ]) {
                    callback(base, cache[base]);
                    return;
                }

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: themifyBuilder.ajaxurl,
                    data: {
                        action: 'module_widget_get_form',
                        tb_load_nonce: themifyBuilder.tb_load_nonce,
                        load_class: val,
                        tpl_loaded: +($('#tmpl-media-modal').length > 0),
                        id_base: base,
                        widget_instance: settings_instance
                    },
                    success: function (data) {
                        if (data && data.form) {
                            callback(base, data);

                            cache[base] = {
                                form: data.form,
                                init: data.src.length > 0,
                                widgetID: widget_id
                            };
                        }
                    }
                });
            });

            $('body').on('editing_module_option', function (e, type, settings, context) {
                if (type === 'widget' && settings.instance_widget) {
                    settings_instance = settings.instance_widget;
                    setTimeout(function () {
                        $('#class_widget', context).trigger('change');
                        settings_instance = false;
                    }, 200);
                }
            });
        },
        isValidate: function ($form) {
            var validate = $form.find('[data-validation]');
            if (validate.length === 0) {
                return true;
            }
            var that = this,
                    errors = {};
            validate.each(function () {
                var $this = $(this),
                        rule = $this.data('validation'),
                        value = $this.val();
                if (!that.checkValidate(rule, value)) {
                    errors[ $this.prop('id') ] = $this.data('error-msg');
                }
            });

            $form.find('.tb_field_error').removeClass('tb_field_error').end().find('.tb_field_error_msg').remove();

            if (!_.isEmpty(errors)) {
                var errorCount = 0;
                _.each(errors, function (msg, div_id) {
                    var $field = $form.find('#' + div_id);
                    $field.addClass('tb_field_error');
                    $('<span/>', {class: 'tb_field_error_msg', 'data-error-key': div_id}).text(msg).insertAfter($field);

                    if (!errorCount) {
                        var activeIndex = $form.children().index($field.closest('.themify_builder_options_tab_wrapper')),
                                errorTab = activeIndex > -1 && $form.closest('#themify_builder_lightbox_parent').find('.themify_builder_options_tab > li').eq(activeIndex);

                        errorTab && !errorTab.hasClass('current') && errorTab.trigger('click');
                        errorCount++;
                    }
                });
                return false;
            } else {
                return true;
            }
        },
        checkValidate: function (rule, value) {
            var validator = api.Forms.get_validator(rule);
            return validator(value);
        },
        clear: function () {
            if (api.activeModel) {
                if (api.activeModel.get('is_new') !== undefined) {
                    api.activeModel.trigger('dom:module:unsaved');
                }
                api.activeModel = null;
                if (tinyMCE !== undefined) {
                    for (var i = tinymce.editors.length - 1; i > -1; i--) {
                        if (tinymce.editors[i].id !== 'content') {
                            tinyMCE.execCommand("mceRemoveEditor", true, tinymce.editors[i].id);
                        }
                    }
                }
            }
        }
    };

    // Validators
    api.Forms.register_validator = function (type, fn) {
        this.Validators[ type ] = fn;
    };
    api.Forms.get_validator = function (type) {
        return this.Validators[type] !== undefined ? this.Validators[ type ] : this.Validators.not_empty; // default
    };

    api.Forms.register_validator('email', function (value) {
        var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                arr = value.split(','),
                errors = $.map(arr, function (v, i) {
                    return pattern.test(v) ? null : '1';
                });
        return !(errors.length > 0);
    });

    api.Forms.register_validator('not_empty', function (value) {
        return !(!value || '' === value.trim());
    });

    api.Views.Toolbar = Backbone.View.extend({
        events: {
            // Import
            'click .tb_import': 'import',
            // Layout
            'click .themify_builder_load_layout': 'loadLayout',
            'click .themify_builder_save_layout': 'saveLayout',
            // Duplicate
            'click .themify_builder_dup_link': 'duplicate',
            'click .tb_toolbar_save': 'save',
            'click .tb_toolbar_backend_edit a': 'save',
            'click .tb_toolbar_close_btn': 'panelClose',
            'click .tb_breakpoint_switcher': 'breakpointSwitcher',
            // Zoom
            'click .themify_builder_zoom': 'zoom',
            'click .tb_toolbar_zoom_menu_toggle': 'zoom',
            'click .tb_toolbar_builder_preview': 'previewBuilder'
        },
        initialize: function () {
            var moduleItems = [],
				that = this,
				panel = this.$el.find('.tb_module_panel_modules_wrap'),
				moduleItemTmpl = wp.template('builder_module_item_draggable');

            for (var slug in themifyBuilder.modules) {
                moduleItems.push(moduleItemTmpl({
                    slug: slug,
                    name: themifyBuilder.modules[slug].name,
                    type: (themifyBuilder.modules[slug].type ? themifyBuilder.modules[slug].type : ''),
                    favorite: +themifyBuilder.modules[slug].favorite
                }));
            }

            panel[0].innerHTML = moduleItems.join('') + '<span class="favorite-separator"></span>';
            $('#tmpl-builder_module_item_draggable').remove();
            if (api.mode === 'visual') {
                $('body', top_iframe)[0].appendChild(this.el);
            }
            moduleItems = null;
            setTimeout(function () {
                that.Panel.init();
                that.undoManager.init();
                if (api.mode === 'visual') {
                    window.top.jQuery('body').one('themify_builder_ready', function (e) {
                        new SimpleBar(panel[0]);
                    });
                    that.unload();
                }
                else {
                    new SimpleBar(panel[0]);
                }
                that.preDesignedRows.init();
            }, 1);
            setTimeout(function () {
                that.Revisions.init();
            }, 800);

            // Fire Module Favorite Toggle
            $('body', top_iframe).on('click', '.themify_module_favorite', that.toggleFavoriteModule);
            if (api.mode === 'visual') {
                $('body').on('click', '.themify_module_favorite', that.toggleFavoriteModule);
            }

			// Compact toolbar
			that.compactToolbar();
        },
		compactToolbar: function() {
			var _this = this,
				$body = $( 'body', top_iframe ),
				barInitCSS = {
					display: _this.$el.css( 'display' ),
					opacity: _this.$el.css( 'opacity' )
				},
				toolbarLimit;

			_this.$el.css( { display: 'block', opacity: 0 } );
			toolbarLimit = _this.$el.find( '.tb_toolbar_menu' ).outerWidth() + _this.$el.find( '.tb_toolbar_save_wrap' ).outerWidth();
			_this.$el.css( barInitCSS );

			function updateMobileLayout() {
				setTimeout( function() {
					$body.toggleClass( 'compact-toolbar', toolbarLimit > _this.$el.outerWidth() - 80 );
				}, 50 );
			}

			window.top.jQuery('body').on( 'themify_builder_ready', updateMobileLayout );
			$( window.top ).on( 'resize', updateMobileLayout );

			_this.$el.find( '.tb_toolbar_divider' ).each( function() {
				var elems = $( this ).nextUntil( '.tb_toolbar_divider' );

				if( elems.length > 1 && ! elems.eq(0).hasClass( 'tb_toolbar_compact_item' ) ) {
					var cElems = elems.clone();

					elems.eq(0).addClass( 'tb_toolbar_compact_item' )
						.append( '<ul class="tb_compact_group"></ul>' )
						.find( '.tb_compact_group' ).append( cElems );
				}
			} );
		},
        import: function (e) {
            e.preventDefault();
            var component = ThemifyBuilderCommon.detectBuilderComponent($(e.currentTarget)),
                    callback = null,
                    options = {
                        dataType: 'html',
                        data: {
                            action: 'builder_import',
                            type: component
                        }
                    };
            if (component !== 'file' || confirm(themifyBuilder.i18n.importFileConfirm)) {
                if (component === 'file') {
                    callback = function () {
                        api.Utils.builderPlupload('', true);
                    };
                }
                ThemifyBuilderCommon.Lightbox.open(options, null, callback);
            }
        },
        unload: function () {
            var self = this;
            if (api.mode === 'visual') {
                document.getElementsByTagName('head')[0].insertAdjacentHTML('afterbegin', '<base target="_parent">');
            }
            window.top.onbeforeunload = function () {
                return  !api.editing && self.undoManager.hasUndo() ? 'Are you sure' : null;
            };
        },
        panelClose: function (e) {
            e.preventDefault();
            window.parent.location.reload(true);
        },
        undoManager: {
            stack: [],
            is_working: false,
            index: -1,
            btnUndo: document.getElementsByClassName('tb-undo-btn')[0],
            btnRedo: document.getElementsByClassName('tb-redo-btn')[0],
            init: function () {
                var self = this;
                api.toolbar.$el.find('.tb-undo-redo').on('click', this.do_change.bind(this));
                if (!themifyBuilder.disableShortcuts) {
                    $(top_iframe).on('keydown', this.keypres.bind(this));
                    if (api.mode === 'visual') {
                        $(document).on('keydown', this.keypres.bind(this));
                    }
                }
                api.vent.on('dom:change', function (cid, before, after, type, data) {
                    if (api.hasChanged) {
                        api.editing = false;
                        if (after) {
                            after = ThemifyBuilderCommon.Lightbox.clone(after);
                        }
                        if (api.mode === 'visual' && (type === 'duplicate' || type === 'sort')) {
                            $(window).trigger('tfsmartresize.tfVideo');
                        }
                        self.stack.splice(self.index + 1, self.stack.length - self.index);
                        self.stack.push({'cid': cid, 'type': type, 'data': data, 'before': before, 'after': after});
                        self.index = self.stack.length - 1;
                        self.updateUndoBtns();
                        api.mode === 'visual' && $('body').trigger('builder_dom_changed', [type]);
                    }
                });
            },
            set: function (el) {
                var batch = el[0].querySelectorAll('[data-cid]');
                batch = Array.prototype.slice.call(batch);
                batch.unshift(el[0]);
                for (var i = 0, len = batch.length; i < len; ++i) {
                    var model = api.Models.Registry.lookup(batch[i].getAttribute('data-cid'));
                    if (model) {
                        model.trigger('change:view', batch[i]);
                    }
                }
            },
            doScroll: function (el) {
                var body = api.mode !== 'visual' || api.activeBreakPoint === 'desktop' ? $('body') : $('body', top_iframe);
                body.scrollTop(el.offset().top);
            },
            keypres: function (event) {
                // Redo
                if (90 === event.which && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    if ((true === event.ctrlKey && true === event.shiftKey) || (true === event.metaKey && true === event.shiftKey)) {
                        event.preventDefault();
                        if (this.hasRedo()) {
                            this.changes(false);
                        }
                    } else if (true === event.ctrlKey || true === event.metaKey) { // UNDO
                        event.preventDefault();
                        if (this.hasUndo()) {
                            this.changes(true);
                        }
                    }
                }
            },
            changes: function (is_undo) {
                var index = is_undo ? 0 : 1,
                        stack = this.stack[this.index + index];
                if (stack !== undefined) {
                    this.is_working = true;
                    var el = '',
                            type = stack['type'],
                            item = $('.tb_element_cid_' + stack['cid']),
                            cid = false;
                    if (type === 'row') {
                        if (is_undo) {
                            el = stack.before.clone(true);
                            cid = stack['cid'];
                        }
                        else {
                            el = stack.after.clone(true);
                            cid = stack.before.data('cid');
                            item = $('.tb_element_cid_' + cid);
                        }
                        this.set(el);
                        item.replaceWith(el);
                    }
                    else if (type === 'duplicate') {
                        if (is_undo) {
                            $('.tb_element_cid_' + stack.after.data('cid')).remove();
                        }
                        else {
                            el = stack.after.clone(true);
                            cid = stack.before.data('cid');
                            this.set(el);
                            item.after(el);
                        }
                    }
                    else if (type === 'delete_row') {
                        if (!is_undo) {
                            item.remove();
                        }
                        else {
                            el = stack.before.clone(true);
                            cid = stack['cid'];
                            var position = $('.tb_element_cid_' + stack.data.pos_cid);
                            this.set(el);
                            if (stack.data.pos === 'after') {
                                position.after(el);
                            }
                            else {
                                position.before(el);
                            }
                        }

                    }
                    else if (type === 'sort') {
                        cid = stack['cid'];
                        var after, before;
                        if (is_undo) {
                            after = stack.data['after'];
                            before = stack.data['before'];
                            el = stack.before.clone(true);
                        }
                        else {
                            after = stack.data['before'];
                            before = stack.data['after'];
                            el = stack.after.clone(true);
                            if (api.mode === 'visual') {
                                el.find('.active_module').css({'display': 'block', 'height': 'auto'});
                            }
                        }
                        this.set(el);
                        $('.tb_element_cid_' + after).find('.tb_element_cid_' + cid).remove();
                        $('.tb_element_cid_' + before).replaceWith(el);
                    }
                    else if (type === 'save') {
                        var cid = stack['cid'],
                                model = api.Models.Registry.lookup(cid),
                                is_module = model.get('elType') === 'module',
                                settings = {},
                                k = is_module ? 'mod_settings' : 'styling';
                        if (is_module && stack.data.column) {
                            if (is_undo) {
                                cid = false;
                                item.remove();
                            }
                            else {
                                cid = stack.data.column.data('cid');
                                var el = stack.data.column.clone(true);
                                item = $('.tb_element_cid_' + cid);
                                this.set(el);
                                item.replaceWith(el);
                            }
                        }
                        else {
                            if (is_undo) {
                                settings[k] = stack.data.old;
                                el = stack.before.clone(true);

                            }
                            else {
                                settings[k] = stack.data.new;
                                el = stack.after.clone(true);
                            }
                            model.set(settings, {silent: true});
                            this.set(el);
                            item.replaceWith(el);
                            if (api.mode === 'visual') {
                                api.liveStylingInstance.doUndo(stack.data.styles, is_undo ? stack.data.first : false);
                            }
                        }
                    }
                    else if (type === 'predesign') {
                        var rows = stack.data.rows;
                        if (is_undo) {
                            for (var i = 0, len = rows.length; i < len; ++i) {
                                $('.tb_element_cid_' + rows[i].data('cid')).remove();
                            }
                        }
                        else {
                            var fragment = document.createDocumentFragment(),
                                    el = [];
                            for (var i = 0, len = rows.length; i < len; ++i) {
                                var row = ThemifyBuilderCommon.Lightbox.clone(rows[i]);
                                fragment.appendChild(row[0]);
                                el.push(row);
                            }
                            if (stack.data.prev !== false) {
                                $('.tb_element_cid_' + stack.data.prev).after(fragment);
                            }
                            else {
                                $('#themify_builder_content-' + stack.data.bid).prepend(fragment);
                            }
                            for (var i = 0, len = el.length; i < len; ++i) {
                                this.set(el[i]);
                                api.Mixins.Builder.update(el[i]);
                            }
                        }
                    }
                    else if (type === 'import') {
                        var $builder = $('[data-postid="' + stack.data.bid + '"]'),
                                $elements = is_undo ? stack.data.before.clone(true) : stack.data.after.clone(true),
                                self = this;

                        $builder.children().remove();
                        $builder.prepend($elements);
                        $elements.each(function () {
                            self.set($(this));
                        });
                    }
                    if (cid) {
                        api.Mixins.Builder.update(el);
                    }
                    if (is_undo) {
                        this.index--;
                    }
                    else {
                        this.index++;
                    }
                    this.is_working = false;
                    this.updateUndoBtns();
                }
            },
            hasRedo: function () {
                return this.index < (this.stack.length - 1);
            },
            hasUndo: function () {
                return this.index !== -1;
            },
            disable: function () {
                this.btnUndo.classList.add('tb_disabled');
                this.btnRedo.classList.add('tb_disabled');
            },
            updateUndoBtns: function () {
                if (this.hasUndo()) {
                    this.btnUndo.classList.remove('tb_disabled');
                } else {
                    this.btnUndo.classList.add('tb_disabled');
                }
                if (this.hasRedo()) {
                    this.btnRedo.classList.remove('tb_disabled');
                } else {
                    this.btnRedo.classList.add('tb_disabled');
                }
            },
            reset: function () {
                this.stack = [];
                this.index = -1;
                this.updateUndoBtns();
            },
            do_change: function (e) {
                e.preventDefault();
                if (this.is_working === false && e.currentTarget.className.indexOf('tb_disabled') === -1) {
                    this.changes(e.currentTarget.className.indexOf('tb-undo-btn') !== -1);
                }
            }
        },
        // Layout actions
        loadLayout: function (e) {
            e.preventDefault();
            var self = this;
            function layoutLayoutsList(preview_list) {
                preview_list.each(function (i) {
                    if (i % 4 === 0) {
                        $(this).addClass('layout-column-break');
                    }
                    else {
                        $(this).removeClass('layout-column-break');
                    }
                });
            }
            var options = self.layouts_list ? {loadMethod: 'html', data: self.layouts_list} : {data: {action: 'tb_load_layout'}};
            ThemifyBuilderCommon.Lightbox.open(options,
                    null,
                    function (lightbox) {
                        lightbox = $(lightbox);
                        var container = lightbox.find('#themify_builder_tabs_pre-designed'),
                                filter = container.find('#themify_builder_pre-designed-filter');

                        /* the pre-designed layouts has been disabled */
                        if (container.length == 0) {
                            return;
                        }

                        function reInitJs() {
                            var preview_list = container.find('.layout_preview_list');
                            filter.show().find('a').on('click', function (e) {
                                e.preventDefault();
                                if (!$(this).hasClass('selected')) {
                                    var matched = preview_list;
                                    if ($(this).hasClass('all')) {
                                        matched.show();
                                    } else {
                                        preview_list.hide();
                                        matched = preview_list.filter('[data-category*="' + $(this).text() + '"]');
                                        matched.show();
                                    }
                                    layoutLayoutsList(matched);
                                    filter.find('a').removeClass('selected');
                                    $(this).addClass('selected');
                                    filter.parent().find('.tb_filter_layouts').html($(this).text());
                                }
                            });
                            container.find('#themify_builder_layout_search').on('keyup', function () {
                                var s = $.trim($(this).val()),
                                        matched = preview_list;
                                if (s === '') {
                                    matched.show();
                                } else {
                                    var selected = filter.find('a.all');
                                    if (!selected.hasClass('selected')) {
                                        selected.click();
                                    }
                                    preview_list.hide();
                                    matched = preview_list.find('.layout_title:contains(' + s + ')').closest('.layout_preview_list');
                                    matched.show();
                                }
                                layoutLayoutsList(matched);
                            });
                        }
                        if (self.layouts_list) {
                            reInitJs();
                            return;
                        }
                        ThemifyBuilderCommon.showLoader('show');
                        $.getJSON('https://themify.me/themify-layouts/index.json')
                                .done(function (data) {
                                    var template = window.top.wp.template('themify-builder-layout-item'),
                                            categories = {},
                                            html = '',
                                            parent = $(template(data));
                                    parent.find('li').each(function () {
                                        var cat = $(this).data('category').split(',');
                                        for (var i = 0, len = cat.length; i < len; ++i) {
                                            if ('' !== cat[i] && categories[cat[i]] === undefined) {
                                                html += '<li><a href="#">' + cat[i] + '</a></li>';
                                                categories[cat[i]] = 1;
                                            }
                                        }
                                    });
                                    categories = null;
                                    filter[0].insertAdjacentHTML('beforeend', html);
                                    container[0].insertAdjacentHTML('beforeend', parent[0].outerHTML);
                                    lightbox.find('.themify_builder_tab').each(function () {
                                        layoutLayoutsList($(this).find('.layout_preview_list'));
                                    });
                                    self.layouts_list = lightbox[0];
                                    reInitJs();
                                    new SimpleBar(lightbox[0]);
                                })
                                .fail(function (jqxhr, textStatus, error) {
                                    ThemifyBuilderCommon.LiteLightbox.alert($('#themify_builder_load_layout_error', container).show().text());
                                })
                                .always(function () {
                                    ThemifyBuilderCommon.showLoader('spinhide');
                                });
                    });
        },
        saveLayout: function (e) {
            e.preventDefault();
            var options = {
                data: {
                    action: 'tb_custom_layout_form',
                    postid: themifyBuilder.post_ID
                }
            };
            ThemifyBuilderCommon.Lightbox.open(options, function () {
                api.Utils.builderPlupload('normal');
            });
        },
        // Duplicate actions
        duplicate: function (e) {
            e.preventDefault();
            var self = this;
            function duplicatePageAjax() {
                self.Revisions.ajax({action: 'tb_duplicate_page', 'tb_is_admin': 'visual' !== api.mode}, function (url) {
                    url && (window.top.location.href = $('<div/>').html(url).text());
                });
            }
            if (confirm(themifyBuilder.i18n.confirm_on_duplicate_page)) {
                api.Utils.saveBuilder(duplicatePageAjax);
            } else {
                duplicatePageAjax();
            }
        },
        Revisions: {
            init: function () {
                api.toolbar.$el.find('.tb_revision').on('click', this.revision.bind(this));
                $('body', top_iframe)
                        .on('click', '.js-builder-restore-revision-btn', this.restore.bind(this))
                        .on('click', '.js-builder-delete-revision-btn', this.delete.bind(this));
            },
            revision: function (e) {
                e.preventDefault();
                if (e.currentTarget.classList.contains('tb_save_revision')) {
                    this.save();
                }
                else {
                    this.load();
                }
            },
            load: function () {
                var options = {
                    data: {
                        action: 'tb_load_revision_lists',
                        postid: themifyBuilder.post_ID,
                        tb_load_nonce: themifyBuilder.tb_load_nonce,
                    }
                };
                ThemifyBuilderCommon.Lightbox.open(options);
            },
            ajax: function (data, callback) {
                var _default = {
                    tb_load_nonce: themifyBuilder.tb_load_nonce,
                    postid: themifyBuilder.post_ID,
                };
                data = $.extend({}, data, _default);
                return $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    data: data,
                    beforeSend: function () {
                        ThemifyBuilderCommon.showLoader('show');
                    },
                    complete: function () {
                        ThemifyBuilderCommon.showLoader('hide');
                    },
                    success: function (data) {
                        if ($.isFunction(callback)) {
                            callback.call(this, data);
                        }
                    }
                });
            },
            save: function (callback) {
                var self = this;
                ThemifyBuilderCommon.LiteLightbox.prompt(themifyBuilder.i18n.enterRevComment, function (result) {
                    if (result !== null) {
                        api.Utils.saveBuilder(function () {
                            self.ajax({action: 'tb_save_revision', rev_comment: result}, callback);
                        }, 'main', 0, true);
                    }
                });
            },
            restore: function (e) {
                e.preventDefault();
                var revID = $(e.currentTarget).data('rev-id'),
                        self = this,
                        restoreIt = function () {
                            self.ajax({action: 'tb_restore_revision_page', revid: revID}, function (data) {
                                if (data.status) {
                                    api.Forms.reLoad(data, themifyBuilder.post_ID);
                                    ThemifyBuilderCommon.Lightbox.close();
                                } else {
                                    ThemifyBuilderCommon.showLoader('error');
                                    alert(data.data);
                                }
                            });
                        };

                ThemifyBuilderCommon.LiteLightbox.confirm(themifyBuilder.i18n.confirmRestoreRev, function (response) {
                    if ('yes' === response) {
                        self.save(restoreIt);
                    } else {
                        restoreIt();
                    }
                }, {
                    buttons: {
                        no: {
                            label: 'Don\'t Save'
                        },
                        yes: {
                            label: 'Save'
                        }
                    }
                });

            },
            delete: function (e) {
                e.preventDefault();
                if (!confirm(themifyBuilder.i18n.confirmDeleteRev)) {
                    return;
                }
                var $this = $(e.currentTarget),
                        self = this,
                        revID = $this.data('rev-id');
                self.ajax({action: 'tb_delete_revision', revid: revID}, function (data) {
                    if (!data.success) {
                        ThemifyBuilderCommon.showLoader('error');
                        alert(data.data);
                    }
                    else {
                        $this.closest('li').remove();
                    }
                });
            }
        },
        save: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var link = $(e.currentTarget).closest('.tb_toolbar_backend_edit').length > 0 ? $(e.currentTarget).prop('href') : false;
            api.Utils.saveBuilder(function (jqXHR, textStatus) {
                if (textStatus !== 'success') {
                    alert(themifyBuilder.i18n.errorSaveBuilder);
                }
                else if (link !== false) {
                    if (api.mode === 'visual') {
                        sessionStorage.setItem('focusBackendEditor', true);
                        window.top.location.href = link;
                    } else {
                        api.toolbar.undoManager.reset();
                        api._backendSwitchFrontend();
                    }
                }
            });
        },
        preDesignedRows: {
            is_init: null,
            rows: [],
            btn: null,
            filter_toolbar: null,
            filter_btn: null,
            clicked: null,
            init: function () {
                this.btn = $('<div class="tb_modules_panel_wrap" id="tb_plus_btn_popover"></div>');
                $('body').append(this.btn);
                setTimeout(function () {
                    //resolve dns and cache predessinged rows
                    var link = '<meta http-equiv="x-dns-prefetch-control" content="on"/><link href="//themify.me" rel="dns-prefetch preconnect"/>';
                    link += '<link href="//fonts.googleapis.com" rel="dns-prefetch"/>';
                    link += '<link href="//maps.google.com" rel="dns-prefetch"/>';
                    link += '<link href="https://themify.me/public-api/predesigned-rows/index.json" rel="prefetch"/>';
                    document.getElementsByTagName("head")[0].insertAdjacentHTML('afterbegin', link);
                }, 7000);
                var self = this;
                this.btn.on('click', '.add_module_btn', function (e) {
                    api.toolbar.Panel.add_module(e, self.clicked.closest('.module_row').find('.themify_module_holder').last());
                    self.clicked = null;
                    self.btn.hide();
                })
                        .on('keyup', '.tb_module_panel_search_text', this.search.bind(this));
                if (api.mode === 'visual') {
                    api.toolbar.$el.find('.tb_module_types a').on('click', this.tabs.bind(this));
                }
                api.toolbar.$el.find('.tb_module_panel_search_text').on('keyup', this.search.bind(this));
                $('body').on('click', '.tb_module_types a', this.tabs.bind(this))
                        .on('click', '.tb_row_btn_plus', this.show.bind(this));
                $(document).on('tb_setpredesignedrows', this.get.bind(this));
                $(document).on('tb_setpremaderows', this.grid.bind(this));
            },
            load: function (parent, callback) {
                if (this.is_init === null) {
                    this.is_init = true;
                    var self = this;
                    parent.addClass('tb_busy');
                    $.getJSON('https://themify.me/public-api/predesigned-rows/index.json')
                            .done(function (data) {
                                self.setData(data, parent, callback);
                            }).fail(function (jqxhr, textStatus, error) {
                        this.is_init = null;
                        ThemifyBuilderCommon.showLoader('error');
                        api.toolbar.$el.find('.tb_predesigned_rows_list').html('<h3>Failed to load Pre-Designed Rows from server.</h3>');
                    });
                }
            },
            setData: function (data, parent, callback) {
                var cats = [],
                        cat_html = '',
                        html = '';
                for (var i = 0, len = data.length; i < len; ++i) {
                    var tmp = data[i].category.split(','),
                            item_cats = '';
                    for (var j = 0, clen = tmp.length; j < clen; ++j) {
                        if (cats.indexOf(tmp[j]) === -1) {
                            cats.push(tmp[j])
                        }
                        item_cats += ' tb' + Themify.hash(tmp[j]);
                    }
                    if (data[i].thumbnail === undefined || data[i].thumbnail === '') {
                        data[i].thumbnail = 'https://placeholdit.imgix.net/~text?txtsize=24&txt=' + (encodeURI(data[i].title)) + '&w=181&h=77';
                    }
                    html += '<div class="predesigned_row' + item_cats + '" data-slug="' + data[i].slug + '"><figure class="tb_predesigned_rows_list_image">';
                    html += '<img alt="' + data[i].title + '" title="' + data[i].title + '" src="' + data[i].thumbnail + '" /></figure>';
                    html += '<div class="tb_predesigned_rows_list_title">' + data[i].title + '</div></div>';
                }
                data = null;
                cats.sort();
                for (var i = 0, len = cats.length; i < len; ++i) {
                    cat_html += '<li><a href="#" data-slug="' + Themify.hash(cats[i]) + '">' + cats[i] + '</li>';
                }

                api.toolbar.$el.find('.tb_row_filter')[0].insertAdjacentHTML('beforeend', cat_html);
                var predesigned = api.toolbar.$el.find('.tb_predesigned_rows_list');
                predesigned[0].insertAdjacentHTML('beforeend', html);
                this.btn[0].insertAdjacentHTML('beforeend', api.toolbar.$el.find('#tb_module_panel')[0].innerHTML);
                this.btn.find('.tb_module_panel_lock').remove();
                this.btn.find('.themify_builder_module_outer').show();
                this.btn.find('.tb_module_panel_search_text').val('');
                var self = this;
                predesigned.find('img').last().one('load', function () {
                    self.filter();
                    if (api.mode === 'visual') {
                        api.Mixins.Builder.initRowVisualDrag();
                        api.Mixins.Builder.initRowGridVisualDrag();
                    }
                    else {
                        api.Mixins.Builder.initRowDraggable(api.toolbar.$el);
                        api.Mixins.Builder.initGridsDraggable(api.toolbar.$el);
                    }
                    parent.removeClass('tb_busy');
                    new SimpleBar(predesigned[0]);
                    new SimpleBar(self.btn.find('.tb_module_panel_modules_wrap')[0]);
                    new SimpleBar(self.btn.find('.tb_predesigned_rows_list')[0]);
                    api.Mixins.Builder.initModuleDraggable(self.btn);
                    api.Mixins.Builder.initRowDraggable(self.btn);
                    api.Mixins.Builder.initGridsDraggable(self.btn);
                    if (typeof callback === 'function') {
                        callback();
                    }
                });
            },
            grid: function (e, slug, callback) {
                var cols = [];
                if( slug == 1 ){
					cols.push({"grid_class": "col-full"});
                }else {
					for (var i = 0; i < slug; i++) {
						cols.push({"grid_class": "col" + slug + "-1"});
					}
				}
				callback([{"cols":cols}]);
			},
            get: function (e, slug, callback) {
                ThemifyBuilderCommon.showLoader('show');
                if (this.rows[slug] !== undefined) {
                    if (typeof callback === 'function') {
                        callback(this.rows[slug]);
                    }
                    return;
                }
                var self = this;
                $.getJSON('https://themify.me/public-api/predesigned-rows/' + slug + '.txt')
                        .done(function (data) {
                            self.rows[slug] = data;
                            if (typeof callback === 'function') {
                                callback(data);
                            }

                        }).fail(function (jqxhr, textStatus, error) {
                    ThemifyBuilderCommon.showLoader('error');
                    alert('Failed to fetch row template');
                });
            },
            tabs: function (e) {
                e.preventDefault();
                e.stopPropagation();
                var elm = $(e.currentTarget),
                        target = elm.data('target'),
                        parent = elm.closest('.tb_modules_panel_wrap');
                if (this.is_init === null) {
                    this.load(parent);
                }
                parent.find('.' + target).show().siblings('.tb_module_panel_tab').hide();
                elm.closest('li').addClass('active').siblings().removeClass('active');
                parent.find('.tb_module_panel_search_text').val('');
            },
            show: function (e) {
                e.preventDefault();
                e.stopPropagation();
                var self = this;
                function callback() {
                    if (api.mode === 'visual' && api.activeBreakPoint !== 'desktop') {
                        $('body', top_iframe).height(document.body.scrollHeight + self.btn.outerHeight(true));
                        $('body').css('padding-bottom', 180);
                    }
                }
                this.clicked = $(e.currentTarget);
                var offset = this.clicked.offset();
                this.btn.css( { top: offset.top, left: offset.left - (this.btn.width() / 2) + 12 } ).show();
                if (this.is_init === null) {
                    this.load(this.btn, callback);
                }
                else {
                    callback();
                }
                this.hide();
            },
            hide: function () {
                var self = this;
                function callback(e) {
                    if (!self.btn.is(':hover')) {
                        self.btn.hide();
                        self.clicked = null;
                        $(document).off('click', callback);
                        $(top_iframe).off('click', callback);
                        if (api.mode === 'visual' && api.activeBreakPoint !== 'desktop') {
                            $('body', top_iframe).height(document.body.scrollHeight);
                            $('body').css('padding-bottom', '');
                        }
                    }
                }
                if (api.mode === 'visual') {
                    $(top_iframe).on('click', callback);
                }
                $(document).on('click', callback);
            },
            search: function (e) {
                var el = $(e.currentTarget),
                        parent = el.closest('.tb_modules_panel_wrap'),
                        is_module = parent.find('.tb_module_types .active a').data('target') === 'tb_module_panel_modules_wrap',
                        filter = !is_module && this.is_init ? (parent.prop('id') === 'tb_module_panel' ? this.filter_toolbar : this.filter_btn) : false,
                        search = is_module ? parent.find('.themify_builder_module_outer') : (this.is_init ? parent.find('.predesigned_row') : false),
                        s = $.trim(el.val());
                if (search !== false) {
                    var is_empty = s === '',
                            reg = !is_empty ? new RegExp(s, 'i') : false;
                    search.each(function () {
                        if (filter && !$(this).hasClass(filter)) {
                            return true;
                        }
                        var elm = is_module ? $(this).find('.module_name') : $(this).find('.tb_predesigned_rows_list_title');
                        if (is_empty || reg.test(elm.text())) {
                            $(this).show();
                        }
                        else {
                            $(this).hide();
                        }
                    });
                }
            },
            filter: function () {
                function filter(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var el = $(e.currentTarget),
                            slug = el.data('slug'),
                            parent = el.closest('.tb_modules_panel_wrap'),
                            active = parent.find('.tb_row_filter_active'),
                            rows = parent.find('.predesigned_row');
                    active.text(el.text());
                    parent.find('.tb_module_panel_search_text').val('');
                    var cl;
                    if (slug) {
                        cl = 'tb' + slug;
                        rows.each(function () {
                            if ($(this).hasClass(cl)) {
                                $(this).show();
                            }
                            else {
                                $(this).hide();
                            }
                        });
                    }
                    else {
                        rows.show();
                    }
                    if (parent.prop('id') === 'tb_module_panel') {
                        this.filter_toolbar = cl;
                    }
                    else {
                        this.filter_btn = cl;
                    }
                }
                api.toolbar.$el.find('.tb_row_filter').on('click', 'a', filter.bind(this));
                this.btn.find('.tb_row_filter').on('click', 'a', filter.bind(this));
            }
        },
        breakpointSwitcher: function (e) {
            e.preventDefault();
            if(!themifyBuilder.is_premium){
                return;
            }
            var w = '',
                    self = this,
                    breakpoint = 'desktop',
                    $this = $(e.currentTarget),
                    $body = $('body', top_iframe),
                    prevBreakPoint = api.activeBreakPoint;

            function callback() {
                self.responsive_grids(breakpoint, prevBreakPoint);
                api.mode === 'visual' && iframe.css('will-change', 'auto');

                $body
                        .toggleClass('tb_responsive_mode', breakpoint !== 'desktop')
                        .removeClass('builder-breakpoint-' + prevBreakPoint)
                        .addClass('builder-breakpoint-' + breakpoint);

                $('body').trigger('themify_builder_change_mode', [prevBreakPoint, breakpoint]);

                if (api.mode === 'visual') {
                    api.Mixins.Builder.updateModuleSort(null, breakpoint === 'desktop' ? 'enable' : 'disable');
                    api.Utils._onResize(true, function () {
                        self.iframeScroll(breakpoint !== 'desktop');
                        $('body', top_iframe).height(breakpoint !== 'desktop' ? document.body.scrollHeight : 'auto');

                        api.scrollTo && setTimeout(function () {
                            $(window).add(top_iframe).scrollTop(api.scrollTo.offset().top);
                            api.scrollTo = false;
                        }, 500);

                        setTimeout(function () {
                            api.Utils.setCompactMode(document.getElementsByClassName('module_column'));
                            $body.removeClass('tb_start_animate');
                        }, 200);
                    });
                } else {
                    $body.removeClass('tb_start_animate');
                }
            }

            if ($this.hasClass('breakpoint-tablet')) {
                breakpoint = 'tablet';
            } else if ($this.hasClass('breakpoint-tablet_landscape')) {
                breakpoint = 'tablet_landscape';
            } else if ($this.hasClass('breakpoint-mobile')) {
                breakpoint = 'mobile';
            }

            if (prevBreakPoint === breakpoint && e.originalEvent !== undefined)
                return false;
            api.activeBreakPoint = breakpoint;
            api.mode === 'visual' && ($body = $body.add('body'));
            $body.addClass('tb_start_animate'); //disable all transitions
            breakpoint !== 'desktop' && (w = api.Utils.getBPWidth(breakpoint) - 1);

            if (api.mode === 'visual') {
                // disable zoom if active
                var iframe = $('#themify_builder_site_canvas_iframe', top_iframe);
                $('.tb_toolbar_zoom_menu', top_iframe).removeClass('tb_toolbar_zoom_active').find('.tb_toolbar_zoom_menu_toggle').data('zoom', 100);
                w = ('tablet_landscape' === breakpoint && ThemifyBuilderCommon.Lightbox.dockMode.get() && $('.themify_builder_workspace_container', top_iframe).width() < w) ? $('.themify_builder_workspace_container', top_iframe).width() : w; // make preview fit the screen when dock mode active
				if(w && iframe.width() != w){
					iframe.css('will-change', 'width')
						.one(api.Utils.transitionPrefix(), callback).css('width', w).parent().removeClass('themify_builder_zoom_bg');
				} else {
					iframe.css('width', w).parent().removeClass('themify_builder_zoom_bg');
					callback();
				}
            }
            else {
                callback();
            }
        },
        iframeScroll: function (init) {
            var top = $(top_iframe);
            top.off('scroll.themifybuilderresponsive');
            if (init) {
                top.on('scroll.themifybuilderresponsive', function () {
                    window.scrollTo(0, $(this).scrollTop());
                });
            }
        },
        responsive_grids: function (type, prev) {
            var rows = document.querySelectorAll('.row_inner,.subrow_inner'),
                    is_desktop = type === 'desktop',
                    set_custom_width = is_desktop || prev === 'desktop';
            for (var i = 0, len = rows.length; i < len; ++i) {
                var base = rows[i].getAttribute('data-basecol');
                if (base) {
                    var columns = rows[i].children,
                            grid = rows[i].dataset['col_' + type],
                            first = columns[0],
                            last = columns[columns.length - 1];
                    if (!is_desktop) {
                        if (prev !== 'desktop') {
                            rows[i].classList.remove('tb_3col');
                            var prev_class = rows[i].getAttribute('data-col_' + prev);
                            if (prev_class) {
                                rows[i].classList.remove($.trim(prev_class.replace('tb_3col', '').replace('mobile', 'column').replace('tablet', 'column')));
                            }
                        }
                        if (!grid || grid === '-auto') {
                            rows[i].classList.remove('tb_grid_classes');
                            rows[i].classList.remove('col-count-' + base);
                        }
                        else {
                            var cl = rows[i].getAttribute('data-col_' + type);
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
                    if (set_custom_width) {
                        for (var j = 0, clen = columns.length; j < clen; ++j) {
                            var w = $(columns[j]).data('w');
                            if (w !== undefined) {
                                if (is_desktop) {
                                    columns[j].style['width'] = w + '%';
                                }
                                else {
                                    columns[j].style['width'] = '';
                                }
                            }
                        }
                    }
                    var dir = rows[i].getAttribute('data-' + type + '_dir');
                    if (dir === 'rtl') {
                        first.classList.remove('first')
                        first.classList.add('last');
                        last.classList.remove('last')
                        last.classList.add('first');
                        rows[i].classList.add('direction-rtl');
                    }
                    else {
                        first.classList.remove('last')
                        first.classList.add('first');
                        last.classList.remove('first')
                        last.classList.add('last');
                        rows[i].classList.remove('direction-rtl');
                    }
                }
            }
        },
        Panel: {
            el: null,
            is_locked: false,
            key: 'tb_module_panel_locked',
            init: function () {
                this.el = api.toolbar.$el.find('.tb_toolbar_add_modules_wrap');
                this.el.blur().hover(this.toogle.bind(this)).find('.tb_module_panel_lock').on('click', this.lock.bind(this));
                this._setupModulePanelState();
                this.el.find('.add_module_btn').on('click', this.add_module);
            },
            add_module: function (e, holder) {
                e.preventDefault();
                e.stopPropagation();
                holder = holder || api.Instances.Builder[0].$el.find('.module_row').last().find('.themify_module_holder').first();
                var top = holder.offset().top - 37;

                if (api.mode === 'visual') {
                    if (api.activeBreakPoint !== 'desktop') {
                        $(top_iframe).scrollTop(top);
                    }
                }
                else {
                    top -= 50;
                }
                $(window).scrollTop(top);
                api.Mixins.Builder.moduleDrop($(e.currentTarget).closest('.themify_builder_module'), holder);
            },
            resetPanel: function () {
                this.el.removeClass('tb_disabled');
                if (this.is_locked) {
                    this.toggleLock();
                }
            },
            toogle: function (e) {
                if (!this.is_locked && !this.el.hasClass('tb_disabled')) {
                    $(e.currentTarget).focus();
                }
            },
            lock: function (e) {
                e.preventDefault();
                this.is_locked = !this.is_locked;
                localStorage.setItem(this.key, this.is_locked);
                this.toggleLock();
                if (!this.is_locked) {
                    this.el.addClass('tb_hide_panel');
                    var self = this;
                    setTimeout(function () {
                        api.toolbar.$el.find('#themify_builder_switch_backend').focus();
                        self.el.removeClass('tb_hide_panel');
                    }, 1000);
                }
            },
            toggleLock: function () {
                var tollbars = api.toolbar.$el;
                if (api.mode === 'visual') {
                    tollbars = tollbars.add($('body'));
                    tollbars.addClass('tb_remove_transitions');
                }
                $('body', top_iframe).toggleClass('tb_module_panel_locked');
                $(window).trigger('tfsmartresize');
                tollbars.removeClass('tb_remove_transitions');
            },
            _setupModulePanelState: function () {
                this.is_locked = localStorage.getItem(this.key);
                if (this.is_locked === 'false') {
                    this.is_locked = false;
                }
                if (this.is_locked) {
                    var self = this;
                    setTimeout(function () {
                        self.toggleLock();
                    }, 1200);
                }
            },
            hide: function () {
                this.el.blur();
                if (this.is_locked) {
                    this.el.removeClass('tb_disabled');
                    var tollbars = api.toolbar.$el;
                    if (api.mode === 'visual') {
                        tollbars = tollbars.add($('body'));
                        tollbars.addClass('tb_remove_transitions');
                    }
                    $('body', top_iframe).removeClass('tb_module_panel_locked');
                    $(window).trigger('tfsmartresize');
                    tollbars.removeClass('tb_remove_transitions');
                }
                this.el.addClass('tb_disabled');
            },
            show: function () {
                if (!this.is_locked) {
                    this.el.focus();
                }
            }
        },
        toggleFavoriteModule: function () {
            var $this = $(this),
                    moduleBox = $this.closest('.themify_builder_module_outer'),
                    slug = $this.parent().data('module-slug');

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tb_module_favorite',
                    module_name: slug,
                    module_state: +!moduleBox.hasClass('favorited')
                },
                beforeSend: function (xhr) {
                    var prefix = api.Utils.transitionPrefix();
                    function callback(box, repeat) {

                        function finish() {
                            box.removeAttr('style');
                            if (repeat) {
                                var p = box.closest('#tb_plus_btn_popover').length > 0 ? api.toolbar.$el : $('#tb_plus_btn_popover');
                                callback(p.find('.module-type-' + slug).closest('.themify_builder_module_outer'), false);
                            }
                        }
                        if (!box.is(':visible')) {
                            box.toggleClass('favorited');
                            finish();
                            return;
                        }
                        box.css({
                            opacity: 0,
                            transform: 'scale(0.5)'
                        }).one(prefix, function () {
                            box.toggleClass('favorited').css({
                                opacity: 1,
                                transform: 'scale(1)'
                            }).one(prefix, finish);
                        });
                    }
                    callback(moduleBox, true);
                }
            });
        },
        zoom: function (e) {
            e.preventDefault();
            if ('desktop' !== api.activeBreakPoint)
                return true;

            var $link,
                    $this = $(e.currentTarget),
                    zoom_size = $this.data('zoom'),
                    $canvas = $('.themify_builder_site_canvas_iframe', top_iframe),
                    $parentMenu = $this.closest('.tb_toolbar_zoom_menu');

            if ($this.hasClass('tb_toolbar_zoom_menu_toggle')) {
                zoom_size = '100' == zoom_size ? 50 : 100;
                $this.data('zoom', zoom_size);
                $link = $this.next('ul').find('[data-zoom="' + zoom_size + '"]');
            } else {
                $link = $this;
                $parentMenu.find('.tb_toolbar_zoom_menu_toggle').data('zoom', zoom_size);
            }

            $canvas.removeClass('themify_builder_zooming_50 themify_builder_zooming_75');
            $link.parent().addClass('selected-zoom-size').siblings().removeClass('selected-zoom-size');
            if ('50' == zoom_size || '75' == zoom_size) {
                var scale = '50' == zoom_size ? 2 : 1.25;
                $canvas.addClass('themify_builder_zooming_' + zoom_size).parent().addClass('themify_builder_zoom_bg')
                        .css('height', Math.max(window.top.innerHeight * scale, 600));
                $parentMenu.addClass('tb_toolbar_zoom_active');
                api.zoomMeta.isActive = true;
                api.zoomMeta.size = zoom_size;
                $('body').addClass('tb-zoom-only');
            }
            else {
                $canvas.addClass('themify_builder_zooming_' + zoom_size).parent().css('height', '');
                $parentMenu.removeClass('tb_toolbar_zoom_active');
                api.zoomMeta.isActive = false;
                $('body').removeClass('tb-zoom-only');
            }
        },
        previewBuilder: function (e) {
            e.preventDefault();
            function hide_empty_rows() {
                if (api.isPreview) {
                    var row_inner = $('.col-count-1.row_inner');
                    row_inner.each(function () {
                        if (this.getElementsByClassName('active_module').length === 0) {
                            var column = this.getElementsByClassName('module_column')[0],
                                    mcolumn = api.Models.Registry.lookup(column.dataset.cid);
                            if (mcolumn && Object.keys(mcolumn.get('styling')).length === 0) {
                                var row = $(this).closest('.module_row'),
                                        mrow = api.Models.Registry.lookup(row.data('cid'));
                                if (mrow && Object.keys(mrow.get('styling')).length === 0) {
                                    row.addClass('tb-hide');
                                }
                            }

                        }
                    });
                }
                else {
                    $('.tb-hide.module_row').removeClass('tb-hide');
                }
            }
            $(e.currentTarget).toggleClass('tb_toolbar_preview_active');
            api.isPreview = !api.isPreview;
            $('body').toggleClass('tb-preview-only themify_builder_active');
            $('body', top_iframe).toggleClass('tb-preview-parent');

            if (api.isPreview) {
                this.Panel.hide();
            } else {
                this.Panel.resetPanel();
            }
            hide_empty_rows();
            $('.builder-breakpoint-' + api.activeBreakPoint + ' a.breakpoint-' + api.activeBreakPoint, top_iframe).trigger('click');
            api.vent.trigger('dom:preview');
        }
    });

    api.Views.bindEvents = function () {
        ThemifyBuilderCommon.Lightbox.setup();
        ThemifyBuilderCommon.LiteLightbox.modal.on('attach', function () {
            this.$el.addClass('themify_builder_lite_lightbox_modal');
        });
        api.Utils.mediaUploader();
        api.Utils.openGallery();
    };

    api.Utils = {
        onResizeEvents: [],
        gridClass: ['col-full', 'col4-1', 'col4-2', 'col4-3', 'col3-1', 'col3-2', 'col6-1', 'col5-1'],
        _onResize: function (trigger, callback) {
            var events = $._data(window, 'events')['resize'];
            if (tbLocalScript.fullwidth_support === '') {
                $(window.top).off('tfsmartresize.tb_visual').on('tfsmartresize.tb_visual', function (e) {
                    $(window).trigger('tfsmartresize.tbfullwidth').trigger('tfsmartresize.tfVideo');
                });
            }
            $(window.top).off('tfsmartresize.zoom').on('tfsmartresize.zoom', function () {
                if (api.zoomMeta.isActive) {
                    var scale = '50' == api.zoomMeta.size ? 2 : 1.25;
                    $('.themify_builder_workspace_container', top_iframe).css('height', Math.max(window.top.innerHeight * scale, 600));
                }
            });
            if (events !== undefined) {
                for (var i = 0, len = events.length; i < len; ++i) {
                    if (events[i].handler !== undefined) {
                        this.onResizeEvents.push(events[i].handler);
                    }
                }
            }
            $(window).off('resize');
            if (trigger) {
                var e = $.Event('resize', {type: 'resize', isTrigger: false});
                for (var i = 0, len = this.onResizeEvents.length; i < len; ++i) {
                    try {
                        this.onResizeEvents[i].apply(window, [e, $]);
                    }
                    catch (e) {
                    }
                }
                if (typeof callback === 'function') {
                    callback();
                }
            }

        },
        _addNewColumn: function (params, $context) {
            var columnView = api.Views.init_column({grid_class: params.newclass, component_name: params.component}, params.type);
            $context.append(columnView.view.render().$el);
        },
        filterClass: function (str) {
            var n = str.split(' '),
                    new_arr = [];

            for (var i = 0, len = n.length; i < len; ++i) {
                if (this.gridClass.indexOf(n[i]) !== -1) {
                    new_arr.push(n[i]);
                }
            }
            return new_arr.join(' ');
        },
        _getRowSettings: function ($base, index, type) {
            var cols = {},
                    type = type || 'row',
                    option_data = {},
                    styling,
                    model_r = api.Models.Registry.lookup($base.dataset.cid);
            if (model_r) {
                // cols
                var inner = $base.getElementsByClassName(type + '_inner')[0],
                        columns = inner.children;
                for (var i = 0, len = columns.length; i < len; ++i) {
                    var modules = {},
                            model_c = api.Models.Registry.lookup(columns[i].dataset.cid);
                    if (model_c) {
                        // mods
                        var modules = columns[i].getElementsByClassName('themify_module_holder'),
                                items = {};
                        if (modules.length > 0) {
                            modules = modules[0].children;
                            for (var j = 0, clen = modules.length; j < clen; ++j) {
                                var module_m = api.Models.Registry.lookup(modules[j].dataset.cid);
                                if (module_m) {
                                    styling = api.Utils.clear(module_m.get('mod_settings'), true);
                                    items[j] = {mod_name: module_m.get('mod_name')};
                                    if (Object.keys(styling).length > 0) {
                                        items[j]['mod_settings'] = styling;
                                    }
                                    // Sub Rows
                                    if (modules[j].className.indexOf('module_subrow') !== -1) {
                                        items[j] = this._getRowSettings(modules[j], j, 'subrow');
                                    }
                                }
                            }
                        }
                        cols[i] = {
                            column_order: i,
                            grid_class: this.filterClass(columns[i].className)
                        };
                        if (Object.keys(items).length > 0) {
                            cols[i]['modules'] = items;
                        }
                        var custom_w = parseFloat(columns[i].style.width);
                        if (custom_w > 0 && !isNaN(custom_w)) {
                            cols[i]['grid_width'] = custom_w;
                        }
                        styling = api.Utils.clear(model_c.get('styling'), true);
                        if (Object.keys(styling).length > 0) {
                            cols[i]['styling'] = styling;
                        }
                    }
                }

                option_data = {
                    row_order: index,
                    cols: cols,
                    column_alignment: model_r.get('column_alignment'),
                    gutter: model_r.get('gutter')
                };
                var default_data = {
                    gutter: 'gutter-default',
                    column_alignment: is_fullSection ? 'col_align_middle' : 'col_align_top'
                },
                row_opt = {
                    desktop_dir: 'ltr',
                    tablet_dir: 'ltr',
					tablet_landscape_dir: 'ltr',
                    mobile_dir: 'ltr',
                    col_tablet_landscape: '-auto',
                    col_tablet: '-auto',
                    col_mobile: '-auto'
                };
                for (var i in option_data) {
                    if (option_data[i] === '' || option_data[i] === default_data[i]) {
                        delete option_data[i];
                    }
                }
                styling = api.Utils.clear(model_r.get('styling'), true);
                for (var i in row_opt) {
                    var v = $.trim(inner.getAttribute('data-' + i));
                    if (v !== undefined && v !== '' && v !== row_opt[i]) {
                        option_data[i] = v;
                    }
                }
                if (Object.keys(styling).length > 0) {
                    option_data['styling'] = styling;
                }

            }
            return option_data;
        },
        selectedGridMenu: function (context) {
			var grids = context.getElementsByClassName('grid_menu'),
				directions = ['mobile', 'tablet', 'tablet_landscape', 'desktop'];

			for ( var i = 0, len = grids.length; i < len; ++i ) {
				var $this = $(grids[i]),
					handle = $this.data('handle');

				if (handle !== 'module') {
					var row = $this.closest('.module_' + handle),
						model = api.Models.Registry.lookup(row.data('cid')),
						grid_base = [],
						$base = row.find('.' + handle + '_inner').first(),
						gutter = model.get('gutter'),
						column_aligment = model.get('column_alignment'),
						dir = model.get('desktop_dir'),
						styling = model.get('styling'),
						cl = '',
						attr = {},
						columns = $base[0].children;

					for (var j = 0, clen = columns.length; j < clen; ++j) {
						grid_base.push(api.Utils._getColClass(columns[j].className.split(' ')));
						columns[j].className = columns[j].className.replace(/first|last/ig, '');
						if (clen !== 1) {
							if (j === 0) {
								columns[j].className += dir === 'rtl' ? ' last' : ' first';
							}
							else if (j === (clen - 1)) {
								columns[j].className += dir === 'rtl' ? ' first' : ' last';
							}
						}
					}

					var $selected = $this.find('.themify_builder_grid_desktop .grid-layout-' + grid_base.join('-')),
						$col = $selected.data('col');

					if ($selected.length > 0) {
						$selected.parent().addClass('selected').siblings().removeClass('selected');
						row.addClass('col-count-' + $col);
						cl = 'col-count-' + $col;
						attr['data-basecol'] = $col;
					}

					if (dir !== 'ltr') {
						cl += ' direction-rtl';
					}

					for (var j = 0; j < 4; ++j) {
						var dir = model.get(directions[j] + '_dir');

						if (dir !== 'ltr' && dir !== '') {
							attr['data-' + directions[j] + '_dir'] = dir;
							$selected = $this.find('.themify_builder_grid_' + directions[j] + ' .column-dir-' + dir);
							$selected.parent().addClass('selected').siblings().removeClass('selected');
						}
						if (directions[j] !== 'desktop') {
							var _col = model.get('col_' + directions[j]);
							if (_col !== '-auto' && _col !== '' && _col !== undefined) {
								attr['data-col_' + directions[j]] = _col;
								$selected = $this.find('.themify_builder_grid_' + directions[j] + ' .grid-layout-' + _col.replace(/column|tb_3col/ig, ''));
								$selected.parent().addClass('selected').siblings().removeClass('selected');
							}
						}
					}

					if (styling && styling['row_anchor'] !== undefined && styling['row_anchor'] !== '') {
						row.find('.row-anchor-name').first().text(styling['row_anchor']);
					}

					styling = null;
					if (column_aligment !== 'col_align_top') {
						$this.find('.column-alignment-' + column_aligment).parent().addClass('selected').siblings().removeClass('selected');
						cl += ' ' + column_aligment;
					}

					if (gutter !== 'gutter-default') {
						$this.find('.gutter_select').val(gutter);
						cl += ' ' + gutter;
					}

					$base.addClass(cl).attr(attr);
                }
            }
        },
        clear: function (items, clear_all, is_array) {
            var res = is_array ? [] : {};
            for (var i in items) {
                if (Array.isArray(items[i])) {
                    var data = this.clear(items[i], clear_all, true);
                    if (data.length > 0) {
                        res[i] = data;
                    }
                }
                else if (typeof items[i] === 'object') {
                    var data = this.clear(items[i], clear_all, false);
                    if (!$.isEmptyObject(data)) {
                        res[i] = data;
                    }
                }
                else if (items[i] && items[i] !== 'px' && items[i] !== 'pixels' && items[i] !== 'solid' && items[i] !== 'linear' && items[i] !== 'default' && items[i] !== '|') {
                    if (//remove old stored data
                            (i.indexOf('_gradient-css') !== -1)
                            || (i === 'cover_gradient_hover-css')
                            || (i === 'background_image-css')
                            || (i === 'background_image-type_gradient')
                            || (i.indexOf('gradient-angle') !== -1 && items[i] == '180')
                            || (i.indexOf('_padding_apply_all_padding') !== -1)
                            || (i.indexOf('_margin_apply_all_margin') !== -1)
                            || (i.indexOf('_border_apply_all_border') !== -1)
                            || (i === 'text_align_right')
                            || (i === 'text_align_center')
                            || (i === 'text_align_left')
                            || (i === 'text_align_justify')
                            || (items[i] === 'show' && i.indexOf('visibility_') !== -1)) {
                            continue;
                    }
                    else if (clear_all && (i === 'cover_gradient_hover-gradient' || i === 'background_image-gradient' || (i.indexOf('gradient-angle') === -1 && i.indexOf('_gradient-gradient') !== -1))) {
                        var mode = i.indexOf('background_gradient') !== -1 ? i.replace('_gradient', '_type').replace('-gradient', '') : i.replace('-gradient', '-type').replace('_gradient', '_color');
                        if (items[mode] !== 'gradient' && items[mode] !== 'cover_gradient' && items[mode] !== 'hover_gradient') {
                            var gfields = ['gradient-angle', 'type_image', 'circle-radial', 'gradient-type'],
                                    tmp_id = i.replace('-gradient', '');
                            for (var j = 0, len = gfields.length; j < len; ++j) {
                                var tmp = tmp_id + '-' + gfields[j];
                                if (items[tmp] !== undefined) {
                                    items[tmp] = res[tmp] = null;
                                    delete items[tmp];
                                    delete res[tmp];
                                }
                            }
                            continue;
                        }
                    }
                    res[i] = items[i];
                }

            }
            return res;
        },
        builderPlupload: function (action_text, is_import) {
            var class_new = is_import ? '' : (action_text === 'new_elemn' ? '.plupload-clone' : ''),
                    $builderPlupoadUpload = $('.themify-builder-plupload-upload-uic' + class_new, top_iframe);
            if ($builderPlupoadUpload.length > 0) {
                var self = this;
                if (self.pconfig === undefined) {
                    self.pconfig = JSON.parse(JSON.stringify(themify_builder_plupload_init));
                    self.pconfig['multipart_params']['_ajax_nonce'] = themifyBuilder.tb_load_nonce;
                    self.pconfig['multipart_params']['topost'] = themifyBuilder.post_ID;
                }
                $builderPlupoadUpload.each(function () {
                    var $this = $(this),
                            id1 = $this.prop('id'),
                            imgId = id1.replace('themify-builder-plupload-upload-ui', ''),
                            config = $.extend(true, {}, self.pconfig),
                            parts = ['browse_button', 'container', 'drop_element', 'file_data_name'];
                    config['multipart_params']['imgid'] = imgId;
                    for (var i = 0, len = parts.length; i < len; ++i) {
                        config[parts[i]] = imgId + self.pconfig[parts[i]];
                    }

                    if ($this.data('extensions')) {
                        config['filters'][0]['extensions'] = $this.data('extensions');
                    }
                    else {
                        config['filters'][0]['extensions'] = api.activeModel !== null ?
                                config['filters'][0]['extensions'].replace(/\,zip|\,txt/, '')
                                : 'zip,txt';
                    }
                    var uploader = new window.top.plupload.Uploader(config);
                    uploader.init();

                    // a file was added in the queue
                    uploader.bind('FilesAdded', function (up, files) {
                        up.refresh();
                        up.start();
                        ThemifyBuilderCommon.showLoader('show');
                    });

                    uploader.bind('Error', function (up, error) {
                        var $promptError = $('.prompt-box .show-error');
                        $('.prompt-box .show-login').hide();
                        $promptError.show();

                        if ($promptError.length > 0) {
                            $promptError.html('<p class="prompt-error">' + error.message + '</p>');
                        }
                        $('.overlay, .prompt-box').fadeIn(500);
                    });

                    // a file was uploaded
                    uploader.bind('FileUploaded', function (up, file, response) {
                        var json = JSON.parse(response['response']),
                                alertData = $("#themify_builder_alert", top_iframe),
                                status = '200' === response['status'] && !json.error ? 'done' : 'error';
                        if (json.error) {
                            ThemifyBuilderCommon.showLoader(status);
                            alert(json.error);
                            return;
                        }
                        if (is_import) {
                            var before = $('#themify_builder_row_wrapper').children().clone(true);
                            alertData.promise().done(function () {
                                api.Forms.reLoad(json, themifyBuilder.post_ID);
                                var after = $('#themify_builder_row_wrapper').children().clone(true);
                                ThemifyBuilderCommon.Lightbox.close();
                                api.vent.trigger('dom:change', '', '', '', 'import', {before: before, after: after, bid: themifyBuilder.post_ID});
                            });
                        }
                        else {
                            ThemifyBuilderCommon.showLoader(status);
                            var response_url = json.large_url ? json.large_url : json.url;
                            $this.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(response_url).trigger('change')
                                    .parent().find('.img-placeholder')
                                    .html($('<img/>', {src: json.thumb, width: 50, height: 50}));
                        }
                    });
                    $this.removeClass('plupload-clone');
                });
            }
        },
        columnDrag: function ($container, $remove, old_gutter, new_gutter) {
            var self = this;
            if ($remove) {
                var columns = $container ? $container.children('.module_column') : $('.module_column');
                columns.css('width', '');
                self.setCompactMode(columns);
            }
            var _margin = {
                default: 3.2,
                narrow: 1.6,
                none: 0
            };
            if (old_gutter && new_gutter) {
                var cols = $container.children('.module_column'),
                        new_margin = new_gutter === 'gutter-narrow' ? _margin.narrow : (new_gutter === 'gutter-none' ? _margin.none : _margin.default),
                        old_margin = old_gutter === 'gutter-narrow' ? _margin.narrow : (old_gutter === 'gutter-none' ? _margin.none : _margin.default),
                        margin = old_margin - new_margin;
                margin = parseFloat((margin * (cols.length - 1)) / cols.length);
                cols.each(function (i) {
                    if ($(this).prop('style').width) {
                        var w = parseFloat($(this).prop('style').width) + margin;
                        $(this).css('width', w + '%');
                    }
                });
                return;
            }
            var $cdrags = $container ? $container.children('.module_column').find('.themify_grid_drag') : $('.themify_grid_drag'),
                    _cols = {
                        default: {'col6-1': 14, 'col5-1': 17.44, 'col4-1': 22.6, 'col4-2': 48.4, 'col2-1': 48.4, 'col4-3': 74.2, 'col3-1': 31.2, 'col3-2': 65.6},
                        narrow: {'col6-1': 15.33, 'col5-1': 18.72, 'col4-1': 23.8, 'col4-2': 49.2, 'col2-1': 49.2, 'col4-3': 74.539, 'col3-1': 32.266, 'col3-2': 66.05},
                        none: {'col6-1': 16.666, 'col5-1': 20, 'col4-1': 25, 'col4-2': 50, 'col2-1': 50, 'col4-3': 75, 'col3-1': 33.333, 'col3-2': 66.666}
                    },
            $min = 5;
            $cdrags.each(function () {

                var $el,
                        $row,
                        $columns,
                        $current,
                        $el_width = 0,
                        dir,
                        cell = false,
                        cell_w = 0,
                        before = false,
                        $helperClass,
                        row_w,
                        dir_rtl,
                        origpos;
                $(this).draggable({
                    axis: 'x',
                    cursor: 'col-resize',
                    distance: 0,
                    scroll: false,
                    snap: false,
                    containment: '.row_inner',
                    helper: function (e) {
                        $el = $(e.currentTarget);
                        $row = $el.closest('.subrow_inner');
                        if ($row.length === 0) {
                            $row = $el.closest('.row_inner');
                        }
                        dir = $el.hasClass('themify_drag_right') ? 'w' : 'e';
                        $helperClass = dir === 'w' ? 'themify_grid_drag_placeholder_right' : 'themify_grid_drag_placeholder_left',
                                before = ThemifyBuilderCommon.Lightbox.clone($row.closest('.module_row'));
                        return $('<div class="ui-widget-header themify_grid_drag_placeholder ' + $helperClass + '"></div><div class="ui-widget-header themify_grid_drag_placeholder"></div>');
                    },
                    start: function (e, ui) {
                        $columns = $row.children('.module_column');
                        $current = $el.closest('.module_column');
                        dir_rtl = $row.hasClass('direction-rtl');
                        if (dir === 'w') {
                            cell = dir_rtl ? $current.prev('.module_column') : $current.next('.module_column');
                            $el_width = $el.outerWidth();
                        }
                        else {
                            cell = dir_rtl ? $current.next('.module_column') : $current.prev('.module_column');
                            $el_width = $current.outerWidth();
                        }
                        cell_w = cell.outerWidth() - 2;
                        origpos = ui.position.left;
                        row_w = $row.outerWidth();
                    },
                    stop: function (e, ui) {
                        $('.themify_grid_drag_placeholder').remove();
                        var percent = Math.ceil(100 * ($current.outerWidth() / row_w));
                        $current.css('width', percent + '%');
                        var cols = _cols.default,
                                margin = _margin.default;
                        if ($row.hasClass('gutter-narrow')) {
                            cols = _cols.narrow;
                            margin = _margin.narrow;
                        }
                        else if ($row.hasClass('gutter-none')) {
                            cols = _cols.none;
                            margin = _margin.none;
                        }
                        var cellW = margin * ($columns.length - 1);
                        $columns.each(function (i) {
                            if (i !== cell.index()) {
                                var w;
                                if ($(this).prop('style').width) {
                                    w = parseFloat($(this).prop('style').width);
                                }
                                else {
                                    var col = $.trim(self.filterClass($(this).attr('class')).replace('first', '').replace('last', ''));
                                    w = cols[col];
                                }
                                cellW += w;
                            }
                        });
                        cell.css('width', (100 - cellW) + '%');
                        cell = cell.add($current);
                        self.setCompactMode(cell);
                        var after = $row.closest('.module_row');
                        api.vent.trigger('dom:change', after.data('cid'), before, after, 'row');
                    },
                    drag: function (e, ui) {

                        if (cell && cell.length > 0) {
                            var px = $el_width + (dir === 'e' ? -(ui.position.left) : ui.position.left),
                                    $width = parseFloat((100 * px) / row_w);
                            if ($width >= $min && $width < 100) {
                                var $max = cell_w + origpos + (dir === 'w' ? -(ui.position.left) : ui.position.left),
                                        $max_percent = parseFloat((100 * $max) / row_w);

                                if ($max_percent > $min && $max_percent < 100) {
                                    cell.css('width', $max + 'px');
                                    $current.css('width', px + 'px').children('.' + $helperClass).html($width.toFixed(2) + '%');
                                    $current.children('.themify_grid_drag_placeholder').last().html($max_percent.toFixed(2) + '%');
                                    self.setCompactMode($current);
                                }
                            }
                        }

                    }

                });
            });
        },
        setCompactMode: function (col) {
            var self = this;
            if (col instanceof jQuery) {
                col = col.get();
            }
            for (var i = 0, len = col.length; i < len; ++i) {
                var w = col[i].clientWidth,
                        cl = w < 234 ? ['compact-mode-action', 'compact-mode'] : (w < 370 ? ['compact-mode'] : '');
                col[i].classList.remove('compact-mode-action');
                col[i].classList.remove('compact-mode');
                if (cl !== '') {
                    for (var j in cl) {
                        col[i].classList.add(cl[j]);
                    }
                }
                if ((cl === '' || cl[0] === 'compact-mode')) {
                    var sub_column = col[i].getElementsByClassName('sub_column');
                    if (sub_column.length > 0) {
                        self.setCompactMode(sub_column);
                    }
                }
            }
        },
        initNewEditor: function (editor_id) {
            var $settings = tinyMCEPreInit.mceInit['tb_lb_hidden_editor'];
            $settings['elements'] = editor_id;
            $settings['selector'] = '#' + editor_id;
            // v4 compatibility
            return this.initMCEv4(editor_id, $settings);
        },
        initMCEv4: function (editor_id, $settings) {
            // v4 compatibility
            if (parseInt(tinyMCE.majorVersion) > 3) {
                // Creates a new editor instance
                var ed = new tinyMCE.Editor(editor_id, $settings, tinyMCE.EditorManager);
                ed.render();
                return ed;
            }
        },
        initQuickTags: function (editor_id) {
            // add quicktags
            if (typeof window.parent.QTags === 'function') {
                window.parent.quicktags({id: editor_id});
                window.parent.QTags._buttonsInit();
            }
        },
        setColorPicker: function (context) {
            $('.minicolors-swatch', context).each(function () {
                var $this = $(this),
                        parent = $this.closest('.minicolors_wrapper');
                $(this).one('click', function () {
                    var input = parent.find('.minicolors-input');
                    parent.prepend(input).find('.minicolors').remove();
                    api.hasChanged = true;
                    api.Views.init_control('color', {el: input, binding_type: input.data('control-binding')});
                }).prev('.minicolors-input').one('focusin', function () {
                    $(this).next('.minicolors-swatch').trigger('click');
                });
                parent.find('.color_opacity').one('change', function () {
                    $this.prev('.minicolors-input').attr('data-opacity', $(this).val());
                    $this.trigger('click');
                });
            });
        },
        _getColClass: function (classes) {
            for (var i = 0, len = classes.length; i < len; ++i) {
                if (this.gridClass.indexOf(classes[i]) !== -1) {
                    return classes[i].replace('col', '');
                }
            }
        },
        saveBuilder: function (callback, saveto, i, onlyData) {
            saveto = saveto || 'main';
            i = i || 0;
            if (i === 0) {
                if (saveto === 'main' && api.activeModel) {
                    $('.builder_save_button', top_iframe).trigger('click');
                }
                ThemifyBuilderCommon.showLoader('show');
            }
            var len = Object.keys(api.Instances.Builder).length,
                    view = api.Instances.Builder[i],
                    self = this,
                    id = view.$el.data('postid'),
                    data = view.toJSON();

            function sendData(id, data, saveto, onlyData) {
                var data = {
                    action: 'tb_save_data',
                    tb_load_nonce: themifyBuilder.tb_load_nonce,
                    id: id,
                    data: JSON.stringify(data),
                    tb_saveto: saveto,
                    sourceEditor: 'visual' === api.mode ? 'frontend' : 'backend'
                };

                if (onlyData) {
                    data.only_data = onlyData;
                }
                return $.ajax({
                    type: 'POST',
                    url: themifyBuilder.ajaxurl,
                    cache: false,
                    data: data
                });
            }
            ;
            sendData(id, data, saveto, onlyData).always(function (jqXHR, textStatus) {
                ++i;
                if (len === i) {
                    // load callback
                    if ($.isFunction(callback)) {
                        callback.call(self, jqXHR, textStatus);
                    }
                    if (textStatus !== 'success') {
                        ThemifyBuilderCommon.showLoader('error');
                    }
                    else {
                        ThemifyBuilderCommon.showLoader('hide');
                        api.editing = true;
                        $('body').trigger('themify_builder_save_data', [jqXHR, textStatus]);
                    }
                }
                else {
                    setTimeout(function () {
                        self.saveBuilder(callback, saveto, i);
                    }, 50);
                }
            });
        },
        loadContentJs: function (el, type) {
            ThemifyBuilderModuleJs.loadOnAjax(el, type); // load module js ajax
            // hook
            if (api.saving === false) {
                var mediaelements = $('.wp-audio-shortcode, .wp-video-shortcode', el).not('.mejs-container')
                        .filter(function () {
                            return !$(this).parent().hasClass('mejs-mediaelement');
                        });
                if (mediaelements.length > 0) {
                    if (themifyBuilder.media_css) {
                        for (var i in themifyBuilder.media_css) {
                            Themify.LoadCss(themifyBuilder.media_css[i]);
                        }
                        themifyBuilder.media_css = null;
                    }
                    var settings = typeof window.top._wpmejsSettings !== 'undefined' ? window.top._wpmejsSettings : {};
                    mediaelements.mediaelementplayer(settings);
                }
            }
            $('body').trigger('builder_load_module_partial', [el, type]);
        },
        mediaUploader: function () {
            var _frames = {};
            $('body', top_iframe).on('click', '.themify-builder-media-uploader', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $el = $(this),
                        file_frame,
                        $builderInput = $el.closest('.themify_builder_input'),
                        title = $el.data('uploader-title'),
                        text = $el.data('uploader-button-text'),
                        type = $el.data('library-type') ? $el.data('library-type') : 'image',
                        hkey = Themify.hash(type + title + text);
                if (_frames[hkey] !== undefined) {
                    file_frame = _frames[hkey];
                }
                else {
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: title,
                        library: {
                            type: type
                        },
                        button: {
                            text: text
                        },
                        multiple: false
                    });
                    _frames[hkey] = file_frame;
                }
                file_frame.off('select').on('select', function () {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    $builderInput.find('.themify-builder-uploader-input').val(attachment.url).trigger('change')
                            .parent().find('.img-placeholder')
                            .html($('<img/>', {
                                src: attachment.url,
                                width: 50,
                                height: 50
                            }));
                    api.hasChanged = true;
                    $builderInput.find('.themify-builder-uploader-input-attach-id').val(attachment.id);
                });
                // Finally, open the modal
                file_frame.open();

            }).on('click', '.themify-builder-delete-thumb', function (e) {
                e.preventDefault();
                api.hasChanged = true;
                $(this).prev().empty().closest('.themify_builder_input').find('.themify-builder-uploader-input').val('').trigger('change');

            }).on('click', '.insert-media', function (e) {
                api.hasChanged = true;
                window.top.wpActiveEditor = $(this).data('editor');
            });
        },
        openGallery: function () {
            var clone = wp.media.gallery.shortcode,
                    $self = this,
                    file_frame = null;
            $('body', top_iframe).on('click', '.tb-gallery-btn', function (e) {
                e.preventDefault();
                var shortcode_val = $(this).closest('.themify_builder_input').find('.tb-shortcode-input');
                if (file_frame === null) {
                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        frame: 'post',
                        state: 'gallery-edit',
                        title: wp.media.view.l10n.editGalleryTitle,
                        editing: true,
                        multiple: true,
                        selection: false
                    });
                    file_frame.$el.addClass('themify_gallery_settings');
                }
                wp.media.gallery.shortcode = function (attachments) {
                    var props = attachments.props.toJSON(),
                            attrs = _.pick(props, 'orderby', 'order');

                    if (attachments.gallery) {
                        _.extend(attrs, attachments.gallery.toJSON());
                    }
                    attrs.ids = attachments.pluck('id');
                    // Copy the `uploadedTo` post ID.
                    if (props.uploadedTo) {
                        attrs.id = props.uploadedTo;
                    }
                    // Check if the gallery is randomly ordered.
                    if (attrs._orderbyRandom) {
                        attrs.orderby = 'rand';
                        delete attrs._orderbyRandom;
                    }
                    // If the `ids` attribute is set and `orderby` attribute
                    // is the default value, clear it for cleaner output.
                    if (attrs.ids && 'post__in' === attrs.orderby) {
                        delete attrs.orderby;
                    }
                    // Remove default attributes from the shortcode.
                    _.each(wp.media.gallery.defaults, function (value, key) {
                        if (value === attrs[key]) {
                            delete attrs[key];
                        }
                    });
                    var shortcode = new window.top.wp.shortcode({
                        tag: 'gallery',
                        attrs: attrs,
                        type: 'single'
                    });

                    shortcode_val.val(shortcode.string()).trigger('change');

                    wp.media.gallery.shortcode = clone;
                    return shortcode;
                };

                file_frame.on('update', function (selection) {
                    var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                    shortcode_val.val('[' + shortcode + ']');
                    $self.setShortcodePreview(selection.models, shortcode_val);
                    api.hasChanged = true;
                });

                if ($.trim(shortcode_val.val()).length > 0) {
                    file_frame = wp.media.gallery.edit($.trim(shortcode_val.val()));
                    file_frame.state('gallery-edit').on('update', function (selection) {
                        var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                        shortcode_val.val('[' + shortcode + ']');
                        $self.setShortcodePreview(selection.models, shortcode_val);
                        api.hasChanged = true;
                    });
                } else {
                    file_frame.open();
                    file_frame.$el.find('.media-menu .media-menu-item').last().trigger('click');
                }

            });

        },
        setShortcodePreview: function (images, $input) {
            var $preview = $input.next('.tb_shortcode_preview'),
                    html = '';
            if ($preview.length === 0) {
                $preview = $('<div class="tb_shortcode_preview"></div>');
                $input.after($preview);
            }
            for (var i = 0, len = images.length; i < len; ++i) {
                var attachment = images[i].attributes,
                        url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                html += '<img src="' + url + '" width="50" height="50" />';
            }
            $preview[0].innerHTML = html;
        },
        createGradientPicker: function ($input, value, update) {
            var $field = $input.closest('.themify-gradient-field'),
                    instance = null, // the ThemifyGradient object instance
                    is_removed = false,
                    $id = $input.data('id'),
                    $angleInput = $field.find('#' + $id + '-gradient-angle'),
                    gradient = $input.prev(),
                    args = {
                        angle: $angleInput.val(),
                        onChange: function (stringGradient, cssGradient) {
                            if (is_removed) {
                                stringGradient = cssGradient = '';
                            }
                            if ('visual' === api.mode) {
                                if ($id === 'cover_gradient' || $id === 'cover_gradient_hover') {

                                    api.liveStylingInstance.addOrRemoveComponentOverlay($id, cssGradient);
                                }
                                else {
                                    api.liveStylingInstance.bindBackgroundGradient($id, cssGradient);
                                }

                            }
                            $input.val(stringGradient);
                            themifybuilderapp.hasChanged = true;
                        },
                        onInit: function () {
                            gradient.show();
                        }
                    };

            args.gradient = value ? value : ($input.data('default-gradient') ? $input.data('default-gradient') : undefined);

            if (!update) {
                gradient.ThemifyGradient(args);
            }
            instance = $input.prev().data('themifyGradient');
            // Linear or Radial select field
            var type = $field.find('#' + $id + '-gradient-type'),
                    circle = $field.find('#' + $id + '-circle-radial input'),
                    callback = function (val) {
                        var $angelparent = $angleInput.parent('.gradient-angle-knob'),
                                $radial_circle = $field.find('#' + $id + '-circle-radial');

                        if (val === 'radial') {
                            $angelparent.hide();
                            $angelparent.next('span').hide();
                            $radial_circle.show();
                        }
                        else {
                            $angelparent.show();
                            $angelparent.next('span').show();
                            $radial_circle.hide();
                        }
                    };
            if (update) {
                instance.settings = $.extend({}, instance.settings, args);
                instance.settings.type = type.val();
                instance.settings.circle = circle.is(':checked');
                instance.isInit = false;
                instance.update();
                instance.isInit = true;
            }
            else {
                $field.find('.themify-clear-gradient').on('click', function (e) {
                    e.preventDefault();
                    is_removed = true;
                    instance.settings.gradient = $.ThemifyGradient.default;
                    instance.update();
                    is_removed = false;
                });

                type.on('change', function (e) {
                    instance.setType($(this).val());
                    callback($(this).val());
                });

                circle.on('change', function () {
                    instance.setRadialCircle($(this).is(':checked'));
                });
                $angleInput.on('change', function () {
                    var $val = parseInt($(this).val());
                    if (!$val) {
                        $val = 0;
                    }
                    instance.setAngle($val);
                }).knob({
                    change: function (v) {
                        instance.setAngle(Math.round(v));
                    }
                });

                // angle input popup style
                $angleInput.removeAttr('style').parent().addClass('gradient-angle-knob').find('canvas').insertAfter($angleInput);
            }
            callback(type.val());
        },
        toRGBA: function (color) {
            var colorArr = color.split('_'),
                    patt = /^([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
            if (colorArr[0] !== undefined) {
                var matches = patt.exec(colorArr[0].replace('#', '')),
                        opacity = colorArr[1] !== undefined && colorArr[1] != '0.99' ? colorArr[1] : 1;
                return matches ? 'rgba(' + parseInt(matches[1], 16) + ', ' + parseInt(matches[2], 16) + ', ' + parseInt(matches[3], 16) + ', ' + opacity + ')' : color;
            }
            return color;
        },
        // get breakpoint width
        getBPWidth: function (device) {
            var breakpoints = _.isArray(themifyBuilder.breakpoints[ device ]) ? themifyBuilder.breakpoints[ device ] : themifyBuilder.breakpoints[ device ].toString().split('-');
			return breakpoints[ breakpoints.length - 1 ];
        },
        transitionPrefix: function () {
            if (this.transitionPrefix.pre === undefined) {
                var el = document.createElement('fakeelement'),
                        transitions = {
                            transition: 'transitionend',
                            OTransition: 'oTransitionEnd',
                            MozTransition: 'transitionend',
                            WebkitTransition: 'webkitTransitionEnd'
                        }

                for (var t in transitions) {
                    if (el.style[t] !== undefined) {
                        this.transitionPrefix.pre = transitions[t];
                        break;
                    }
                }
            }
            return this.transitionPrefix.pre;
        }
    };

    _.extend(api.Views.BaseElement.prototype, api.Mixins.Common);
    _.extend(api.Views.Builder.prototype, api.Mixins.Builder);

    /**
     * Form control views.
     */

    api.Views.ControlRegistry = {
        items: {},
        register: function (id, object) {
            this.items[id] = object;
        },
        lookup: function (id) {
            return this.items[id] || null;
        },
        remove: function (id) {
            delete this.items[id];
        },
        destroy: function () {
            _.each(this.items, function (view, cid) {
                view.remove();
            });
            this.items = {};
        }
    };

    api.Views.Controls[ 'default' ] = Backbone.View.extend({
        initialize: function (args) {
            api.Views.ControlRegistry.register(this.$el.prop('id'), this);
            if (args.binding_type) {
                this.binding_type = args.binding_type;
            }
            if (args.selector) {
                this.selector = args.selector;
            }
        },
        preview_element: function (value) {
            if (this.binding_type === undefined) {
                return;
            }
            api.hasChanged = true;
            var type = this.$el.data('control-type'),
                    repeater_id = type === 'repeater' ? this.$el.prop('id') : this.$el.data('control-repeater');
            if (repeater_id && api.mode === 'visual') {
                var $repeater = $('#' + repeater_id, top_iframe);
                if ($repeater.length > 0) {
                    tempSettings[ repeater_id ] = api.Forms.parseSettings($repeater[0]).v;
                }
            }
            else {
                tempSettings[ this.$el.prop('id') ] = value;
            }

            if (api.mode === 'visual') {
                if ('live' === this.binding_type) {
                    api.activeModel.trigger('custom:preview:live', tempSettings, type === 'wp_editor' || this.el.tagName === 'TEXTAREA', null, this.selector, value, this.$el);
                } else if ('refresh' === this.binding_type) {
                    api.activeModel.trigger('custom:preview:refresh', tempSettings, this.selector, value, this.$el);
                }
            }
            else if (api.activeModel.get('elType') === 'module') {
                api.activeModel.backendLivePreview();
            }
        }
    });

    api.Views.Controls.default.extend = function (child) {
        var self = this,
                view = Backbone.View.extend.apply(this, arguments);
        view.prototype.events = _.extend({}, this.prototype.events, child.events);
        view.prototype.initialize = function () {
            if (_.isFunction(self.prototype.initialize))
                self.prototype.initialize.apply(this, arguments);
            if (_.isFunction(child.initialize))
                child.initialize.apply(this, arguments);
        }
        return view;
    };

    api.Views.register_control = function (type, args) {
        if ('default' !== type) {
            this.Controls[ type ] = this.Controls.default.extend(args);
        }
    };

    api.Views.get_control = function (type) {
        return this.control_exists(type) ? this.Controls[ type ] : this.Controls.default;
    };

    api.Views.control_exists = function (type) {

        return this.Controls.hasOwnProperty(type);
    };

    api.Views.init_control = function (type, args) {
        args = args || {};
        if (!args['binding_type'] && !args.el.hasClass('minicolors-input')) {
            args['binding_type'] = 'refresh';
        }
        if (!type) {
            type = 'change';
        }
        else if ('wp_editor' === type && args.el.hasClass('data_control_binding_live')) {
            args['binding_type'] = 'live';
        }
        var id = args.el.data('input-id');
        if (!id) {
            id = args.el.prop('id');
        }
        if('wp_editor' !== type){
            var exist = this.ControlRegistry.lookup(id);
            if (exist !== null) {
                exist.setElement(args.el).render();
                return exist;
            }
        }
        var control = api.Views.get_control(type);
        return new control(args);

    };

    // Register core controls
    api.Views.register_control('wp_editor', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    timer = 'refresh' === this.binding_type && this.selector === undefined ? 1000 : 50,
                    this_option_id = this.$el.prop('id'),
                    previous = '',
                    callback = _.throttle(function (e) {
                        var content = this.type === 'setupeditor' ? this.getContent() : $(this).val();
                        if (api.activeModel === null || previous === content) {
                            return;
                        }
                        previous = content;
                        if (is_widget !== false) {
                            that.$el.val(content).trigger('change');
                        }
                        else {
                            that.preview_element(content);
                        }
                    }, timer);
            api.Utils.initQuickTags(this_option_id);
            if (tinyMCE !== undefined && this.binding_type !== undefined) {

                if (!_.isUndefined(tinymce.editors[ this_option_id ])) { // clear the prev editor
                    tinyMCE.execCommand("mceRemoveEditor", true, this_option_id);
                }

                var ed = api.Utils.initNewEditor(this_option_id),
                        is_widget = this.$el.hasClass('wp-editor-area') ? this.$el.closest('#instance_widget').length > 0 : false;
                ed.on('change keyup', callback);
            }
            this.$el.on('change keyup', callback);
            return this;
        }
    });

    api.Views.register_control('change', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    timer = 'refresh' === this.binding_type && this.selector === undefined ? 1000 : 50;
            var event = this.$el.data('control-event');
            if (event === undefined || event === '') {
                event = 'change';
                timer = 1;
            }
            this.$el.on(event, _.throttle(function () {
                that.preview_element(this.value);
            }, timer));
            return this;
        }
    });


    api.Views.register_control('query_category', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    parent = that.$el.parent(),
                    single_cat = parent.find('.query_category_single'),
                    multiple_cat = parent.find('.query_category_multiple');

            single_cat.add(multiple_cat).on('change', function (e) {
                var is_single = $(this).hasClass('query_category_single'),
                        option_value = !is_single ? (multiple_cat.val() + '|multiple') : (single_cat.val() + '|single');
                if (is_single) {
                    multiple_cat.val($(this).val());
                }
                that.preview_element(option_value);
            })
            return this;
        }
    });
    api.Views.register_control('layout', {
        initialize: function () {
            this.render();
        },
        render: function () {
			var that = this,
				this_option_id = this.$el.data('input-id'),
				defaultLayout = that.$('.tfl-icon.selected').prop('id');

				if(!this_option_id){
					this_option_id = this.$el.prop('id');
				}

			this.$( '.tfl-icon' ).on( 'click', function ( e ) {
				e.preventDefault();

				var $this = $( this ),
					selectedLayout = $this.prop('id');
				
				$this.addClass( 'selected' ).siblings().removeClass( 'selected' );

				if ('visual' === api.mode && 'live' === that.binding_type && that.$el.data('control-selector') !== undefined) {
					var $elmtToApplyTo = api.liveStylingInstance.$liveStyledElmt,
						prevLayout = api.liveStylingInstance.getStylingVal(this_option_id)
					if (that.$el.data('control-selector') !== '') {
						$elmtToApplyTo = api.liveStylingInstance.$liveStyledElmt.find(that.$el.data('control-selector'));
					}
					tempSettings[ this_option_id ] = selectedLayout;
					if (this_option_id === 'layout_feature') {
						selectedLayout = 'layout-' + selectedLayout;
						prevLayout = 'layout-' + prevLayout;
					}
					else if (this_option_id === 'columns') {
						selectedLayout = this_option_id + '-' + selectedLayout;
						prevLayout = this_option_id + '-' + prevLayout;
					}
					if (!prevLayout && defaultLayout) {
						prevLayout = defaultLayout;
					}
					$elmtToApplyTo.removeClass(prevLayout).addClass(selectedLayout);

					if (this_option_id === 'layout_feature') {
						selectedLayout = selectedLayout.substr(7);
					}
					else if (this_option_id === 'columns') {
						selectedLayout = selectedLayout.substr(8);
					}
					api.liveStylingInstance.setStylingVal(this_option_id, selectedLayout);
					api.hasChanged = true;
				} else {
					that.preview_element(selectedLayout);
				}
            });
            return this;
        }
    });

    api.Views.register_control('radio', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this;

            this.$('input[type="radio"]').on('change', function () {
                that.preview_element(this.value);
            });
            return this;
        }
    });

    api.Views.register_control('checkbox', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this;
            this.$('input[type="checkbox"]').on('click', function () {
                if (!_.isUndefined(that.binding_type)) {
                    var checked = that.$('input[type="checkbox"]:checked').map(function () {
                        return this.value;
                    }).get();

                    that.preview_element(checked.join('|'));
                }
            });
            return this;
        }
    });

    api.Views.register_control('color', {
        is_typing: false,
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    $colorOpacity = this.$el.next('.color_opacity'),
                    id = this.$el.prop('id');
            this.$el.minicolors({
                opacity: 1,
                changeDelay: 200,
                beforeShow: function () {
                    var lightbox = ThemifyBuilderCommon.Lightbox.$lightbox,
                            p = that.$el.closest('.minicolors'),
                            el = p.find('.minicolors-panel');
                    el.css('visibility', 'hidden').show();//get offset
                    if ((lightbox.offset().left + lightbox.width()) <= el.offset().left + el.width()) {
                        p.addClass('tb-minicolors-right');
                    }
                    else {
                        p.removeClass('tb-minicolors-right');
                    }
                    el.css('visibility', '').hide();
                },
                change: function (hex, opacity) {
                    if (!hex) {
                        opacity = '';
                    }
                    else if (opacity && '0.99' == opacity) {
                        opacity = 1;
                    }
                    if (!that.is_typing && !$colorOpacity.is(':focus')) {
                        $colorOpacity.attr('data-opacity', opacity).data('opacity', opacity).val(opacity);
                    }
                    var value = hex ? $(this).minicolors('rgbaString') : '';
                    if (that.binding_type !== undefined) {
                        that.preview_element(value);
                    }
                    else if (api.mode === 'visual') {
                        $('body').trigger('themify_builder_color_picker_change', [id, that.$el, hex ? value : '']);
                    }
                }
            }).minicolors('show');

            $colorOpacity.on('blur keyup', function (e) {
                var opacity = parseFloat($.trim($(this).val()));
                if (opacity > 1 || isNaN(opacity) || opacity === '' || opacity < 0) {
                    opacity = !that.$el.val() ? '' : 1;
                    if (e.type === 'blur') {
                        $(this).val(opacity);
                    }
                }
                $(this).attr('data-opacity', opacity);
                that.is_typing = 'keyup' === e.type;
                that.$el.minicolors('opacity', opacity);
            });
        }
    });

    api.Views.register_control('repeater', {
        events: {
            'click .toggle_row': 'toggleField',
            'click .themify_builder_duplicate_row': 'duplicateRowField',
            'click .themify_builder_delete_row': 'deleteRowField'
        },
        initialize: function () {
            this.render();
        },
        render: function () {
            var el = this.$el,
                    that = this,
                    toggleCollapse = false;

            // sortable accordion builder
            el.sortable({
                items: '.tb_repeatable_field',
                handle: '.tb_repeatable_field_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                tolerance: 'pointer',
                cursor: 'move',
                start: _.debounce(function (e, ui) {
                    if (tinyMCE !== undefined) {
                        el.find('.tb_lb_wp_editor').each(function () {
                            var id = $(this).prop('id'),
                                    content = tinymce.get(id).getContent();
                            $(this).data('content', content);
                            tinyMCE.execCommand('mceRemoveEditor', false, id);
                        });
                    }
                }, 300),
                stop: _.debounce(function (e, ui) {
                    if (tinyMCE !== undefined) {
                        el.find('.tb_lb_wp_editor').each(function () {
                            var id = $(this).prop('id');
                            tinyMCE.execCommand('mceAddEditor', false, id);
                            tinymce.get(id).setContent($(this).data('content'));
                        });
                    }

                    if (toggleCollapse) {
                        ui.item.removeClass('collapsed').find('.tb_repeatable_field_content').show();
                        toggleCollapse = false;
                    }
                    el.find('.themify_builder_ui_state_highlight').remove();
                    that.preview_element();
                }, 300),
                sort: function (e, ui) {
                    el.find('.themify_builder_ui_state_highlight').height(30);
                },
                beforeStart: function (event, ui) {
                    if (!ui.item.hasClass('collapsed')) {
                        ui.item.addClass('collapsed').find('.tb_repeatable_field_content').hide();
                        toggleCollapse = true;
                        el.sortable('refresh');
                    }
                }
            });

            return this;
        },
        toggleField: function (e) {
            e.preventDefault();
            $(e.currentTarget).closest('.tb_repeatable_field').toggleClass('collapsed').find('.tb_repeatable_field_content').slideToggle();
        },
        duplicateRowField: function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.$el.next('.add_new').find('a').trigger('click', $(e.currentTarget).closest('.tb_repeatable_field'));
            this.preview_element();
        },
        deleteRowField: function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (confirm(themifyBuilder.i18n.rowDeleteConfirm)) {
                $(e.currentTarget).closest('.tb_repeatable_field').remove();
                this.preview_element();
            }
        }
    });

    api.Views.register_control('widget_form', {
        initialize: function () {
            this.render();
        },
        render: function () {
            this.$el.on('change', ':input', this._updateWidgetPreview.bind(this));
            return this;
        },
        _updateWidgetPreview: function () {
            this.preview_element(this.$el.find(':input').themifySerializeObject());
        }
    });

})(jQuery);
