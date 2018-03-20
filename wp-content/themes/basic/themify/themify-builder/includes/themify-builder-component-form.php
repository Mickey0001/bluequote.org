<form id="tb_component_form">

	<div id="themify_builder_lightbox_options_tab_items">
		<li class="title"><?php printf( __( '%s %s', 'themify' ),$is_import?'Import':'Export', $component ); ?></li>
	</div>
        <?php if($is_import):?>
            <div id="themify_builder_lightbox_actions_items">
                    <button id="builder_submit_import_component_form" class="builder_button"><?php _e('Save', 'themify') ?></button>
            </div>
        <?php endif;?>
	<div class="themify_builder_options_tab_wrapper">
		<div class="themify_builder_options_tab_content">
                    <div class="themify_builder_field">
                        <div class="themify_builder_label"><?php echo $label?></div>
                        <div class="themify_builder_input">                
                            <textarea autofocus id="tb_data_field" name="tb_data_field" class="xlarge tb_lb_option" rows="13"></textarea>
                            <small>
                                <br/>
                                <?php echo $is_import?sprintf(__('Paste %s data here', 'themify'),$component):__('You can copy & paste this data to another Builder site', 'themify');?>
                           </small>
                        </div>
                    </div>
		</div>
	</div>
</form>
