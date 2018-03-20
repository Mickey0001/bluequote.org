<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Post
 * Description: Display Posts
 */
class TB_Post_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Post', 'themify'),
			'slug' => 'post'
		));
	}

	public function get_title( $module ) {
		$type = isset( $module['mod_settings']['type_query_post'] ) ? $module['mod_settings']['type_query_post'] : 'category';
		$category = isset( $module['mod_settings']['category_post'] ) ? $module['mod_settings']['category_post'] : '';
		$slug_query = isset( $module['mod_settings']['query_slug_post'] ) ? $module['mod_settings']['query_slug_post'] : '';

		if ( 'category' === $type ) {
			return sprintf( '%s : %s', __('Category', 'themify'), $category );
		} else {
			return sprintf( '%s : %s', __('Slugs', 'themify'), $slug_query );
		}
	}

	public function get_options() {
                $is_img_enabled = Themify_Builder_Model::is_img_php_disabled();
		$image_sizes = !$is_img_enabled?themify_get_image_sizes_list( false ):array();
		$taxonomies = Themify_Builder_Model::get_public_taxonomies();
		$term_options = array();

		foreach( $taxonomies as $key => $label ) {
			$term_options[] = array(
				'id' => "{$key}_post",
				'label' => $label,
				'type' => 'query_category',
				'options' => array( 'taxonomy' => $key ),
				'wrap_with_class' => "tb-group-element tb-group-element-{$key}"
			);
		}

		/* allow query posts by slug */
		$taxonomies['post_slug'] = __('Slug', 'themify');

		return array(
			array(
				'id' => 'mod_title_post',
				'type' => 'text',
				'label' => __('Module Title', 'themify'),
				'class' => 'large',
                                'render_callback' => array(
                                    'live-selector'=>'.module-title'
                                )
			),
			array(
				'id' => 'layout_post',
				'type' => 'layout',
				'label' => __('Post Layout', 'themify'),
                                'mode'=>'sprite',
				'options' => array(
					array('img' => 'list-post', 'value' => 'list-post', 'label' => __('List Post', 'themify')),
                                        array('img' => 'grid2', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
					array('img' => 'grid3', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
					array('img' => 'grid4', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
					array('img' => 'list-thumb-image', 'value' => 'list-thumb-image', 'label' => __('List Thumb Image', 'themify')),
					array('img' => 'grid2-thumb', 'value' => 'grid2-thumb', 'label' => __('Grid 2 Thumb', 'themify'))
				),
				'render_callback' => array(
					'binding' => 'live',
					'selector' => '> .builder-posts-wrap'
				)
			),
			array(
				'id' => 'post_type_post',
				'type' => 'select',
				'label' => __('Post Type', 'themify'),
				'options' => Themify_Builder_Model::get_public_post_types()
			),
			array(
				'id' => 'type_query_post',
				'type' => 'radio',
				'label' => __('Query by', 'themify'),
				'options' => $taxonomies,
				'default' => 'category',
				'option_js' => true
			),
			array(
				'type' => 'group',
				'fields' => $term_options
			),
			array(
				'id' => 'query_slug_post',
				'type' => 'text',
				'label' => __('Post Slugs', 'themify'),
				'class' => 'large',
				'wrap_with_class' => 'tb-group-element tb-group-element-post_slug',
				'help' => '<br/>' . __( 'Insert post slug. Multiple slug should be separated by comma (,)', 'themify')
			),
			array(
				'id' => 'post_per_page_post',
				'type' => 'text',
				'label' => __('Limit', 'themify'),
				'class' => 'xsmall',
				'help' => __('number of posts to show', 'themify')
			),
			array(
				'id' => 'offset_post',
				'type' => 'text',
				'label' => __('Offset', 'themify'),
				'class' => 'xsmall',
				'help' => __('number of post to displace or pass over', 'themify')
			),
			array(
				'id' => 'order_post',
				'type' => 'select',
				'label' => __('Order', 'themify'),
				'help' => __('Descending = show newer posts first', 'themify'),
				'options' => array(
					'desc' => __('Descending', 'themify'),
					'asc' => __('Ascending', 'themify')
				)
			),
			array(
				'id' => 'orderby_post',
				'type' => 'select',
				'label' => __('Order By', 'themify'),
				'options' => array(
					'date' => __('Date', 'themify'),
					'id' => __('Id', 'themify'),
					'author' => __('Author', 'themify'),
					'title' => __('Title', 'themify'),
					'name' => __('Name', 'themify'),
					'modified' => __('Modified', 'themify'),
					'rand' => __('Random', 'themify'),
					'comment_count' => __('Comment Count', 'themify')
				)
			),
			array(
				'id' => 'display_post',
				'type' => 'select',
				'label' => __('Display', 'themify'),
				'options' => array(
					'content' => __('Content', 'themify'),
					'excerpt' => __('Excerpt', 'themify'),
					'none' => __('None', 'themify')
				)
			),
			array(
				'id' => 'hide_feat_img_post',
				'type' => 'select',
				'label' => __('Hide Featured Image', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'image_size_post',
				'type' => 'select',
				'label' => __('Image Size', 'themify'),
				'hide' => !$is_img_enabled,
				'options' => $image_sizes
			),
			array(
				'id' => 'img_width_post',
				'type' => 'text',
				'label' => __('Image Width', 'themify'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_post',
				'type' => 'text',
				'label' => __('Image Height', 'themify'),
				'class' => 'xsmall'
			),
			array(
				'id' => 'unlink_feat_img_post',
				'type' => 'select',
				'label' => __('Unlink Featured Image', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'hide_post_title_post',
				'type' => 'select',
				'label' => __('Hide Post Title', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'unlink_post_title_post',
				'type' => 'select',
				'label' => __('Unlink Post Title', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'hide_post_date_post',
				'type' => 'select',
				'label' => __('Hide Post Date', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'hide_post_meta_post',
				'type' => 'select',
				'label' => __('Hide Post Meta', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			array(
				'id' => 'hide_page_nav_post',
				'type' => 'select',
				'label' => __('Hide Page Navigation', 'themify'),
				'options' => array(
                                        ''=>'',
					'yes' => __('Yes', 'themify'),
					'no' => __('No', 'themify')
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_post',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
			)
		);
	}

	public function get_default_settings() {
		return array(
			'layout_post' => 'grid4',
			'post_per_page_post' => 4,
			'display_post' => 'excerpt',
                        'post_type_post'=>'post'
		);
	}
        
        public function get_visual_type() {
            return 'ajax';            
        }


	public function get_styling() {
                $general = array(
			// Background
                        self::get_seperator('image_bacground',__( 'Background', 'themify' ),false),
                        self::get_color('.module-post .post', 'background_color',__( 'Background Color', 'themify' ),'background-color'),
			// Font
                        self::get_seperator('font',__('Font', 'themify')),
                        self::get_font_family(array( '.module-post .post-title', '.module-post .post-title a' )),
                        self::get_color(array( '.module-post .post', '.module-post h1', '.module-post h2', '.module-post h3:not(.module-title)', '.module-post h4', '.module-post h5', '.module-post h6', '.module-post .post-title', '.module-post .post-title a' ),'font_color',__('Font Color', 'themify')),
                        self::get_font_size('.module-post .post'),
                        self::get_line_height('.module-post .post'),
                        self::get_letter_spacing('.module-post .post'),
                        self::get_text_align('.module-post .post'),
                        self::get_text_transform('.module-post .post'),
                        self::get_font_style('.module-post .post'),
			// Link
                        self::get_seperator('link',__('Link', 'themify')),
                        self::get_color( '.module-post a','link_color'),
                        self::get_color('.module-post a:hover','link_color_hover',__('Color Hover', 'themify')),
                        self::get_text_decoration('.module-post a'),
			// Padding
                        self::get_seperator('padding',__('Padding', 'themify')),
                        self::get_padding('.module-post .post'),
			// Margin
                        self::get_seperator('margin',__('Margin', 'themify')),
                        self::get_margin('.module-post .post'),
                        // Border
                        self::get_seperator('border',__('Border', 'themify')),
                        self::get_border('.module-post .post')
		);
                
                $post_title = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family(array( '.module-post .post-title', '.module-post .post-title a' ),'font_family_title'),
                        self::get_color(array( '.module-post .post-title', '.module-post .post-title a' ),'font_color_title',__('Font Color', 'themify')),
                        self::get_color(array( '.module-post .post-title:hover', '.module-post .post-title a:hover' ),'font_color_title_hover',__('Color Hover', 'themify')),
                        self::get_font_size('.module-post .post-title','font_size_title'),
                        self::get_line_height('.module-post .post-title','line_height_title')
		);
                
                $post_meta = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family(array( '.module-post .post-content .post-meta', '.module-post .post-content .post-meta a' ),'font_family_meta'),
                        self::get_color(array( '.module-post .post-content .post-meta', '.module-post .post-content .post-meta a' ),'font_color_meta',__('Font Color', 'themify')),
                        self::get_color(array('.module-post .post-content .post-meta:hover', '.module-post .post-content .post-meta a:hover'),'font_color_meta_hover',__('Color Hover', 'themify')),
                        self::get_font_size( '.module-post .post-content .post-meta','font_size_meta'),
                        self::get_line_height( '.module-post .post-content .post-meta','line_height_meta')
		);
                
                $post_date = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family(array('.module-post .post .post-date', '.module-post .post .post-date a'),'font_family_date'),
                        self::get_color(array('.module-post .post .post-date', '.module-post .post .post-date a'),'font_color_date',__('Font Color', 'themify')),
                        self::get_color(array('.module-post .post .post-date:hover', '.module-post .post .post-date a:hover'),'font_color_date_hover',__('Color Hover', 'themify')),
                        self::get_font_size('.module-post .post .post-date','font_size_date'),
                        self::get_line_height('.module-post .post .post-date','line_height_date')
		);
                
                $post_content = array(
			// Font
                        self::get_seperator('font',__('Font', 'themify'),false),
                        self::get_font_family('.module-post .post-content .entry-content','font_family_content'),
                        self::get_color('.module-post .post-content .entry-content','font_color_content',__('Font Color', 'themify')),
                        self::get_font_size('.module-post .post-content .entry-content','font_size_content'),
                        self::get_line_height('.module-post .post-content .entry-content','line_height_content')
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
					'title' => array(
						'label' => __('Post Title', 'themify'),
						'fields' => $post_title
					),
					'meta' => array(
						'label' => __('Post Meta', 'themify'),
						'fields' => $post_meta
					),
					'date' => array(
						'label' => __('Post Date', 'themify'),
						'fields' => $post_date
					),
					'content' => array(
						'label' => __('Post Content', 'themify'),
						'fields' => $post_content
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
		return ''; // no static content for dynamic content
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Post_Module' );

/**
 * Title tag settings for Post module
 *
 * @return array
 */
function themify_builder_post_title_args( $args ) {
	$args['tag'] = 'h2';
	return $args;
}