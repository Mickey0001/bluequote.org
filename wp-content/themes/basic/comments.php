<?php if ( post_password_required() ) : ?>
	<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'themify' ); ?></p>
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() || comments_open() ) : ?>

<?php themify_comment_before(); //hook ?>
<div id="comments" class="commentwrap">
<?php endif; // end commentwrap ?>
	<?php themify_comment_start(); //hook ?>

<?php if ( have_comments() ) : ?>
	<h4 class="comment-title"><?php comments_number(__('No Comments','themify'), __('One Comment','themify'), __('% Comments','themify') );?></h4>

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<div class="pagenav top clearfix">
			<?php paginate_comments_links( array('prev_text' => '&laquo;', 'next_text' => '&raquo;') );?>
		</div> 
		<!-- /.pagenav -->
	<?php endif; // check for comment navigation ?>

	<ol class="commentlist">
		<?php wp_list_comments('callback=themify_theme_comment'); ?>
	</ol>

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<div class="pagenav bottom clearfix">
			<?php paginate_comments_links( array('prev_text' => '&laquo;', 'next_text' => '&raquo;') );?>
		</div>
		<!-- /.pagenav -->
	<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>

<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

<?php comment_form(); ?>

<?php if ( have_comments() || comments_open() ) : ?>
<?php themify_comment_end(); //hook ?>
</div>
<?php themify_comment_after(); //hook ?>
<!-- /.commentwrap -->
<?php endif; // end commentwrap ?>