<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Widget
 * Description: Display any available widgets
 */
class TB_Widget_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Widget', 'themify'),
			'slug' => 'widget'
		));

		add_action( 'themify_builder_lightbox_fields', array( $this, 'widget_fields' ), 10, 2 );
		add_action( 'wp_ajax_module_widget_get_form', array( $this, 'widget_get_form' ), 10 );
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_widget',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
                                'render_callback' => array(
                                    'live-selector'=>'.module-title'
				)
			),
			array(
				'id' => 'class_widget',
				'type' => 'widget_select',
				'label' => __('Select Widget', 'themify'),
				'class' => 'large',
				'help' => __('Select Available Widgets', 'themify'),
				'separated' => 'bottom',
				'break' => true,
                                'render_callback' => array(
					'binding' => false
				)
			),
			array(
				'id' => 'instance_widget',
				'type' => 'widget_form',
				'label' => false
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'custom_css_widget',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') ),
				'class' => 'large exclude-from-reset-field'
			)
		);
	}


	public function get_styling() {
		$general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_image('.module-widget'),
                        self::get_color('.module-widget', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
						self::get_repeat('.module-widget'),
			// Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family(array( '.module-widget', '.module-widget a' )),
                        self::get_color(array( '.module-widget', '.module-widget a' ),'font_color',__('Font Color', 'themify')),
                        self::get_font_size(array( '.module-widget', '.module-widget a' )),
                        self::get_line_height(array( '.module-widget', '.module-widget a' )),
                        self::get_letter_spacing('.module-widget'),
                        self::get_text_align('.module-widget'),
                        self::get_text_transform('.module-widget'),
                        self::get_font_style('.module-widget'),
                        // Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color( '.module-widget a','link_color'),
                        self::get_color('.module-widget a:hover','link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration('.module-widget a'),
                        // Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-widget'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-widget'),
                        // Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-widget')
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

        public function get_visual_type() {
            return 'ajax';            
        }
        
	public function widget_fields( $field, $mod_name ) {
                if ( $mod_name !== 'widget' ){
                    return;
                }
		global $wp_widget_factory;
		$output = '';
		switch ( $field['type'] ) {
			case 'widget_select':
				$output= '<div class="selectwrapper"><select name="'. $field['id'] .'" id="'. $field['id']  .'" class="tb_lb_option module-widget-select-field"'. themify_builder_get_control_binding_data( $field ) .'>';
				$output .= '<option></option>';
				foreach ($wp_widget_factory->widgets as $class => $widget ) {
					$output .= '<option value="' . esc_attr( $class ) . '" data-idbase="' . esc_attr( $widget->id_base ) . '">' . esc_html( $widget->name ) . '</option>';
				}
				$output .= '</select></div>';
			break;

			case 'widget_form':
			$output= '<div id="'. $field['id'] .'" class="module-widget-form-container module-widget-form-placeholder tb_lb_option"'. themify_builder_get_control_binding_data( $field ) .'></div>';
			break;
		}
		echo $output;
	}
        

        /*
         * Sanitize keys for widget fields
         * This is required to provide backward compatibility with how widget data was saved.
         *
         * @return array
         * @since 3.2.0
         */
        public static function sanitize_widget_instance( $instance ) {
                $new_instance = array();
                if (is_array($instance) && !empty($instance) ) {
                        foreach ($instance as $key => $val) {
                                /* check if the keys are "clean" */
                                if( false === strpos( $key, '[' ) ) {
                                        $new_instance[ $key ] = $val;
                                } else {
                                        preg_match_all('/\[([^\]]*)\]/', $key, $matches);
                                        if (is_array($matches)) {
                                            $count = count($matches);
                                            if (isset($matches[$count - 1]) && isset($matches[$count - 1][1])) {
                                                $new_instance[$matches[$count - 1][1]] = $val;
                                            }
                                        }
                                }
                        }
                }

                return $new_instance;
        }

	public function widget_get_form() {
		if ( ! wp_verify_nonce( $_POST['tb_load_nonce'], 'tb_load_nonce' ) ) die(-1);

		global $wp_widget_factory;
		require_once ABSPATH . 'wp-admin/includes/widgets.php';
		$widget_class = $_POST['load_class'];
		if ( $widget_class == '') die(-1);

		$instance = !empty( $_POST['widget_instance'] ) ? $_POST['widget_instance'] : array();

		/**
		 * Backward compatibility for versions prior to 3.2.0
		 * Previsouly the widget $instance was stored as a multidimensional array
		 */
		if( ! isset( $instance['id_base'] ) ) {
			if ( is_array( $instance ) && !empty( $instance )) {
				foreach ( $instance as $k => $s ) {
					$instance = $s;
				}
			}
		}

		$instance = self::sanitize_widget_instance( $instance );
                       
		$widget = new $widget_class();
		$widget->number = next_widget_id_number( $_POST['id_base'] );
		ob_start();
		$instance = stripslashes_deep( $instance );
                $template  = '';
                $src = array();
		if($widget_class==='WP_Widget_Archives'){// WP checks checkbox === true in WP_Widget_Archives
			if(!empty($instance['count'])){
				$instance['count'] = true;
			}
			if(!empty($instance['dropdown'])){
				$instance['dropdown'] = true;
			}
		}
                elseif(method_exists($widget, 'render_control_template_scripts')){
                    require_once ABSPATH . WPINC . '/media-template.php';
                    ob_start();
                    $widget->render_control_template_scripts();
                    if($widget->id_base!=='text' && empty($_POST['tpl_loaded'])){
                        wp_print_media_templates();
                    }
                    $template = ob_get_contents();
                    ob_end_clean();
                    global $wp_scripts;
                    $type = str_replace('_','-',$widget->id_base).'-widget';
                    if($widget->id_base==='text'){
                        $type.='s'; 
                    }
                    wp_enqueue_script($type);
                    if(isset($wp_scripts->registered[$type])){
                        if($widget->id_base!=='text' && !empty($wp_scripts->registered[$type]->deps)){
                            foreach($wp_scripts->registered[$type]->deps as $deps){
                                $src[] = $wp_scripts->registered[$deps]->src;
                            }
                        }
                        $src[] = $wp_scripts->registered[$type]->src;
                    }
                }
               
		$widget->form($instance);
		$form = ob_get_clean();

		$base_name = 'widget-' . $wp_widget_factory->widgets[$widget_class]->id_base . '\[' . $widget->number . '\]';
		$form = preg_replace( "/{$base_name}/", '', $form ); // remove extra names
		$form = str_replace( array( '[', ']' ), '', $form ); // remove extra [ & ] characters
		$widget->form = $form;
                
                /**
                 * The widget-id is not used to save widget data, it is however needed for compatibility
                 * with how core renders the module forms.
                 */
		$form= '<div><div class="widget-inside">
			<div class="form">
			<div class="widget-content">'
                        .$form.
			'</div>
			<input type="hidden" class="id_base" name="id_base" value="' . esc_attr( $widget->id_base ) . '" />
			<input type="hidden" class="widget-id" name="widget-id" value="' . time() . '" />
			</div>
		</div><br/></div>';
		die(json_encode(array(
                        'form'=>$form,
                        'template'=>$template,
                        'src'=>$src
                    ))
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
			'mod_title_widget' => '',
			'class_widget' => '',
			'instance_widget' => array(),
		) );
		$text = '';

		if ( '' !== $mod_settings['mod_title_widget'] ) 
			$text .= sprintf( '<h3>%s</h3>', $mod_settings['mod_title_widget'] );

		if ( 'Themify_Twitter' == $mod_settings['class_widget'] ) {
			$mod_settings['instance_widget'] = self::sanitize_widget_instance( $mod_settings['instance_widget'] );
			$username = isset( $mod_settings['instance_widget']['username'] ) ? $mod_settings['instance_widget']['username'] : '';
			$text .= sprintf( '<p>https://twitter.com/%s</p>', $username );
			return $text;
		}

		if ( 'Themify_Social_Links' == $mod_settings['class_widget'] ) 
			return $this->_themify_social_links_plain_content();
		
		return parent::get_plain_content( $module );
	}

	protected function _themify_social_links_plain_content() {
		if ( ! function_exists('themify_get_data') ) return;

		$data = themify_get_data();
		$pre = 'setting-link_';
		$out = '';

		$field_ids = isset( $data[$pre.'field_ids'] ) ? json_decode( $data[$pre.'field_ids'] ) : '';

		if ( is_array( $field_ids ) || is_object( $field_ids ) ) {
			$out .= '<ul>';
			foreach($field_ids as $key => $fid){

				$title_name = $pre.'title_'.$fid;

				if ( function_exists( 'icl_t' ) ) {
					$title_val = icl_t('Themify', $title_name, $data[$title_name]);
				} else {
					$title_val = isset($data[$title_name])? $data[$title_name] : '';
				}

				$link_name = $pre.'link_'.$fid;
				$link_val = isset($data[$link_name])? trim( $data[$link_name] ) : '';
				if ( '' == $link_val ) {
					continue;
				}

				if('' != $link_val){
					$out .= sprintf('
						<li>
							<a href="%s">%s</a>
						</li>',
						esc_url( $link_val ),
						$title_val
					);
				}
			}
			$out .= '</ul>';
		}
		return $out;
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Widget_Module' );
