<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Divider
 *
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache($mod_name, self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_divider' => '',
        'style_divider' => 'solid',
        'stroke_w_divider' => 1,
        'color_divider' => '',
        'top_margin_divider' => '',
        'bottom_margin_divider' => '',
        'css_divider' => '',
        'divider_type' => 'fullwidth',
        'divider_width' => 200,
        'divider_align' => 'left',
        'animation_effect' => ''
    );

    if (isset($mod_settings['stroke_w_divider'])) {
        $mod_settings['stroke_w_divider'] = 'border-width: ' . $mod_settings['stroke_w_divider'] . 'px;';
    } else {
		$mod_settings['stroke_w_divider'] = 'border-width: ' . $fields_default['stroke_w_divider'] . 'px;';
	}
    if (isset($mod_settings['color_divider'])) {
        $mod_settings['color_divider'] = 'border-color: ' . Themify_Builder_Stylesheet::get_rgba_color($mod_settings['color_divider']) . ';';
    }
    if (isset($mod_settings['top_margin_divider'])) {
        $mod_settings['top_margin_divider'] = 'margin-top: ' . $mod_settings['top_margin_divider'] . 'px;';
    }
    if (isset($mod_settings['bottom_margin_divider'])) {
        $mod_settings['bottom_margin_divider'] = 'margin-bottom: ' . $mod_settings['bottom_margin_divider'] . 'px;';
    }
    
    $fields_args = wp_parse_args($mod_settings, $fields_default);
    unset($mod_settings);
    if ($fields_args['divider_type'] === 'custom') {
        if ($fields_args['divider_width'] > 0) {
            $fields_args['divider_width'] = 'width:' . $fields_args['divider_width'] . 'px;';
        }
        $fields_args['divider_align'] = 'divider-' . $fields_args['divider_align'];
        $divider_type = 'divider-' . $fields_args['divider_type'];
    } else {
        $divider_type = $fields_args['divider_align'] = $fields_args['divider_width'] = '';
    }
    $animation_effect = self::parse_animation_effect($fields_args['animation_effect'], $fields_args);
    $style = $fields_args['stroke_w_divider'] . $fields_args['color_divider'] . $fields_args['top_margin_divider'] . $fields_args['bottom_margin_divider'] . $fields_args['divider_width'];
    
    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $fields_args['style_divider'], $fields_args['css_divider'], $animation_effect, $divider_type, $fields_args['divider_align']
                    ), $mod_name, $module_ID, $fields_args)
    );
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $module_ID,
        'class' => $container_class,
            ), $fields_args, $mod_name, $module_ID);
    if ($style) {
        $container_props['style'] = esc_attr($style);
    }
    ?>
    <!-- module divider -->
    <div <?php echo self::get_element_attributes($container_props); ?>>
        <?php if ($fields_args['mod_title_divider'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_divider'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
    </div>
    <!-- /module divider -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
