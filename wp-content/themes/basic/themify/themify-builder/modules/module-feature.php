<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Feature
 * Description: Display Feature content
 */

class TB_Feature_Module extends Themify_Builder_Component_Module {

    function __construct() {
        parent::__construct(array(
            'name' => __('Feature', 'themify'),
            'slug' => 'feature'
        ));
    }

    public function get_options() {
        return array(
            array(
                'id' => 'mod_title_feature',
                'type' => 'text',
                'label' => __('Module Title', 'themify'),
                'class' => 'large',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector'=>'.module-title'
                )
            ),
            array(
                'id' => 'title_feature',
                'type' => 'text',
                'label' => __('Feature Title', 'themify'),
                'class' => 'Large',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector'=>'.module-feature-title'
                )
            ),
            array(
                'id' => 'content_feature',
                'type' => 'wp_editor',
                'class' => 'fullwidth',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector'=>'.module-feature-content p'
                )
            ),
            array(
                'id' => 'layout_feature',
                'type' => 'layout',
                'label' => __('Layout', 'themify'),
                'mode' => 'sprite',
                'options' => array(
                    array('img' => 'icon-left', 'value' => 'icon-left', 'label' => __('Icon Left', 'themify')),
                    array('img' => 'icon-right', 'value' => 'icon-right', 'label' => __('Icon Right', 'themify')),
                    array('img' => 'icon-top', 'value' => 'icon-top', 'label' => __('Icon Top', 'themify'))
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'multi_circle_feature',
                'type' => 'multi',
                'label' => __('Circle', 'themify'),
                'fields' => array(
                    array(
                        'id' => 'circle_percentage_feature',
                        'type' => 'text',
                        'label' => __('Percentage', 'themify'),
                        'class' => 'xsmall',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'circle_stroke_feature',
                        'type' => 'text',
                        'label' => __('Stroke', 'themify'),
                        'class' => 'xsmall',
                        'after' => 'px',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'circle_color_feature',
                        'type' => 'text',
                        'colorpicker' => true,
                        'label' => __('Color', 'themify'),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'circle_size_feature',
                        'type' => 'select',
                        'label' => __('Size', 'themify'),
                        'options' => array(
                            'small' => __('Small', 'themify'),
                            'medium' => __('Medium', 'themify'),
                            'large' => __('Large', 'themify')
                        ),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                )
            ),
            array(
                'id' => 'icon_type_feature',
                'type' => 'radio',
                'label' => __('Icon Type', 'themify'),
                'options' => array(
                    'icon' => __('Icon', 'themify'),
                    'image_icon' => __('Image', 'themify')
                ),
                'default' => 'icon',
                'option_js' => true,
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'image_feature',
                'type' => 'image',
                'label' => __('Image URL', 'themify'),
                'class' => 'xlarge',
                'wrap_with_class' => 'tb-group-element tb-group-element-image_icon',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'multi_icon_feature',
                'type' => 'multi',
                'label' => '&nbsp;',
                'fields' => array(
                    array(
                        'id' => 'icon_feature',
                        'type' => 'icon',
                        'label' => __('Icon', 'themify'),
                        'wrap_with_class' => 'tb-group-element tb-group-element-icon',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'icon_color_feature',
                        'type' => 'text',
                        'colorpicker' => true,
                        'label' => __('Color', 'themify'),
                        'class' => 'medium',
                        'wrap_with_class' => 'tb-group-element tb-group-element-icon',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'icon_bg_feature',
                        'type' => 'text',
                        'colorpicker' => true,
                        'label' => __('Background', 'themify'),
                        'class' => 'medium',
                        'wrap_with_class' => 'tb-group-element tb-group-element-icon',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                )
            ),
            array(
                'id' => 'link_feature',
                'type' => 'text',
                'label' => __('Link', 'themify'),
                'class' => 'fullwidth',
                'binding' => array(
                    'empty' => array(
                        'hide' => array('link_options', 'lightbox_size')
                    ),
                    'not_empty' => array(
                        'show' => array('link_options', 'lightbox_size')
                    )
                ),
                'render_callback' => array(
                    'binding' => false
                )
            ),
            array(
                'id' => 'overlap_image_feature',
                'type' => 'image',
                'label' => __('Overlap Image', 'themify'),
                'class' => 'xlarge',
                'binding' => array(
                    'empty' => array('hide' => array('overlap_image_size')),
                    'not_empty' => array('show' => array('overlap_image_size')),
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'overlap_image_size',
                'type' => 'multi',
                'label' => '&nbsp;',
                'fields' => array(
                    array(
                        'id' => 'overlap_image_width',
                        'type' => 'text',
                        'label' => __('Width', 'themify'),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'overlap_image_height',
                        'type' => 'text',
                        'label' => __('Height', 'themify'),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                ),
            ),
            array(
                'id' => 'link_options',
                'type' => 'radio',
                'label' => __('Open Link In', 'themify'),
                'options' => array(
                    'regular' => __('Same window', 'themify'),
                    'lightbox' => __('Lightbox ', 'themify'),
                    'newtab' => __('New tab ', 'themify')
                ),
                'new_line' => false,
                'default' => 'regular',
                'option_js' => true,
                'wrap_with_class' => 'link_options',
                'render_callback' => array(
                    'binding' => false
                )
            ),
            array(
                'id' => 'lightbox_size',
                'type' => 'multi',
                'label' => __('Lightbox Dimension', 'themify'),
                'fields' => array(
                    array(
                        'id' => 'lightbox_width',
                        'type' => 'text',
                        'label' => __('Width', 'themify'),
                        'value' => '',
                        'render_callback' => array(
                            'binding' => false
                        )
                    ),
                    array(
                        'id' => 'lightbox_size_unit_width',
                        'type' => 'select',
                        'label' => __('Units', 'themify'),
                        'options' => array(
                            'pixels' => __('px ', 'themify'),
                            'percents' => __('%', 'themify')
                        ),
                        'default' => 'pixels',
                        'render_callback' => array(
                            'binding' => false
                        )
                    ),
                    array(
                        'id' => 'lightbox_height',
                        'type' => 'text',
                        'label' => __('Height', 'themify'),
                        'value' => '',
                        'render_callback' => array(
                            'binding' => false
                        )
                    ),
                    array(
                        'id' => 'lightbox_size_unit_height',
                        'type' => 'select',
                        'label' => __('Units', 'themify'),
                        'options' => array(
                            'pixels' => __('px ', 'themify'),
                            'percents' => __('%', 'themify')
                        ),
                        'default' => 'pixels',
                        'render_callback' => array(
                            'binding' => false
                        )
                    )
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-lightbox lightbox_size',
                'render_callback' => array(
                    'binding' => false
                )
            ),
            array(
                'type' => 'separator',
                'meta' => array('html' => '<hr />')
            ),
            array(
                'id' => 'css_feature',
                'type' => 'text',
                'label' => __('Additional CSS Class', 'themify'),
                'help' => sprintf('<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify')),
                'class' => 'large exclude-from-reset-field',
                'render_callback' => array(
                    'binding' => 'live'
                )
            )
        );
    }

    public function get_default_settings() {
        return array(
            'title_feature' => esc_html__('Feature title', 'themify'),
            'content_feature' => esc_html__('Feature content', 'themify'),
            'circle_percentage_feature' => '100',
            'circle_stroke_feature' => '3',
            'icon_feature' => 'fa-home',
            'layout_feature' => 'icon-top',
            'circle_size_feature' => 'small',
            'circle_color_feature' => '#de5d5d'
        );
    }

    public function get_styling() {
        $general = array(
            // Background
            self::get_seperator('image_bacground', __('Background', 'themify'), false),
            self::get_image('.module-feature'),
            self::get_color('.module-feature', 'background_color', __('Background Color', 'themify'), 'background-color'),
            self::get_repeat('.module-feature'),
            // Font
            self::get_seperator('font', __('Font', 'themify')),
            self::get_font_family(array('.module-feature', '.module-feature .module-feature-title', '.module-feature h1', '.module-feature h2', '.module-feature h3:not(.module-title)', '.module-feature h4', '.module-feature h5', '.module-feature h6')),
            self::get_color(array('.module-feature', '.module-feature h1', '.module-feature h2', '.module-feature h3', '.module-feature h4', '.module-feature h5', '.module-feature h6', '.module-feature .module-feature-title'), 'font_color', __('Font Color', 'themify')),
            self::get_font_size('.module-feature'),
            self::get_line_height('.module-feature'),
            self::get_letter_spacing('.module-feature'),
            self::get_text_align('.module-feature'),
            self::get_text_transform('.module-feature'),
            self::get_font_style('.module-feature'),
            // Link
            self::get_seperator('link', __('Link', 'themify')),
            self::get_color('.module-feature a', 'link_color'),
            self::get_color('.module-feature a:hover', 'link_color_hover', __('Color Hover', 'themify')),
            self::get_text_decoration('.module-feature a'),
            // Padding
            self::get_seperator('padding', __('Padding', 'themify')),
            self::get_padding('.module-feature'),
            // Margin
            self::get_seperator('margin', __('Margin', 'themify')),
            self::get_margin('.module-feature'),
            // Border
            self::get_seperator('border', __('Border', 'themify')),
            self::get_border('.module-feature')
        );

        $feature_title = array(
            // Font
            self::get_seperator('font', __('Font', 'themify'), false),
            self::get_font_family(array('.module-feature .module-feature-title:not(.module-title)', '.module-feature .module-feature-title a'), 'font_family_title'),
            self::get_color(array('.module-feature .module-feature-title:not(.module-title)', '.module-feature .module-feature-title a'), 'font_color_title', __('Font Color', 'themify')),
            self::get_color(array('.module-feature .module-feature-title:not(.module-title):hover', '.module-feature .module-feature-title a:hover'), 'font_color_title_hover', __('Color Hover', 'themify')),
            self::get_font_size('.module-feature .module-feature-title:not(.module-title)', 'font_size_title'),
            self::get_line_height('.module-feature .module-feature-title:not(.module-title)', 'line_height_title')
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
                    ),
                    'title' => array(
                        'label' => __('Feature Title', 'themify'),
                        'fields' => $feature_title
                    )
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args();
        $chart_vars = apply_filters('themify_chart_init_vars', array(
            'trackColor' => 'rgba(0,0,0,.1)',
            'size' => 150
        ));
        ?>
        <#
        var chart_vars = JSON.parse('<?php echo json_encode($chart_vars) ?>');
        _.defaults(data, {
            mod_title_feature: '',
            title_feature: '',
            overlap_image_feature: '',
            overlap_image_width: '',
            overlap_image_height: '',
            layout_feature: 'icon-top',
            circle_percentage_feature: '',
            circle_color_feature:'#de5d5d',
            circle_stroke_feature:'',
            icon_type_feature:'icon',
            image_feature:'',
            icon_feature:'',
            icon_color_feature: '#000',
            icon_bg_feature:'',
            circle_size_feature: 'medium',
            link_feature: '',
            link_options: false,
            css_feature : ''
        });
        if (data.layout_feature === '') {
            data.layout_feature = 'icon-top';
        }
        var chart_class = '';
        if (data.circle_size_feature === 'large') {
            chart_vars.size = 200;
        } else if (data.circle_size_feature === 'medium') {
            chart_vars.size = 150;
        } else if (data.circle_size_feature === 'small') {
            chart_vars.size = 100;
        }
        data.circle_percentage_feature = data.circle_percentage_feature?data.circle_percentage_feature.replace('%',''):'';
        if(!data.circle_percentage_feature){
            chart_class='no-chart';
            data.circle_percentage_feature = 0;
            chart_vars.trackColor = 'rgba(0,0,0,0)';
        }
        else{
        if(data.circle_percentage_feature>100){
            data.circle_percentage_feature = '100';
        }
            chart_class = 'with-chart';
        }
        if(data.overlap_image_feature!==''){
            chart_class+=' with-overlay-image';
        }
        var prefix = Themify.getVendorPrefix(),
            module_id = '<?php echo $this->slug ?>-'+data.cid+'-'+data.cid,
            style = '',
            insetColor = data.icon_bg_feature!==''?themifybuilderapp.Utils.toRGBA(data.icon_bg_feature):'';
        if(data.circle_stroke_feature){
            var circleBackground = chart_vars.trackColor,
                    circleColor = themifybuilderapp.Utils.toRGBA(data.circle_color_feature),
                    insetSize =  parseInt(data.circle_stroke_feature),
                    style='#'+module_id+' .module-feature-chart-html5{'+prefix+'box-shadow: inset 0 0 0 '+insetSize+'px '+circleBackground+';box-shadow: inset 0 0 0 '+insetSize+'px '+circleBackground+';}';
                    style+='#'+module_id+' .chart-loaded.chart-html5-fill{'+prefix+'box-shadow: inset 0 0 0 '+insetSize+'px '+circleColor+';box-shadow: inset 0 0 0 '+insetSize+'px '+circleColor+';}';
        }
        if(insetColor!==''){
            style+='#'+module_id+' .chart-html5-inset{background-color:'+insetColor+';}';
        }
        #>
        <div id="{{module_id}}" class="module module-<?php echo $this->slug; ?> {{chart_class}} layout-{{ data.layout_feature }} size-{{data.circle_size_feature}} {{ data.css_feature }}">
            <style type="text/css">
                {{style}}
            </style> 
            <# if( data.mod_title_feature ) { #>
            <?php echo $module_args['before_title']; ?>
            {{{ data.mod_title_feature }}}
            <?php echo $module_args['after_title']; ?>
            <# } #>

            <div class="module-feature-image">
                <# if(data.overlap_image_feature){
                var style = 'width:' + ( data.overlap_image_width ? data.overlap_image_width + 'px;' : 'auto;' );
                style += 'height:' + ( data.overlap_image_height ? data.overlap_image_height + 'px;' : 'auto;' );
                #>
                    <img src="{{data.overlap_image_feature}}" style="{{style}}"/>
                <#}#>
                <# if(data.link_feature){ #>
                <a href="{{ data.link_feature }}">
                    <#}#>
                    <div class="module-feature-chart-html5"
                         <# if(data.circle_percentage_feature){ #>
                         data-progress="0"
                         data-progress-end="{{data.circle_percentage_feature}}"
                         data-size="{{chart_vars.size}}"
                         <#}#>>
                         <div class="chart-html5-circle">
                            <# if(data.circle_percentage_feature){ #>
                            <div class="chart-html5-mask chart-html5-full">
                                <div class="chart-html5-fill"></div>
                            </div>
                            <div class="chart-html5-mask chart-html5-half">
                                <div class="chart-html5-fill"></div>
                            </div>
                            <#}#>
                            <div class="chart-html5-inset<# if(data.icon_type_feature==='icon' && data.icon_feature!==''){ #> chart-html5-inset-icon<# } #>">
                                <# if (data.icon_type_feature.indexOf('image')!==-1 && data.image_feature !== ''){ #>
                                <img src="{{data.image_feature}}" />
                                <# }
                                else{ #>
                                <# if ('' !== insetColor){ #><div class="module-feature-background" style="background:{{insetColor}}"></div><# } #>
                                <# if ('' !== data.icon_feature){ #><i class="module-feature-icon fa {{data.icon_feature}}"<# if(data.icon_color_feature!==''){ #> style="color:<# print(themifybuilderapp.Utils.toRGBA(data.icon_color_feature)) #>"<# } #>></i><# } #>
                                <# } #>
                            </div>
                        </div>
                    </div>
                    <# if(data.link_feature){ #>
                </a>
                <# } #>
            </div>
            <div class="module-feature-content">
                <# if(data.title_feature!==''){ #>
                <h3 class="module-feature-title">
                    <# if(data.link_feature){ #>
                    <a href="{{data.link_feature}}">
                        <#}#>
                        {{data.title_feature}}
                        <# if(data.link_feature){ #>
                    </a>
                    <#}#>
                </h3>
                <# } #>
                {{{ data.content_feature }}}
            </div>
        </div>
        <?php
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Feature_Module');
