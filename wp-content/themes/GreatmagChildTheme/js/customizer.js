/**
 * File customizer.js.
 *
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {

	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );

	wp.customize( 'site_title', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).css('color', to );
		} );
	} );
	wp.customize( 'site_description', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).css('color', to );
		} );
	} );
	wp.customize( 'menu_bg', function( value ) {
		value.bind( function( to ) {
			$( '.navbar.bgf6' ).css('background-color', to );
		} );
	} );
	wp.customize( 'social_bar_bg', function( value ) {
		value.bind( function( to ) {
			$( '.top-header' ).css('background-color', to );
		} );
	} );
	wp.customize( 'top_level_menu_items', function( value ) {
		value.bind( function( to ) {
			$( '.navbar .navbar-nav > li > a' ).css('color', to );
		} );
	} );
	wp.customize( 'branding_bg', function( value ) {
		value.bind( function( to ) {
			$( '.site-branding' ).css('background-color', to );
		} );
	} );

	wp.customize( 'fwidgets_bg', function( value ) {
		value.bind( function( to ) {
			$( '.footer-widgets' ).css('background-color', to );
		} );
	} );
	wp.customize( 'fwidgets_color', function( value ) {
		value.bind( function( to ) {
			$( '.footer-widgets, .footer-widgets a' ).css('color', to );
		} );
	} );
	wp.customize( 'fwidgets_titles_color', function( value ) {
		value.bind( function( to ) {
			$( '.footer-widgets .widget-title' ).css('color', to );
		} );
	} );
	wp.customize( 'siteinfo_bg', function( value ) {
		value.bind( function( to ) {
			$( '.bottom-footer' ).css('background-color', to );
		} );
	} );
	wp.customize( 'siteinfo_color', function( value ) {
		value.bind( function( to ) {
			$( '.site-info, .site-info a' ).css('color', to );
		} );
	} );
	wp.customize( 'body_color', function( value ) {
		value.bind( function( to ) {
			$( 'body, .sidebar-area .widget, .sidebar-area .widget a, .sidebar-area .widget select' ).css('color', to );
		} );
	} );

} )( jQuery );
