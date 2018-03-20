<?php
/**
 * Most popular posts this week sorted by comment count
 *
 */

class Athemes_Most_Popular extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_most_popular',
			'description' => __( 'Most popular posts in the current week, sorted by comment count', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-most-popular', __( 'GreatMag: Most popular', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_most_popular';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 3;
		if ( ! $number )
			$number = 3;

		$r = new WP_Query( array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby' 			  => 'comment_count',
			'date_query' => array(
				array(
					'year' => date( 'Y' ),
					'week' => date( 'W' ),
				),
			),			
		) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>

		<div class="widget-most-popular">
			<?php if ( $title ) : ?>
			<?php echo $args['before_title'] . $title . $args['after_title']; ?>
			<?php endif; ?>

			<div class="mpopular-post-lists" id="mpopular-post-lists">
			
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>

					<?php $meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . esc_html( get_the_date() ) . '</a></h5>'; ?>				
					
					<div class="media post mpopular-post">
						<?php if ( has_post_thumbnail() ) : ?>
						<div class="media-left">
							<a href="<?php the_permalink(); ?>" class="media-object"><?php the_post_thumbnail('greatmag-extra-small'); ?></a>
						</div>
						<?php endif; ?>	
						<div class="media-body">
							<a href="<?php the_permalink(); ?>" class="post-title-small"><?php the_title(); ?></a>
							<?php echo $meta; ?>
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
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];

		return $instance;
	}

	public function form( $instance ) {
		$title     			= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 3;
		
?>

		<p><em><?php _e('Please note: this widget will display the most popular posts from the current week, sorted by comment count.', 'greatmag'); ?></em></p>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'greatmag' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'greatmag' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

<?php
	}
}
