<div id="tmpl-builder-restriction"<?php if($takeover):?> class="tb-istakeover"<?php endif;?>>
    <div class="tb-locked-popup">
        <div class="tb-locked-avatar">
            <?php echo get_avatar($this->restriction_id,64,'mystery')?>
        </div>
        <div class="tb-locked-content">
            <div class="tb-locked-info">
                <?php   $string = $takeover?__('<strong>%s</strong> has taken over and is currently editing.<br/> Your latest changes were saved as a revision','themify'):__('<strong>%s</strong> is already editing this Builder. Do you want to take over?','themify');
                        printf($string,$data->display_name); 
                        if($takeover){
                            $current_user = wp_get_current_user();
                            echo '(<strong class="tb-locked-revision">'.$current_user->user_login.'_'.date('Y-m-d-H-i-s').'</strong></i>)';
                        }
                        if(!isset($id)){
                            $id = null;
                        }
                    ?>
            </div>
            <div class="tb-locked-takeover">
                <a class="tb-button" href="<?php echo admin_url('edit.php?post_type='.  get_post_type($id))?>"><?php _e('All Pages','themify')?></a>
                <?php if(!$takeover):?>
                    <button class="tb-button tb-locked-btn"><?php _e('Take over','themify'); ?></button>
                <?php endif;?>
            </div>
        </div>
        <?php if(!$takeover):?>
            <span class="tb-locked-close">X</span>
        <?php endif;?>
    </div>
</div>