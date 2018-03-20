<?php
/**
 * GreatMag Theme Customizer.
 *
 * @package GreatMag
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function greatmag_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    $wp_customize->get_section( 'colors' )->title 		= __('General', 'greatmag');
    $wp_customize->get_section( 'colors' )->panel 		= 'greatmag_colors_panel';
    $wp_customize->get_section( 'colors' )->priority 	= '9';
    $wp_customize->get_section( 'header_image' )->panel = 'greatmag_header_panel';
    $wp_customize->get_section( 'title_tagline' )->priority    = '8';


	/**
	 * Header
	 */
    $wp_customize->add_panel( 'greatmag_header_panel', array(
        'priority'       => 10,
        'capability'     => 'edit_theme_options',
        'theme_supports' => '',
        'title'          => __('Header area', 'greatmag'),
    ) );

    //___General___//
    $wp_customize->add_section(
        'greatmag_header_general',
        array(
            'title'         => __('General', 'greatmag'),
            'panel'			=> 'greatmag_header_panel',
            'priority'      => 10,
        )
    );
    //Branding style
    $wp_customize->add_setting(
        'branding_style',
        array(
            'default'           => 'left',
            'sanitize_callback' => 'greatmag_sanitize_branding',
        )
    );
    $wp_customize->add_control(
        'branding_style',
        array(
            'type'        => 'radio',
            'label'       => __('Branding style', 'greatmag'),
            'section'     => 'greatmag_header_general',
            'choices' => array(
                'left'    	=> __('Left aligned', 'greatmag'),
                'centered'  => __('Centered', 'greatmag'),
            ),
        )
    );
    //Menu position
    $wp_customize->add_setting(
        'menu_position',
        array(
            'default'           => 'after-branding',
            'sanitize_callback' => 'greatmag_sanitize_menu',
        )
    );
    $wp_customize->add_control(
        'menu_position',
        array(
            'type'        => 'radio',
            'label'       => __('Menu position', 'greatmag'),
            'section'     => 'greatmag_header_general',
            'choices' => array(
                'before-branding' => __('Before branding', 'greatmag'),
                'after-branding'  => __('After branding', 'greatmag'),
            ),
        )
    );

		$wp_customize->add_setting(
      'hide_login_dropdown',
      array(
        'sanitize_callback' => 'greatmag_sanitize_checkbox',
        'default' => 0,
      )
    );
    $wp_customize->add_control(
      'hide_login_dropdown',
        array(
            'type' => 'checkbox',
            'label' => __('Hide login form dropdown?', 'greatmag'),
            'section' => 'greatmag_header_general',
            'priority' => 10,
        )
    );

		$wp_customize->add_setting(
      'hide_search_icon',
      array(
        'sanitize_callback' => 'greatmag_sanitize_checkbox',
        'default' => 0,
      )
    );
    $wp_customize->add_control(
      'hide_search_icon',
        array(
            'type' => 'checkbox',
            'label' => __('Hide search icon?', 'greatmag'),
            'section' => 'greatmag_header_general',
            'priority' => 13,
        )
    );

    //___Latest news___//
    $wp_customize->add_section(
        'greatmag_latest_news',
        array(
            'title'         => __('Latest news', 'greatmag'),
            'panel'         => 'greatmag_header_panel',
            'priority'      => 11,
        )
    );
    $wp_customize->add_setting(
        'latest_news_number',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '5',
        )
    );
    $wp_customize->add_control( 'latest_news_number', array(
        'type'        => 'number',
        'priority'    => 10,
        'section'     => 'greatmag_latest_news',
        'label'       => __('Number of posts', 'greatmag'),
        'input_attrs' => array(
            'min'   => 2,
            'max'   => 15,
            'step'  => 1,
        ),
    ) );
    $wp_customize->add_setting(
        'latest_news_title',
        array(
            'sanitize_callback' => 'greatmag_sanitize_text',
            'default'           => __('Latest news', 'greatmag'),
        )
    );
    $wp_customize->add_control(
        'latest_news_title',
        array(
            'label'         => __( 'Latest news title', 'greatmag' ),
            'section'       => 'greatmag_latest_news',
            'type'          => 'text',
            'priority'      => 14
        )
     );

    //___Social icons___//
    $wp_customize->add_section(
        'greatmag_social_icons',
        array(
            'title'         => __('Social icons', 'greatmag'),
            'panel'			=> 'greatmag_header_panel',
            'priority'      => 10,
        )
    );
    $socials = array(
			'twitter',
			'instagram',
			'facebook',
			'linkedin',
			'pinterest',
			'youtube',
			'googleplus',
			'dribbble',
			'flickr',
			'vimeo',
			'foursquare',
			'tumblr',
			'behance',
			'deviantart',
			'soundcloud',
			'spotify',
			'weibo',
			'xing',
			'trello'
		);
    foreach ( $socials as $social ) {
	    $wp_customize->add_setting(
	        'social_link_' . $social,
	        array(
	            'default' => '',
	            'sanitize_callback' => 'esc_url_raw',
	        )
	    );
			$label = ucfirst($social);
			if($social == 'googleplus'){
				$label = 'Google Plus';
			}
			if($social == 'soundcloud'){
				$label = 'SoundCloud';
			}
	    $wp_customize->add_control(
	        'social_link_' . $social,
	        array(
	            'label' 	=> $label,
	            'section' 	=> 'greatmag_social_icons',
	            'type' 		=> 'url',
	            'priority' 	=> 10
	        )
	    );
    }

    //___Header AD___//
    $wp_customize->add_section(
        'greatmag_header_ad',
        array(
            'title'         => __('Header ad', 'greatmag'),
            'panel'			=> 'greatmag_header_panel',
            'priority'      => 10,
        )
    );
	$wp_customize->add_setting(
	    'header_ad_image',
	    array(
	        'sanitize_callback' => 'esc_url_raw',
	    )
	);
	$wp_customize->add_control(
	    new WP_Customize_Image_Control(
	        $wp_customize,
	    	'header_ad_image',
	        array(
	           'label'     => __( 'Header ad image', 'greatmag' ),
	           'type'      => 'image',
	           'section'     => 'greatmag_header_ad',
	           'description' => __('Recommended size: 728 x 90px', 'greatmag'),
	           'priority'  => 10,
	        )
	    )
	);
	$wp_customize->add_setting(
	    'header_ad_url',
	    array(
	        'default' => '',
	        'sanitize_callback' => 'esc_url_raw',
	    )
	);
	$wp_customize->add_control(
	    'header_ad_url',
	    array(
	        'label' 	=> __( 'Header ad link', 'greatmag' ),
	        'section' 	=> 'greatmag_header_ad',
	        'type' 		=> 'url',
	        'priority' 	=> 10
	    )
	);
    /**
     * Footer
     */
    $wp_customize->add_panel( 'greatmag_footer_panel', array(
        'priority'       => 10,
        'capability'     => 'edit_theme_options',
        'theme_supports' => '',
        'title'          => __('Footer area', 'greatmag'),
    ) );

    //___Footer widget areas___//
    $wp_customize->add_section(
        'greatmag_footer',
        array(
            'title'         => __('Footer widgets', 'greatmag'),
            'priority'      => 11,
            'panel'         => 'greatmag_footer_panel'
        )
    );
    $wp_customize->add_setting(
        'footer_widget_areas',
        array(
            'default'           => '3',
            'sanitize_callback' => 'greatmag_sanitize_fwidgets',
        )
    );
    $wp_customize->add_control(
        'footer_widget_areas',
        array(
            'type'        => 'radio',
            'label'       => __('Footer widget areas', 'greatmag'),
            'section'     => 'greatmag_footer',
            'description' => __('Choose the number of widget areas in the footer, then go to Appearance > Widgets and add your widgets.', 'greatmag'),
            'choices' => array(
                '1'     => __('One', 'greatmag'),
                '2'     => __('Two', 'greatmag'),
                '3'     => __('Three', 'greatmag'),
            ),
        )
    );

    //___Editors choice___//
    $wp_customize->add_section(
        'greatmag_editors_choice',
        array(
            'title'         => __('Editor\'s choice', 'greatmag'),
            'priority'      => 12,
            'panel'         => 'greatmag_footer_panel'
        )
    );
	$wp_customize->add_setting(
		'footer_posts_title',
		array(
			'default'			=> __('Editor\'s choice', 'greatmag'),
			'sanitize_callback' => 'greatmag_sanitize_text',
		)
	);
	$wp_customize->add_control(
		'footer_posts_title',
		array(
			'label' 		=> __( 'Section title', 'greatmag' ),
			'section' 		=> 'greatmag_editors_choice',
			'type' 			=> 'text',
			'priority' 		=> 9
		)
	);
	$wp_customize->add_setting(
		'footer_posts_ids',
		array(
	       'sanitize_callback' => 'greatmag_sanitize_text',
		)
	);
	$wp_customize->add_control(
		'footer_posts_ids',
		array(
			'label' 		=> __( 'Posts to show', 'greatmag' ),
			'section' 		=> 'greatmag_editors_choice',
			'type' 			=> 'text',
			'description' 	=> __( 'Comma separated list of post IDs. Example: <strong>34,56,92</strong>', 'greatmag' ),
			'priority' 		=> 10
		)
	);



	/**
	 * Colors
	 */
    $wp_customize->add_panel( 'greatmag_colors_panel', array(
        'priority'       => 15,
        'capability'     => 'edit_theme_options',
        'theme_supports' => '',
        'title'          => __('Colors', 'greatmag'),
    ) );
    //___General___//
    //Primary
    $wp_customize->add_setting(
        'primary_color',
        array(
            'default'           => '#f8c200',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'primary_color',
            array(
                'label'         => __('Primary color', 'greatmag'),
                'section'       => 'colors',
                'priority'      => 9
            )
        )
    );
    $wp_customize->add_setting(
        'body_color',
        array(
            'default'           => '#666666',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'body_color',
            array(
                'label'         => __('Body color', 'greatmag'),
                'section'       => 'colors',
                'priority'      => 10
            )
        )
    );
    //___Category colors___//
    $wp_customize->add_section(
        'greatmag_cats_colors',
        array(
            'title'       => __( 'Category colors', 'greatmag' ),
            'description' => __( 'Select a color for any category', 'greatmag' ),
            'priority'    => 10,
            'panel'		  => 'greatmag_colors_panel',
            'capability'  => 'edit_theme_options',
        )
    );
    $args = array(
	   'hide_empty'               => 0,
	   'taxonomy'                 => 'category'
    );

    $categories = get_categories( $args );

    foreach( $categories as $category ){
        $wp_customize->add_setting( 'cats_color_' . $category->term_id,
            array(
                'default'           => '#908e8e',
                'sanitize_callback' => 'sanitize_hex_color',
            )
        );
        $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'cats_color_' . $category->term_id,
            array(
                'label'    => $category->name,
                'section'  => 'greatmag_cats_colors',
                'settings' => 'cats_color_' . $category->term_id,
            )
        ));

    }
    //___Header colors___//
    $wp_customize->add_section(
        'greatmag_header_colors',
        array(
            'title'       => __( 'Header', 'greatmag' ),
            'priority'    => 10,
            'panel'       => 'greatmag_colors_panel',
            'capability'  => 'edit_theme_options',
        )
    );
    //Site title
    $wp_customize->add_setting(
        'site_title',
        array(
            'default'           => '#000000',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'site_title',
            array(
                'label'         => __('Site title', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 13
            )
        )
    );
    //Site desc
    $wp_customize->add_setting(
        'site_description',
        array(
            'default'           => '#999999',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'site_description',
            array(
                'label'         => __('Site description', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 13
            )
        )
    );
    //Site branding
    $wp_customize->add_setting(
        'branding_bg',
        array(
            'default'           => '#eeeeee',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'branding_bg',
            array(
                'label'         => __('Site branding background', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 13
            )
        )
    );
    //Menu
    $wp_customize->add_setting(
        'menu_bg',
        array(
            'default'           => '#f6f6f6',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_bg',
            array(
                'label'         => __('Menu background', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 13
            )
        )
    );
    //Menu items
    $wp_customize->add_setting(
        'top_level_menu_items',
        array(
            'default'           => '#999999',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'top_level_menu_items',
            array(
                'label'         => __('Top level menu items', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 13
            )
        )
    );
    //Social
    $wp_customize->add_setting(
        'social_bar_bg',
        array(
            'default'           => '#222222',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'social_bar_bg',
            array(
                'label'         => __('Social&amp;latest news', 'greatmag'),
                'section'       => 'greatmag_header_colors',
                'priority'      => 14
            )
        )
    );
    //___Footer colors___//
    $wp_customize->add_section(
        'greatmag_footer_colors',
        array(
            'title'       => __( 'Footer', 'greatmag' ),
            'priority'    => 10,
            'panel'       => 'greatmag_colors_panel',
            'capability'  => 'edit_theme_options',
        )
    );
    //Footer widgets bg
    $wp_customize->add_setting(
        'fwidgets_bg',
        array(
            'default'           => '#222222',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'fwidgets_bg',
            array(
                'label'         => __('Footer widgets background', 'greatmag'),
                'section'       => 'greatmag_footer_colors',
                'priority'      => 10
            )
        )
    );
    //Footer widgets color
    $wp_customize->add_setting(
        'fwidgets_color',
        array(
            'default'           => '#bbbbbb',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'fwidgets_color',
            array(
                'label'         => __('Footer widgets color', 'greatmag'),
                'section'       => 'greatmag_footer_colors',
                'priority'      => 10
            )
        )
    );
    //Footer widget titles color
    $wp_customize->add_setting(
        'fwidgets_titles_color',
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'fwidgets_titles_color',
            array(
                'label'         => __('Footer widget titles color', 'greatmag'),
                'section'       => 'greatmag_footer_colors',
                'priority'      => 10
            )
        )
    );
    //Credits bg
    $wp_customize->add_setting(
        'siteinfo_bg',
        array(
            'default'           => '#191919',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'siteinfo_bg',
            array(
                'label'         => __('Footer credits background', 'greatmag'),
                'section'       => 'greatmag_footer_colors',
                'priority'      => 10
            )
        )
    );
    //Credits color
    $wp_customize->add_setting(
        'siteinfo_color',
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'siteinfo_color',
            array(
                'label'         => __('Footer credits color', 'greatmag'),
                'section'       => 'greatmag_footer_colors',
                'priority'      => 10
            )
        )
    );
    /**
     * Preloader
     */
    $wp_customize->add_section(
        'greatmag_preloader',
        array(
            'title'         => __('Preloader', 'greatmag'),
            'priority'      => 18,
        )
    );
    $wp_customize->add_setting(
      'disable_preloader',
      array(
        'sanitize_callback' => 'greatmag_sanitize_checkbox',
        'default' => 0,
      )
    );
    $wp_customize->add_control(
      'disable_preloader',
        array(
            'type' => 'checkbox',
            'label' => __('Disable the preloader?', 'greatmag'),
            'section' => 'greatmag_preloader',
            'priority' => 10,
        )
    );
    $wp_customize->add_setting(
        'preloader_text',
        array(
            'sanitize_callback' => 'greatmag_sanitize_text',
        )
    );
    $wp_customize->add_control(
        'preloader_text',
        array(
            'label'         => __( 'Preloader text', 'greatmag' ),
            'section'       => 'greatmag_preloader',
            'type'          => 'text',
            'priority'      => 11
        )
     );
    /**
     * Blog
     */
    $wp_customize->add_section(
        'greatmag_blog',
        array(
            'title'         => __('Blog options', 'greatmag'),
            'priority'      => 16,
        )
    );
    // Blog layout
    $wp_customize->add_setting(
        'blog_layout',
        array(
            'default'           => 'list',
            'sanitize_callback' => 'greatmag_sanitize_blog',
        )
    );
    $wp_customize->add_control(
        'blog_layout',
        array(
            'type'      => 'radio',
            'label'     => __('Blog layout', 'greatmag'),
            'section'   => 'greatmag_blog',
            'priority'  => 11,
            'choices'   => array(
                'list'              => __( 'List', 'greatmag' ),
                'classic'           => __( 'Classic', 'greatmag' ),
                'masonry'           => __( 'Masonry (grid style)', 'greatmag' ),
                'masonry-full'      => __( 'Masonry full width', 'greatmag' )
            ),
        )
    );
		// Search layout
		$wp_customize->add_setting(
        'search_layout',
        array(
            'default'           => 'list',
            'sanitize_callback' => 'greatmag_sanitize_blog',
        )
    );
    $wp_customize->add_control(
        'search_layout',
        array(
            'type'      => 'radio',
            'label'     => __('Search result layout', 'greatmag'),
            'section'   => 'greatmag_blog',
            'priority'  => 12,
            'choices'   => array(
                'list'              => __( 'List', 'greatmag' ),
                'classic'           => __( 'Classic', 'greatmag' ),
                'masonry'           => __( 'Masonry (grid style)', 'greatmag' ),
                'masonry-full'      => __( 'Masonry full width', 'greatmag' )
            ),
        )
    );
    //Excerpt
    $wp_customize->add_setting(
        'exc_length',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '30',
        )
    );
    $wp_customize->add_control( 'exc_length', array(
        'type'        => 'number',
        'priority'    => 13,
        'section'     => 'greatmag_blog',
        'label'       => __('Excerpt length', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 200,
            'step'  => 5,
        ),
    ) );
    $wp_customize->add_setting(
        'custom_read_more',
        array(
            'sanitize_callback' => 'greatmag_sanitize_text',
        )
    );
    $wp_customize->add_control(
        'custom_read_more',
        array(
            'label'         => __( 'Read more text', 'greatmag' ),
            'description'   => __( 'Fill this field to replace the [&hellip;] with a link', 'greatmag' ),
            'section'       => 'greatmag_blog',
            'type'          => 'text',
            'priority'      => 14
        )
     );

    //Meta
    $wp_customize->add_setting(
      'hide_meta_singles',
      array(
        'sanitize_callback' => 'greatmag_sanitize_checkbox',
        'default' => 0,
      )
    );
    $wp_customize->add_control(
      'hide_meta_singles',
      array(
        'type' => 'checkbox',
        'label' => __('Hide meta on single posts?', 'greatmag'),
        'section' => 'greatmag_blog',
        'priority' => 15,
      )
    );
    $wp_customize->add_setting(
      'hide_meta_index',
      array(
        'sanitize_callback' => 'greatmag_sanitize_checkbox',
        'default' => 0,
      )
    );
    $wp_customize->add_control(
      'hide_meta_index',
      array(
        'type' => 'checkbox',
        'label' => __('Hide meta on blog index?', 'greatmag'),
        'section' => 'greatmag_blog',
        'priority' => 16,
      )
    );
    //Featured images
    $wp_customize->add_setting(
        'hide_featured_singles',
        array(
            'sanitize_callback' => 'greatmag_sanitize_checkbox',
        )
    );
    $wp_customize->add_control(
        'hide_featured_singles',
        array(
            'type' => 'checkbox',
            'label' => __('Hide featured images on single posts?', 'greatmag'),
            'section' => 'greatmag_blog',
            'priority' => 17,
        )
    );

    /**
     * Custom sidebars
     */
    $wp_customize->add_section(
        'greatmag_custom_sidebars',
        array(
            'title'         => __('Custom sidebars', 'greatmag'),
            'description'   => __('You can create sidebars here for use with the <strong>GreatMag: Sidebar</strong> widget. Create your sidebars then go to Appearance > Widgets and add widgets to them.', 'greatmag'),
            'priority'      => 21,
        )
    );
    $wp_customize->add_setting(
        'custom_sidebars',
        array(
            'default' => '',
            'sanitize_callback' => 'greatmag_sanitize_text',
        )
    );
    $wp_customize->add_control(
        'custom_sidebars',
        array(
            'label'         => __( 'Custom sidebars', 'greatmag' ),
            'description'   => __('Comma separated list of sidebar names (example: <strong>main,home,mycoolsidebar</strong>)', 'greatmag'),
            'section'       => 'greatmag_custom_sidebars',
            'type'          => 'text',
            'priority'      => 10
        )
    );

    /**
     * Fonts
     */
    $wp_customize->add_panel( 'greatmag_typography_panel', array(
        'priority'       => 17,
        'capability'     => 'edit_theme_options',
        'theme_supports' => '',
        'title'          => __('Fonts', 'greatmag'),
    ) );
    $wp_customize->add_section(
        'greatmag_fonts',
        array(
            'title'     => __('Font selection', 'greatmag'),
            'priority'  => 10,
            'panel'     => 'greatmag_typography_panel',
            'description' => sprintf( '%1$s<a target="_blank" href="//fonts.google.com">%2$s</a>&#46;&nbsp;%3$s<a target="_blank" href="//athemes.com/documentation/greatmag">%4$s</a>',
                _x( 'Find the fonts ', 'Fonts option description', 'greatmag' ),
                _x( 'here', 'Fonts option description', 'greatmag' ),
                _x( 'If you need help, check the ', 'Fonts option description', 'greatmag' ),
                _x( 'documentation', 'Fonts option description', 'greatmag' )
            )
        )
    );
    //Body fonts family
    $wp_customize->add_setting(
        'body_font_family',
        array(
            'sanitize_callback' => 'greatmag_sanitize_text',
            'default' => 'Open Sans',
        )
    );
    $wp_customize->add_control(
        'body_font_family',
        array(
            'label' => __( 'Body font', 'greatmag' ),
            'section' => 'greatmag_fonts',
            'type' => 'text',
            'priority' => 12
        )
    );
    //Headings fonts family
    $wp_customize->add_setting(
        'headings_font_family',
        array(
            'sanitize_callback' => 'greatmag_sanitize_text',
            'default' => 'Lato',
        )
    );
    $wp_customize->add_control(
        'headings_font_family',
        array(
            'label' => __( 'Headings font', 'greatmag' ),
            'section' => 'greatmag_fonts',
            'type' => 'text',
            'priority' => 15
        )
    );

    $wp_customize->add_setting(
        'font_weights',
        array(
            'default'           => array( '400', '400italic', '600', '600italic' ),
            'sanitize_callback' => 'greatmag_sanitize_font_weights'
        )
    );
    $wp_customize->add_control(
        new GreatMag_Multiselect_Control(
            $wp_customize,
            'font_weights',
            array(
                'section' => 'greatmag_fonts',
                'label'   => __( 'Font weights to load', 'greatmag' ),
                'choices' => array(
                    '300'           => __( '300', 'greatmag' ),
                    '300italic'     => __( '300 italic',     'greatmag' ),
                    '400'           => __( '400',       'greatmag' ),
                    '400italic'     => __( '400 italic',     'greatmag' ),
                    '500'           => __( '500', 'greatmag' ),
                    '500italic'     => __( '500 italic', 'greatmag' ),
                    '600'           => __( '600', 'greatmag' ),
                    '600italic'     => __( '600 italic', 'greatmag' )
                ),
                'priority' => 16
            )
        )
    );

    $wp_customize->add_section(
        'greatmag_typography',
        array(
            'title'     => __('Typography', 'greatmag'),
            'priority'  => 11,
            'panel'     => 'greatmag_typography_panel',
        )
    );
    // Site title
    $wp_customize->add_setting(
        'site_title_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '24',
        )
    );
    $wp_customize->add_control( 'site_title_size', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Site title', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );
    // Site desc
    $wp_customize->add_setting(
        'site_desc_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '16',
        )
    );
    $wp_customize->add_control( 'site_desc_size', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Site description', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );
    // Menu items
    $wp_customize->add_setting(
        'menu_items',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '13',
        )
    );
    $wp_customize->add_control( 'menu_items', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Menu items', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 40,
            'step'  => 1,
        ),
    ) );
    // Body
    $wp_customize->add_setting(
        'body_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '14',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control( 'body_size', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Body (sitewide)', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );
    // Index post titles
    $wp_customize->add_setting(
        'index_post_title',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '16',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control( 'index_post_title', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Index post titles', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );
    // Single post titles
    $wp_customize->add_setting(
        'single_post_title',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '24',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control( 'single_post_title', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Single post titles', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );
    // Sidebar widget titles
    $wp_customize->add_setting(
        'sidebar_widgets_title',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '12',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control( 'sidebar_widgets_title', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'greatmag_typography',
        'label'       => __('Sidebar widget titles', 'greatmag'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 80,
            'step'  => 1,
        ),
    ) );

}
add_action( 'customize_register', 'greatmag_customize_register' );


/**
 * Sanitize
 */
//Text
function greatmag_sanitize_text( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}
//Checkboxes
function greatmag_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return '';
    }
}
//Branding style
function greatmag_sanitize_branding( $input ) {
    if ( in_array( $input, array( 'left', 'centered' ), true ) ) {
        return $input;
    }
}
//Menu position
function greatmag_sanitize_menu( $input ) {
    if ( in_array( $input, array( 'before-branding', 'after-branding' ), true ) ) {
        return $input;
    }
}
//Footer widget areas
function greatmag_sanitize_fwidgets( $input ) {
    if ( in_array( $input, array( '1', '2', '3' ), true ) ) {
        return $input;
    }
}
//Blog layout
function greatmag_sanitize_blog( $input ) {
    if ( in_array( $input, array( 'list', 'classic', 'masonry', 'masonry-full' ), true ) ) {
        return $input;
    }
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function greatmag_customize_preview_js() {
	wp_enqueue_script( 'greatmag_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', 'greatmag_customize_preview_js' );

/**
 * Load custom controls
 */
function greatmag_load_customize_controls() {

    require_once( trailingslashit( get_template_directory() ) . 'inc/controls/control-multicheckbox.php' );
}
add_action( 'customize_register', 'greatmag_load_customize_controls', 0 );
