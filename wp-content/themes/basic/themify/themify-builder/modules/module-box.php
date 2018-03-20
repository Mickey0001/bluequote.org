<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Box
 * Description: Display box content
 */
class TB_Box_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Box', 'themify'),
			'slug' => 'box'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_box',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live',
                                        'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'content_box',
				'type' => 'wp_editor',
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_box',
				'type' => 'layout',
                                'mode'=>'sprite',
                                'class'=>'tb-colors',
				'label' => __('Box Color', 'themify'),
				'options' => Themify_Builder_Model::get_colors(),
				'bottom' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'appearance_box',
				'type' => 'checkbox',
				'label' => __('Appearance', 'themify'),
				'options' =>Themify_Builder_Model::get_appearance(),
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
				'id' => 'add_css_box',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') ),
				'class' => 'large exclude-from-reset-field',
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'content_box' => esc_html__( 'Box content', 'themify' )
		);
	}

	

	public function get_styling() {
		$general = array(
                        //bacground
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module-box .module-box-content'),
                        self::get_color('.module-box .module-box-content', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
                        self::get_repeat('.module-box .module-box-content'),
			// Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family(array('.module-box','.module-box h1','.module-box h2','.module-box h3:not(.module-title)','.module-box h4','.module-box h5','.module-box h6')),
                        self::get_color(array('.module-box .module-box-content','.module-box h1','.module-box h2','.module-box h3:not(.module-title)','.module-box h4','.module-box h5','.module-box h6'),'font_color',__('Font Color', 'themify')),
                        self::get_font_size('.module-box'),
			self::get_line_height('.module-box'),
                        self::get_letter_spacing('.module-box'),
                        self::get_text_align('.module-box'),
			self::get_text_transform('.module-box'),
                        self::get_font_style('.module-box'),
			// Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color( '.module-box a','link_color'),
                        self::get_color( '.module-box a:hover','link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration( '.module-box a'),
			// Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-box .module-box-content'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-box'),
			// Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-box .module-box-content')
                       
		);
                $heading = array();
                for($i=1;$i<=6;++$i){
                    $h = 'h'.$i;
                    $heading = array_merge($heading,array( 
                                    self::get_seperator('font',sprintf(__('Heading %s Font', 'themify'),$i),$i!==1),
                                    self::get_font_family('.module.module-box '.$h.($i===3?':not(.module-title)':''),'font_family_'.$h),
                                    self::get_color('.module.module-box '.$h.($i===3?':not(.module-title)':''),'font_color_'.$h,__('Font Color', 'themify')),
                                    self::get_font_size('.module-box '.$h,'font_size_'.$h),
                                    self::get_line_height('.module-box '.$h,'line_height_'.$h),
                                    // Heading  Margin
                                    self::get_heading_margin_multi_field('.module-box', $h, 'top' ),
                                    self::get_heading_margin_multi_field('.module-box', $h, 'bottom' ),
                            ));
                }
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
					)
				)
			),
		);

	}

	protected function _visual_template() { 
		$module_args = self::get_module_args(); ?>
		<div class="module module-<?php echo $this->slug ; ?>">
			<# if ( data.mod_title_box ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_box }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			
			<div class="ui module-<?php echo $this->slug; ?>-content {{ data.color_box }} {{ data.add_css_box }} {{ data.background_repeat }} <# ! _.isUndefined( data.appearance_box ) ? print( data.appearance_box.split('|').join(' ') ) : ''; #>">
				{{{ data.content_box }}}
			</div>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Box_Module' );
