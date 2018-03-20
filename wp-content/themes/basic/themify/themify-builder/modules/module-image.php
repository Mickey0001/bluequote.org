<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Image
 * Description: Display Image content
 */

class TB_Image_Module extends Themify_Builder_Component_Module {

    function __construct() {
        parent::__construct(array(
            'name' => __('Image', 'themify'),
            'slug' => 'image'
        ));
    }

    public function get_options() {
        $is_img_enabled = Themify_Builder_Model::is_img_php_disabled();
        $image_sizes = $is_img_enabled ? themify_get_image_sizes_list(false) : array();

        return array(
            array(
                'id' => 'mod_title_image',
                'type' => 'text',
                'label' => __('Module Title', 'themify'),
                'class' => 'large',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector' => '.module-title'
                )
            ),
            array(
                'id' => 'style_image',
                'type' => 'layout',
                'label' => __('Image Style', 'themify'),
                'mode' => 'sprite',
                'options' => array(
                    array('img' => 'image-top', 'value' => 'image-top', 'label' => __('Image Top', 'themify')),
                    array('img' => 'image-left', 'value' => 'image-left', 'label' => __('Image Left', 'themify')),
                    array('img' => 'image-right', 'value' => 'image-right', 'label' => __('Image Right', 'themify')),
                    array('img' => 'image-overlay', 'value' => 'image-overlay', 'label' => __('Image Overlay', 'themify')),
                    array('img' => 'image-center', 'value' => 'image-center', 'label' => __('Centered Image', 'themify'))
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'url_image',
                'type' => 'image',
                'label' => __('Image URL', 'themify'),
                'class' => 'xlarge',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'appearance_image',
                'type' => 'checkbox',
                'label' => __('Image Appearance', 'themify'),
                'options' => array(
                    array('name' => 'rounded', 'value' => __('Rounded', 'themify')),
                    array('name' => 'drop-shadow', 'value' => __('Drop Shadow', 'themify')),
                    array('name' => 'bordered', 'value' => __('Bordered', 'themify')),
                    array('name' => 'circle', 'value' => __('Circle', 'themify'), 'help' => __('(square format image only)', 'themify'))
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'image_size_image',
                'type' => 'select',
                'label' => __('Image Size', 'themify'),
                'hide' => !$is_img_enabled,
                'options' => $image_sizes,
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'image_fullwidth_container',
                'type' => 'multi',
                'label' => __('Width', 'themify'),
                'fields' => array(
                    array(
                        'id' => 'width_image',
                        'type' => 'text',
                        'label' => '',
                        'class' => 'xsmall',
                        'help' => 'px',
                        'value' => '',
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'auto_fullwidth',
                        'type' => 'checkbox',
                        'options' => array(array('name' => '1', 'value' => __('Auto fullwidth image', 'themify'))),
                        'render_callback' => array(
                            'binding' => 'live'
                        )
                    )
                )
            ),
            array(
                'id' => 'height_image',
                'type' => 'text',
                'label' => __('Height', 'themify'),
                'class' => 'xsmall',
                'help' => 'px',
                'value' => '',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'title_image',
                'type' => 'text',
                'label' => __('Image Title', 'themify'),
                'class' => 'fullwidth',
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'link_image',
                'type' => 'text',
                'label' => __('Image Link', 'themify'),
                'class' => 'fullwidth',
                'binding' => array(
                    'empty' => array(
                        'hide' => array('param_image', 'image_zoom_icon', 'lightbox_size')
                    ),
                    'not_empty' => array(
                        'show' => array('param_image', 'image_zoom_icon', 'lightbox_size')
                    )
                ),
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'param_image',
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
                'render_callback' => array(
                    'binding' => 'live'
                )
            ),
            array(
                'id' => 'image_zoom_icon',
                'type' => 'checkbox',
                'label' => false,
                'pushed' => 'pushed',
                'options' => array(
                    array('name' => 'zoom', 'value' => __('Show zoom icon', 'themify'))
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-lightbox tb-group-element-newtab',
                'render_callback' => array(
                    'binding' => 'live'
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
                            'binding' => 'live'
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
                            'binding' => 'live'
                        )
                    ),
                    array(
                        'id' => 'lightbox_height',
                        'type' => 'text',
                        'label' => __('Height', 'themify'),
                        'value' => '',
                        'render_callback' => array(
                            'binding' => 'live'
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
                            'binding' => 'live'
                        )
                    )
                ),
                'wrap_with_class' => 'tb-group-element tb-group-element-lightbox'
            ),
            array(
                'id' => 'caption_image',
                'type' => 'textarea',
                'label' => __('Image Caption', 'themify'),
                'class' => 'fullwidth',
                'render_callback' => array(
                    'binding' => 'live',
                    'live-selector' => '.image-caption'
                )
            ),
            array(
                'id' => 'alt_image',
                'type' => 'text',
                'label' => __('Image Alt Tag', 'themify'),
                'class' => 'fullwidth',
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
                'id' => 'css_image',
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
            'url_image' => 'https://themify.me/demo/themes/wp-content/uploads/image-placeholder-small.jpg'
        );
    }

    public function get_styling() {
        $general = array(
            // Background
            self::get_seperator('image_bacground', __('Background', 'themify'), false),
            self::get_image('.module-image'),
            self::get_color('.module-image', 'background_color', __('Background Color', 'themify'), 'background-color'),
            self::get_repeat('.module-image'),
            // Font
            self::get_seperator('font', __('Font', 'themify')),
            self::get_font_family(array('.module-image .image-content', '.module-image .image-title', '.module-image .image-title a')),
            self::get_color(array('.module-image .image-content', '.module-image .image-title', '.module-image .image-title a', '.module-image h1', '.module-image h2', '.module-image h3:not(.module-title)', '.module-image h4', '.module-image h5', '.module-image h6'), 'font_color', __('Font Color', 'themify')),
            self::get_font_size('.module-image .image-content'),
            self::get_line_height('.module-image .image-content'),
            self::get_letter_spacing('.module-image .image-content'),
            self::get_text_align('.module-image .image-content'),
            self::get_text_transform('.module-image .image-content'),
            self::get_font_style('.module-image .image-content'),
            // Link
            self::get_seperator('link', __('Link', 'themify')),
            self::get_color('.module-image a', 'link_color'),
            self::get_color('.module-image a:hover', 'link_color_hover', __('Color Hover', 'themify')),
            self::get_text_decoration('.module-image a'),
            // Padding
            self::get_seperator('padding', __('Padding', 'themify')),
            self::get_padding('.module-image'),
            // Margin
            self::get_seperator('margin', __('Margin', 'themify')),
            self::get_margin('.module-image'),
            // Border
            self::get_seperator('border', __('Border', 'themify')),
            self::get_border('.module-image')
        );

        $image_title = array(
            // Font
            self::get_seperator('font', __('Font', 'themify'), false),
            self::get_font_family(array('.module-image .image-title', '.module-image .image-title a'), 'font_family_title'),
            self::get_color(array('.module-image .image-title', '.module-image .image-title a'), 'font_color_title', __('Font Color', 'themify')),
            self::get_color(array('.module-image .image-title:hover', '.module-image .image-title a:hover'), 'font_color_title_hover', __('Color Hover', 'themify')),
            self::get_font_size('.module-image .image-title', 'font_size_title'),
            self::get_line_height('.module-image .image-title')
        );

        $image_caption = array(
            // Font
            self::get_seperator('font', __('Font', 'themify'), false),
            self::get_font_family('.module-image .image-content .image-caption', 'font_family_caption'),
            self::get_color('.module-image .image-content .image-caption', 'font_color_caption', __('Font Color', 'themify')),
            self::get_font_size('.module-image .image-content .image-caption', 'font_size_caption'),
            self::get_line_height('.module-image .image-content .image-caption', 'line_height_caption')
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
                        'label' => __('Image Title', 'themify'),
                        'fields' => $image_title
                    ),
                    'caption' => array(
                        'label' => __('Image Caption', 'themify'),
                        'fields' => $image_caption
                    )
                )
            ),
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args();
        ?>
        <# var fullwidth = data.auto_fullwidth == '1' ? 'auto_fullwidth' : ''; #>
        <div class="module module-<?php echo $this->slug; ?> {{ fullwidth }} {{ data.style_image }} {{ data.css_image }} <# ! _.isUndefined( data.appearance_image ) ? print( data.appearance_image.split('|').join(' ') ) : ''; #>">
            <# if ( data.mod_title_image ) { #>
            <?php echo $module_args['before_title']; ?>{{{ data.mod_title_image }}}<?php echo $module_args['after_title']; ?>
            <# } #>

            <#
            var style='';
            if(!fullwidth){
            style = 'width:' + ( data.width_image ? data.width_image + 'px;' : 'auto;' );
            style += 'height:' + ( data.height_image ? data.height_image + 'px;' : 'auto;' );
            }
            var image = '<img src="'+ data.url_image +'" style="' + style + '"/>';
            #>
            <div class="image-wrap">
                <# if ( data.link_image ) { #>
                <a href="{{ data.link_image }}">
                    <# if( data.image_zoom_icon === 'zoom' ) { #>
                    <span class="zoom fa <# print( data.param_image == 'lightbox' ? 'fa-search' : 'fa-external-link' ) #>"></span>
                    <# } #>
                    {{{ image }}}
                </a>
                <# } else { #>
                {{{ image }}}
                <# } #>

                <# if ( 'image-overlay' !== data.style_image ) { #>
            </div>
            <# } #>

            <# if( data.title_image || data.caption_image ) { #>
            <div class="image-content">
                <# if ( data.title_image ) { #>
                <h3 class="image-title">
                    <# if ( data.link_image ) { #>
                    <a href="{{ data.link_image }}">{{{ data.title_image }}}</a>
                    <# } else { #>
                    {{{ data.title_image }}}
                    <# } #>
                </h3>
                <# } #>

                <# if( data.caption_image ) { #>
                <div class="image-caption">{{{ data.caption_image }}}</div>
                <# } #>
            </div>
            <# } #>
            <# if ( 'image-overlay' === data.style_image ) { #>
        </div>
        <# } #>

        </div>
        <?php
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Image_Module');
