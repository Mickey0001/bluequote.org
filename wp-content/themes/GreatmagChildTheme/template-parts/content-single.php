<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package GreatMag
 */

?>

<?php $hide_meta = get_theme_mod('hide_meta_singles'); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( !$hide_meta ) : ?>
	<div class="single-post-cats">
		<?php greatmag_get_post_cats( $first_cat = false ); ?>
	</div>
	<?php endif; ?>

	<header class="entry-header">
		<?php
		the_title( '<h1 class="entry-title post-title-big">', '</h1>' );

		if ( 'post' === get_post_type() && !$hide_meta ) : ?>
		<div class="entry-meta">
			<?php greatmag_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php
		endif; ?>
	</header><!-- .entry-header -->

	<div class="post-main-image row text-center">
		<?php the_post_thumbnail('greatmag-single'); ?>
	</div>

	<div class="entry-content">
		<?php
			the_content( sprintf(
				/* translators: %s: Name of current post. */
				wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'greatmag' ), array( 'span' => array( 'class' => array() ) ) ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			) );

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'greatmag' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<?php if ( !$hide_meta ) : ?>
	<footer class="entry-footer">
		<?php greatmag_entry_footer(); ?>
	</footer><!-- .entry-footer -->
	<?php endif; ?>
		
</article><!-- #post-## -->
