<script type="text/html" id="tmpl-builder_module_item">
	<div class="module_menu">
		<div class="menu_icon"></div>
                <ul class="tb_down">
                    <li>
                        <a href="#" class="themify_module_options">
                            <?php _e('Edit', 'themify') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="themify_builder_module_styling ti-brush">
                            <?php _e('Styling', 'themify') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#"  class="themify_module_duplicate themify_duplicate">
                            <?php _e('Duplicate', 'themify') ?>
                        </a>
                    </li>
                    <li class="separator"><div></div></li>
                    <li><a href="#" class="themify_builder_export_component ti-export" data-component="module">
                            <?php _e('Export', 'themify') ?>
                        </a></li>
                    <li>
                        <a href="#" class="themify_builder_import_component ti-import" data-component="module">
                            <?php _e('Import', 'themify') ?>
                        </a>
                    </li>
                    <li class="separator">
                        <div></div>
                    </li>
                    <li>
                        <a href="#" class="themify_builder_copy_component ti-files" data-component="module">
                            <?php _e('Copy', 'themify') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="themify_builder_paste_component ti-clipboard" data-component="module">
                            <?php _e('Paste', 'themify') ?>
                        </a>
                    </li>
                    <li class="separator"><div></div></li>
                    <li>
                        <a href="#" class="tb_visibility_component ti-eye">
                            <?php _e('Visibility', 'themify') ?>
                        </a>
                    </li>
                    <li class="separator"><div></div></li>
                    <li>
                        <a href="#" class="themify_module_delete themify_delete" data-component="module">
                            <?php _e('Delete', 'themify') ?>
                        </a>
                    </li>
                </ul>
	</div>
	<div class="module_label">
		<strong class="module_name">{{ data.name }}</strong>
		<em class="module_excerpt">{{ data.excerpt }}</em>
	</div>
</script>
