<?php
/**
 * Displays posts that use the video post format
 *
 */

class Athemes_Video_Posts extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_video_posts',
			'description' => __( 'Displays your latest video format posts', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-video-posts', __( 'GreatMag: Video Posts', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_video_posts';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 4;
		if ( ! $number )
			$number = 4;

		$r = new WP_Query( array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
	        'tax_query' => array( array(
	            'taxonomy' => 'post_format',
	            'field' => 'slug',
	            'terms' => array('post-format-video'),
	            'operator' => 'IN'
	        ) ),
		) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>

			<div class="post-by-cats pbc-contain-boxed">

				<?php if ( $title ) {
					echo $args['before_title'] . '<span class="ispan">' . $title . '</span>' . $args['after_title'];
				} ?>

				<div class="row">
					<div class="box-posts-carousel catp-defined video-posts-carousel">
					<?php while ( $r->have_posts() ) : $r->the_post(); ?>

						<?php $meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . esc_html( get_the_date() ) . '</a></h5>'; ?>				
												
						<div class="item post type-video">
							<div class="inner row">
								<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>" class="featured-img"><?php the_post_thumbnail('greatmag-medium'); ?></a>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>" class="post-title-standard"><?php the_title(); ?></a>
								<?php echo $meta; ?>
							</div>
						</div>
					<?php endwhile; ?>
					</div>
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
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 4;
		
?>
		<p><em><?php _e('Please note: this widget will display posts that have the Video post format assigned.', 'greatmag'); ?></em></p>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'greatmag' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'greatmag' ); ?></label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
