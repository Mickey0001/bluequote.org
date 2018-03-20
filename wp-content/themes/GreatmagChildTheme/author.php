<?php
/**
 * The template for displaying author pages.
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
	if ( $layout != 'masonry-full' ) :

		$author_id 	 = get_the_author_meta( 'ID' );
		$author_data = get_userdata( $author_id );
		?>

		<aside id="secondary" class="author-info col-md-4" role="complementary">
			<div class="row author-details">
				<?php echo get_avatar( get_the_author_meta('email') , 400 ); ?>
				<h3 class="h3 gh author-dname"><?php echo $author_data->nickname; ?></h3>
				<h5 class="author-dintro"><?php echo ucfirst($author_data->roles[0]); ?></h5>
				<p><?php the_author_meta( 'description' ); ?></p>
			</div>		
		</aside><!-- #secondary -->	
	<?php endif; ?>

<?php
get_footer();