<?php
/**
 * GreatMag functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package GreatMag
 */

if ( ! function_exists( 'greatmag_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function greatmag_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on GreatMag, use a find and replace
	 * to change 'greatmag' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'greatmag', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'greatmag-extra-small', 100, 70, true );
	add_image_size( 'greatmag-small', 270, 180, true );
	add_image_size( 'greatmag-medium', 380, 250, true );
	add_image_size( 'greatmag-featured-a', 1200, 850, true );
	add_image_size( 'greatmag-featured-b', 790, 535, true );
	add_image_size( 'greatmag-featured-c', 600, 425, true );
	add_image_size( 'greatmag-single', 710 );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'greatmag' ),
		'footer'  => esc_html__( 'Footer', 'greatmag' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'greatmag_custom_background_args', array(
		'default-color' => 'eeeeee',
		'default-image' => '',
	) ) );

	//Logo support
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 200,
		'flex-height' => true,
	) );

	//Video post format support
	add_theme_support( 'post-formats', array( 'video' ) );

}
endif;
add_action( 'after_setup_theme', 'greatmag_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function greatmag_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'greatmag_content_width', 1170 );
}
add_action( 'after_setup_theme', 'greatmag_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function greatmag_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Right Sidebar', 'greatmag' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'greatmag' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar(array(
		'name' => 'Left Sidebar',
		'id' => 'sidebar-2',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
));

	//Custom sidebars
	// $sidebars = get_theme_mod('custom_sidebars');

	// if ( $sidebars != '' ) {

	// 	$sidebars = explode(',', $sidebars);

	// 	foreach ( $sidebars as $sidebar ) {
	// 		register_sidebar( array(
	// 			'name'          => ucfirst( esc_html($sidebar) ),
	// 			'id'            => $sidebar,
	// 			'description'   => esc_html__( 'Add widgets here.', 'greatmag' ),
	// 			'before_widget' => '<section id="%1$s" class="widget %2$s">',
	// 			'after_widget'  => '</section>',
	// 			'before_title'  => '<h3 class="widget-title">',
	// 			'after_title'   => '</h3>',
	// 		) );
	// 	}
	// }

	//Footer widget areas
	$widget_areas = get_theme_mod('footer_widget_areas', '3');
	for ($i=1; $i<=$widget_areas; $i++) {
		register_sidebar( array(
			'name'          => __( 'Footer ', 'greatmag' ) . $i,
			'id'            => 'footer-' . $i,
			'description'   => '',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}

	//Register widgets
	register_widget( 'Athemes_Posts_Carousel' );
	register_widget( 'Athemes_Featured_Posts_Type_A' );
	register_widget( 'Athemes_Single_Category_Posts_A' );
	register_widget( 'Athemes_Sidebar_Widget' );
	register_widget( 'Athemes_Multiple_Cats_Posts' );
	register_widget( 'Athemes_Latest_Posts_Home' );
	register_widget( 'Athemes_Most_Popular_Home' );
	register_widget( 'Athemes_Most_Popular' );
	register_widget( 'Athemes_Video_Posts' );
}
add_action( 'widgets_init', 'greatmag_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function greatmag_scripts() {
	wp_enqueue_style( 'greatmag-style', get_stylesheet_uri() );

	wp_enqueue_style( 'greatmag-fonts', esc_url( greatmag_fonts_url() ), array(), null );

	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/fonts/font-awesome.min.css' );

	wp_enqueue_script( 'greatmag-scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery', 'imagesloaded'), '', true );

	wp_enqueue_script( 'greatmag-main', get_template_directory_uri() . '/js/main.min.js', array('jquery'), '', true );

	wp_enqueue_script( 'greatmag-html5shiv', get_template_directory_uri() . '/js/html5shiv.js', array(), '', true );
    wp_script_add_data( 'greatmag-html5shiv', 'conditional', 'lt IE 9' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'greatmag_scripts' );

/**
 * Enqueue Bootstrap
 */
function greatmag_enqueue_bootstrap() {
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css', array(), true );
}
add_action( 'wp_enqueue_scripts', 'greatmag_enqueue_bootstrap', 9 );

/**
 * Google Fonts - adapted from TwentySixteen
 *
 * Lets you load Google Fonts by adding only the name
 */
if ( ! function_exists( 'greatmag_fonts_url' ) ) :
function greatmag_fonts_url() {

	$fonts_url 		= '';
	$subsets   		= 'latin,latin-ext,cyrillic'; //Fallback for browsers with no unicode-range support
	$weights 		= get_theme_mod('font_weights', array( '400', '400italic', '600', '600italic' ));
	$weights 		= implode(',', $weights);
	$body_font 		= get_theme_mod('body_font_family', 'Open Sans');
	$headings_font 	= get_theme_mod('headings_font_family', 'Lato');
	$fonts     		= array();
	$fonts[] 		= esc_attr($body_font) . ':' . esc_attr($weights);
	$fonts[] 		= esc_attr($headings_font) . ':' . esc_attr($weights);

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;
}
endif;

/**
 * Scripts and styles for the Page Builder plugin
 */
function greatmag_load_pagebuilder_scripts() {
	wp_enqueue_script( 'greatmag-chosen', get_template_directory_uri() . '/js/chosen.jquery.min.js', array('jquery', 'jquery-ui-sortable'), '', true );

	wp_enqueue_script( 'greatmag-chosen-init', get_template_directory_uri() . '/js/chosen-init.js', array('jquery'), '', true );

	wp_enqueue_style( 'greatmag-chosen-styles', get_template_directory_uri() . '/css/chosen.min.css', array(), true );
}
add_action( 'siteorigin_panel_enqueue_admin_scripts', 'greatmag_load_pagebuilder_scripts' );

/**
 * Widgets
 */
require get_template_directory() . "/widgets/widget-posts-carousel.php";
require get_template_directory() . "/widgets/widget-featured-posts-a.php";
require get_template_directory() . "/widgets/widget-single-category-posts-a.php";
require get_template_directory() . "/widgets/widget-sidebar.php";
require get_template_directory() . "/widgets/widget-multiple-cats-posts.php";
require get_template_directory() . "/widgets/widget-latest-posts-home.php";
require get_template_directory() . "/widgets/widget-most-popular.php";
require get_template_directory() . "/widgets/widget-most-popular-home.php";
require get_template_directory() . "/widgets/widget-video-posts.php";

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Menu walker
 */
if ( !class_exists( 'wp_bootstrap_navwalker' ) ) {
	require get_template_directory() . '/inc/nav_walker.php';
}

/**
 * Functions
 */
require get_template_directory() . '/inc/functions/loader.php';

/**
 * Page Builder support
 */
require get_template_directory() . '/inc/page-builder.php';

/**
 * Custom styles
 */
require get_template_directory() . '/inc/styles.php';

/**
 * Demo content
 */
require_once dirname( __FILE__ ) . '/demo-content/setup.php';

/**
 *TGM Plugin activation.
 */
require_once dirname( __FILE__ ) . '/inc/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'greatmag_recommend_plugin' );
function greatmag_recommend_plugin() {

    $plugins[] = array(
            'name'               => 'Page Builder by SiteOrigin',
            'slug'               => 'siteorigin-panels',
            'required'           => false,
    );

    tgmpa( $plugins);

}


//Sidebar experimemt

