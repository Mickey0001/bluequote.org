<div class="themify_builder_options_tab_wrapper">
	<div class="themify_builder_options_tab_content">
		<form id="themify_builder_load_template_form" method="POST">
			<div id="themify_builder_lightbox_options_tab_items">
				<li class="title"><?php _e('Layouts', 'themify'); ?></li>
			</div>
			<div id="themify_builder_lightbox_actions_items"></div>

			<?php if ( !empty( $this->provider_instances )): ?>
                            <?php $first = true;?>
                            <div id="themify_builder_options_styling">
                                    <div class="themify_builder_tabs">
                                            <ul class="clearfix">
                                                <?php $instances = array()?>
                                                    <?php foreach( $this->provider_instances as $provider => $instance ) : ?>
                                                            <?php if( $instance->has_layouts() ) : ?>
                                                                <?php $instances[] = $instance;?>
                                                                <li class="title<?php if($first): $first = false;?> current<?php endif;?>"><a href="#themify_builder_tabs_<?php echo $provider; ?>"><?php echo $instance->get_label(); ?></a></li>
                                                            <?php endif; ?>
                                                    <?php endforeach; ?>
                                            </ul>
                                            <?php  foreach($instances as $instance ) : ?>
                                                    <?php $instance->get_list_output();
                                                        $instance->print_template_form();
                                                        
                                                    ?>
                                            <?php endforeach; ?>

                                    </div>
                            </div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<a href="<?php echo admin_url('post-new.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank" class="add_new">
                            <span class="themify_builder_icon add"></span>
                            <?php _e('Add new layout', 'themify') ?>
			</a>
			<a href="<?php echo admin_url('edit.php?post_type=' . $this->layout->post_type_name); ?>" target="_blank" class="add_new">
                            <span class="themify_builder_icon ti-folder"></span>
                            <?php _e('Manage Layouts', 'themify') ?>
			</a>

		</form>
	</div>
</div>