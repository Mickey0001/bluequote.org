<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Text
 * Description: Display text content
 */
class TB_Text_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Text', 'themify'),
			'slug' => 'text'
		));
	}
        public function get_title( $module ) {
            return isset( $module['mod_settings']['content_text'] ) ? wp_trim_words($module['mod_settings']['content_text'],100 ) : '';
	}
	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_text',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
				'render_callback' => array(
                                    'binding' => 'live',
                                    'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'content_text',
				'type' => 'wp_editor',
				'class' => 'fullwidth',
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
				'id' => 'add_css_text',
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
			'content_text' => esc_html__( 'Text content', 'themify' )
		);
	}


	public function get_styling() {
		$general = array(
			// Background
			self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
			self::get_image('.module-text'),
			self::get_color('.module-text', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
			self::get_repeat('.module-text'),
			// Font
			self::get_seperator('font',__('Font', 'themify')),
			self::get_font_family(array( '.module-text', '.module-text h1', '.module-text h2', '.module-text h3:not(.module-title)', '.module-text h4', '.module-text h5', '.module-text h6' )),
			self::get_color(array( '.module-text', '.module-text h1', '.module-text h2', '.module-text h3:not(.module-title)', '.module-text h4', '.module-text h5', '.module-text h6' ),'font_color',__('Font Color', 'themify')),
			self::get_font_size('.module-text'),
			self::get_line_height('.module-text'),
			self::get_letter_spacing('.module-text'),
			self::get_text_align('.module-text'),
			self::get_text_transform('.module-text'),
			self::get_font_style('.module-text'),
			// Paragraph
			self::get_seperator('paragraph',__('Paragraph', 'themify')),
			self::get_heading_margin_multi_field( '.module-text', 'p', 'top' ),
			self::get_heading_margin_multi_field( '.module-text', 'p', 'bottom' ),
			// Link
			self::get_seperator('link',__('Link', 'themify')),
			self::get_color( '.module-text a','link_color'),
			self::get_color('.module-text a:hover','link_color_hover',__('Color Hover', 'themify')),
			self::get_text_decoration('.module-text a'),
			// Multi-column
			self::get_seperator('multi_columns', __('Multi-columns', 'themify')),
			self::get_multi_columns_count( '.module-text' ),
			self::get_multi_columns_gap( '.module-text' ),
			self::get_multi_columns_divider( '.module-text' ),
			// // Padding
			self::get_seperator('padding',__('Padding', 'themify')),
			self::get_padding('.module-text'),
			// Margin
			self::get_seperator('margin',__('Margin', 'themify')),
			self::get_margin('.module-text'),
			// Border
			self::get_seperator('border',__('Border', 'themify')),
			self::get_border('.module-text')
		);

		$heading = array();

		for($i=1;$i<=6;++$i){
			$h = 'h'.$i;
			$heading = array_merge($heading,array( 
				self::get_seperator('font',sprintf(__('Heading %s Font', 'themify'),$i),$i!==1),
				self::get_font_family('.module.module-text '.$h.($i===3?':not(.module-title)':''),'font_family_'.$h),
				self::get_color('.module.module-text '.$h.($i===3?':not(.module-title)':''),'font_color_'.$h,__('Font Color', 'themify')),
				self::get_font_size('.module-text '.$h,'font_size_'.$h),
				self::get_line_height('.module-text '.$h,'line_height_'.$h),
				// Heading  Margin
				self::get_heading_margin_multi_field('.module-text', $h, 'top' ),
				self::get_heading_margin_multi_field('.module-text', $h, 'bottom' ),
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
						'module-title' => array(
						'label' => __( 'Module Title', 'themify' ),
						'fields' => $this->module_title_custom_style()
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
		$module_args = self::get_module_args();?>
		<div class="module module-<?php echo $this->slug; ?> {{ data.add_css_text }}">
			<# if ( data.mod_title_text ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_text }}}<?php echo $module_args['after_title']; ?>
			<# } #>

			{{{ data.content_text }}}
		</div>
	<?php
	}

	/**
	 * Generate read more link for text module
	 *
	 * @param string $content
	 * @return string generated load more link in the text.
	 */
	public static function generate_read_more( $content ){
		if ( preg_match( '/(<|&lt;)!--more(.*?)?--(>|&gt;)/', $content, $matches ) ) {
			$text = trim($matches[2]);
			if( !empty( $text ) ){
				$read_more_text = $text;
			}else {
				$read_more_text = apply_filters('themify_builder_more_text', __('More &rarr;', 'themify'));
			}
			$read_more = '<div><a href="#" class="more-link module-text-more">' . $read_more_text . '</a></div>';
			$content = str_replace( $matches[0] , $read_more , $content );
		}
		return $content;
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Text_Module' );
