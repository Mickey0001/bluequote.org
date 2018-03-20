<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package GreatMag
 */

get_header(); ?>

	<div id="primary" class="content-area col-md-8">
		<main id="main" class="site-main">

		<?php
		if ( have_posts() ) : ?>

		<header class="archive-header">
			<?php
				the_archive_title( '<h2 class="page-title">', '</h2>' );
				the_archive_description( '<div class="archive-description">', '</div>' );
			?>
		</header><!-- .archive-header -->

		<div class="<?php greatmag_blog_layout(); ?>">
			<?php greatmag_grid_sizer(); ?>

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();

				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_format() );

			endwhile; ?>

			</div>

			<?php the_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
$layout = get_theme_mod( 'blog_layout', 'list' );
if ( $layout != 'masonry-full' ) {
	get_sidebar();
}
get_footer();
