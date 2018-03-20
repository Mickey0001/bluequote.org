<?php
/**
 * Displays posts from all categories
 *
 */

class Athemes_Multiple_Cats_Posts extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_multiple_cats_posts',
			'description' => __( 'Displays posts from multiple categories', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-multiple-cats-posts', __( 'GreatMag: Multiple category posts', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_multiple_cats_posts';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$categories = isset( $instance['category_dropdown'] ) ? $instance['category_dropdown'] : '';
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 6;
		if ( ! $number )
			$number = 6;

		echo $args['before_widget']; ?>

		<div class="post-by-cats multiple-cats clearfix">

		<?php foreach ( $categories as $cat ) {

			$r = new WP_Query( array(
				'posts_per_page'      => $number,
				'no_found_rows'       => true,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'cat'		  		  => $cat			
			) );
			$color = greatmag_get_category_color($cat_id = $cat);

			if ($r->have_posts()) :
			?>
			<div class="col-md-4 col-sm-6">
				<h6 class="post-cat big-bline"><span class="ispan" style="color:<?php echo esc_html( $color ); ?>"><?php echo get_cat_name( $cat ) ?><span style="background-color:<?php echo esc_html( $color ); ?>;"></span></span></h6>

				<div class="pbc-carousel">
				<?php $counter = 1; ?>
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
				
					<?php $meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . esc_html( get_the_date() ) . '</a></h5>'; ?>				

					<?php if ( ( $counter + 2 ) % 3 == 0 ) : ?>
					<div class="this-cat-post post top-one">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="featured-img">
							<?php the_post_thumbnail('greatmag-medium'); ?>
							</div>
						<?php endif; ?>	
						<a href="<?php the_permalink(); ?>" class="post-title-standard"><?php the_title(); ?></a>
						<?php echo $meta; ?>
						<div class="post-excerpt"><?php the_excerpt(); ?></div>
					</div>

					<?php else : ?>

					<div class="this-cat-post other-two media post">
						<?php if ( has_post_thumbnail() ) : ?>
						<div class="media-left">
							<div class="media-object"><?php the_post_thumbnail('greatmag-extra-small'); ?></div>
						</div>
						<?php endif; ?>
						<div class="media-body">
							<a href="<?php the_permalink(); ?>" class="post-title-small"><?php the_title(); ?></a>
							<h5 class="post-meta"><a href="<?php the_permalink(); ?>" class="date"><?php echo esc_html( get_the_date() ); ?></a></h5>
						</div>
					</div>
					<?php endif; ?>

					<?php $counter++; ?>
				<?php endwhile; ?>
				</div>
			</div>
			<?php
			wp_reset_postdata();

			endif;
		} ?>

		</div>

		<?php
		echo $args['after_widget'];

	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		if ( is_array($instance['category_dropdown']) ) {
			$instance['category_dropdown'] = array_map( 'sanitize_text_field', $new_instance['category_dropdown'] );			
		} else {
			$instance['category_dropdown'] = sanitize_text_field( $new_instance['category_dropdown'] );			
		}
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	public function form( $instance ) {
		$title     			= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$category_dropdown  = isset( $instance['category_dropdown'] ) ? array_map( 'esc_attr', $instance['category_dropdown'] ) : '';		
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 6;
		
?>		
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to display for each category:', 'greatmag' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id('category_dropdown'); ?>"><?php _e('Choose as many categories as you want:', 'greatmag'); ?></label>

        <select data-placeholder="<?php echo esc_attr__('Select the categories you wish to display posts from.', 'greatmag'); ?>" multiple="multiple" name="<?php echo $this->get_field_name('category_dropdown'); ?>" id="<?php echo $this->get_field_id('category_dropdown'); ?>" class="widefat chosen-dropdown chosen-sortable">
		<?php
		$cats = get_categories();
		foreach( $cats as $cat ) : ?>
                <?php printf(
                    '<option value="%s" %s>%s</option>',
                    $cat->cat_ID,
                    in_array( $cat->cat_ID, (array)$category_dropdown) ? 'selected="selected"' : '',
                    $cat->cat_name
                );?>
               <?php endforeach; ?>
       	</select>

        </p>  

<?php
	}
}
