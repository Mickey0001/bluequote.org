<script type="text/html" id="tmpl-builder_grid_menu">
	<?php Themify_Builder_Model::grid('module'); ?>
</script>
<script type="text/html" id="tmpl-builder_lightbox">
		<?php //create fix overlay on top iframe,mouse position will be always on top iframe on resizing?>
		<div class="tb_resizable_overlay"></div>
	<div id="themify_builder_lightbox_parent" class="themify_builder themify_builder_admin builder-lightbox clearfix {{ data.is_themify_theme }}">
		<div class="themify_builder_lightbox_top_bar clearfix">
			<ul class="themify_builder_options_tab clearfix"></ul>
			<div class="themify_builder_lightbox_actions">
				<a class="builder_cancel_docked_mode"><i class="ti-new-window"></i></a>
				<a class="builder_cancel_lightbox"><?php _e( 'Cancel', 'themify' ) ?><i class="ti-close"></i></a>
				<span class="tb_lightbox_actions_wrap"></span>	
			</div>
		</div>
		<div id="themify_builder_lightbox_container"></div>
		<div class="tb_resizable tb_resizable-e"></div>
		<div class="tb_resizable tb_resizable-s"></div>
		<div class="tb_resizable tb_resizable-w"></div>
		<div class="tb_resizable tb_resizable-se"></div>
	</div>
</script>
<script type="text/html" id="tmpl-builder_lite_lightbox_confirm">
	<p>{{ data.message }}</p>
	<p>
	<# _.each(data.buttons, function(value, key) { #> 
		<button data-type="{{ key }}">{{ value.label }}</button> 
	<# }); #>
	</p>
</script>
<script type="text/html" id="tmpl-builder_lite_lightbox_prompt">
	<p>{{ data.message }}</p>
	<p><input type="text" class="themify_builder_litelightbox_prompt_input"></p>
	<p>
	<# _.each(data.buttons, function(value, key) { #> 
		<button data-type="{{ key }}">{{ value.label }}</button> 
	<# }); #>
	</p>
</script>


<script type="text/html" id="tmpl-builder_row_item">
	<div class="themify_builder_row_top tb_top">
		<?php Themify_Builder_Model::grid('row'); ?>
		<ul class="row_action">
			<li><a href="#" class="themify_builder_option_row themify-tooltip-bottom">
					<span class="ti-pencil"></span>
					<span class="themify_tooltip"><?php _e('Options', 'themify') ?></span>
				</a></li>
			<li><a href="#"class="themify_builder_style_row themify-tooltip-bottom">
					<span class="ti-brush"></span>
					<span class="themify_tooltip"><?php _e('Styling', 'themify') ?></span>
				</a></li>
			<li><a href="#" class="themify_duplicate themify-tooltip-bottom">
					<span class="ti-layers"></span>
					<span class="themify_tooltip"><?php _e('Duplicate', 'themify') ?></span>
				</a></li>
			<li class="tb_row_action_more">
				<span class="ti-more"></span>
				<ul>
					<li>
						<a href="#" class="themify_builder_export_component ti-export themify-tooltip-bottom" data-component="row">
							<?php _e('Export', 'themify') ?>
						</a>
					 </li>
					<li>
						<a href="#" class="themify_builder_import_component ti-import themify-tooltip-bottom" data-component="row">
							<?php _e('Import', 'themify') ?>
						</a>
					</li>
					<li>
						<a href="#" class="themify_builder_copy_component ti-files themify-tooltip-bottom" data-component="row">
							<?php _e('Copy', 'themify') ?>
						</a>
					</li>
					<li>
						<a href="#" class="themify_builder_paste_component ti-clipboard themify-tooltip-bottom" data-component="row">
						   <?php _e('Paste', 'themify') ?>
						</a>
					</li>
					<li>
						<a href="#" class="tb_visibility_component ti-eye themify-tooltip-bottom">
						   <?php _e('Visibility', 'themify') ?>
						</a>
					</li>
				</ul>
			</li>
			<li><a href="#" class="themify_delete themify-tooltip-bottom" data-component="row">
					<span class="ti-close"></span>
					<span class="themify_tooltip"><?php _e('Delete', 'themify') ?></span>
				</a></li>
			<li class="separator"></li>
			<li><a href="#" class="themify_builder_toggle_row">
					<span class="ti-angle-up"></span>
					<span class="themify_tooltip"><?php _e('Toggle Row', 'themify') ?></span>
				</a></li>
		</ul>
	</div>
	<div class="row_inner"></div>
	<div class="tb_row_btn_plus"></div>
</script>
<script type="text/html" id="tmpl-builder_subrow_item">
	<div class="themify_builder_subrow_top tb_top">
			<?php Themify_Builder_Model::grid('subrow'); ?>
			<ul class="themify_builder_subrow_action">
				<li class="menu_icon"><a href="javascript:void(0);" class="js-subrow-menu-icon"><span class="ti-menu"></span></a>
					<ul class="subrow_action">
						<li><a href="#" class="themify_builder_export_component themify-tooltip-bottom" data-component="subrow">
								<span class="ti-export"></span> <?php _e('Export', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Export', 'themify') ?></span>
							</a></li>
						<li><a href="#" class="themify_builder_import_component themify-tooltip-bottom" data-component="subrow">
								<span class="ti-import"></span> <?php _e('Import', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Import', 'themify') ?></span>
							</a></li>
						<li class="separator"></li>
						<li>
							<a href="#" class="themify_builder_copy_component themify-tooltip-bottom" data-component="subrow">
								<span class="ti-files"></span> <?php _e('Copy', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Copy', 'themify') ?></span>
							</a>
						</li>
						<li><a href="#" class="themify_builder_paste_component themify-tooltip-bottom" data-component="subrow">
								<span class="ti-clipboard"></span> <?php _e('Paste', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Paste', 'themify') ?></span>
							</a>
						</li>
						<li class="separator"></li>
						<li><a href="#" class="themify_builder_style_subrow themify-tooltip-bottom">
								<span class="ti-brush"></span> <?php _e('Styling', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Styling', 'themify') ?></span>
							</a></li>
						<li><a href="#" class="themify_duplicate themify-tooltip-bottom">
								<span class="ti-layers"></span> <?php _e('Duplicate', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Duplicate', 'themify') ?></span>
							</a></li>
						<li><a href="#" class="themify_delete themify-tooltip-bottom" data-component="subrow">
								<span class="ti-close"></span> <?php _e('Delete', 'themify') ?>
								<span class="themify_tooltip"><?php _e('Delete', 'themify') ?></span>
							</a>
						</li>
					</ul>
				</li>
			</ul>
	</div>
	<div class="subrow_inner"></div>
</script>
<script type="text/html" id="tmpl-builder_column_item">
	<div class="themify_grid_drag themify_drag_right"></div>
	<div class="themify_grid_drag themify_drag_left"></div>
	<ul class="themify_builder_column_action">
		<li class="menu_icon"><a href="javascript:void(0);" class="js-column-menu-icon"><span class="ti-menu"></span></a>
			<ul>
				<li>
					<a href="#" class="themify_builder_option_column themify-tooltip-bottom">
						<span class="ti-brush"></span> 
						<?php _e('Styling', 'themify'); ?>
						<span class="themify_tooltip"><?php _e('Styling', 'themify') ?></span>
					</a>
				</li>
				<li class="separator"></li>
				<li>
					<a href="#" class="themify_builder_export_component themify-tooltip-bottom" data-component="{{ data.component_name }}">
						<span class="ti-export"></span> <?php _e('Export', 'themify'); ?>
						<span class="themify_tooltip"><?php _e('Export', 'themify') ?></span>
					</a>
				</li>
				<li>
					<a href="#" class="themify_builder_import_component themify-tooltip-bottom" data-component="{{ data.component_name }}">
						<span class="ti-import"></span>
						<?php _e('Import', 'themify'); ?>
						<span class="themify_tooltip"><?php _e('Import', 'themify') ?></span>
					</a>
				</li>
				<li class="separator"></li>
				<li>
					<a href="#" class="themify_builder_copy_component themify-tooltip-bottom" data-component="{{ data.component_name }}">
						<span class="ti-files"></span> 
						<?php _e('Copy', 'themify'); ?>
						<span class="themify_tooltip"><?php _e('Copy', 'themify') ?></span>
					</a>
				</li>
				<li>
					<a href="#" class="themify_builder_paste_component themify-tooltip-bottom" data-component="{{ data.component_name }}">
						<span class="ti-clipboard"></span> 
						<?php _e('Paste', 'themify'); ?>
						<span class="themify_tooltip"><?php _e('Paste', 'themify') ?></span>
					</a>
				</li>
				<li class="separator last-sep"></li>
			</ul>
		</li>
		<li class="themify_builder_column_dragger_li"><a href="javascript:void(0);" class="themify_builder_column_dragger"><span class="ti-arrows-horizontal"></span></a></li>
	</ul>
	<div class="themify_module_holder"></div>
</script>
<script type="text/html" id="tmpl-builder_module_item_draggable">
	<div class="themify_builder_module_outer <# data.favorite && print( 'favorited' ) #>">
		<div class="themify_builder_module module-type-{{data.slug}}" data-type="{{data.type}}" data-module-slug="{{data.slug}}" data-module-name="{{data.name}}">
			<span class="themify_module_favorite ti-star"></span>
			<strong class="module_name">{{data.name}}</strong> <a href="#" class="add_module_btn" title="<?php esc_attr_e( 'Add module', 'themify' );?>"></a>
		</div>
	</div>
</script>