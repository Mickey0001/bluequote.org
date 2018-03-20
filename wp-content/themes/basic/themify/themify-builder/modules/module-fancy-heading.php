<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Fancy Heading
 * Description: Heading with fancy styles
 */
class TB_Fancy_Heading_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Fancy Heading', 'themify'),
			'slug' => 'fancy-heading'
		));
	}
	

	public function get_options() {
		return array(
			array(
				'id' => 'heading',
				'type' => 'text',
				'label' => __('Heading', 'themify'),
				'class' => 'fullwidth',
				'render_callback' => array(
                                    'binding' => 'live',
                                    'live-selector'=>'.main-head'
				)
			),
			array(
				'id' => 'sub_heading',
				'type' => 'text',
				'label' => __('Sub Heading', 'themify'),
				'class' => 'fullwidth',
				'render_callback' => array(
                                    'binding' => 'live',
                                    'live-selector'=>'.sub-head'
				)
			),
			array(
				'id' => 'heading_tag',
				'label' => __( 'HTML Tag', 'themify' ),
				'type' => 'select',
				'options' => array(
					'h1' => __( 'h1', 'themify' ),
					'h2' => __( 'h2', 'themify' ),
					'h3' => __( 'h3', 'themify' )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'text_alignment',
				'label' => __( 'Text Alignment', 'themify' ),
				'type' => 'select',
				'options' => array(
					'themify-text-center' => __( 'Center', 'themify' ),
					'themify-text-left' => __( 'Left', 'themify' ),
					'themify-text-right' => __( 'Right', 'themify' )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_class',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'heading' => esc_html__( 'Heading', 'themify' ),
			'sub_heading' => esc_html__( 'Sub Heading', 'themify' )
		);
	}

	public function get_styling() {
		$general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module-fancy-heading'),
                        self::get_color('.module-fancy-heading', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
						self::get_repeat('.module-fancy-heading'),
			// Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-fancy-heading'),
                        // Margin
			self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-fancy-heading'),
			// Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-fancy-heading')
		);

		$heading = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family('.module .main-head'),
                        self::get_color('.module .main-head','font_color',__('Font Color', 'themify')),
                        self::get_font_size('.module .main-head'),
                        self::get_line_height('.module .main-head'),
                        self::get_letter_spacing('.module .main-head'),
			// Main Heading Margin
			self::get_heading_margin_multi_field( '.module-fancy-heading .fancy-heading .main-head','main', 'top'),
			self::get_heading_margin_multi_field( '.module-fancy-heading .fancy-heading .main-head','main', 'bottom')
		);

		$subheading = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family('.module .sub-head','font_family_subheading'),
                        self::get_color('.module .sub-head','font_color_subheading',__('Font Color', 'themify')),
                        self::get_font_size('.module .sub-head','font_size_subheading'),
                        self::get_line_height('.module .sub-head','line_height_subheading'),
                        self::get_letter_spacing('.module .sub-head','letter_spacing_subheading'),
			// Sub Heading Margin
			self::get_heading_margin_multi_field('.module-fancy-heading .fancy-heading .sub-head','sub', 'top'),
			self::get_heading_margin_multi_field('.module-fancy-heading .fancy-heading .sub-head','sub', 'bottom')
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
					'heading' => array(
						'label' => __('Heading', 'themify'),
						'fields' => $heading
					),
					'subheading' => array(
						'label' => __('Sub Heading', 'themify'),
						'fields' => $subheading
					)
				)
			)
		);
	}

	protected function _visual_template() { 
		$module_args = self::get_module_args(); ?>
		<div class="module module-<?php echo $this->slug; ?> {{ data.css_class }}">
			<# 
			var heading_tag = _.isUndefined( data.heading_tag ) ? 'h1' : data.heading_tag,
				text_alignment = _.isUndefined( data.text_alignment ) ? 'themify-text-center' : data.text_alignment;
			#>
			<{{ heading_tag }} class="fancy-heading {{ text_alignment }}">
				<span class="main-head">{{{ data.heading }}}</span>
				<span class="sub-head">{{{ data.sub_heading }}}</span>
			</{{ heading_tag }}>
		</div>
	<?php
	}

	/**
	 * Render plain content for static content.
	 * 
	 * @param array $module 
	 * @return string
	 */
	public function get_plain_content( $module ) {
		$mod_settings = wp_parse_args( $module['mod_settings'], array(
			'heading' => '',
			'heading_tag' => 'h1',
			'sub_heading' => ''
		) );
		$text = sprintf('<%s>%s<br/>%s</%s>', $mod_settings['heading_tag'], $mod_settings['heading'], $mod_settings['sub_heading'], $mod_settings['heading_tag'] );
		return $text;
	}
}
///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Fancy_Heading_Module' );