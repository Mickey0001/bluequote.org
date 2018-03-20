<?php
/**
 * Most popular posts this week sorted by comment count
 *
 */

class Athemes_Most_Popular_Home extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_most_popular_home',
			'description' => __( 'Most popular posts in the current week, sorted by comment count', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-most-popular-home', __( 'GreatMag: Most popular (homepage)', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_most_popular_home';
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

		<div class="post-by-cats boxed-pbc style2">
			<?php if ( $title ) : ?>
			<h6 class="post-cat big-bline"><span class="ispan"><span class="dark-dec"><?php echo $title; ?></span></span></h6>
			<?php endif; ?>
			<div class="pbc-carousel">
		
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>

					<?php $meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . esc_html( get_the_date() ) . '</a></h5>'; ?>				
					
					<div class="this-cat-post media post">
						<?php if ( has_post_thumbnail() ) : ?>
						<div class="media-left">
							<div class="featured-img row m0">
								<a href="<?php the_permalink(); ?>" class="media-object"><?php the_post_thumbnail('greatmag-small'); ?></a>
								<?php greatmag_get_post_cats( $first_cat = true, $featured = false ); ?>
							</div>
						</div>
						<?php endif; ?>	
						<div class="media-body">
							<a href="<?php the_permalink(); ?>" class="post-title-standard"><?php the_title(); ?></a>
							<?php echo $meta; ?>
							<div class="post-excerpt"><?php the_excerpt(); ?></div>
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
