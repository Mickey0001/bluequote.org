<?php
/**
 * Featured posts
 *
 */

class Athemes_Featured_Posts_Type_A extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_featured_posts_type_a',
			'description' => __( '5 featured posts displayed in a grid', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-featured-posts-type-a', __( 'GreatMag: Featured Posts', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_featured_posts_type_a';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$posts = isset( $instance['posts_dropdown'] ) ? $instance['posts_dropdown'] : '';


		$r = new WP_Query( array(
			'posts_per_page'      => 5,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'post__in'			  => $posts
		) );

		if ($r->have_posts()) :
		?>
		<?php echo $args['before_widget']; ?>

		<div class="sticky-posts-blocks sticky-posts isotope-gallery">
			<div class="col-sm-3 grid-sizer"></div>

			<?php $counter = 1; ?>	
			<?php while ( $r->have_posts() ) : $r->the_post(); ?>
				<?php if( $counter != 2 ) {
					$col = 'col-md-3';
					$title_size = 'standard';
					$meta = '';
					$thumb_size = 'greatmag-featured-c';
				} else {
					$col = 'col-md-6';
					$title_size = 'big';					
					$meta = '<h5 class="post-meta"><a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="author">' . esc_html( get_the_author() ) . '</a>  -  <a href="' . esc_url( get_the_permalink() ) . '" class="date">' . get_the_date() . '</a></h5>';
					$thumb_size = 'greatmag-featured-a';
				} ?>
				<?php if ( has_post_thumbnail() ) : ?>
				<div class="col-sm-6 <?php echo $col; ?> featured-item sticky-post post sticky-post-style2">
					<?php the_post_thumbnail($thumb_size); ?>
					<div class="this-contents">
						<h6 class="post-cat big-bline"><span class="ispan"><?php greatmag_get_post_cats( $first_cat = true, $featured = true ); ?><span style="background-color:<?php echo greatmag_get_category_color(); ?>;"></span></span></h6>
						<a href="<?php the_permalink(); ?>" class="post-title-<?php echo $title_size; ?>"><?php the_title(); ?></a>
						<?php echo $meta; ?>
					</div>
				</div>
				<?php endif; ?>					

			<?php  $counter++; ?>
			<?php endwhile; ?>
		</div>

		<?php echo $args['after_widget']; ?>

		<?php
		wp_reset_postdata();

		endif;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['posts_dropdown'] = array_map( 'sanitize_text_field', (array) $new_instance['posts_dropdown'] );

		return $instance;
	}

	public function form( $instance ) {
		$posts_dropdown  = isset( $instance['posts_dropdown'] ) ? array_map( 'esc_attr', $instance['posts_dropdown'] ) : '';			
		
	?>
		<p><em><?php _e('Please note: you can select up to five posts to display in this widget.', 'greatmag'); ?></em></p>
		<p><label for="<?php echo $this->get_field_id('posts_dropdown'); ?>"><?php _e('Choose your posts', 'greatmag'); ?></label>
        <select data-placeholder="<?php echo esc_attr__('Select five posts to display in this widget', 'greatmag'); ?>" multiple="multiple" name="<?php echo $this->get_field_name('posts_dropdown'); ?>" id="<?php echo $this->get_field_id('posts_dropdown'); ?>" class="widefat chosen-dropdown-5">
		<?php
		global $post;
		$args = array( 'numberposts' => -1);
		$posts = get_posts($args);
		foreach( $posts as $post ) : setup_postdata($post); ?>
                <?php printf(
                    '<option value="%s" %s>%s</option>',
                    $post->ID,
                    in_array( $post->ID, (array)$posts_dropdown) ? 'selected="selected"' : '',
                    $post->post_title
                );?>
               <?php endforeach; ?>
       	</select>
        </p>  

	<?php
	}
}
