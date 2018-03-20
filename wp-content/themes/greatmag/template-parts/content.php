<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package GreatMag
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="media-left">
			<div class="media-object">
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="featured-img"><?php the_post_thumbnail( greatmag_index_image_sizes() ); ?></a>
				<?php greatmag_get_post_cats( $first_cat = true ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="media-body">
		<header class="entry-header">
			<?php

			the_title( '<h2 class="entry-title"><a class="post-title-standard" href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );

			if ( 'post' === get_post_type() && get_theme_mod('hide_meta_index') != 1 ) : ?>
			<div class="entry-meta">
				<?php greatmag_posted_on(); ?>
			</div><!-- .entry-meta -->
			<?php
			endif; ?>
		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php the_excerpt(); ?>
		</div><!-- .entry-content -->

	</div>
</article><!-- #post-## -->
