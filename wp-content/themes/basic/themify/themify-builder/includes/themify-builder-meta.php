<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Main Meta Box HTML
 */
global $post;
?>

<div class="themify_builder themify_builder_admin clearfix">
	
	<?php include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-module-panel.php'; ?>
	
	<div id="tb_scroll_anchor"></div>
	<div id="themify_builder_module_tmp"></div>
	<!-- /themify_builder_module_panel -->

	<div class="themify_builder_row_panel clearfix">

		<div id="themify_builder_row_wrapper" class="themify_builder_row_js_wrapper themify_builder_editor_wrapper" data-postid="<?php echo $post->ID; ?>"></div> <!-- /#themify_builder_row_wrapper - Load by js later -->

	</div>
	<!-- /themify_builder_row_panel -->

	<div style="display: none;">
		<?php
			wp_editor( ' ', 'tb_lb_hidden_editor' );
		?>
	</div>

</div>
<!-- /themify_builder -->