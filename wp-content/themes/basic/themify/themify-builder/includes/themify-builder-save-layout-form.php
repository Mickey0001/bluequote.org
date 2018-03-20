<form id="tb_save_layout_form">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php _e('Save as Layout', 'themify'); ?></li>
	</div>

	<div id="themify_builder_lightbox_actions_items">
		<button id="builder_submit_layout_form" class="builder_button"><?php _e('Save', 'themify') ?></button>
	</div>

	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
			<?php themify_builder_module_settings_field( $fields ); ?>
		</div>
	</div>
    
	<input type="hidden" name="postid" value="<?php echo esc_attr( $postid ); ?>">
</form>