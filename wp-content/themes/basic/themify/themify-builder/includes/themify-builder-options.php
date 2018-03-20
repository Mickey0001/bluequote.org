<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function themify_builder_tabs($field, $module_name, $styling = false) {
    $id = $field['id'];
    $first = true;
    ?>
    <div class="themify_builder_tabs" id="themify_builder_tabs_<?php echo $id; ?>">
        <ul class="clearfix">
            <?php foreach ($field['tabs'] as $key => $tab) : ?>
                <li <?php if ($first === true): ?><?php $first = false; ?> class="current"<?php endif; ?>><a href="#tb_<?php echo $id . '_' . $key; ?>"> <?php echo esc_html($tab['label']); ?> </a></li>
            <?php endforeach; ?>
        </ul>

        <?php foreach ($field['tabs'] as $key => $tab) : ?>
            <div id="tb_<?php echo $id . '_' . $key; ?>" class="themify_builder_tab">
                <?php
                if ($styling) {
                    themify_render_styling_settings($tab['fields']);
                } else {
                    themify_builder_module_settings_field($tab['fields'], $module_name);
                }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function themify_render_styling_settings($fields) {
    foreach ($fields as $styling):

        if ($styling['type'] === 'tabs') {
            themify_builder_tabs($styling, '', true);
            continue;
        }
        $is_premium = isset($styling['is_premium']) && !$styling['is_premium'];
        $field_class = array('themify_builder_field');
        $is_seperator = $styling['type'] === 'separator';
        if (!empty($styling['wrap_with_class'])) {
            $field_class[] = $styling['wrap_with_class'];
        }
        if (isset($styling['id'])) {
            $field_class[] = $styling['id'];
        }
        if($is_premium){
            $field_class[] = 'themify_builder_lite';
        }
        echo !$is_seperator ? '<div class="' . implode(' ', $field_class) . '">' : '';
        if (isset($styling['label'])) {
            echo '<div class="themify_builder_label">' . esc_html($styling['label']) . '</div>';
        }
        if(!$is_seperator){
            echo '<div class="themify_builder_input">';
            if($is_premium){
                 echo '<span class="themify_lite_tooltip"></span>';
            }
        }
        if ($styling['type'] === 'multi') {
            foreach ($styling['fields'] as $field) {
                themify_builder_styling_field($field);
            }
        } else {
            themify_builder_styling_field($styling);
        }
        echo!$is_seperator ? '</div></div>' : ''; // themify_builder_input,themify_builder_field

    endforeach;
}

function themify_builder_get_binding_data($field) {
    if (isset($field['binding'])) {
        return " data-binding='" . json_encode($field['binding']) . "'";
    }
}

function themify_builder_get_control_binding_data($field) {
    $return = '';
    if (isset($field['type'])) {
        $default_type = $field['type'];
        if (!isset($field['render_callback'])) {
            $field['render_callback'] = array();
        }
        elseif(isset($field['render_callback']['binding']) && $field['render_callback']['binding']===false){
            return;
        }
        if (in_array($default_type, array('text', 'textarea', 'select_menu', 'select', 'layout_part_select', 'selectbasic', 'widget_select', 'image', 'icon','widgetized_select'), true)) {
            if (!isset($field['render_callback']['event']) && (($default_type === 'textarea' && (!isset($field['class']) || strpos($field['class'], 'tb-shortcode-input') === false)) || ($default_type === 'text' && !isset($field['colorpicker']) && !isset($field['iconpicker'])))) {
                $field['render_callback']['event'] = 'keyup';
            }
            if (isset($field['render_callback']['event']) && $field['render_callback']['event'] !== 'change') {
                $return = ' data-control-event="' . $field['render_callback']['event'] . '"';
            }
            if (isset($field['colorpicker'])) {
                $default_type = '';
                unset($field['render_callback']['control_type']);
            } else {
                $default_type = 'change';
            }
        } elseif ('builder' === $default_type) {
            $default_type = 'repeater';
        }
        if (!isset($field['render_callback']['binding'])) {
            $field['render_callback']['binding'] = 'refresh';
        }
        if (!isset($field['render_callback']['control_type'])) {
            $field['render_callback']['control_type'] = $default_type;
        }
        
        if(isset($field['render_callback']['live-selector']) && ($field['type']==='text' || $field['type']==='wp_editor' || $field['type']==='textarea')){
            $return.=' data-live-selector="'.$field['render_callback']['live-selector'].'"';
        }
        if($field['render_callback']['binding']){
            $return.= ' data-control-binding="' . $field['render_callback']['binding'] . '"';
        }
        if($field['render_callback']['control_type']!=='change'){
           $return.= ' data-control-type="' . $field['render_callback']['control_type'] . '"'; 
        }
        if (!empty($field['render_callback']['repeater'])) {
            $return .= ' data-control-repeater="' . $field['render_callback']['repeater'] . '"';
        }
        if (isset($field['render_callback']['selector'])) {
            $return .= ' data-control-selector="' . $field['render_callback']['selector'] . '"';
        }
    }

    return $return;
}

function themify_builder_module_settings_field_builder($field) {
    ?>
    <?php foreach ($field['options'] as $option): ?>
        <?php if (isset($option['separated']) && $option['separated'] === 'top'): ?>
            <hr />
        <?php endif; ?>
        <?php if ($option['type'] === 'multi') : ?>
            <div class="themify_builder_field<?php echo !empty($option['wrap_with_class']) ?' '.$option['wrap_with_class'] : ''; ?>">

                <?php if (!empty($option['label'])): ?>
                    <div class="themify_builder_label"><?php echo esc_html($option['label']); ?></div><!-- /themify_builder_input_title -->
                <?php endif; ?>

                <div class="<?php echo $option['id'], ' tb_multi_fields tb_fields_count_', count($option['options']) ?>">
                    <?php themify_builder_module_settings_field_builder($option); ?>
                </div>
            </div>
            <?php
            continue;
        endif;
        ?>
        <div class="themify_builder_field<?php echo !empty($option['wrap_with_class']) ?' '.$option['wrap_with_class'] : ''; ?>">

            <?php if (!empty($option['label'])): ?>
                <div class="themify_builder_label"><?php echo esc_html($option['label']); ?></div><!-- /themify_builder_input_title -->
            <?php endif; ?>

            <div class="themify_builder_input"<?php echo ( 'wp_editor' === $option['type'] ) ? ' style="width:100%;"' : ''; ?>>
                <?php if ($option['type'] === 'text'): ?>

                    <?php if (!empty($option['colorpicker'])) : ?>
                        <div class="minicolors_wrapper">
                            <div class="minicolors minicolors-theme-default">
                                <input type="text" name="<?php echo $option['id']; ?>" class="<?php echo isset($option['class']) ? $option['class'] : ''; ?> minicolors-input tb_lb_option_child" data-input-id="<?php echo $option['id']; ?>" <?php
                                echo themify_builder_get_binding_data($option);
                                echo themify_builder_get_control_binding_data($option);
                                ?>  />
                                <span class="minicolors-swatch">
                                    <span class="minicolors-swatch-color"></span>
                                </span>
                            </div> 
                            <input type="text" class="<?php echo isset($option['class']) ? $option['class'] : ''; ?> color_opacity" <?php echo themify_builder_get_binding_data($option); ?> />
                        </div>
                    <?php else : ?>
                        <input name="<?php echo $option['id']; ?>" class="<?php echo isset($option['class']) ? $option['class'] : ''; ?> tb_lb_option_child" type="text" data-input-id="<?php echo $option['id']; ?>" <?php
                        echo themify_builder_get_binding_data($option);
                        echo themify_builder_get_control_binding_data($option);
                        ?> />
                               <?php if (isset($option['iconpicker']) && $option['iconpicker'] == true) : ?>
                            <a class="button button-secondary themify_fa_toggle" href="#"><?php _e('Insert Icon', 'themify'); ?></a>
                        <?php endif; ?>
                        <?php
                        if (isset($option['after'])) : echo wp_kses_post($option['after']);
                        endif;
                        ?>
                    <?php endif; ?>

                <?php elseif ('image' === $option['type'] || 'video' === $option['type'] || 'audio' === $option['type']): ?>
                    <input data-input-id="<?php echo $option['id'] ?>" name="<?php echo $option['id'] ?>" placeholder="<?php if (isset($option['value'])) echo esc_attr($option['value']); ?>" class="<?php echo isset($option['class']) ? $option['class'] : ''; ?> themify-builder-uploader-input tb_lb_option_child" type="text"<?php echo themify_builder_get_control_binding_data($option); ?> /><br />
                    <div class="small">

                        <?php if (is_multisite() && !is_upload_space_available()): ?>
                            <?php echo sprintf(__('Sorry, you have filled your %s MB storage quota so uploading has been disabled.', 'themify'), get_space_allowed()); ?>
                        <?php else: ?>
                            <?php $extension = 'video' === $option['type'] ? wp_get_video_extensions() : ('audio' === $option['type'] ? wp_get_audio_extensions() : false); ?>
                            <div class="themify-builder-plupload-upload-uic tb-upload-btn" id="<?php echo $option['id'] ?>themify-builder-plupload-upload-ui"<?php if ($extension): ?> data-extensions="<?php echo esc_attr(implode(',', $extension)) ?>"<?php endif; ?>>
                                <input id="<?php echo $option['id'] ?>themify-builder-plupload-browse-button" type="button" value="<?php _e('Upload', 'themify'); ?>" class="builder_button" />
                                <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($option['id'] . 'themify-builder-plupload'); ?>"></span>
                            </div> <?php _e('or', 'themify'); ?> <a href="#" data-library-type="<?php echo $option['type'] ?>" class="themify-builder-media-uploader tb-upload-btn" data-uploader-title="<?php esc_attr_e('Upload an Image', 'themify') ?>" data-uploader-button-text="<?php esc_attr_e('Insert file URL', 'themify') ?>"><?php _e('Browse Library', 'themify') ?></a>
                        <?php endif; ?>

                    </div>

                    <?php if ('image' === $option['type']): ?>
                        <p class="thumb_preview">
                            <span class="img-placeholder"></span>
                            <a href="#" class="themify_builder_icon small delete themify-builder-delete-thumb"></a>
                        </p>
                        <?php
                    elseif (isset($option['description'])):
                        echo '<small>' . wp_kses_post($option['description']) . '</small>';
                    endif;
                    ?>

                <?php elseif ($option['type'] === 'textarea'): ?>
                    <textarea name="<?php echo $option['id']; ?>" class="<?php echo $option['class']; ?> tb_lb_option_child" <?php echo (isset($option['rows'])) ? 'rows="' . esc_attr($option['rows']) . '"' : ''; ?> data-input-id="<?php echo $option['id']; ?>"<?php echo themify_builder_get_control_binding_data($option); ?>></textarea><br />

                    <?php if (isset($option['radio'])): ?>
                        <div data-input-id="<?php echo $option['radio']['id']; ?>" class="tb_lb_option_child tb-radio-input-container">
                            <?php echo esc_html($option['radio']['label']); ?>
                            <?php foreach ($option['radio']['options'] as $k => $v): ?>
                                <input id="<?php echo $option['radio']['id'] . '_' . $k; ?>" type="radio" name="<?php echo $option['radio']['id']; ?>" class="themify-builder-radio-dnd" value="<?php echo $k; ?>" />
                                <label for="<?php echo$option['radio']['id'] . '_' . $k; ?>" class="pad-right themify-builder-radio-dnd-label"><?php echo $k; ?></label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; // endif radio input    ?>

                <?php elseif ($option['type'] === 'select') : ?>
                    <div class="selectwrapper">
                        <select data-input-id="<?php echo $option['id'] ?>" name="<?php echo $option['id'] ?>" class="tb_lb_option_child" <?php echo themify_builder_get_binding_data($option), themify_builder_get_control_binding_data($option); ?>>
                            <?php
                            foreach ($option['options'] as $key => $value) {
                                $selected = ( isset($option['default']) && $option['default'] == $value ) ? ' selected="selected"' : '';
                                echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($value) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                <?php elseif ('layout' === $option['type']): ?>

                    <p data-input-id="<?php echo $option['id'] ?>" class="tb_lb_option_child themify-layout-icon<?php if (isset($option['class'])): ?> <?php echo $option['class'] ?><?php endif; ?>"<?php echo themify_builder_get_binding_data($option), themify_builder_get_control_binding_data($option); ?>>

                        <?php foreach ($option['options'] as $opt): ?>
                            <a href="#" id="<?php echo $opt['value'] ?>" class="tfl-icon">
                                <?php if (isset($option['mode']) && $option['mode'] === 'sprite' && strpos($opt['img'], '.png') === false): ?>
                                    <span class="tb-sprite tb-<?php echo $opt['img'] ?>"></span>
                                <?php else: ?>
                                    <?php $image_url = ( filter_var($opt['img'], FILTER_VALIDATE_URL) ) ? $opt['img'] : THEMIFY_BUILDER_URI . '/img/builder/' . $opt['img']; ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($opt['label']); ?>" />
                                <?php endif; ?>
                                <span class="themify_tooltip"><?php echo $opt['label']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </p>

                    <?php
                elseif ('wp_editor' === $option['type']):
                    $editor_class = $option['class'] . ' tb_lb_wp_editor tb_lb_option_child';
                    ?>
                    <div id="wp-<?php echo $option['id'] ?>-wrap" class="wp-core-ui wp-editor-wrap tmce-active">
                        <div id="wp-<?php echo $option['id'] ?>-editor-tools" class="wp-editor-tools">
                            <div id="wp-<?php echo $option['id'] ?>-media-buttons" class="wp-media-buttons">
                                <button type="button" class="button insert-media add_media" data-editor="<?php echo $option['id'] ?>"><span class="wp-media-buttons-icon"></span><?php _e('Add Media', 'themify') ?></button>
                            </div>
                            <div class="wp-editor-tabs">
                                <button type="button" id="<?php echo $option['id'] ?>-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="<?php echo $option['id'] ?>"><?php _e('Visual', 'themify') ?></button>
                                <button type="button" id="<?php echo $option['id'] ?>-html" class="wp-switch-editor switch-html" data-wp-editor-id="<?php echo $option['id'] ?>"><?php _e('Text', 'themify') ?></button>
                            </div>
                        </div>
                        <div id="wp-<?php echo $option['id'] ?>-editor-container" class="wp-editor-container">
                            <div id="qt_<?php echo $option['id'] ?>_toolbar" class="quicktags-toolbar"></div>
                            <textarea  rows="12" cols="40" data-input-id="<?php echo $option['id'] ?>" class="<?php echo $editor_class ?>" <?php echo themify_builder_get_binding_data($option), themify_builder_get_control_binding_data($option); ?> id="<?php echo $option['id'] ?>" name="<?php echo $option['id'] ?>"></textarea>
                        </div>
                    </div>

                <?php elseif ('checkbox' === $option['type']): ?>
                    <?php
                    if (isset($option['before'])) : echo wp_kses_post($option['before']);
                    endif;
                    $option_js_wrap = (isset($option['option_js']) && $option['option_js'] == true) ? ' tb-option-checkbox-enable' : '';
                    if ($option_js_wrap !== '' && isset($option['reverse'])) {
                        $option_js_wrap.=' tb-option-checkbox-revert';
                    }
                    ?>

                    <div id="<?php echo $option['id'] ?>"  class="tb_lb_option_child themify-checkbox<?php echo $option_js_wrap ?>" data-input-id="<?php echo $option['id'] ?>" <?php echo themify_builder_get_control_binding_data( $option ); ?>>
                        <?php foreach ($option['options'] as $opt): ?>
                            <?php
                            $checkbox_checked = '';
                            if (isset($option['default']) && is_array($option['default'])) {
                                if (in_array($opt['name'], $option['default'])) {
                                    $checkbox_checked = 'checked="checked"';
                                }
                            } elseif (isset($option['default'])) {
                                $checkbox_checked = checked($option['default'], $opt['name'], false);
                            }
                            $data_el = (isset($option['option_js']) && $option['option_js'] == true) ? 'data-selected="tb-checkbox-element-' . $opt['name'] . '"' : '';
                            ?>
                            <label class="pad-right"><input id="<?php echo $option['id'] . '_' . $opt['name']; ?>" name="<?php echo $option['id'] ?>[]" type="checkbox" class="<?php echo isset($option['class']) ? $option['class'] : '' ?> tb-checkbox" value="<?php echo $opt['name']; ?>" <?php echo $checkbox_checked . ' ' . $data_el; ?> /><?php echo $opt['value'] ?></label>
                            <?php if (isset($opt['help'])): ?>
                                <small><?php echo wp_kses_post($opt['help']); ?></small>
                            <?php endif; ?>

                            <?php if (!isset($option['new_line']) || $option['new_line'] == true): ?>
                                <br />
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>
                    <?php
                    if (isset($field['after'])) : echo wp_kses_post($field['after']);
                    endif;
                    ?>

                <?php elseif ('radio' === $option['type']): ?>
                    <?php $option_js = !empty($option['option_js']) ?>
                    <div data-input-id="<?php echo $option['id'] ?>" class="tb_lb_option_child tb-radio-input-container<?php if ($option_js): ?> tb-option-radio-enable<?php endif; ?>"<?php echo themify_builder_get_control_binding_data($option); ?>>
                        <?php foreach ($option['options'] as $k => $v): ?>
                            <?php $checked = isset($option['default']) && $k === $option['default'] ? 'checked="checked" data-checked="checked"' : ''; ?>
                            <?php $data_el = $option_js ? 'data-selected="tb-group-element-' . $k . '"' : ''; ?>
                            <input <?php if (is_array($v) && !empty($v['disable'])): ?>disabled="disabled"<?php endif; ?> id="<?php echo $option['id'] . '_' . $k; ?>" type="radio" name="<?php echo $option['id'] ?>" class="themify-builder-radio-dnd" value="<?php echo $k; ?>" <?php echo " $checked $data_el"; ?>/>
                            <label for="<?php echo $option['id'] . '_' . $k; ?>" class="pad-right themify-builder-radio-dnd-label"><?php echo $v; ?></label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($option['help'])): ?>
                    <?php if (isset($option['help']['new_line'])): ?>
                        <br />
                    <?php endif; ?>
                    <small><?php echo wp_kses_post($option['help']['text']); ?></small>
                <?php endif; ?>

            </div><!-- /themify_builder_input -->

        </div>
        <!-- /themify_builder_field -->

        <?php
    endforeach;
}

if (!function_exists('themify_builder_module_settings_field')) {

    /**
     * Module Settings Fields
     * @param array $module_options 
     * @return string
     */
    function themify_builder_module_settings_field($module_options, $module_name) {
        foreach ($module_options as $field):
            if (!empty($field['hide'])) {
                continue;
            }
            $id = isset($field['id']) ? $field['id'] : '';

            // custom field types used by 3rd party module authors
            if (function_exists("themify_builder_field_{$field['type']}")) {
                call_user_func("themify_builder_field_{$field['type']}", $field, $module_name);
                continue;
            } elseif ($field['type'] === 'group') { // simple wrapper for multiple related options
                $classes = !empty($field['wrap_with_class']) ? $field['wrap_with_class'] : '';
                
                echo '<div class="themify_builder_field ' . $id . ' ' . $classes . '">';
                themify_builder_module_settings_field($field['fields'], $module_name);
                echo '</div>';
                continue;
            } else if ($field['type'] === 'tabs') {
                themify_builder_tabs($field, $module_name);
                continue;
            }


            if (isset($field['separated']) && $field['separated'] === 'top'):
                ?>
                <hr />
            <?php endif; ?>

            <?php if ($field['type'] !== 'builder'): ?>
                <div class="themify_builder_field <?php echo $id; ?> <?php echo (!empty($field['wrap_with_class'])) ? $field['wrap_with_class'] : ''; ?>">
                <?php endif; ?>

                <?php
                if ($field['type'] === 'separator') {
                    echo isset($field['meta']['html']) && '' != $field['meta']['html'] ? $field['meta']['html'] : '<hr class="meta_fields_separator" />';
                    echo '</div><!-- .themify_builder_field -->';
                    continue;
                }
                ?>

                <?php if (isset($field['id']) && !empty($field['label'])): ?>
                    <div class="themify_builder_label"><?php echo esc_html($field['label']); ?></div>
                <?php endif; ?>

                <?php
                if ($field['type'] === 'multi') {
                    echo '<div class="' . $id . ' tb_multi_fields tb_fields_count_' . count($field['fields']). (!empty($field['pushed']) ? ' ' . $field['pushed'] : '' ) . '">';
                    foreach ($field['fields'] as $_field) {
                        themify_builder_module_settings_field(array($_field), $module_name);
                    }
                    echo '</div>';
                } else if ('wp_editor' === $field['type']) {
                    $editor_class = $field['class'] . ' tb_lb_wp_editor tb_lb_option';
                    ?>
                    <div id="wp-<?php echo $field['id'] ?>-wrap" class="wp-core-ui wp-editor-wrap tmce-active">
                        <div id="wp-<?php echo $field['id'] ?>-editor-tools" class="wp-editor-tools">
                            <div id="wp-<?php echo $field['id'] ?>-media-buttons" class="wp-media-buttons">
                                <button type="button" class="button insert-media add_media" data-editor="<?php echo $field['id'] ?>"><span class="wp-media-buttons-icon"></span><?php _e('Add Media', 'themify') ?></button>
                            </div>
                            <div class="wp-editor-tabs"><button type="button" id="<?php echo $field['id'] ?>-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="<?php echo $field['id'] ?>"><?php _e('Visual', 'themify') ?></button>
                                <button type="button" id="<?php echo $field['id'] ?>-html" class="wp-switch-editor switch-html" data-wp-editor-id="<?php echo $field['id'] ?>"><?php _e('Text', 'themify') ?></button>
                            </div>
                        </div>
                        <div id="wp-<?php echo $field['id'] ?>-editor-container" class="wp-editor-container">
                            <div id="qt_<?php echo $field['id'] ?>_toolbar" class="quicktags-toolbar"></div>
                            <textarea  autocomplete="off" rows="12"  cols="40" class="<?php echo $editor_class ?>" <?php echo themify_builder_get_binding_data($field), themify_builder_get_control_binding_data($field); ?> id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>"></textarea>
                        </div>
                    </div>
                    <?php
                } elseif ('builder' === $field['type']) {
                    ?>
                    <div<?php if(!empty($field['wrap_with_class'])):?> class="<?php echo $field['wrap_with_class']; ?>"<?php endif; ?>>
                        <hr />

                        <div id="<?php echo $field['id']; ?>" class="themify_builder_module_opt_builder_wrap themify_builder_row_js_wrapper tb_lb_option"<?php echo themify_builder_get_control_binding_data($field); ?>>

                            <div class="tb_repeatable_field clearfix">

                                <div class="tb_repeatable_field_top">
                                    <div class="row_menu">
                                        <div class="menu_icon"></div>
                                        <ul class="tb_down">
                                            <li><a href="#" class="themify_builder_duplicate_row"><?php _e('Duplicate', 'themify') ?></a></li>
                                            <li><a href="#" class="themify_builder_delete_row"><?php _e('Delete', 'themify') ?></a></li>
                                        </ul>
                                    </div>
                                    <!-- /row_menu -->
                                    <div class="toggle_row"></div><!-- /toggle_row -->
                                </div>
                                <!-- /row_top -->

                                <div class="tb_repeatable_field_content">
                                    <?php themify_builder_module_settings_field_builder($field); ?>
                                </div>

                            </div>
                            <!-- /tb_repeatable_field -->

                        </div>
                        <!-- /themify_builder_module_opt_builder_wrap -->

                        <p class="add_new"><a href="#"><span class="themify_builder_icon add"></span><?php echo isset($field['new_row_text']) ? $field['new_row_text'] : __('Add new row', 'themify'); ?></a></p>
                    </div>
                    <!-- /builder wrapper -->
                    <?php
                } else {
                    ?>
                    <div class="themify_builder_input<?php echo!empty($field['pushed']) ? ' ' . $field['pushed'] : ''; ?>">
                        <?php
                        $input_props = array();
                        $input_props['id'] = $field['id'];
                        $input_props['name'] = $input_props['id'];
                        $input_props['class'] = 'tb_lb_option';
                        if (isset($field['class']))
                            $input_props['class'] .= ' ' . $field['class'];

                        // validation rules
                        if (isset($field['required'])) {
                            $default_required = array(
                                'rule' => 'not_empty',
                                'message' => esc_html__('Please enter required field.', 'themify')
                            );
                            if (is_array($field['required'])) {
                                $field['required'] = wp_parse_args($field['required'], $default_required);
                            } else {
                                $field['required'] = $default_required;
                            }
                            $input_props['data-validation'] = $field['required']['rule'];
                            $input_props['data-error-msg'] = $field['required']['message'];
                        }
                        if (isset($field['data']) && is_array($field['data'])) {
                            foreach ($field['data'] as $data_key => $data_value) {
                                $input_props['data-' . $data_key] = $data_value;
                            }
                        }
                        $data = themify_builder_get_binding_data($field);
                        $control = themify_builder_get_control_binding_data($field);
                        ?>
                        <?php if ('text' === $field['type']): ?>

                            <?php if (!empty($field['colorpicker'])) : ?>
                                <?php
                                if (!isset($input_props['class'])) {
                                    $input_props['class'] = '';
                                }
                                $input_props['class'] .= ' minicolors-input';
                                ?>
                                <div class="minicolors_wrapper">
                                    <div class="minicolors minicolors-theme-default">
                                        <input type="text" <?php echo Themify_Builder_Component_Base::get_element_attributes($input_props); ?> value="<?php if (isset($field['value'])) echo esc_attr($field['value']); ?>"<?php echo $control; ?> />
                                        <span class="minicolors-swatch">
                                            <span class="minicolors-swatch-color"></span>
                                        </span>
                                    </div> 
                                    <input type="text" class="<?php
                                    if (!empty($field['class'])) {
                                        echo $field['class'];
                                    }
                                    ?> color_opacity" <?php echo $data; ?> />
                                </div>
                            <?php else : ?>
                                <input type="text" <?php echo Themify_Builder_Component_Base::get_element_attributes($input_props); ?> value="<?php if (isset($field['value'])) echo esc_attr($field['value']); ?>" <?php echo $data, $control; ?> />
                                <?php
                                if (isset($field['after'])) : echo wp_kses_post($field['after']);
                                endif;
                                ?>

                                <?php if (isset($field['unit'])): ?>
                                    <div class="selectwrapper">
                                        <select id="<?php echo $field['unit']['id']; ?>" class="tb_lb_option" <?php
                                        echo $data;
                                        echo themify_builder_get_control_binding_data($field['unit']);
                                        ?>>
                                            <?php foreach ($field['unit']['options'] as $u): ?>
                                                <option value="<?php echo esc_attr($u['value']); ?>" <?php echo ( isset($field['unit']['selected']) && $field['unit']['selected'] == $u['value'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_html($u['value']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; // unit  ?>
                            <?php endif; ?>

                        <?php elseif ('icon' === $field['type']): ?>
                            <input id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" value="<?php if (isset($field['value'])) echo esc_attr($field['value']); ?>" class="themify_field_icon <?php if (isset($field['class'])) echo esc_attr($field['class']); ?> tb_lb_option" type="text" <?php echo $data, $control; ?> />
                            <a class="button button-secondary themify_fa_toggle" href="#" data-target="#<?php echo $field['id']; ?>"><?php _e('Insert Icon', 'themify'); ?></a>

                        <?php elseif ('radio' === $field['type']): ?>
                            <?php $option_js = !empty($field['option_js']); ?>
                            <div id="<?php echo $field['id']; ?>" class="tb_lb_option tb-radio-input-container<?php echo $option_js ? ' tb-option-radio-enable' : ''; ?>"<?php echo $control; ?>>
                                <?php if (isset($field['before'])) echo esc_html($field['before']); ?>
                                <?php foreach ($field['options'] as $k => $v): ?>
                                    <?php
                                    $default_checked = (isset($field['default']) && $field['default'] == $k) ? 'checked="checked"' : '';
                                    $data_el = $option_js ? 'data-selected="tb-group-element-' . $k . '"' : '';
                                    ?>
                                    <input <?php if (!empty($v['disable']) && is_array($v)): ?>disabled="disabled"<?php endif; ?> id="<?php echo $field['id'] . '_' . $k; ?>" name="<?php echo $field['id']; ?>" type="radio" value="<?php echo esc_attr($k); ?>" <?php echo " $default_checked $data_el"; ?> <?php echo themify_builder_get_binding_data($field); ?>/>
                                    <label for="<?php echo $field['id'] . '_' . $k; ?>" class="pad-right"><?php echo wp_kses_post($v); ?></label>

                                    <?php if (!empty($field['break'])): ?>
                                        <br />
                                    <?php endif; ?>

                                <?php endforeach; ?>
                                <?php if (isset($field['after'])) echo esc_html($field['after']); ?>
                            </div>

                        <?php elseif ('layout' === $field['type']): ?>

                            <p id="<?php echo $field['id']; ?>" class="tb_lb_option themify-layout-icon<?php if (isset($field['class'])): ?> <?php echo $field['class'] ?><?php endif; ?>"<?php echo $data, $control; ?>>

                                <?php foreach ($field['options'] as $opt): ?>
                                    <a href="#" id="<?php echo $opt['value']; ?>" class="tfl-icon">
                                        <?php if (isset($field['mode']) && $field['mode'] === 'sprite' && strpos($opt['img'], '.png') === false): ?>
                                            <?php if (filter_var($opt['img'], FILTER_VALIDATE_URL)) : ?>
                                                <span class="tb-sprite" style="background-image: url('<?php echo $opt['img']; ?>')"></span>
                                            <?php else : ?>
                                                <span class="tb-sprite tb-<?php echo $opt['img'] ?>"></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php $image_url = ( filter_var($opt['img'], FILTER_VALIDATE_URL) ) ? $opt['img'] : THEMIFY_BUILDER_URI . '/img/builder/' . $opt['img']; ?>
                                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($opt['label']); ?>" />
                                        <?php endif; ?>
                                        <span class="themify_tooltip"><?php echo $opt['label']; ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </p>

                        <?php elseif ('image' === $field['type']): ?>
                            <input id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" placeholder="<?php if (isset($field['value'])) echo esc_attr($field['value']); ?>" class="<?php echo esc_attr($field['class']); ?> themify-builder-uploader-input tb_lb_option" type="text" <?php echo $data, $control; ?> /><br/>
                            <input type="hidden" name="<?php echo esc_attr($field['id'] . '_attach_id'); ?>" class="themify-builder-uploader-input-attach-id" value=""/>
                            <div class="small">

                                <?php if (is_multisite() && !is_upload_space_available()): ?>
                                    <?php echo sprintf(__('Sorry, you have filled your %s MB storage quota so uploading has been disabled.', 'themify'), get_space_allowed()); ?>
                                <?php else: ?>
                                    <div class="themify-builder-plupload-upload-uic tb-upload-btn" id="<?php echo $field['id']; ?>themify-builder-plupload-upload-ui">
                                        <input id="<?php echo $field['id']; ?>themify-builder-plupload-browse-button" type="button" value="<?php _e('Upload', 'themify'); ?>" class="builder_button" />
                                        <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($field['id'] . 'themify-builder-plupload'); ?>"></span>
                                    </div> <?php _e('or', 'themify') ?> <a href="#" class="themify-builder-media-uploader tb-upload-btn" data-uploader-title="<?php esc_attr_e('Upload an Image', 'themify') ?>" data-uploader-button-text="<?php esc_attr_e('Insert file URL', 'themify') ?>"><?php _e('Browse Library', 'themify') ?></a>
                                <?php endif; ?>

                            </div>

                            <p class="thumb_preview">
                                <span class="img-placeholder"></span>
                                <a href="#" class="themify_builder_icon small delete themify-builder-delete-thumb"></a>
                            </p>

                        <?php elseif ('checkbox' === $field['type']): ?>
                            <?php
                            if (isset($field['before'])) : echo wp_kses_post($field['before']);
                            endif;
                            $option_js_wrap = (isset($field['option_js']) && $field['option_js'] == true) ? ' tb-option-checkbox-enable' : '';
                            if ($option_js_wrap !== '' && isset($field['reverse'])) {
                                $option_js_wrap.=' tb-option-checkbox-revert';
                            }
                            ?>
                            <div id="<?php echo $field['id']; ?>" class="tb_lb_option themify-checkbox<?php echo $option_js_wrap ?>"<?php echo $control; ?>>
                                <?php foreach ($field['options'] as $opt): ?>
                                    <?php
                                    $checkbox_checked = '';
                                    if (isset($field['default']) && is_array($field['default'])) {
                                        if (in_array($opt['name'], $field['default'])) {
                                            $checkbox_checked = 'checked="checked"';
                                        }
                                    } elseif (isset($field['default'])) {
                                        $checkbox_checked = checked($field['default'], $opt['name'], false);
                                    }
                                    $data_el = !empty($field['option_js']) ? 'data-selected="tb-checkbox-element-' . $opt['name'] . '"' : '';
                                    ?>
                                    <input id="<?php echo $field['id'] . '_' . $opt['name']; ?>" name="<?php echo $field['id']; ?>[]" type="checkbox" class="<?php echo isset($opt['class']) ? $opt['class'] : '' ?> tb-checkbox" value="<?php echo esc_attr($opt['name']); ?>" <?php echo $checkbox_checked . ' ' . $data_el; ?> <?php echo $data; ?> />
                                    <label for="<?php echo $field['id'] . '_' . $opt['name']; ?>" class="pad-right"><?php echo wp_kses_post($opt['value']); ?></label>

                                    <?php if (isset($opt['help'])): ?>
                                        <small><?php echo wp_kses_post($opt['help']); ?></small>
                                    <?php endif; ?>

                                    <?php if (!isset($field['new_line']) || $field['new_line'] == true): ?>
                                        <br />
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            </div>
                            <?php
                            if (isset($field['after'])) : echo wp_kses_post($field['after']);
                            endif;
                            ?>

                        <?php elseif ('textarea' === $field['type']): ?>
                            <textarea <?php echo Themify_Builder_Component_Base::get_element_attributes($input_props); ?> <?php if (isset($field['rows'])) echo 'rows="' . $field['rows'] . '"'; ?><?php echo $data, $control; ?>></textarea>

                        <?php elseif ('select' === $field['type']): ?>
                            <div class="selectwrapper">
                                <select <?php echo Themify_Builder_Component_Base::get_element_attributes($input_props); ?><?php echo $data, $control; ?>>
                                    <?php
                                    foreach ($field['options'] as $key => $value) {
                                        $selected = ( isset($field['default']) && $field['default'] === $value ) ? ' selected="selected"' : '';
                                        echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($value) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if (isset($field['after'])) echo wp_kses_post($field['after']); ?>
                            <?php if (isset($field['help'])): ?>
                                <br />
                            <?php endif; // isset help    ?>

                        <?php elseif ('selectbasic' === $field['type']): ?>
                            <div class="selectwrapper">
                                <select id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" class="tb_lb_option" <?php echo $data, $control; ?>>
                                    <?php
                                    foreach ($field['options'] as $value) {
                                        $selected = ( isset($field['default']) && $field['default'] == $value ) ? ' selected="selected"' : '';
                                        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                        <?php elseif ('select_menu' === $field['type']): ?>
                            <div class="selectwrapper">
                                <select id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" class="tb_lb_option select_menu_field" <?php echo $data, $control; ?>>
                                    <option value=""><?php esc_html_e('Select a Menu...', 'themify'); ?></option>
                                    <?php
                                    foreach ($field['options'] as $key => $value) {
                                        $selected = ( isset($field['default']) && $field['default'] == $value ) ? ' selected="selected"' : '';
                                        echo '<option value="' . esc_attr($value->slug) . '" ' . $selected . ' data-termid="' . esc_attr($value->term_id) . '">' . esc_html($value->name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                        <?php elseif ('query_category' === $field['type']): ?>
                        <?php
                                $terms_tax = isset($field['options']['taxonomy']) ? $field['options']['taxonomy'] : 'category';
                                if (taxonomy_exists($terms_tax)) {
                                    $terms_count = wp_count_terms($terms_tax, array('hide_empty' => true));
                                    $max_count = 100;
                                    if ($terms_count < $max_count) {
                                        $terms_by_tax = get_terms($terms_tax, array('hide_empty' => true, 'number' => $max_count));
                                        $terms_list = array();
                                        $terms_list['0'] = array(
                                            'title' => __('All Categories', 'themify'),
                                            'slug' => '0'
                                        );
                                        if (!is_wp_error($terms_by_tax)) {
                                            foreach ($terms_by_tax as $term) {
                                                $terms_list[$term->term_id] = array(
                                                    'title' => $term->name,
                                                    'slug' => $term->slug
                                                );
                                            }
                                        }
                                        ?>
                                        <div class="selectwrapper">
                                            <select id="<?php echo $field['id'] . '_dropdown'; ?>" class="query_category_single" <?php echo $data; ?>>
                                                <option></option>
                                                <?php
                                                foreach ($terms_list as $term_id => $term) {
                                                    $term_selected = $term_id === 0 ? 'selected' : '';
                                                    printf(
                                                            '<option value="%s" data-termid="%s" %s>%s</option>', esc_attr($term['slug']), esc_attr($term_id), $term_selected, esc_html($term['title'])
                                                    );
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <?php
                                    } else {
                                        if (!wp_script_is('jquery-ui-autocomplete')) {
                                            wp_enqueue_script('jquery-ui-autocomplete');
                                        }
                                        ?>
                                        <input id="themify_search_cat_<?php echo $terms_tax ?>" type="text" value="" autocomplete="off" placeholder="<?php printf(__('Search by %s', 'themify'), $field['label']) ?>" data-tax="<?php echo $terms_tax ?>" data-action="themify_get_tax" class="themify_tax_autocomplete"/>
                                        <?php
                                    }
                                    _e('or', 'themify');
                                    ?>
                                    <input class="small query_category_multiple" type="text" /><br /><small><?php _e('multiple category IDs (eg. 2,5,8) or slug (eg. news,blog,featured) or exclude category IDs(eg. -2,-5,-8)', 'themify'); ?></small><br />
                                    <input type="hidden" id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" value="" class="tb_lb_option themify-option-query-cat"<?php echo $control; ?> />
                                <?php } ?>
                            <?php
                            ///////////////////////////////////////////
                            // Query category single field
                            ///////////////////////////////////////////
                            elseif ('query_category_single' === $field['type']):
                                                    ?>
                            <?php
                            echo preg_replace('/>/', '><option></option>', wp_dropdown_categories(
                                            array(
                                                'taxonomy' => isset($field['options']['taxonomy']) ? $field['options']['taxonomy'] : 'category',
                                                'class' => 'tb_lb_option',
                                                'show_option_all' => __('All Categories', 'themify'),
                                                'hide_empty' => 0,
                                                'echo' => 0,
                                                'name' => $field['id'],
                                                'selected' => ''
                                    )), 1);
                            echo '<br />';
                            ?>

                            <?php
                        ///////////////////////////////////////////
                        // Multifield
                        ///////////////////////////////////////////
                        elseif ('multifield' === $field['type']):
                            ?>

                            <?php if (isset($field['options']['select'])): ?>
                                <div class="selectwrapper">
                                    <select id="<?php echo esc_attr($field['options']['select']['id']); ?>" class="tb_lb_option" <?php echo $data; ?>>
                                        <?php foreach ($field['options']['select']['options'] as $opt => $label): ?>
                                            <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($field['options']['text'])): ?>
                                <input id="<?php echo esc_attr($field['options']['text']['id']); ?>" class="xsmall tb_lb_option" type="text" <?php echo $data; ?> />
                                <?php if (isset($field['options']['text']['help'])): ?>
                                    <small><?php echo wp_kses_post($field['options']['text']['help']); ?></small>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($field['options']['colorpicker'])): ?>
                                <?php $color_class = isset($field['options']['colorpicker']['class']) ? $field['options']['colorpicker']['class'] : 'xsmall'; ?>
                                <span class="builderColorSelect"><span></span></span> 
                                <input id="<?php echo esc_attr($field['options']['colorpicker']['id']); ?>" class="<?php echo esc_attr($color_class); ?> tb_lb_option builderColorSelectInput" type="text" />

                            <?php endif; ?>

                            <?php
                        ///////////////////////////////////////////
                        // Type Slider option
                        ///////////////////////////////////////////
                        elseif ('slider' === $field['type']):
                            ?>

                            <?php foreach ($field['options'] as $fieldsec): ?>

                                <?php if ($fieldsec['type'] === 'select'): ?>
                                    <div class="selectwrapper">
                                        <select id="<?php echo $fieldsec['id'] ?>" name="<?php echo $fieldsec['id'] ?>" class="tb_lb_option" <?php echo themify_builder_get_binding_data($fieldsec), themify_builder_get_control_binding_data($fieldsec); ?>>
                                            <?php
                                            foreach ($fieldsec['options'] as $key => $value) {
                                                $selected = ( isset($fieldsec['default']) && $fieldsec['default'] === $value ) ? ' selected="selected"' : '';
                                                echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($value) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php echo isset($fieldsec['help']) ? wp_kses_post($fieldsec['help']) : ''; ?><br />

                                <?php elseif ($fieldsec['type'] === 'text'): ?>
                                    <input id="<?php echo $fieldsec['id'] ?>" name="<?php echo $fieldsec['id'] ?>" class="<?php echo $fieldsec['class'] ?> tb_lb_option" type="text"<?php echo themify_builder_get_control_binding_data($fieldsec); ?> />
                                    <?php echo (isset($fieldsec['unit'])) ? '<small>' . esc_html($fieldsec['unit']) . '</small>' : ''; ?>
                                    <?php echo isset($fieldsec['help']) ? wp_kses_post($fieldsec['help']) : ''; ?><br />
                                <?php else: ?>
                                    <?php themify_builder_module_settings_field(array($fieldsec), $module_name); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php
                        // hook actions
                        do_action('themify_builder_lightbox_fields', $field, $module_name);
                        ?>

                        <?php if (!empty($field['break'])): ?>
                            <br />
                        <?php endif; ?>

                        <?php if (isset($field['help'])): ?>
                            <small><?php echo wp_kses_post($field['help']); ?></small>
                        <?php endif; ?>
                    </div>
                    <!-- /themify_builder_input -->
                <?php } ?>

                <?php if ($field['type'] !== 'builder'): ?>
                </div>
                <!-- /themify_builder_field -->
            <?php endif; ?>

            <?php if (isset($field['separated']) && $field['separated'] === 'bottom'): ?>
                <hr />
                <?php
            endif;
        endforeach;
    }

}

if (!function_exists('themify_builder_styling_field')) {

    /**
     * Module Styling Fields
     * @param array $styling 
     * @return string
     */
    function themify_builder_styling_field($styling) {
        switch ($styling['type']) {

            case 'text':
                ?>
                <input id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" type="text" value="" class="<?php echo $styling['class']; ?> tb_lb_option"/>
                <?php
                if (isset($styling['description'])) {
                    echo '<small>' . wp_kses_post($styling['description']) . '</small>';
                }
                ?>
                <?php
                break;

            case 'textarea':
                ?>
                <textarea id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" class="<?php echo $styling['class'] ?> tb_lb_option"><?php
                    if (isset($styling['value'])) : echo esc_textarea($styling['value']);
                    endif;
                    ?></textarea>
                <?php
                if (isset($styling['description'])) {
                    echo '<small>' . wp_kses_post($styling['description']) . '</small>';
                }
                ?>
                <?php
                break;

            case 'separator':
                echo isset($styling['meta']['html']) && '' !== $styling['meta']['html'] ? $styling['meta']['html'] : '<hr class="meta_fields_separator"/>';
                break;

            case 'image':
                ?>
                <input id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" placeholder="<?php if (isset($styling['value'])) echo esc_attr($styling['value']); ?>" class="<?php echo $styling['class']; ?> themify-builder-uploader-input tb_lb_option" type="text"  <?php echo themify_builder_get_binding_data($styling); ?> /><br />

                <div class="small">

                    <?php if (is_multisite() && !is_upload_space_available()): ?>
                        <?php echo sprintf(__('Sorry, you have filled your %s MB storage quota so uploading has been disabled.', 'themify'), get_space_allowed()); ?>
                    <?php else: ?>
                        <div class="themify-builder-plupload-upload-uic tb-upload-btn" id="<?php echo $styling['id']; ?>themify-builder-plupload-upload-ui">
                            <input id="<?php echo $styling['id']; ?>themify-builder-plupload-browse-button" type="button" value="<?php _e('Upload', 'themify'); ?>" class="builder_button" />
                            <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($styling['id'] . 'themify-builder-plupload'); ?>"></span>
                        </div> <?php _e('or', 'themify') ?> <a href="#" class="themify-builder-media-uploader tb-upload-btn" data-uploader-title="<?php esc_attr_e('Upload an Image', 'themify') ?>" data-uploader-button-text="<?php esc_attr_e('Insert file URL', 'themify') ?>"><?php _e('Browse Library', 'themify') ?></a>
                    <?php endif; ?>
                </div>

                <p class="thumb_preview">
                    <span class="img-placeholder"></span>
                    <a href="#" class="themify_builder_icon small delete themify-builder-delete-thumb"></a>
                </p>
                <?php
                break;

            case 'video':
                ?>
                <input id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" placeholder="<?php if (isset($styling['value'])) echo esc_attr($styling['value']); ?>" class="<?php echo $styling['class']; ?> themify-builder-uploader-input tb_lb_option" type="text"/><br />

                <div class="small">

                    <?php if (is_multisite() && !is_upload_space_available()): ?>
                        <?php echo sprintf(__('Sorry, you have filled your %s MB storage quota so uploading has been disabled.', 'themify'), get_space_allowed()); ?>
                    <?php else: ?>
                        <div class="themify-builder-plupload-upload-uic tb-upload-btn" id="<?php echo $styling['id']; ?>themify-builder-plupload-upload-ui" data-extensions="<?php echo esc_attr(implode(',', wp_get_video_extensions())); ?>">
                            <input id="<?php echo $styling['id']; ?>themify-builder-plupload-browse-button" type="button" value="<?php _e('Upload', 'themify'); ?>" class="builder_button" />
                            <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($styling['id'] . 'themify-builder-plupload'); ?>"></span>
                        </div> <?php _e('or', 'themify') ?> <a href="#" class="themify-builder-media-uploader tb-upload-btn" data-uploader-title="<?php _e('Upload a Video', 'themify') ?>" data-uploader-button-text="<?php esc_attr_e('Insert file URL', 'themify') ?>" data-library-type="video"><?php _e('Browse Library', 'themify') ?></a>

                    <?php endif; ?>

                </div>

                <?php
                if (isset($styling['description'])) {
                    echo '<small>' . wp_kses_post($styling['description']) . '</small>';
                }
                ?>

                <?php
                break;

            case 'select':
                if (strpos($styling['id'], '_unit', 2) !== false) {
                    if (isset($styling['class'])) {
                        $styling['class'].=' tb_unit';
                    } else {
                        $styling['class'] = 'tb_unit';
                    }
                }
                ?>
                <div class="selectwrapper">
                    <select id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" class="tb_lb_option <?php echo isset($styling['class']) ? $styling['class'] : ''; ?>" <?php echo themify_builder_get_binding_data($styling); ?>>
                        <?php foreach ($styling['meta'] as $option): ?>
                            <option <?php if (isset($styling['default']) && $styling['default'] === $option['value']): ?>selected="selected"<?php endif; ?><?php if (is_array($option) && !empty($option['disable'])): ?>disabled="disabled"<?php endif; ?> value="<?php echo esc_attr($option['value']); ?>"><?php echo esc_html($option['name']); ?></option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <?php
                if (isset($styling['description'])) {
                    echo wp_kses_post($styling['description']);
                }
                ?>

                <?php
                break;

            case 'animation_select':
                ?>
                <?php $class = isset($styling['class']) ? $styling['class'] : ''; ?>
                <div class="selectwrapper">
                    <select id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" class="tb_lb_option <?php echo $class ?>">
                        <option value=""></option>
                        <?php
                        $animation = Themify_Builder_model::get_preset_animation();
                        foreach ($animation as $group):
                            ?>
                            <optgroup label="<?php echo esc_attr($group['group_label']); ?>">
                                <?php foreach ($group['options'] as $opt): ?>
                                    <option value="<?php echo esc_attr($opt['value']); ?>"><?php echo esc_html($opt['name']); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php
                if (isset($styling['description'])) {
                    echo wp_kses_post($styling['description']);
                }
                ?>

                <?php
                break;
            case 'font_select':
                static $google_fonts_group = FALSE;
                static $web_safe_group = FALSE;
                if (!$web_safe_group) {
                    $web_safe = themify_get_web_safe_font_list();
                    $google_fonts = themify_get_google_web_fonts_list();
                    $web_safe_group = $web_safe[1]['name'];
                    unset($web_safe[1]);
                    $google_fonts_group = $google_fonts[1]['name'];
                    unset($google_fonts[1]);
                }
                $default = isset($styling['default']);
                if (!$default) {
                    $styling['default'] = 'default';
                }
                ?>
                <div class="themify_builder_font_preview_wrapper">
                    <select  id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" class="tb_lb_option <?php echo $styling['class']; ?>">
                        <option value="<?php echo esc_attr($styling['default']); ?>"><?php echo $default ? esc_html($styling['default']) : '---'; ?></option>
                        <optgroup label="<?php echo $web_safe_group ?>"></optgroup>
                        <optgroup  label="<?php echo $google_fonts_group ?>"></optgroup>
                    </select>
                    <span class="themify_builder_font_preview"><span><?php _e('Font Preview', 'themify') ?></span></span>
                </div>
                <?php
                if (isset($styling['description'])) {
                    echo wp_kses_post($styling['description']);
                }
                break;
            case 'color':
                ?>  <div class="minicolors_wrapper">
                    <div class="minicolors minicolors-theme-default">
                        <input id="<?php echo $styling['id'] ?>" name="<?php echo $styling['id'] ?>" type="text" class="<?php
                        if (isset($styling['class'])) {
                            echo $styling['class'];
                        }
                        ?> minicolors-input tb_lb_option"/>
                        <span class="minicolors-swatch"><span class="minicolors-swatch-color"></span></span>
                    </div>
                    <input type="text" class="color_opacity" />
                </div>
                <?php
                break;
            case 'gradient' :
                ?>
                <div class="themify-gradient-field<?php if (!empty($styling['option_js'])): ?> tb-group-element tb-group-element-gradient<?php endif; ?>">
                    <div class="selectwrapper">
                        <select class="tb_lb_option themify-gradient-type" id="<?php echo $styling['id'] ?>-gradient-type" name="<?php echo $styling['id'] ?>-gradient-type">
                            <option value="linear"><?php _e('Linear', 'themify'); ?></option>
                            <option value="radial"><?php _e('Radial', 'themify'); ?></option>
                        </select>
                    </div>
                    <input type="text" class="xsmall tb_lb_option themify-gradient-angle" id="<?php echo $styling['id'] ?>-gradient-angle" name="<?php echo $styling['id'] ?>-gradient-angle" value="180" data-min="0" data-max="360" data-cursor="true" data-thickness=".4" data-step="1" data-width="63" data-height="63" />
                    <span><?php _e('Rotation', 'themify'); ?></span>
                    <?php
                    themify_builder_styling_field(array(
                        'type' => 'checkbox',
                        'id' => $styling['id'] . '-circle-radial',
                        'options' => array(
                            array(
                                'name' => 1,
                                'value' => __('Circle Radial', 'themify')
                            )
                        )
                    ));
                    ?>
                    <div tabindex="-1" class="themify-gradient-container"></div>
                    <input type="hidden" class="themify-gradient tb_lb_option" data-id="<?php echo $styling['id'] ?>" id="<?php echo $styling['id'] ?>-gradient" name="<?php echo $styling['id'] ?>-gradient" <?php if (isset($styling['default-gradient'])): ?>data-default-gradient="<?php echo $styling['default-gradient']; ?><?php endif; ?>" />
                    <a href="#" title="<?php _e('Clear Gradient', 'themify'); ?>" class="themify-clear-gradient"><i class="ti-close"></i></a>
                </div>
                <?php
                break;
            case 'image_and_gradient' :
                $is_premium  = Themify_Builder_Model::is_premium();
                ?>
                <div class="tb-image-gradient-field">
                    <?php
                    $original = $styling;
                    $styling['type'] = 'radio';
                    $styling['meta'] = array(
                        array('value' => 'image', 'name' => __('Image', 'themify'), 'selected' => true),
                        array('value' => 'gradient', 'name' => __('Gradient', 'themify'), 'disable' => !$is_premium)
                    );
                    $styling['option_js'] = true;
                    $styling['default'] = 'image';
                    $styling['id'].= '-type';
                    themify_builder_styling_field($styling);
                    if(!$is_premium){
                        echo '<span class="themify_lite_tooltip"></span>';
                    }
                    $styling = $original;
                    $styling['type'] = 'image';
                    ?>
                    <div class="themify-image-field tb-group-element tb-group-element-image">
                        <?php themify_builder_styling_field($styling); ?>
                    </div>
                    <?php
                    if ($is_premium) {
                        $styling['type'] = 'gradient';
                        themify_builder_styling_field($styling);
                    }
                    ?>
                </div>
                <?php
                break;
            case 'checkbox':
                if (isset($styling['before'])) : echo wp_kses_post($styling['before']);
                endif;
                $option_js_wrap = (isset($styling['option_js']) && $styling['option_js'] === true) ? ' tb-option-checkbox-enable' : '';
                if ($option_js_wrap !== '' && isset($styling['reverse'])) {
                    $option_js_wrap.=' tb-option-checkbox-revert';
                }
                ?>
                <div id="<?php echo $styling['id'] ?>" class="tb_lb_option themify-checkbox<?php echo $option_js_wrap ?>">
                    <?php foreach ($styling['options'] as $opt): ?>
                        <?php
                        $checkbox_checked = '';
                        if (isset($styling['default']) && is_array($styling['default'])) {
                            $checkbox_checked = in_array($opt['name'], $styling['default']) ? 'checked="checked"' : '';
                        } elseif (isset($styling['default'])) {
                            $checkbox_checked = checked($styling['default'], $opt['name'], false);
                        }
                        $data_el = $option_js_wrap !== '' ? 'data-selected="tb-checkbox-element-' . $opt['name'] . '"' : '';
                        ?>
                        <input id="<?php echo $styling['id'] . '_' . $opt['name']; ?>" name="<?php echo $styling['id']; ?>" type="checkbox" class="<?php echo isset($styling['class']) ? $styling['class'] : '' ?> tb-checkbox" value="<?php echo esc_attr($opt['name']); ?>" <?php echo $checkbox_checked . ' ' . $data_el; ?>/>
                        <label for="<?php echo $styling['id'] . '_' . $opt['name']; ?>" class="pad-right"><?php echo wp_kses_post($opt['value']); ?></label>

                        <?php if (isset($opt['help'])): ?>
                            <small><?php echo wp_kses_post($opt['help']); ?></small>
                        <?php endif; ?>

                        <?php if (!isset($styling['new_line']) || $styling['new_line'] == true): ?>
                            <br />
                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
                <?php
                if (isset($styling['after'])) : echo wp_kses_post($styling['after']);
                endif;
                break;
            case 'radio':
            case 'icon_radio':
                if (isset($styling['before'])) : echo wp_kses_post($styling['before']);
                endif;
                $option_js = !empty($styling['option_js']);
                $option_js_wrap = $option_js ? ' tb-option-radio-enable' : '';
                $is_icon = 'icon_radio' === $styling['type'];
                if ($is_icon) {
                    $option_js_wrap.=' tb-icon-radio';
                }
                ?>
                <div <?php if (isset($styling['default'])): ?>data-default="<?php echo $styling['default'] ?>" <?php endif; ?>id="<?php echo $styling['id']; ?>" class="tb_lb_option tb-radio-input-container<?php echo $option_js_wrap; ?>">
                    <?php
                    foreach ($styling['meta'] as $k => $option) {
                        $checked = isset($option['selected']) && $option['selected'] === true ? 'checked="checked"' : '';
                        $data_el = $option_js ? 'data-selected="tb-group-element-' . $option['value'] . '"' : '';
                        if (!$checked && isset($styling['default']) && $styling['default'] === $k) {
                            $checked = 'checked="checked"';
                        }
                        ?>
                        <input <?php if (!empty($option['disable'])): ?>disabled="disabled"<?php endif; ?> <?php if (!empty($option['class'])): ?>class="<?php echo $option['class'] ?>"<?php endif; ?> type="radio" id="<?php echo $styling['id'] . '_' . $option['value']; ?>" name="<?php echo $styling['id']; ?>" value="<?php echo $option['value']; ?>" <?php echo $checked . ' ' . $data_el; ?>><label for="<?php echo $styling['id'] . '_' . $option['value']; ?>"><?php echo $is_icon ? $option['icon'] : (isset($option['name']) ? $option['name'] : ''); ?><?php if ($is_icon): ?><span class="themify_tooltip"><?php echo isset($option['name']) ? $option['name'] : '' ?></span><?php endif; ?></label>
                        <?php
                    }
                    ?>
                    <?php
                    if (isset($styling['description'])) {
                        echo '<br/><small>' . wp_kses_post($styling['description']) . '</small>';
                    }
                    ?>
                </div>
                <?php
                if (isset($styling['after'])) : echo wp_kses_post($styling['after']);
                endif;
                break;

            case 'builder':
                ?>
                <div id="<?php echo $styling['id'] ?>" class="themify_builder_row_opt_builder_wrap themify_builder_row_js_wrapper tb_lb_option">
                    <div class="tb_repeatable_field clearfix">
                        <div class="tb_repeatable_field_top">
                            <div class="row_menu">
                                <div class="menu_icon"></div>
                                <ul class="tb_down">
                                    <li><a href="#" class="themify_builder_duplicate_row"><?php _e('Duplicate', 'themify') ?></a></li>
                                    <li><a href="#" class="themify_builder_delete_row"><?php _e('Delete', 'themify') ?></a></li>
                                </ul>
                            </div>
                            <div class="toggle_row"></div>
                        </div>
                        <div class="tb_repeatable_field_content">
                            <?php themify_builder_module_settings_field_builder($styling) ?>
                        </div>
                    </div>
                </div>
                <p class="add_new"><a href="#"><span class="themify_builder_icon add"></span><?php echo isset($styling['new_row_text']) ? $styling['new_row_text'] : __('Add new row', 'themify') ?></a></p>
                <?php
                break;
            case 'padding':
            case 'margin':
                $values = array('top', 'right', 'bottom', 'left');
                $key = isset($styling['key']) ? $styling['key'] : false;
                $select = $styling;
                $select['type'] = 'select';
                $select['meta'] = Themify_Builder_Model::get_units();
                unset($select['prop']);
                ?>
                <ul class="tb_seperate_items">
                    <?php foreach ($values as $v): ?>
                        <li>
                            <?php $id = $key ? sprintf($key, $v) : $styling['id'] . '_' . $v; ?>
                            <input id="<?php echo $id ?>" name="<?php echo $id ?>" type="text" value="" class="xsmall tb_lb_option tb_multi_field"/>
                            <?php
                            $select['id'] = $id . '_unit';
                            $select['description'] = '<span class="tb_text">' . sprintf(__('%s', 'themify'), $v) . '</span>';
                            themify_builder_styling_field($select);
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php
                $select['type'] = 'checkbox';
                $select['id'] = 'checkbox_' . $styling['id'] . '_apply_all';
                $select['label'] = false;
                $select['options'] = array(array('name' => 1, 'value' => sprintf(__('Apply to all %s', 'themify'), $styling['type'])));
                $select['class'] = 'style_apply_all';
                $select['default'] = 1;
                themify_builder_styling_field($select);
                break;
            case 'border':
                $values = array('top', 'right', 'bottom', 'left');
                $select = $styling;
                $select['meta'] = Themify_Builder_Model::get_border_styles();
                unset($select['prop']);
                ?>
                <ul class="tb_seperate_items tb_borders">
                    <?php foreach ($values as $v): ?>
                        <li>
                            <div class="tb_border_wrapper">
                                <?php
                                $id = $styling['id'] . '_' . $v;
                                $select['id'] = $id . '_color';
                                $select['type'] = 'color';
                                $select['description'] = '';
                                $select['class'] = 'border_color';
                                themify_builder_styling_field($select);
                                $select['id'] = $id . '_width';
                                $select['type'] = 'text';
                                $select['description'] = 'px';
                                $select['class'] = 'xsmall border_width';
                                themify_builder_styling_field($select);
                                ?>
                            </div>
                            <?php
                            $select['id'] = $id . '_style';
                            $select['type'] = 'select';
                            $select['class'] = 'border_style tb_multi_field';
                            $select['description'] = '<span class="tb_text">' . sprintf(__('%s', 'themify'), $v) . '</span>';
                            themify_builder_styling_field($select);
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php
                $select['type'] = 'checkbox';
                $select['id'] = 'checkbox_' . $styling['id'] . '_apply_all';
                $select['label'] = false;
                $select['class'] = 'style_apply_all';
                $select['options'] = array(array('name' => 1, 'value' => sprintf(__('Apply to all %s', 'themify'), $styling['type'])));
                $select['default'] = 1;
                themify_builder_styling_field($select);
                break;
        }
    }

}
