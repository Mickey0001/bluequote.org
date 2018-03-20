<?php
/**
 * Displays posts from a single category
 *
 */

class Athemes_Single_Category_Posts_A extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_single_category_posts_a',
			'description' => __( 'Displays posts from a single category', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-single-category-posts-a', __( 'GreatMag: Single category posts', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_single_category_posts_a';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$category = isset( $instance['category_dropdown'] ) ? $instance['category_dropdown'] : '';
		$color = greatmag_get_category_color($cat_id = $category);
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 12;
		if ( ! $number )
			$number = 12;

		$r = new WP_Query( array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'cat'		  		  => $category			
		) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>

		<div class="post-by-cats style3">
			<h6 class="post-cat big-bline"><span class="ispan" style="color:<?php echo esc_html( $color ); ?>"><?php echo get_cat_name( $category ) ?><span style="background-color:<?php echo esc_html( $color ); ?>;"></span></span></h6>
				<div class="pbc-carousel2">
			
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

		<?php echo $args['after_widget']; ?>

		<?php
		wp_reset_postdata();

		endif;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['category_dropdown'] = sanitize_text_field( $new_instance['category_dropdown'] );
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	public function form( $instance ) {
		$category_dropdown  = isset( $instance['category_dropdown'] ) ? esc_attr( $instance['category_dropdown'] ) : '';			
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 12;
	?>
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'greatmag' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><label for="<?php echo $this->get_field_id('category_dropdown'); ?>"><?php _e('Choose your category:', 'greatmag'); ?></label>
		<?php $args = array(
			'name'               => $this->get_field_name('category_dropdown'),
			'id'                 => $this->get_field_id('category_dropdown'),
			'class'              => 'chosen-dropdown-1',
			'selected'			=> $category_dropdown,
		); ?>
       	<?php wp_dropdown_categories($args); ?>
        </p>  

	<?php
	}
}
