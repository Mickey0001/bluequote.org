<?php
/**
 * @package Greatmag
 */


function greatmag_custom_styles($custom) {


	//Colors
	$site_title 			= get_theme_mod( 'site_title', '#000000' );
	$site_desc 				= get_theme_mod( 'site_description', '#999999' );
	$branding_bg    		= get_theme_mod( 'branding_bg', '#eeeeee' );
	$menu_bg    			= get_theme_mod( 'menu_bg', '#f6f6f6' );
	$tl_menu_items  		= get_theme_mod( 'top_level_menu_items', '#999999' );
	$social_bg				= get_theme_mod( 'social_bar_bg', '#222222' );
	$primary_color  		= get_theme_mod( 'primary_color', '#f8c200');
	$fwidgets_bg  			= get_theme_mod( 'fwidgets_bg', '#222222' );
	$fwidgets_color  		= get_theme_mod( 'fwidgets_color', '#bbbbbb');
	$fwidgets_titles_color  = get_theme_mod( 'fwidgets_titles_color', '#ffffff' );
	$siteinfo_bg  			= get_theme_mod( 'siteinfo_bg', '#191919' );
	$siteinfo_color 		= get_theme_mod( 'siteinfo_color', '#ffffff' );
	$body_color				= get_theme_mod( 'body_color', '#666666' );
    $footer_image   		= get_theme_mod( 'footer_background_img' );
	$headings_font 			= get_theme_mod('headings_font_family', 'Lato');
	$body_font 				= get_theme_mod('body_font_family', 'Open Sans');
    $site_title_size 		= get_theme_mod( 'site_title_size', '24' );
    $site_desc_size 		= get_theme_mod( 'site_desc_size', '16' );
    $menu_items 			= get_theme_mod( 'menu_items', '13' );
    $body_size 				= get_theme_mod( 'body_size', '14' );
    $index_post_title 		= get_theme_mod( 'index_post_title', '16' );
    $single_post_title 		= get_theme_mod( 'single_post_title', '24' );
    $sidebar_widgets_title 	= get_theme_mod( 'sidebar_widgets_title', '12' );

	//Header
	$branding_style = get_theme_mod('branding_style', 'left');

	//Build CSS
	$custom 	= '';

	$custom .= ".site-title,.site-title a,.site-title a:hover { color:" . esc_attr($site_title) . "}"."\n";
	$custom .= ".site-description { color:" . esc_attr($site_desc) . "}"."\n";
	$custom .= ".site-branding { background-color:" . esc_attr($branding_bg) . "}"."\n";
	$custom .= ".navbar.bgf6 { background-color:" . esc_attr($menu_bg) . "}"."\n";
	$custom .= ".top-header { background-color:" . esc_attr($social_bg) . "}"."\n";
	$custom .= ".navbar .navbar-nav > li > a { color:" . esc_attr($tl_menu_items) . "}"."\n";
	$custom .= ".preloader,.progress-bar,.comment-form .btn:hover, .comment-form .btn:focus,.contact-form .btn,.back-to-page:hover, .back-to-page:focus,.ready-to-contact .btn,.dc2:first-letter,.list-style1 li:before,.navbar .navbar-nav > li .dropdown-menu > li .absp-cat:hover, .navbar .navbar-nav > li .dropdown-menu > li .absp-cat:focus,.absp-cat:hover, .absp-cat:focus,.btn-primary:hover, .btn-primary:focus,.button:hover,button:hover,input[type=\"button\"]:hover,input[type=\"reset\"]:hover,input[type=\"submit\"]:hover { background-color:" . esc_attr($primary_color) . "}"."\n";
	$custom .= "a:hover,a:focus,.nav>li>a:hover, .nav>li>a:focus,.sidebar-area .widget a:hover,.ps-quote:before,.author-posts-link,.fun-fact .this-icon,.dc1:first-letter,.list-style3 li:before,.list-style2 li:before,.pbc-carousel .owl-prev:hover, .pbc-carousel .owl-prev:focus, .pbc-carousel .owl-next:hover, .pbc-carousel .owl-next:focus, .pbc-carousel2 .owl-prev:hover, .pbc-carousel2 .owl-prev:focus, .pbc-carousel2 .owl-next:hover, .pbc-carousel2 .owl-next:focus, .video-posts-carousel .owl-prev:hover, .video-posts-carousel .owl-prev:focus, .video-posts-carousel .owl-next:hover, .video-posts-carousel .owl-next:focus,.post-title-small:hover, .post-title-small:focus,.post-title-standard:hover, .post-title-standard:focus,.go-top:hover, .go-top:focus,.mob-social-menu li a:hover, .mob-social-menu li a:focus,.off-close,.navbar .navbar-nav > li .dropdown-menu > li .this-title a:hover, .navbar .navbar-nav > li .dropdown-menu > li .this-title a:focus,.section-title .this-title span,.breaking-news.media a:hover, .breaking-news.media a:focus, .review-stars li { color:" . esc_attr($primary_color) . "}"."\n";	
	$custom .= ".comment-form .btn:hover, .comment-form .btn:focus,.fun-fact .this-icon,.login-drop { border-color:" . esc_attr($primary_color) . "}"."\n";

	$custom .= ".footer-widgets { background-color:" . esc_attr($fwidgets_bg) . "}"."\n";
	$custom .= ".footer-widgets, .footer-widgets a:not(:hover) { color:" . esc_attr($fwidgets_color) . "}"."\n";
	$custom .= ".footer-widgets .widget-title { color:" . esc_attr($fwidgets_titles_color) . "}"."\n";
	$custom .= ".bottom-footer { background-color:" . esc_attr($siteinfo_bg) . "}"."\n";
	$custom .= ".site-info, .site-info a:not(:hover) { color:" . esc_attr($siteinfo_color) . "}"."\n";
	$custom .= "body, .sidebar-area .widget, .sidebar-area .widget a, .sidebar-area .widget select { color:" . esc_attr($body_color) . "}"."\n";
	$custom .= "body { font-family:" . esc_attr(ucwords(strtolower($body_font))) . ";}"."\n";
	$custom .= "h1,h2,h3,h4,h5,h6,.site-title,.post-title-standard,.post-title-small,.post-title-big { font-family:" . esc_attr(ucwords(strtolower($headings_font))) . ";}"."\n";
    $custom .= ".site-title { font-size:" . intval($site_title_size) . "px; }"."\n";
    $custom .= ".site-description { font-size:" . intval($site_desc_size) . "px; }"."\n";
    $custom .= "body { font-size:" . intval($body_size) . "px; }"."\n";
    $custom .= ".navbar .navbar-nav > li > a { font-size:" . intval($menu_items) . "px; }"."\n";
    $custom .= ".post-title-standard { font-size:" . intval($index_post_title) . "px; }"."\n";
    $custom .= ".entry-title.post-title-big { font-size:" . intval($single_post_title) . "px; }"."\n";
    $custom .= ".widget-area .widget-title, .footer-widgets .widget-title { font-size:" . intval($sidebar_widgets_title) . "px; }"."\n";

	if ( $branding_style == 'centered' ) {
		$custom 	.= ".site-branding.vhome3 .main-logo { float:none;margin:0 auto; }"."\n";
		$custom 	.= ".site-branding .header-ad { float:none;margin:30px auto 0; }"."\n";
	} 

    if ($footer_image) {
        $custom .= ".footer-widgets { background:url(" . esc_url($footer_image) . ") no-repeat center;background-size:cover;}"."\n";
        $custom .= ".footer-widgets::after { width:100%;height:100%;opacity:0.7;position:absolute;content:'';z-index:-1;top:0;left:0;background-color:" . esc_attr($fwidgets_bg) . "}"."\n";   
    }

	//Output all the styles
	wp_add_inline_style( 'greatmag-style', $custom );	

}
add_action( 'wp_enqueue_scripts', 'greatmag_custom_styles' );