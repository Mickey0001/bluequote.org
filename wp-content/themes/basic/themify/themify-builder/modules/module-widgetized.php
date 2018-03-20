<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Widgetized
 * Description: Display any registered sidebar
 */
class TB_Widgetized_Module extends Themify_Builder_Component_Module {
	public function __construct() {
		parent::__construct(array(
			'name' => __('Widgetized', 'themify'),
			'slug' => 'widgetized'
		));

		add_action( 'themify_builder_lightbox_fields', array( $this, 'widgetized_fields' ), 10, 2 );
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_widgetized',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
                                'render_callback' => array(
                                    'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'sidebar_widgetized',
				'type' => 'widgetized_select',
				'label' => __('Widgetized Area', 'themify'),
				'class' => 'large'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>' )
			),
			array(
				'id' => 'custom_css_widgetized',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') ),
				'class' => 'large exclude-from-reset-field'
			)
		);
	}
        
        public function get_visual_type() {
            return 'ajax';            
        }
        
	public function get_styling() {
		$general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module-widgetized .widget'),
                        self::get_color('.module-widgetized .widget', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
						self::get_repeat('.module-widgetized .widget'),
			// Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family('.module-widgetized'),
                        self::get_color('.module-widgetized','font_color',__('Font Color', 'themify')),
                        self::get_font_size('.module-widgetized'),
                        self::get_line_height('.module-widgetized'),
                        self::get_letter_spacing('.module-widgetized'),
                        self::get_text_align('.module-widgetized'),
                        self::get_text_transform('.module-widgetized'),
                        self::get_font_style('.module-widgetized'),
			// Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color( '.module-widgetized a','link_color'),
                        self::get_color('.module-widgetized a:hover','link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration('.module-widgetized a'),
                        // Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-widgetized'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-widgetized'),
                        // Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-widgetized')
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
						'fields' =>  $this->module_title_custom_style()
					)
				)
			)
		);
	}

	function widgetized_fields($field, $mod_name) {
                if ( $mod_name !== 'widgetized' ){
                    return false;
                }
		global $wp_registered_sidebars;
		$output = '';
                if($field['type']==='widgetized_select'){
                    $output= '<div class="selectwrapper"><select name="'. esc_attr( $field['id'] ) .'" id="'. esc_attr( $field['id'] ) .'" class="tb_lb_option"'. themify_builder_get_control_binding_data( $field ) .'>';
                    $output .= '<option></option>';
                    foreach ( $wp_registered_sidebars as $k => $v ) {
                            $output .= '<option value="'.esc_attr( $v['id'] ).'">'.esc_html( $v['name'] ).'</option>';
                    }
                    $output .= '</select></div>';
                }
		echo $output;
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Widgetized_Module' );
