<?php if(!is_single()){ global $more; $more = 0; } //enable more link ?>

<?php
/** Themify Default Variables
 *  @var object */
global $themify;
?>

<?php themify_post_before(); // hook ?>
<article id="post-<?php the_id(); ?>" <?php post_class("post clearfix " . $themify->get_categories_as_classes(get_the_id())); ?>>
	<?php themify_post_start(); // hook ?>

	<?php themify_post_media(); ?>

	<div class="post-content">

		<?php if($themify->hide_date != 'yes'): ?>
			<time datetime="<?php the_time('o-m-d') ?>" class="post-date entry-date updated"><?php echo get_the_date( apply_filters( 'themify_loop_date', '' ) ) ?></time>

		<?php endif; //post date ?>

		<?php if($themify->hide_title != 'yes'): ?>
			<?php themify_post_title(); ?>
		<?php endif; //post title ?>

		<?php if($themify->hide_meta != 'yes'): ?>
			<p class="post-meta entry-meta">

				<span class="post-author"><?php echo themify_get_author_link() ?></span>
				<span class="post-category"><?php the_category(', ') ?></span>
				<?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
				<?php  if( !themify_get('setting-comments_posts') && comments_open() ) : ?>
					<span class="post-comment"><?php comments_popup_link( __( '0 Comments', 'themify' ), __( '1 Comment', 'themify' ), __( '% Comments', 'themify' ) ); ?></span>
				<?php endif; //post comment ?>
			</p>
		<?php endif; //post meta ?>

		<div class="entry-content">

		<?php if ( 'excerpt' == $themify->display_content && ! is_attachment() ) : ?>

			<?php the_excerpt(); ?>

			<?php if( themify_check('setting-excerpt_more') ) : ?>
				<p><a href="<?php the_permalink(); ?>" class="more-link"><?php echo themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify') ?></a></p>
			<?php endif; ?>

		<?php elseif ( 'none' == $themify->display_content && ! is_attachment() ) : ?>

		<?php else: ?>

			<?php the_content(themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

		<?php endif; //display content ?>

		</div><!-- /.entry-content -->

		<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>

	</div>
	<!-- /.post-content -->
	<?php themify_post_end(); // hook ?>

</article>
<!-- /.post -->
<?php themify_post_after(); // hook ?>
