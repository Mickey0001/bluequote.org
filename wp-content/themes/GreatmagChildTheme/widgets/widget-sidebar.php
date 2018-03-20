<?php
/**
 * Posts carousel
 *
 */


class Athemes_Sidebar_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'athemes_sidebar_widget',
			'description' => __( 'Display widgets from a sidebar of your choice', 'greatmag' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'athemes-sidebar-widget', __( 'GreatMag: Sidebar', 'greatmag' ), $widget_ops );
		$this->alt_option_name = 'athemes_sidebar_widget';
	}

	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$sidebar = isset( $instance['sidebars'] ) ? esc_attr($instance['sidebars']) : '';

		?>
		<?php echo $args['before_widget']; ?>

		<?php if ( is_active_sidebar($sidebar) ) : ?>
		<aside class="widget-area sidebar-area">
			<?php dynamic_sidebar($sidebar); ?>					
		</aside>
		<?php endif; ?>

		<?php echo $args['after_widget']; ?>
		<?php

	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['sidebars'] 	= sanitize_text_field($new_instance['sidebars']);
		return $instance;
	}

	public function form( $instance ) {
		$sidebars = isset( $instance['sidebars'] ) ? esc_attr( $instance['sidebars'] ) : '';
	?>
		<p><label for="<?php echo $this->get_field_id('sidebars'); ?>"><?php _e('Choose the sidebar you want to display in this widget', 'greatmag'); ?></label></p>
		<p><em><?php _e('To register more custom sidebars, please go to Customize > Sidebars.', 'greatmag'); ?></em></p>
        <p><select name="<?php echo $this->get_field_name('sidebars'); ?>" id="<?php echo $this->get_field_id('sidebars'); ?>" class="widefat">
		<?php
		global $wp_registered_sidebars;
		foreach( $wp_registered_sidebars as $sidebar ) : ?>
			<option <?php selected( $sidebars, $sidebar['id']); ?> value="<?php echo $sidebar['id']; ?>"><?php echo $sidebar['name']; ?></option> 
        <?php endforeach; ?>
       	</select>
        </p>
	<?php
	}
}
