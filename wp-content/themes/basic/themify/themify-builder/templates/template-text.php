<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Text
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
$fields_default = array(
    'mod_title_text' => '',
    'content_text' => '',
    'add_css_text' => '',
    'background_repeat' => '',
    'animation_effect' => ''
);

$fields_args = wp_parse_args($mod_settings, $fields_default);
unset($mod_settings);
$animation_effect = self::parse_animation_effect($fields_args['animation_effect'], $fields_args);
$container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $module_ID, $fields_args['add_css_text'], $fields_args['background_repeat'], $animation_effect
                ), $mod_name, $module_ID, $fields_args)
);
$container_props = apply_filters('themify_builder_module_container_props', array(
    'id' => $module_ID,
    'class' => $container_class
        ), $fields_args, $mod_name, $module_ID);
$fields_args['content_text'] = TB_Text_Module::generate_read_more( $fields_args['content_text'] );
?>
<!-- module text -->
<div <?php echo self::get_element_attributes($container_props); ?>>
    <?php if ($fields_args['mod_title_text'] !== ''): ?>
        <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_text'], $fields_args). $fields_args['after_title']; ?>
    <?php endif; ?>

    <?php echo apply_filters('themify_builder_module_content', $fields_args['content_text']); ?>
</div>
<!-- /module text -->