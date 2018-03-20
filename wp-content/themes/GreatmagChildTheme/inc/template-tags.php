<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package GreatMag
 */

if ( ! function_exists( 'greatmag_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function greatmag_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'greatmag' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author vcard">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . $time_string . '</a></h5>';

	echo $meta;
}
endif;

if ( ! function_exists( 'greatmag_entry_footer' ) ) :
/**
 * Prints HTML with meta information for the categories, tags and comments.
 */
function greatmag_entry_footer() {
	// Hide category and tag text for pages.
	if ( 'post' === get_post_type() ) {
		/* translators: used between list items, there is a space after the comma */
		$tags_list = get_the_tag_list( '', esc_html__( ', ', 'greatmag' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'greatmag' ) . '</span>', $tags_list ); // WPCS: XSS OK.
		}
	}

	if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		/* translators: %s: post title */
		comments_popup_link( sprintf( wp_kses( __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'greatmag' ), array( 'span' => array( 'class' => array() ) ) ), get_the_title() ) );
		echo '</span>';
	}

	edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			esc_html__( 'Edit %s', 'greatmag' ),
			the_title( '<span class="screen-reader-text">"', '"</span>', false )
		),
		'<span class="edit-link">',
		'</span>'
	);
}
endif;

/**
 * Get post categories
 */
function greatmag_get_post_cats( $first_cat = false, $featured = false ) {
	if ( 'post' === get_post_type() ) {
		$cats = get_the_category();

		if ( $first_cat == true ) {
			$cat_color = get_theme_mod( 'cats_color_' . $cats[0]->term_id, '#908e8e' );
			if ( $featured != true ) {
				echo '<a class="absp-cat" data-color="' . $cat_color . '" style="background-color:' . $cat_color . ';" href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '" title="' . esc_attr( $cats[0]->name ) . '">' . esc_html( $cats[0]->name ) . '</a>';
			} else {
				echo '<a  data-color="' . esc_attr( $cat_color ) . '" style="color:' . esc_attr( $cat_color ) . ';" href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '" title="' . esc_attr( $cats[0]->name ) . '">' . esc_html( $cats[0]->name ) . '</a>';				
			}
		} else {
			foreach ( $cats as $cat ) {
				$cat_color = get_theme_mod( 'cats_color_' . $cat->term_id, '#908e8e' );
				echo '<a class="absp-cat prltv" style="background-color:' . esc_attr( $cat_color ) . ';" href="' . esc_url( get_category_link( $cat->term_id ) ) . '" title="' . esc_attr( $cat->name ) . '">' . esc_html( $cat->name ) . '</a>';      
			}
		}
	}
}

/**
 * Get category color
 */
function greatmag_get_category_color( $cat_id = false ) {
	if ( $cat_id ) {
		$cat = $cat_id;
	} else {
		$cats = get_the_category();
		$cat = $cats[0]->term_id;
	}
	$color = get_theme_mod( 'cats_color_' . $cat, '#908e8e' );

	return $color;
}

/**
 * Get first category
 */
function greatmag_get_first_cat() {
	if ( 'post' === get_post_type() ) {
		$cats = get_the_category();
		echo '<a href="' . esc_url( get_category_link( $cats[0]->term_id ) ) . '" title="' . esc_attr( $cats[0]->name ) . '" class="post-cat">' . esc_html( $cats[0]->name ) . '</a>';
	}
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function greatmag_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'greatmag_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'greatmag_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so greatmag_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so greatmag_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in greatmag_categorized_blog.
 */
function greatmag_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'greatmag_categories' );
}
add_action( 'edit_category', 'greatmag_category_transient_flusher' );
add_action( 'save_post',     'greatmag_category_transient_flusher' );
