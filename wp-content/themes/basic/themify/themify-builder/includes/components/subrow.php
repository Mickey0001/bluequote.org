<?php

class Themify_Builder_Component_Subrow extends Themify_Builder_Component_Base {
	public function get_name() {
		return 'subrow';
	}
        
        public function get_label(){
            return __('Sub Row Styling', 'themify');
        }
        
        

	/**
	 * Get template Sub-Row.
	 * 
	 * @param int $rows 
	 * @param int $cols 
	 * @param int $index 
	 * @param array $mod 
	 * @param string $builder_id 
	 * @param boolean $echo 
	 */
	public static function template( $rows, $cols, $index, $mod, $builder_id, $echo = false) {
                $print_sub_row_classes = array();
                $count = 0;
                $is_styling = !empty($mod['styling']);
                if($is_styling){
                    if (!empty($mod['styling']['background_repeat'])) {
                        $print_sub_row_classes[] = $mod['styling']['background_repeat'];
                    }
                    if(isset($mod['styling']['background_type']) && $mod['styling']['background_type']==='image' && isset($mod['styling']['background_zoom']) && $mod['styling']['background_zoom']==='zoom' && $mod['styling']['background_repeat']==='repeat-none'){
                        $print_sub_row_classes[] = 'themify-bg-zoom';
                    }
                    if (!empty($mod['styling']['custom_css_subrow'])) {
                        $print_sub_row_classes[] = $mod['styling']['custom_css_subrow'];
                    }
                }
                if(!Themify_Builder::$frontedit_active){
                    $count = !empty( $mod['cols'] )?count($mod['cols']):0;
                    $print_sub_row_classes[] ='sub_row_' . $rows . '-' . $cols . '-' . $index;
                    $row_content_classes = array();
                    if (!empty($mod['gutter']) && $mod['gutter']!=='gutter-default') {
                        $row_content_classes[] = $mod['gutter'];
                    }
                    if(!empty($mod['column_alignment'])){
                        $row_content_classes[] = $mod['column_alignment'] ;
                    }
                    if($count>0){
                        $row_content_attr = self::get_directions_data($mod,$count);
                        $order_classes = self::get_order($count);
                        $is_phone = themify_is_touch('phone');
                        $is_tablet = !$is_phone && themify_is_touch('tablet');
                        $is_right = false;
                        if($is_tablet){
                            $is_right = isset($row_content_attr['data-tablet_dir']) || isset($row_content_attr['data-tablet_landscape_dir']);
                            if(isset($row_content_attr['data-col_tablet']) || isset($row_content_attr['data-col_tablet_landscape'])){
                                $row_content_classes[] = isset($row_content_attr['data-col_tablet_landscape'])?$row_content_attr['data-col_tablet_landscape']:$row_content_attr['data-col_tablet'];
                            }
                        }
                        elseif($is_phone){
                            $is_right = isset($row_content_attr['data-mobile_dir']);
                            if(isset($row_content_attr['data-col_mobile'])){
                                $row_content_classes[] = $row_content_attr['data-col_mobile'];
                            }
                        }
                        else{
                            $is_right = isset($row_content_attr['data-desktop_dir']);
                        }
                        if($is_right){
                            $row_content_classes[] = 'direction-rtl';
                            $order_classes = array_reverse($order_classes);
                        }
                    }
                    
                    $row_content_classes = implode(' ',$row_content_classes);
                }
		$print_sub_row_classes = implode(' ', $print_sub_row_classes);
		
		// background video
                $video_data = $is_styling && Themify_Builder_Model::is_premium()?Themify_Builder_Include::get_video_background($mod['styling']):'';

		if ( ! $echo ) {
			$output = PHP_EOL; // add line break
			ob_start();
		}

		// Start Sub-Row Render ######
		?>
		<div class="themify_builder_sub_row module_subrow clearfix <?php echo esc_attr($print_sub_row_classes)?>"<?php echo $video_data?>>
		<?php
			if ($is_styling) {
                            $mod['row_order'] = $index;
                            $sub_row_order = $rows . '-' . $cols . '-' . $index;
                            do_action('themify_builder_background_styling',$builder_id,$mod,$sub_row_order,'subrow');
                           
			}
		?>
                    <div class="subrow_inner<?php if(!Themify_Builder::$frontedit_active):?> <?php echo $row_content_classes?><?php endif;?>" <?php if(!empty($row_content_attr)):?> <?php echo self::get_element_attributes($row_content_attr)?><?php endif;?>>
                        <?php 
                        if ($count>0) {
                                foreach ($mod['cols'] as $col_key => $sub_col) {
                                    Themify_Builder_Component_Column::template_sub_column( $rows, $cols, $index, $col_key, $sub_col, $builder_id,$order_classes, true );
                                }
                        }
                        ?>
                    </div>
                </div><!-- /themify_builder_sub_row -->
                <?php
		// End Sub-Row Render ######

		if ( ! $echo ) {
			$output .= ob_get_clean();
			// add line break
			$output .= PHP_EOL;
			return $output;
		}
	}
}