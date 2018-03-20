<?php
/**
 * Footer functions
 *
 * @package Talon
 */


/**
 * Footer Editor's choice section
 */
function greatmag_editors_choice() {
	$title  = get_theme_mod('footer_posts_title', __('Editor\'s choice', 'greatmag'));
	$posts  = get_theme_mod('footer_posts_ids');

	if ( $posts == '' ) {
		return;
	}

	$posts = explode(',', $posts);

	$query = new WP_Query( array(
		'no_found_rows'       => true,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'post__in'			  => $posts
	) );
	if ($query->have_posts()) :
		?>
			<div class="row editor-choice" id="editor-choice">
				<div class="editor-choice-header row">
					<h5 class="editor-choice-title"><?php echo esc_html($title); ?></h5>
					<div class="editor-choice-nav"></div>
				</div>
				<div class="editor-choice-post-carousel">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php if ( has_post_thumbnail() ) : ?>
						<div class="item post">
							<a href="<?php the_permalink(); ?>" class="featured-img"><?php the_post_thumbnail('greatmag-featured-c'); ?></a>
							<div class="this-contents">									
								<?php greatmag_get_first_cat(); ?>
								<a href="<?php the_permalink(); ?>" class="post-title-standard"><?php the_title(); ?></a>
							</div>
						</div>
						<?php endif; ?>
					<?php endwhile; ?>
				</div>
			</div>
		<?php
	endif;
}
add_action('greatmag_before_footer', 'greatmag_editors_choice');

/**
 * Footer sidebar
 */
function greatmag_footer_sidebar() {
	if ( is_active_sidebar( 'footer-1' ) ) {
		get_sidebar('footer');		
	}
}
add_action('greatmag_footer', 'greatmag_footer_sidebar', 7);

/**
 * Footer credits and menu
 */
function greatmag_footer_bottom() {
	?>
		<div class="row bottom-footer" id="bottom-footer">
			<div class="container">
				<?php greatmag_custom_credits(); ?>

				<nav id="footer-navigation" class="footer-navigation footer-menu-box">
					<?php wp_nav_menu( array( 'theme_location' => 'footer', 'menu_class' => 'nav nav-pills footer-menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
				</nav>
			</div>
		</div>
	<?php
}
add_action('greatmag_footer', 'greatmag_footer_bottom', 8);

/**
 * Credits
 */
function greatmag_custom_credits() {
    echo '<div class="site-info">';
	echo '<a href="' . esc_url( __( 'https://wordpress.org/', 'greatmag' ) ) . '">';
		printf( __( 'Powered by %s', 'greatmag' ), 'WordPress' );
	echo '</a>';
	echo '<span class="sep"> | </span>';
	printf( __( 'Theme: %2$s by %1$s.', 'greatmag' ), 'aThemes', '<a href="http://athemes.com/theme/greatmag">Greatmag</a>' );
    echo '</div>';
}
