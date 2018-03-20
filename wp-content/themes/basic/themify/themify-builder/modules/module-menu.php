<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Menu
 * Description: Display Custom Menu
 */
class TB_Menu_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Menu', 'themify'),
			'slug' => 'menu'
		));
	}
		
		public function get_title( $module ) {
		return isset( $module['mod_settings']['custom_menu'] ) ? $module['mod_settings']['custom_menu'] : '';
	}

	public function get_options() {
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
				$colors = Themify_Builder_Model::get_colors();
				$colors[] = array('img' => 'transparent', 'value' => 'transparent', 'label' => __('Transparent', 'themify'));
		return array(
			array(
				'id' => 'mod_title_menu',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
                                'render_callback' => array(
                                    'live-selector'=>'.module-title'
                                )
			),
			array(
				'id' => 'layout_menu',
				'type' => 'layout',
				'label' => __('Menu Layout', 'themify'),
                                'mode'=>'sprite',
				'options' => array(
					array('img' => 'menu-bar', 'value' => 'menu-bar', 'label' => __('Menu Bar', 'themify')),
					array('img' => 'menu-fullbar', 'value' => 'fullwidth', 'label' => __('Menu Fullbar', 'themify')),
					array('img' => 'menu-vertical', 'value' => 'vertical', 'label' => __('Menu Vertical', 'themify'))
				)
			),
			array(
				'id' => 'custom_menu',
				'type' => 'select_menu',
				'label' => __('Custom Menu', 'themify'),
				'options' => $menus,
				'help' => sprintf(__('Add more <a href="%s" target="_blank">menu</a>', 'themify'), admin_url( 'nav-menus.php' )),
				'break' => true
			),
			array(
				'id' => 'allow_menu_breakpoint',
				'pushed' => 'pushed',
				'type' => 'checkbox',
				'label' => false,
				'options' => array(
					array( 'name' => 'allow_menu', 'value' => __( 'Enable mobile menu', 'themify' ) )
				),
				'option_js' => true
			),
			array(
				'id' => 'menu_breakpoint',
				'pushed' => 'pushed',
				'type' => 'text',
				'label' => false,
				'after' => __('Mobile menu breakpoint (px)', 'themify'),
				'binding' => array(
					'empty' => array(
						'hide' => array('menu_slide_direction')
					),
					'not_empty' => array(
						'show' => array('menu_slide_direction')
					)
				),
				'wrap_with_class' => 'ui-helper-hidden tb-group-element tb-checkbox-element tb-checkbox-element-allow_menu'
			),
			array(
				'id' => 'menu_slide_direction',
				'pushed' => 'pushed',
				'type' => 'select',
				'label' => false,
				'after' => __('Mobile slide direction', 'themify'),
				'options' => array(
					'right' => __('Right', 'themify'),
					'left' => __('Left', 'themify')
				),
				'wrap_with_class' => 'ui-helper-hidden tb-group-element tb-checkbox-element tb-checkbox-element-allow_menu'
			),
			array(
				'id' => 'color_menu',
				'type' => 'layout',
				'label' => __('Menu Color', 'themify'),
                                'class'=>'tb-colors',
                                'mode'=>'sprite',
				'options' =>$colors
			),
			array(
				'id' => 'according_style_menu',
				'type' => 'checkbox',
				'label' => __('According Styles', 'themify'),
				'options' => Themify_Builder_Model::get_appearance()
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_menu',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
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
			self::get_color('.module-menu .nav li', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
			// Font
			self::get_seperator('font',__('Font', 'themify')),
			self::get_font_family('.module-menu .nav li'),
			self::get_color('.module-menu .nav li','font_color',__('Font Color', 'themify')),
			self::get_font_size('.module-menu .nav li'),
			self::get_line_height('.module-menu .nav li'),
			self::get_letter_spacing('.module-menu .nav li'),
			self::get_text_align('.module-menu .nav'),
			self::get_text_transform('.module-menu .nav'),
			self::get_font_style('.module-menu .nav'),
			// Padding
			self::get_seperator('padding',__('Padding', 'themify')),
			self::get_padding('.module-menu .nav li'),
			// Margin
			self::get_seperator('margin',__('Margin', 'themify')),
			self::get_margin('.module-menu'),
			// Border
			self::get_seperator('border',__('Border', 'themify')),
			self::get_border( '.module-menu .nav li')
		);

		$menu_links = array (
			// Background
			self::get_seperator('link',__( 'Background', 'themify' ),false),
			self::get_color('.module-menu li a', 'link_background_color',__( 'Background Color', 'themify' ),'background-color'),
			self::get_color('.module-menu li a:hover', 'link_hover_background_color',__( 'Background Hover', 'themify' ),'background-color'),
			// Link
			self::get_seperator('link',__('Font', 'themify')),
			self::get_color( '.module-menu li a','link_color'),
			self::get_color('.module-menu li a:hover','link_color_hover',__('Color Hover', 'themify')),
			self::get_text_decoration('.module-menu a')
		);

		$current_menu_links = array (
			// Background
			self::get_seperator('current-links',__( 'Background', 'themify' ),false),
			self::get_color('.module-menu li.current_page_item > a, .module-menu li.current-menu-item > a', 'current-links_background_color',__( 'Background Color', 'themify' ),'background-color'),
			self::get_color('.module-menu li.current_page_item > a:hover, .module-menu li.current-menu-item > a:hover', 'current-links_hover_background_color',__( 'Background Hover', 'themify' ),'background-color'),
			// Link
			self::get_seperator('current-links',__('Font', 'themify')),
			self::get_color( '.module-menu li.current_page_item > a, .module-menu li.current-menu-item > a','current-links_color'),
			self::get_color('.module-menu li.current_page_item > a:hover, .module-menu li.current-menu-item > a:hover','current-links_color_hover',__('Color Hover', 'themify')),
			self::get_text_decoration('.module-menu li.current_page_item a, .module-menu li.current-menu-item a')
		);

		$menu_dropdown = array (
			// Background
			self::get_seperator('link',__( 'Background', 'themify' ),false),
			self::get_color('.module-menu li > ul a', 'dropdown_background_color',__( 'Background Color', 'themify' ),'background-color'),
			self::get_color('.module-menu li > ul a:hover', 'dropdown_hover_background_color',__( 'Background Hover', 'themify' ),'background-color'),
			// Link
			self::get_seperator('link',__('Font', 'themify')),
			self::get_color( '.module-menu li > ul a','dropdown_color'),
			self::get_color('.module-menu li > ul a:hover','dropdown_hover_color',__('Color Hover', 'themify'))
		);

		$menu_mobile = array (
			// Background
			self::get_seperator('link',__( 'Background', 'themify' ),false),
			self::get_color( '.ui.mobile-menu-module', 'mobile_menu_background_color',__( 'Background Color', 'themify' ),'background-color'),
			// Link
			self::get_seperator('link',__('Font', 'themify')),
			self::get_color( '.ui.mobile-menu-module li a','mobile_menu_color'),
			self::get_color( '.ui.mobile-menu-module li a:hover','mobile_menu_hover_color',__('Color Hover', 'themify') )
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
						'label' => __( 'Module Title', 'themify' ),
						'fields' => $this->module_title_custom_style()
					),
					'links' => array(
						'label' => __('Menu Links', 'themify'),
						'fields' => $menu_links
					),
					'current-links' => array(
						'label' => __('Current Links', 'themify'),
						'fields' => $current_menu_links
					),
					'dropdown' => array(
						'label' => __('Menu Dropdown', 'themify'),
						'fields' => $menu_dropdown
					),
					'mobile' => array(
						'label' => __('Mobile Menu', 'themify'),
						'fields' => $menu_mobile
					)
				)
			)
		);

	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Menu_Module' );
