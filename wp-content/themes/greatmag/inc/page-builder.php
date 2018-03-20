<?php
/**
 * Page builder support
 *
 * @package GreatMag
 */


/**
 * Page builder defaults
 */
add_theme_support( 'siteorigin-panels', array( 
	'margin-bottom' 		=> 0,
	'recommended-widgets' 	=> false,
	'mobile-layout' => true,
	'mobile-width' => 991,
) );

/**
 * Register theme widgets in the page builder
 */
function greatmag_theme_widgets($widgets) {
	$theme_widgets = array(
		'Athemes_Posts_Carousel',
		'Athemes_Featured_Posts_Type_A',
		'Athemes_Single_Category_Posts_A',
		'Athemes_Sidebar_Widget',
		'Athemes_Multiple_Cats_Posts',
		'Athemes_Latest_Posts_Home',
		'Athemes_Most_Popular_Home',
		'Athemes_Video_Posts',
	);
	foreach($theme_widgets as $theme_widget) {
		if( isset( $widgets[$theme_widget] ) ) {
			$widgets[$theme_widget]['groups'] = array('greatmag-theme');
			$widgets[$theme_widget]['icon'] = 'dashicons dashicons-schedule greatmag-builder-color';
		}
	}
	return $widgets;
}
add_filter('siteorigin_panels_widgets', 'greatmag_theme_widgets');

/**
 * Create theme tab in the page builder
 */
function greatmag_theme_widgets_tab($tabs){
	$tabs[] = array(
		'title' => __('GreatMag Widgets', 'greatmag'),
		'filter' => array(
			'groups' => array('greatmag-theme')
		)
	);
	return $tabs;
}
add_filter('siteorigin_panels_widget_dialog_tabs', 'greatmag_theme_widgets_tab', 20);

/**
 * Page builder row options
 */
function greatmag_custom_row_style_fields($fields) {
	$fields['top-padding'] = array(
	    'name'        => __('Top padding', 'greatmag'),
	    'type'        => 'measurement',
	    'group'       => 'layout',
	    'default'		=> '30px',
	    'description' => __('Top padding for this row.', 'greatmag'),
	    'priority'    => 11,
	);
	$fields['bottom-padding'] = array(
	    'name'        => __('Bottom padding', 'greatmag'),
	    'type'        => 'measurement',
	    'group'       => 'layout',
	    'default'		=> '30px',
	    'description' => __('Bottom padding for this row.', 'greatmag'),
	    'priority'    => 12,
	);

  return $fields;
}
add_filter( 'siteorigin_panels_row_style_fields', 'greatmag_custom_row_style_fields');


/**
 * Output page builder row options
 */
function greatmag_custom_row_style_attributes( $attributes, $args ) {

	if ( !empty($args['top-padding']) ) {
		$attributes['style'] .= 'padding-top: ' . esc_attr($args['top-padding']) . '; ';
	} else {
		$attributes['style'] .= 'padding-top: 30px; ';
	}
	if ( !empty($args['bottom-padding']) ) {
		$attributes['style'] .= 'padding-bottom: ' . esc_attr($args['bottom-padding']) . '; ';
	} else {
		$attributes['style'] .= 'padding-bottom: 30px; ';
	}
    return $attributes;
}
add_filter('siteorigin_panels_row_style_attributes', 'greatmag_custom_row_style_attributes', 10, 2);

/**
 * Remove defaults
 */
function greatmag_remove_default_so_row_styles( $fields ) {
	unset( $fields['padding'] );
	return $fields;
}
add_filter('siteorigin_panels_row_style_fields', 'greatmag_remove_default_so_row_styles' );
add_filter( 'siteorigin_premium_upgrade_teaser', '__return_false' );