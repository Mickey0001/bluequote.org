<?php
/**
 * Sample implementation of the Custom Header feature.
 *
 * You can add an optional custom header image to header.php like so ...
 *
	<?php if ( get_header_image() ) : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="">
	</a>
	<?php endif; // End header image check. ?>
 *
 * @link https://developer.wordpress.org/themes/functionality/custom-headers/
 *
 * @package GreatMag
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses greatmag_header_style()
 */
function greatmag_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'greatmag_custom_header_args', array(
		'default-image'          => '',
		'default-text-color'     => '000000',
		'width'                  => 1920,
		'height'                 => 600,
		'flex-height'            => true,
		'wp-head-callback'       => '',
		'header-text'            => false,
	) ) );
}
add_action( 'after_setup_theme', 'greatmag_custom_header_setup' );