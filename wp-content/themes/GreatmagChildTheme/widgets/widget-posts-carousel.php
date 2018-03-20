<?php
/**
 * Posts carousel
 *
 */


class Athemes_Posts_Carousel extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_posts_carousel',
			'description' => __( 'Posts displayed in a carousel', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-posts-carousel', __( 'GreatMag: Posts Carousel', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_posts_carousel';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;

		$r = new WP_Query( array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>

		<div class="highlight-posts row">
			<div class="highlight-posts-carousel box-posts-carousel">
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
					<div class="item post">
						<div class="inner row">
							<?php greatmag_get_post_cats( $first_cat = true ); ?>
							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" class="featured-img"><?php the_post_thumbnail('greatmag-medium'); ?></a>
							<?php endif; ?>
							<a href="<?php the_permalink(); ?>" class="post-title-standard"><?php the_title(); ?></a>
							<h5 class="post-meta"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="author"><?php echo esc_html( get_the_author() ); ?></a>  -  <a href="<?php the_permalink(); ?>" class="date"><?php echo get_the_date(); ?></a></h5>
						</div>
					</div>
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
		$instance['number'] = (int) $new_instance['number'];
		return $instance;
	}

	public function form( $instance ) {
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
?>
		<p><em><?php _e('Please note: this widget will display your latest posts in a carousel.', 'greatmag'); ?></em></p>
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'greatmag' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

<?php
	}
}
