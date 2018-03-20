<?php get_header(); ?>

<?php 
/** Themify Default Variables
 *  @var object */
global $themify;
?>
		
<!-- layout -->
<div id="layout" class="pagewidth clearfix">

	<!-- content -->
    <?php themify_content_before(); //hook ?>
	<div id="content" class="clearfix">
    	<?php themify_content_start(); //hook ?>
		
		<?php
			// author bio
			if( is_author() ) :
				themify_author_bio();
			endif;
		?>

		<?php 
		/////////////////////////////////////////////
		// Search Title	 							
		/////////////////////////////////////////////
		?>
		<?php if(is_search()): ?>
			<h1 class="page-title"><?php _e('Search Results for:','themify'); ?> <em><?php echo get_search_query(); ?></em></h1>
		<?php endif; ?>
	
		<?php
		/////////////////////////////////////////////
		// Date Archive Title
		/////////////////////////////////////////////
		?>
		<?php if ( is_day() ) : ?>
			<h1 class="page-title"><?php printf( __( 'Daily Archives: <span>%s</span>', 'themify' ), get_the_date() ); ?></h1>
		<?php elseif ( is_month() ) : ?>
			<h1 class="page-title"><?php printf( __( 'Monthly Archives: <span>%s</span>', 'themify' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'themify' ) ) ); ?></h1>
		<?php elseif ( is_year() ) : ?>
			<h1 class="page-title"><?php printf( __( 'Yearly Archives: <span>%s</span>', 'themify' ), get_the_date( _x( 'Y', 'yearly archives date format', 'themify' ) ) ); ?></h1>
		<?php endif; ?>

		<?php 
		/////////////////////////////////////////////
		// Category Title	 							
		/////////////////////////////////////////////
		?>
		<?php if(is_category() || is_tag() || is_tax() ): ?>
			<h1 class="page-title"><?php single_cat_title(); ?></h1>
			<?php echo $themify->get_category_description(); ?>
		<?php endif; ?>

		<?php 
		/////////////////////////////////////////////
		// Loop	 							
		/////////////////////////////////////////////
		?>
		<?php if (have_posts()) : ?>
		
			<!-- loops-wrapper -->
			<div id="loops-wrapper" class="loops-wrapper <?php echo $themify->layout . ' ' . $themify->post_layout; ?>">

				<?php while (have_posts()) : the_post(); ?>
		
					<?php if(is_search()): ?>
						<?php get_template_part( 'includes/loop' , 'search'); ?>
					<?php else: ?>
						<?php get_template_part( 'includes/loop' , 'index'); ?>
					<?php endif; ?>
		
				<?php endwhile; ?>
							
			</div>
			<!-- /loops-wrapper -->

			<?php get_template_part( 'includes/pagination'); ?>
		
		<?php 
		/////////////////////////////////////////////
		// Error - No Page Found	 							
		/////////////////////////////////////////////
		?>
	
		<?php else : ?>
	
			<p><?php _e( 'Sorry, nothing found.', 'themify' ); ?></p>
	
		<?php endif; ?>			
	<?php themify_content_end(); //hook ?>
	</div>
    <?php themify_content_after(); //hook ?>
	<!-- /#content -->

	<?php 
	/////////////////////////////////////////////
	// Sidebar							
	/////////////////////////////////////////////
	if ($themify->layout != "sidebar-none"): get_sidebar(); endif; ?>

</div>
<!-- /#layout -->

<?php get_footer(); ?>