<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Map
 * Description: Display Map
 */

class TB_Map_Module extends Themify_Builder_Component_Module {

    function __construct() {
        parent::__construct(array(
            'name' => __('Map', 'themify'),
            'slug' => 'map'
        ));
    }

    public function get_options() {
        $zoom_opt = array();
        for ($i = 1; $i < 17; $i++) {
            $zoom_opt[] = $i;
        }
        return array(
            array(
                'id' => 'mod_title_map',
                'type' => 'text',
                'label' => __('Module Title', 'themify'),
                'class' => 'large',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector' => '.module-title'
                )
            ),
            array(
                'id' => 'map_display_type',
                'type' => 'radio',
                'label' => __('Type', 'themify'),
                'options' => array(
                    'dynamic' => __('Dynamic', 'themify'),
                    'static' => __('Static image', 'themify'),
                ),
                'default' => 'dynamic',
                'option_js' => true,
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'address_map',
                'type' => 'textarea',
                'value' => '',
                'class' => 'fullwidth',
                'label' => __('Address', 'themify'),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'latlong_map',
                'type' => 'text',
                'value' => '',
                'class' => 'large',
                'label' => __('Lat/Long', 'themify'),
                'help' => '<br/>' . __('Use Lat/Long instead of address (Leave address field empty to use this). Exp: 43.6453137,-79.3918391', 'themify'),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'zoom_map',
                'type' => 'select',
                'label' => __('Zoom', 'themify'),
                'default' => 8,
                'options' => $zoom_opt,
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'w_map',
                'type' => 'text',
                'class' => 'xsmall',
                'label' => __('Width', 'themify'),
                'unit' => array(
                    'id' => 'unit_w',
                    'type' => 'select',
                    'options' => array(
                        array('id' => 'pixel_unit_w', 'value' => 'px'),
                        array('id' => 'percent_unit_w', 'value' => '%')
                    )
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-dynamic',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'w_map_static',
                'type' => 'text',
                'class' => 'xsmall',
                'label' => __('Width', 'themify'),
                'value' => 500,
                'after' => 'px',
                'wrap_with_class' => 'tb-group-element tb-group-element-static',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'h_map',
                'type' => 'text',
                'label' => __('Height', 'themify'),
                'class' => 'xsmall',
                'unit' => array(
                    'id' => 'unit_h',
                    'type' => 'select',
                    'options' => array(
                        array('id' => 'pixel_unit_h', 'value' => 'px')
                    )
                ),
                'value' => 300,
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'multi_map_border',
                'type' => 'multi',
                'label' => __('Border', 'themify'),
                'fields' => array(
                    array(
                        'id' => 'b_style_map',
                        'type' => 'select',
                        'label' => '',
                        'options' => array(
                            'solid' => __('Solid', 'themify'),
                            'dashed' => __('Dashed', 'themify'),
                            'dotted' => __('Dotted', 'themify'),
                            'double' => __('Double', 'themify'),
                            '' => __('None', 'themify')
                        ),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'b_width_map',
                        'type' => 'text',
                        'label' => '',
                        'class' => 'medium',
                        'after' => 'px',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'b_color_map',
                        'type' => 'text',
                        'colorpicker' => true,
                        'class' => 'large',
                        'label' => '',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                )
            ),
            array(
                'id' => 'type_map',
                'type' => 'select',
                'label' => __('Type', 'themify'),
                'options' => array(
                    'ROADMAP' => __('Road Map', 'themify'),
                    'SATELLITE' => __('Satellite', 'themify'),
                    'HYBRID' => __('Hybrid', 'themify'),
                    'TERRAIN' => __('Terrain', 'themify')
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'scrollwheel_map',
                'type' => 'select',
                'label' => __('Scrollwheel', 'themify'),
                'options' => array(
                    'disable' => __('Disable', 'themify'),
                    'enable' => __('Enable', 'themify'),
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-dynamic',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'draggable_map',
                'type' => 'select',
                'label' => __('Draggable', 'themify'),
                'options' => array(
                    'enable' => __('Enable', 'themify'),
                    'disable' => __('Disable', 'themify')
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-dynamic',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'draggable_disable_mobile_map',
                'type' => 'select',
                'label' => __('Disable draggable on mobile', 'themify'),
                'options' => array(
                    'yes' => __('Yes', 'themify'),
                    'no' => __('No', 'themify')
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-dynamic',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'info_window_map',
                'type' => 'textarea',
                'value' => '',
                'class' => 'fullwidth',
                'label' => __('Info window', 'themify'),
                'help' => __('Additional info that will be shown when clicking on map marker', 'themify'),
                'wrap_with_class' => 'tb-group-element tb-group-element-dynamic',
                'render_callback' => array(
                    'binding' => false
                )
            ),
            // Additional CSS
            array(
                'type' => 'separator',
                'meta' => array('html' => '<hr/>')
            ),
            array(
                'id' => 'css_map',
                'type' => 'text',
                'label' => __('Additional CSS Class', 'themify'),
                'class' => 'large exclude-from-reset-field',
                'help' => sprintf('<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify')),
                'render_callback' => array(
                    'binding' => 'live'
                )
            )
        );
    }

    public function get_default_settings() {
        return array(
            'address_map' => 'Toronto',
            'b_style_map' => 'solid',
            'w_map' => '100',
            'unit_w' => '%'
        );
    }

    public function get_styling() {
        $general = array(
            // Background
            self::get_seperator('image_bacground', __('Background', 'themify'), false),
            self::get_color('.module-map', 'background_color', __('Background Color', 'themify'), 'background-color'),
            // Padding
            self::get_seperator('padding', __('Padding', 'themify')),
            self::get_padding('.module-map'),
            // Margin
            self::get_seperator('margin', __('Margin', 'themify')),
            self::get_margin('.module-map'),
            // Border
            self::get_seperator('border', __('Border', 'themify')),
            self::get_border('.module-map')
        );

        return array(
            array(
                'type' => 'tabs',
                'id' => 'module-styling',
                'tabs' => array(
                    'general' => array(
                        'label' => __('General', 'themify'),
                        'fields' => $general
                    ),
                    'module-title' => array(
                        'label' => __('Module Title', 'themify'),
                        'fields' => $this->module_title_custom_style()
                    )
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args();
        $default_addres = sprintf('<b>%s</b><br/><p>#s#</p>', __('Address', 'themify'));
        ?>
        <#
        _.defaults(data, {
        'mod_title_map':'',
        'address_map':'',
        'latlong_map':'',
        'zoom_map':15,
        'w_map':'100',
        'w_map_static':500,
        'unit_w':'%',
        'h_map':'300',
        'unit_h' : 'px',
        'b_style_map': 'solid',
        'b_width_map':'',
        'b_color_map':'',
        'type_map':'ROADMAP',
        'scrollwheel_map':'disable',
        'draggable_map':'enable',
        'draggable_disable_mobile_map':'yes',
        'info_window_map':'',
        'map_display_type':'dynamic',
        'css_map':''
        });
        if (data.address_map) {
        data.address_map =data.address_map.trim().replace(/\s\s+/g, ' ');
        }
        if (data.draggable_map==='enable' && data.draggable_disable_mobile_map==='yes' && ThemifyBuilderModuleJs._isMobile()) {
        data.draggable_map = 'disable';
        }
        var info = !data.info_window_map?'<?php echo $default_addres ?>'.replace('#s#',data.address_map):data.info_window_map, 
        style = '';
        if(data.b_width_map){
        style+= 'border: '+data.b_style_map+' '+data.b_width_map+'px';
        if (data.b_color_map) {
        style+=' '+themifybuilderapp.Utils.toRGBA(data.b_color_map);
        }
        style+= ';';
        }
        #>

        <div class="module module-<?php echo $this->slug; ?> {{ data.css_map }}">

            <# if( data.mod_title_map ) { #>
            <?php echo $module_args['before_title']; ?>
            {{{ data.mod_title_map }}}
            <?php echo $module_args['after_title']; ?>
            <# } #>
            <# if(data.map_display_type==='static'){
            var args = 'key='+'<?php echo Themify_Builder_Model::getMapKey() ?>';
            if(data.address_map){
            args+='&center='+data.address_map;
            }
            else if(data.latlong_map){
            args+='&center='+data.latlong_map;
            }
            args+='&zoom='+data.zoom_map;
            args+='&maptype='+data.type_map.toLowerCase();
            args+='&size='+data.w_map_static.replace(/[^0-9]/,'')+'x'+data.h_map.replace(/[^0-9]/,'');
            #>
            <img style="{{ style }}" src="//maps.googleapis.com/maps/api/staticmap?{{ args }}" />
            <#
            }
            else if(data.address_map || data.latlong_map) {
            style+= 'width:'+data.w_map + data.unit_w+';';
            style+= 'height:'+data.h_map + data.unit_h+';';
            var js = {},
            reverse = !data.address_map && data.latlong_map;
            js.address = data.address_map? data.address_map: data.latlong_map;
            js.zoom = data.zoom_map;
            js.type = data.type_map;
            js.scroll = data.scrollwheel_map === 'enable';
            js.drag = data.draggable_map === 'enable';
            #>
            <div data-map="{{ window.btoa(JSON.stringify(js)) }}" class="themify_map map-container"  style="{{ style }}"  data-info-window="{{ info }}" data-reverse-geocoding="{{ reverse }}"></div>
            <# } #>
        </div>
        <?php
    }

    /**
     * Render plain content
     */
    public function get_plain_content($module) {
        $mod_settings = wp_parse_args($module['mod_settings'], array(
            'mod_title_map' => '',
            'zoom_map' => 15
        ));
        if (!empty($mod_settings['address_map'])) {
            $mod_settings['address_map'] = preg_replace('/\s+/', ' ', trim($mod_settings['address_map']));
        }
        $text = sprintf('<h3>%s</h3>', $mod_settings['mod_title_map']);
        $text .= sprintf(
                '<iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=%s&amp;t=m&amp;z=%d&amp;output=embed&amp;iwloc=near"></iframe>', urlencode($mod_settings['address_map']), absint($mod_settings['zoom_map'])
        );
        return $text;
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Map_Module');
