<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Gallery
 * Description: Display WP Gallery Images
 */
class TB_Gallery_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Gallery', 'themify'),
			'slug' => 'gallery'
		));
	}

	public function get_options() {
		$columns = range( 0, 9 );
                $is_img_enabled = Themify_Builder_Model::is_img_php_disabled();
		$image_size = themify_get_image_sizes_list( false );
		return array(
			array(
				'id' => 'mod_title_gallery',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
                                'render_callback' => array(
                                        'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'layout_gallery',
				'type' => 'radio',
				'label' => __('Gallery Layout', 'themify'),
				'options' => array(
					'grid' => __('Grid', 'themify'),
					'showcase' => __('Showcase', 'themify'),
					'lightboxed' => __('Lightboxed', 'themify'),
				),
				'default' => 'grid',
				'option_js' => true
			),
			array(
				'id' => 'layout_masonry',
				'type' => 'checkbox',
				'label' => false,
				'pushed' => 'pushed',
				'options' => array(
					array( 'name' => 'masonry', 'value' => __( 'Use Masonry', 'themify' ) )
				),
				'wrap_with_class' => 'tb-group-element tb-group-element-grid'
			),
			array(
				'id' => 'thumbnail_gallery',
				'type' => 'image',
				'label' => __('Thumbnail', 'themify'),
				'class' => 'large',
				'wrap_with_class' => 'tb-group-element tb-group-element-lightboxed'
			),
			array(
				'id' => 'shortcode_gallery',
				'type' => 'textarea',
				'class' => 'tb-thumbs-preview tb-shortcode-input',
				'label' => __('Insert Gallery Shortcode', 'themify'),
				'help' => sprintf('<a href="#" class="builder_button tb-gallery-btn">%s</a>', __('Insert Gallery', 'themify'))
			),
			array(
				'id' => 'gallery_pagination',
				'type' => 'checkbox',
				'label' => __('Pagination', 'themify'),
				'wrap_with_class' => 'tb-group-element tb-group-element-grid',
				'options' => array(array( 'name' => 'pagination', 'value' =>'') ),
				'option_js' => true
			),
			array(
				'id' => 'gallery_per_page',
				'type' => 'text',
				'label' => __('Images per page', 'themify'),
				'wrap_with_class' => 'tb-group-element tb-group-element-grid tb-checkbox-element tb-checkbox-element-pagination',
				'class' => 'xsmall'
			),
			array(
				'id' => 'gallery_image_title',
				'type' => 'checkbox',
				'label' => __('Image Title', 'themify'),
				'options' => array(array( 'value' => __('Display library image title', 'themify'), 'name' =>'yes') )
			),
			array(
				'id' => 'gallery_exclude_caption',
				'type' => 'checkbox',
				'label' => __( 'Exclude Caption', 'themify' ),
				'options' => array(array( 'value' => __( 'Hide Image Caption', 'themify' ), 'name' =>'yes' ) ),
			),
			array(
				'id' => 's_image_w_gallery',
				'type' => 'text',
				'label' => __('Showcase Image Width', 'themify'),
				'class' => 'xsmall',
				'hide' => $is_img_enabled,
				'help' => 'px',
				'wrap_with_class' => 'tb-group-element tb-group-element-showcase'
			),
			array(
				'id' => 's_image_h_gallery',
				'type' => 'text',
				'label' =>__('Showcase Image Height', 'themify'),
				'class' => 'xsmall',
				'hide' => $is_img_enabled,
				'help' => 'px',
				'wrap_with_class' => 'tb-group-element tb-group-element-showcase'
			),
			array(
				'id' => 's_image_size_gallery',
				'type' => 'select',
				'label' => __('Main Image Size', 'themify'),
				'hide' => !$is_img_enabled,
				'options' => $image_size
			),
			array(
				'id' => 'thumb_w_gallery',
				'type' => 'text',
				'label' => __('Thumbnail Width', 'themify'),
				'class' => 'xsmall',
				'hide' => $is_img_enabled,
				'help' => 'px'
			),
			array(
				'id' => 'thumb_h_gallery',
				'type' => 'text',
				'label' =>__('Thumbnail Height', 'themify'),
				'class' => 'xsmall',
				'hide' => $is_img_enabled,
				'help' => 'px'
			),
			array(
				'id' => 'image_size_gallery',
				'type' => 'select',
				'label' => __('Image Size', 'themify'),
				'hide' => !$is_img_enabled,
				'options' => $image_size
			),
			array(
				'id' => 'gallery_columns',
				'type' => 'select',
				'label' =>__('Columns', 'themify'),
				'options' => $columns,
				'wrap_with_class' => 'tb-group-element tb-group-element-grid'
			),
			array(
				'id' => 'link_opt',
				'type' => 'select',
				'label' => __('Link to', 'themify'),
				'options' => array(
					'post' => __('Attachment Page','themify'),
					'file' => __('Media File','themify'),
					'none' => __('None','themify')
				),
				'default' => __('Media File','themify'),
				'wrap_with_class' => 'tb-group-element tb-group-element-grid',
				'binding' => array(
					'file' => array( 'show' => array( 'link_image_size' ) ),
					'post' => array( 'hide' => array( 'link_image_size' ) ),
					'none' => array( 'hide' => array( 'link_image_size' ) )
				)
			),
			array(
				'id' => 'link_image_size',
				'type' => 'select',
				'label' => __('Link to Image Size', 'themify'),
				'options' => $image_size,
				'default' => __( 'Original Image', 'themify' ),
				'wrap_with_class' => 'tb-group-element tb-group-element-grid'
			),
			array(
				'id' => 'appearance_gallery',
				'type' => 'checkbox',
				'label' => __('Image Appearance', 'themify'),
				'options' => array(
					array( 'name' => 'rounded', 'value' => __('Rounded', 'themify')),
					array( 'name' => 'drop-shadow', 'value' => __('Drop Shadow', 'themify')),
					array( 'name' => 'bordered', 'value' => __('Bordered', 'themify')),
					array( 'name' => 'circle', 'value' => __('Circle', 'themify'), 'help' => __('(square format image only)', 'themify'))
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_gallery',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
			)
		);
	}

	public function get_default_settings() {
		return array(
			'gallery_columns' => 4,
                        'layout_gallery'=>'grid'
		);
	}
        
        public function get_visual_type() {
            return 'ajax';            
        }
        

	public function get_styling() {
		$general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module-gallery'),
                        self::get_color('.module-gallery', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
						self::get_repeat('.module-gallery'),
			// Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family('.module-gallery'),
                        self::get_color('.module-gallery','font_color',__('Font Color', 'themify')),
                        self::get_font_size('.module-gallery'),
                        self::get_line_height('.module-gallery'),
                        self::get_letter_spacing('.module-gallery'),
                        self::get_text_align('.module-gallery'),
                        self::get_text_transform('.module-gallery'),
                        self::get_font_style('.module-gallery'),
			// Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color( '.module-gallery a','link_color'),
                        self::get_color('.module-gallery a:hover','link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration('.module-gallery a'),
			// Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-gallery'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-gallery'),
                        // Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-gallery')
		);

		return array(
			array(
				'type' => 'tabs',
				'id' => 'module-styling',
				'tabs' => array(
					'general' => array(
                                            'label' => __( 'General', 'themify' ),
                                            'fields' => $general
					),
					'module-title' => array(
						'label' => __( 'Module Title', 'themify' ),
						'fields' => $this->module_title_custom_style()
					)
				)
			)
		);

	}

	/**
	 * Render plain content for static content.
	 * 
	 * @param array $module 
	 * @return string
	 */
	public function get_plain_content( $module ) {
		$mod_settings = wp_parse_args( $module['mod_settings'], array(
			'mod_title_gallery' => '',
			'shortcode_gallery' => ''
		) );
		$text = '';

		if ( '' !== $mod_settings['mod_title_gallery'] ) 
			$text .= sprintf( '<h3>%s</h3>', $mod_settings['mod_title_gallery'] );
		
		$text .= $mod_settings['shortcode_gallery'];
		return $text;
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Gallery_Module' );
