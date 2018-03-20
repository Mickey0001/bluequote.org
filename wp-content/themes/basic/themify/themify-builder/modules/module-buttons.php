<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Button
 * Description: Display Button content
 */

class TB_Buttons_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct( array(
			'name' => __('Button', 'themify'),
			'slug' => 'buttons'
		));
	}

	public function get_title( $module ) {
		return  isset( $module['mod_settings']['mod_title_button'] ) ? wp_trim_words($module['mod_settings']['mod_title_button'], 100 ) : '';
	}

	public function get_options() {
		$colors = Themify_Builder_Model::get_colors();
		$colors[] = array('img' => 'transparent', 'value' => 'transparent', 'label' => __('Transparent', 'themify'));

		return  array(
			array(
				'id'=>'buttons_size',
				'type' => 'radio',
				'label' => __( 'Size', 'themify' ),
				'options' => array(
					'normal'=> __( 'Normal', 'themify' ),
					'small'=>__( 'Small', 'themify' ),
					'large'=> __( 'Large', 'themify' ),
					'xlarge'=>__( 'xLarge', 'themify' )
				),
				'default' => 'normal',
				'render_callback' => array(
                                    'binding' => 'live'
				)
			),
			array(
				'id'=>'buttons_style',
				'type' => 'radio',
				'label' => __( 'Button Background Style', 'themify' ),
				'options' => array(
					'circle' => __( 'Circle', 'themify' ),
					'rounded' => __( 'Rounded', 'themify' ),
					'squared' => __( 'Squared', 'themify' ),
					'outline' => __( 'Outlined', 'themify' ),
					'transparent' => __( 'Transparent', 'themify' )
				),
				'default' => 'rounded',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'fullwidth_button',
				'type' => 'checkbox',
				'label' => __('Fullwidth Button', 'themify'),
				'options' => array(
					array( 'name' => 'buttons-fullwidth', 'value' => __('Display buttons fullwidth', 'themify') )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id'=>'display',
				'type' => 'radio',
				'label' => __( 'Display', 'themify' ),
				'options' => array(
					'buttons-horizontal' => __( 'Horizontal', 'themify' ),
					'buttons-vertical' => __( 'Vertical', 'themify' ),
				),
				'default' => 'buttons-horizontal',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'content_button',
				'type' => 'builder',
				'new_row_text'=>__('Add new button','themify'),
				'options' => array(
					array(
						'id' => 'label',
						'type' => 'text',
						'label' => __( 'Text', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live',
                                                        'live-selector'=>'.builder_button span'
						)
					),
					array(
						'id' => 'link',
						'type' => 'text',
						'label' => __( 'Link', 'themify' ),
						'class' => 'fullwidth',
						'binding' => array(
							'empty' => array(
								'hide' => array('link_options', 'button_color')
							),
							'not_empty' => array(
								'show' => array('link_options', 'button_color')
							)
						),
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live'
						)
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
							'repeater' => 'content_button',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'lightbox_size',
						'type' => 'multi',
						'label' => __('Lightbox Dimension', 'themify'),
						'options' => array(
							array(
								'id' => 'lightbox_width',
								'type' => 'text',
								'label' => __( 'Width', 'themify' ),
								'value' => '',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							),
							array(
								'id' => 'lightbox_size_unit_width',
								'type' => 'select',
								'label' => __( 'Units', 'themify' ),
								'options' => array(
									'pixels' => __('px ', 'themify'),
									'percents' => __('%', 'themify')
								),
								'default' => 'pixels',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							),
							array(
								'id' => 'lightbox_height',
								'type' => 'text',
								'label' => __( 'Height', 'themify' ),
								'value' => '',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							),
							array(
								'id' => 'lightbox_size_unit_height',
								'type' => 'select',
								'label' => __( 'Units', 'themify' ),
								'options' => array(
									'pixels' => __('px ', 'themify'),
									'percents' => __('%', 'themify')
								),
								'default' => 'pixels',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							)
						),
						'wrap_with_class' => 'tb-group-element tb-group-element-lightbox lightbox_size'
					),
					array(
						'id' => 'button_container',
						'type' => 'multi',
						'label' => __( 'Color', 'themify' ),
						'wrap_with_class' => 'button_color',
						'options' => array(
							array(
								'id' => 'button_color_bg',
								'type' => 'layout',
								'label' =>'',
								'class' => 'tb-colors',
								'mode' => 'sprite',
								'options' => $colors,
								'bottom' => false,
								'wrap_with_class' => 'fullwidth',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							)
						)
					),
					array(
						'id' => 'icon_container',
						'type' => 'multi',
						'label' => __('Icon', 'themify'),
						'wrap_with_class' => 'fullwidth',
						'options' => array(
							array(
								'id' => 'icon',
								'type' => 'text',
								'iconpicker' => true,
								'label' => '',
								'class' => 'fullwidth themify_field_icon',
								'wrap_with_class' => 'fullwidth',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							)
						)
					)
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array('html' => '<hr/>')
			),
			array(
				'id' => 'css_button',
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
			'content_button' => array(
				array( 
					'label' => esc_html__( 'Button Text', 'themify' ), 
					'link' => 'https://themify.me/',
					'link_options' => 'regular'
				)
			)
		);
	}
        

	public function get_styling() {
		$general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module.module-buttons'),
						self::get_color('.module.module-buttons', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
						self::get_repeat('.module.module-buttons'),
                        // Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family(' div.module-buttons'),
                        self::get_color(' .module-buttons-item span','font_color',__('Font Color', 'themify')),
                        self::get_font_size( array(' div.module-buttons i',' div.module-buttons a',' div.module-buttons span')),
                        self::get_line_height(array(' div.module-buttons i',' div.module-buttons a',' div.module-buttons span')),
                        self::get_letter_spacing(array(' div.module-buttons i',' div.module-buttons a',' div.module-buttons span')),
                        self::get_text_align(' div.module-buttons'),
                        self::get_text_transform(' div.module-buttons'),
                        self::get_font_style(' div.module-buttons'),
			// Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding(' div.module-buttons'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin(' div.module-buttons'),
			// Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border(' div.module-buttons:not(.module)')
		);

		$button_link = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_color(' .module-buttons .module-buttons-item a', 'button_background_color',__( 'Background Color', 'themify' ),'background-color'),
                        self::get_color(' .module-buttons .module-buttons-item a:hover', 'button_hover_background_color',__( 'Background Hover', 'themify' ),'background-color'),
			
			// Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color(' .module-buttons .module-buttons-item a', 'link_color'),
                        self::get_color(' .module-buttons .module-buttons-item a:hover', 'link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration(array(' .module-buttons .module-buttons-item a span',' .module-buttons .module-buttons-item a i')),
                        // Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding(' .module-buttons .module-buttons-item a','padding_link'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin(' .module-buttons .module-buttons-item a','link_margin'),
			// Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border(' .module-buttons .module-buttons-item a','link_border')
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
					'button_link' => array(
						'label' => __('Button Link', 'themify'),
						'fields' => $button_link
					)
				)
			)
		);

	}

	protected function _visual_template() { ?>
		<div class="module module-<?php echo $this->slug; ?> {{ data.css_button }}">
			<# if ( data.content_button ) { #>
				<div class="module-<?php echo $this->slug; ?> {{ data.buttons_size }} {{ data.buttons_style }}">
					<# _.each( data.content_button, function( item ) { #>

						<div class="module-buttons-item {{ data.fullwidth_button }} {{ data.display }}">
							<# if ( item.link ) { #>
							<a class="ui builder_button {{ item.button_color_bg }}" href="{{ item.link }}">
							<# } #>
							
							<# if ( item.icon ) { #>
							<i class="fa {{ item.icon }}"></i>
							<# } #>

							<span>{{ item.label }}</span>

							<# if ( item.link ) { #>
							</a>
							<# } #>
						</div>

					<# } ); #>
				</div>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Buttons_Module' );
