<?php
/**
 * @package GreatMag
 */


/**
 * Posts layout
 */
function greatmag_post_classes( $classes ) {

	$layout = get_theme_mod( 'blog_layout', 'list' );

	$slmod = get_theme_mod( 'search_layout' );
	$slayout = get_theme_mod( 'search_layout', 'list' );

	if ( !is_single() && !is_page() && !is_search() ) {
		if ( $layout == 'list' ) {
			$classes[] = 'list-style-post';
		} elseif ( $layout == 'classic' ) {
			$classes[] = 'list-bigimage-post';
		} elseif ( $layout == 'masonry' ) {
			$classes[] = 'col-sm-6';
		} elseif ( $layout == 'masonry-full' ) {
			$classes[] = 'col-sm-6 col-md-4';
		}
	}

	if( isset($slmod) && $slmod != false ) {
		if ( is_search() ) {
			if ( $slayout == 'list' ) {
				$classes[] = 'list-style-post';
			} elseif ( $slayout == 'classic' ) {
				$classes[] = 'list-bigimage-post';
			} elseif ( $slayout == 'masonry' ) {
				$classes[] = 'col-sm-6';
			} elseif ( $slayout == 'masonry-full' ) {
				$classes[] = 'col-sm-6 col-md-4';
			}
	  }
	} else {
		if ( is_search() ) {
			if ( $layout == 'list' ) {
				$classes[] = 'list-style-post';
			} elseif ( $layout == 'classic' ) {
				$classes[] = 'list-bigimage-post';
			} elseif ( $layout == 'masonry' ) {
				$classes[] = 'col-sm-6';
			} elseif ( $layout == 'masonry-full' ) {
				$classes[] = 'col-sm-6 col-md-4';
			}
	  }
	}

  return $classes;
}
add_filter('post_class', 'greatmag_post_classes');

/**
 * Excerpt length
 */
function greatmag_excerpt_length( $length ) {

	if ( is_admin() ) {
		return $length;
	}

	$excerpt = get_theme_mod('exc_length', '30');
	return intval( $excerpt );
}
add_filter( 'excerpt_length', 'greatmag_excerpt_length', 999 );

/**
 * Excerpt read more
 */
function greatmag_custom_excerpt( $more ) {
	if ( is_admin() ) {
		return $more;
	}

	$more = get_theme_mod('custom_read_more');
	if ($more == '') {
		return '&nbsp;[ &hellip; ]';
	} else {
		return ' <a class="read-more" href="' . esc_url( get_permalink( get_the_ID() ) ) . '">' . esc_html($more) . '</a>';
	}
}
add_filter( 'excerpt_more', 'greatmag_custom_excerpt' );

/**
 * Single comment template
 */
function greatmag_comment_template($comment, $args, $depth) {

	?>
	<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $comment->has_children ? 'parent' : '' ); ?>>
		<article id="div-comment-<?php comment_ID(); ?>" class="comment-body media">
				<div class="media-left vcard">
					<?php echo get_avatar( $comment, $args['avatar_size'] ); ?>
				</div>

				<div class="media-body">
					<h5 class="comment-info">
						<?php printf( '<b class="fn">%s</b>', get_comment_author_link() ) ; ?>
						<a class="small" href="<?php echo esc_url( get_comment_link( $comment->comment_ID, $args ) ); ?>">
							<time datetime="<?php comment_time( 'c' ); ?>">
								<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'greatmag' ), get_comment_date(), get_comment_time() ); ?>
							</time>
						</a>
						<?php edit_comment_link( __( 'Edit', 'greatmag' ), '<span class="edit-link">', '</span>' ); ?>
						<div class="reply-link">
							<?php comment_reply_link( array_merge( $args, array( 'add_below' => 'edit-link', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
						</div>
					</h5>

					<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'greatmag' ); ?></p>
					<?php endif; ?>

					<div class="comment-content">
						<?php comment_text(); ?>
					</div>
				</div>
		</article>
	<?php
}

/**
 * Comment form
 */
function greatmag_comment_form() {

	$commenter = wp_get_current_commenter();
	$req = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );

	$fields =  array(

	  'author' =>
	    '<div class="row"><div class="col-sm-6"><input placeholder="' . __( 'Name', 'greatmag' ) . ( $req ? '*' : '' ) . '" id="author" name="author" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
	    '" size="30"' . $aria_req . ' /></div>',

	  'email' =>
	    '<div class="col-sm-6"><input id="email" placeholder="' . __( 'Email', 'greatmag' ) . ( $req ? '*' : '' ) . '" name="email" class="form-control" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
	    '" size="30"' . $aria_req . ' /></div></div>',

	  'url' =>
	    '<input id="url" placeholder="' . __( 'Website', 'greatmag' ) . '" name="url" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
	    '" size="30" />',
	);

	$comments_args = array(
	  	'comment_field' 		=> '<p class="comment-form-comment"><textarea placeholder="' . __( 'Comment', 'greatmag' ) . '" id="comment" class="form-control" name="comment" cols="45" rows="8" aria-required="true">' . '</textarea></p>',
		'title_reply_before' 	=> '<h6 class="post-cat big-bline"><span class="ispan"><span class="dark-dec">',
		'title_reply_after' 	=> '</span></span></h6>',
		'fields' 				=> apply_filters( 'comment_form_default_fields', $fields ),
		'class_submit'			=> 'submit btn'
	);

	comment_form($comments_args);
}

/**
 * Blog layout
 */
function greatmag_blog_layout() {
	$layout = get_theme_mod( 'blog_layout', 'list');
	if ( ( $layout == 'masonry' ) || ( $layout == 'masonry-full' ) ) {
		echo 'posts-grid layout-masonry';
	} else {
		echo 'posts-layout';
	}
}

/**
 * Grid sizer
 */
function greatmag_grid_sizer() {
	$layout = get_theme_mod( 'blog_layout', 'list');

	if ( $layout == 'masonry' ) {
		echo '<div class="col-sm-6 grid-sizer"></div>';
	} elseif ( $layout == 'masonry-full' ) {
		echo '<div class="col-sm-6 col-md-4 grid-sizer"></div>';
	}
}

/**
 * Index image sizes
 */
function greatmag_index_image_sizes() {

	$layout = get_theme_mod( 'blog_layout', 'list');

	if ( $layout == 'classic' ) {
		$thumb_size = '';
	} else {
		$thumb_size = 'greatmag-medium';
	}

	return $thumb_size;
}
