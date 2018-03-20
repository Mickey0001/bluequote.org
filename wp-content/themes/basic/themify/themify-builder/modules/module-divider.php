<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Divider
 * Description: Display Divider
 */
class TB_Divider_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Divider', 'themify'),
			'slug' => 'divider'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_divider',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live',
                                        'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'style_divider',
				'type' => 'layout',
                                'mode'=>'sprite',
				'label' => __('Divider Style', 'themify'),
				'options' => array(
					array('img' => 'solid', 'value' => 'solid', 'label' => __('Solid', 'themify')),
					array('img' => 'dotted', 'value' => 'dotted', 'label' => __('Dotted', 'themify')),
					array('img' => 'dashed', 'value' => 'dashed', 'label' => __('Dashed', 'themify')),
					array('img' => 'double', 'value' => 'double', 'label' => __('Double', 'themify'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'stroke_w_divider',
				'type' => 'text',
				'label' => __('Stroke Thickness', 'themify'),
				'class' => 'xsmall',
				'help' => 'px',
				'value'=> 1,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_divider',
				'type' => 'text',
				'label' => __('Divider Color', 'themify'),
				'colorpicker' => true,
				'value'=>'000',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'top_margin_divider',
				'type' => 'text',
				'label' => __('Top Margin', 'themify'),
				'class' => 'xsmall',
				'help' => 'px',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'bottom_margin_divider',
				'type' => 'text',
				'label' => __('Bottom Margin', 'themify'),
				'class' => 'xsmall',
				'help' => 'px',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_type',
				'type' => 'radio',
				'label' => __('Divider Width', 'themify'),
				'options' => array(
					'fullwidth' => __('Fullwidth ', 'themify'),
					'custom' => __('Custom', 'themify'),
				),
				'default' => 'fullwidth',
				'option_js' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_width',
				'type' => 'text',
				'label' => __('Width', 'themify'),
				'class' => 'xsmall',
				'help' => 'px',
				'wrap_with_class' => 'tb-group-element tb-group-element-custom',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_align',
				'type' => 'select',
				'label' =>__('Alignment', 'themify'),
				'options' => array(
					'left' => __('Left ', 'themify'),
					'center' => __('Center', 'themify'),
					'right' => __('Right', 'themify'),
				),
				'default' => 'left',
				'wrap_with_class' => 'tb-group-element tb-group-element-custom',
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
				'id' => 'css_divider',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'stroke_w_divider' => 1,
			'color_divider' => '000000',
			'divider_width' => 150
		);
	}
	

	public function get_styling() {
		return array();
	}

	protected function _visual_template() { 
		$module_args = self::get_module_args(); ?>
		<#
		var style = '',
			align = 'custom' === data.divider_type && ! _.isUndefined( data.divider_align ) ? 'divider-' + data.divider_align : '';
		if ( data.stroke_w_divider ) style += 'border-width:'+ data.stroke_w_divider +'px; ';
		if ( data.color_divider ) style += 'border-color:' + themifybuilderapp.Utils.toRGBA(data.color_divider) + '; ';
		if ( data.top_margin_divider ) style += 'margin-top:' + data.top_margin_divider + 'px; ';
		if ( data.bottom_margin_divider ) style += 'margin-bottom:'+ data.bottom_margin_divider +'px; ';
		if ( 'custom' === data.divider_type && data.divider_width > 0 ) style += 'width:'+ data.divider_width +'px; ';
		if ( _.isUndefined( data.style_divider ) ) data.style_divider = 'solid';
		#>
		<div class="module module-<?php echo $this->slug ; ?> divider-{{ data.divider_type }} {{ data.style_divider }} {{ align }} {{ data.css_divider }}" style="{{ style }}">
			<# if ( data.mod_title_divider ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_divider }}}<?php echo $module_args['after_title']; ?>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Divider_Module' );
