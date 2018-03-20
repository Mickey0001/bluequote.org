<?php

/**
 * Framework Name: Themify Builder
 * Framework URI: http://themify.me/
 * Description: Page Builder with interactive drag and drop features
 * Version: 1.0
 * Author: Themify
 * Author URI: http://themify.me
 *
 *
 * @package ThemifyBuilder
 * @category Core
 * @author Themify
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Define builder constant
 */
define('THEMIFY_BUILDER_DIR', dirname(__FILE__));
define('THEMIFY_BUILDER_MODULES_DIR', THEMIFY_BUILDER_DIR . '/modules');
define('THEMIFY_BUILDER_TEMPLATES_DIR', THEMIFY_BUILDER_DIR . '/templates');
define('THEMIFY_BUILDER_CLASSES_DIR', THEMIFY_BUILDER_DIR . '/classes');
define('THEMIFY_BUILDER_INCLUDES_DIR', THEMIFY_BUILDER_DIR . '/includes');
define('THEMIFY_BUILDER_LIBRARIES_DIR', THEMIFY_BUILDER_INCLUDES_DIR . '/libraries');


// URI Constant
define('THEMIFY_BUILDER_URI', THEMIFY_URI . '/themify-builder');

/**
 * Include builder class
 */
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-model.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/premium/class-themify-builder-layouts.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder.php' );
///////////////////////////////////////////
// Version Getter
///////////////////////////////////////////
if (!function_exists('themify_builder_get')) {

    function themify_builder_get($theme_var, $builder_var = false) {
        if (Themify_Builder_Model::is_themify_theme()) {
            return themify_get($theme_var);
        }
        if ($builder_var === false) {
            return false;
        }
        global $post;
        $data = Themify_Builder_Model::get_builder_settings();
        if (isset($data[$builder_var]) && $data[$builder_var] !== '') {
            return $data[$builder_var];
        } else if (is_object($post) && ($val = get_post_meta($post->ID, $builder_var, true)) !== '') {
            return $val;
        }
        return null;
    }

}
/**
 * Init themify builder class
 */
add_action('after_setup_theme', 'themify_builder_init', 15);

function themify_builder_init() {
    if (class_exists('Themify_Builder')) {
        global $ThemifyBuilder, $Themify_Builder_Layouts;
        do_action('themify_builder_before_init');

        if (Themify_Builder_Model::builder_check()) {
            $Themify_Builder_Layouts = new Themify_Builder_Layouts();

            $ThemifyBuilder = new Themify_Builder();
            $ThemifyBuilder->init();
        }
        // class_exists check
        if (is_admin() && current_user_can('update_plugins')) {
            include THEMIFY_BUILDER_DIR . '/themify-builder-updater.php';
        }
    }
}

if (!function_exists('themify_manage_builder')) {

    /**
     * Builder Settings
     * @param array $data
     * @return string
     * @since 1.2.7
     */
    function themify_manage_builder($data = array()) {
        $data = themify_get_data();
        $pre = 'setting-page_builder_';

        $output = '';
        $modules = Themify_Builder_Model::get_modules('all');
        foreach ($modules as $m) {
            $exclude = $pre . 'exc_' . $m['id'];
            $checked = !empty($data[$exclude]) ? 'checked="checked"' : '';
            $output .= '<p><span><input id="' . 'builder_module_' . $m['id']. '" type="checkbox" name="' . esc_attr($exclude) . '" value="1" ' . $checked . '/> <label for="' . 'builder_module_' . $m['id'] . '">' . wp_kses_post(sprintf(__('Exclude %s module', 'themify'), $m['name'])) . '</label></span></p>';
        }

        return $output;
    }

}

if (!function_exists('themify_manage_builder_active')) {

    /**
     * Builder Settings
     * @param array $data
     * @return string
     * @since 1.2.7
     */
    function themify_manage_builder_active($data = array()) {
        $pre = 'setting-page_builder_';
        $options = array(
            array('name' => __('Enable', 'themify'), 'value' => 'enable'),
            array('name' => __('Disable', 'themify'), 'value' => 'disable')
        );

        $output = sprintf('<p><span class="label">%s</span><select id="%s" name="%s">%s</select>%s</p>', esc_html__('Themify Builder:', 'themify'), $pre . 'is_active', $pre . 'is_active', themify_options_module($options, $pre . 'is_active'), sprintf('<small class="pushlabel" data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="disable">%s</small>'
                        , esc_html__('WARNING: When Builder is disabled, all Builder content/layout will not appear. They will re-appear once Builder is enabled.', 'themify'))
        );

        if ('disable' !== themify_builder_get($pre . 'is_active')) {

            $output .= sprintf('<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>', $pre . 'disable_shortcuts', $pre . 'disable_shortcuts', $pre . 'disable_shortcuts', checked('on', themify_builder_get($pre . 'disable_shortcuts', 'builder_disable_shortcuts'), false), wp_kses_post(__('Disable Builder shortcuts (eg. disable shortcut like Cmd+S = save)', 'themify'))
            );
        }

        return $output;
    }

}

if (!function_exists('themify_manage_builder_animation')) {

    /**
     * Builder Setting Animations
     * @param array $data
     * @return string
     * @since 2.0.0
     */
    function themify_manage_builder_animation($data = array()) {
        $opt_data = themify_get_data();
        $pre = 'setting-page_builder_animation_';
        $options = array(
            array('name' => '', 'value' => ''),
            array('name' => esc_html__('Disable on mobile & tablet', 'themify'), 'value' => 'mobile'),
            array('name' => esc_html__('Disable on all devices', 'themify'), 'value' => 'all')
        );

        $output = sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'appearance', esc_html__('Appearance Animation', 'themify'), $pre . 'appearance', $pre . 'appearance', themify_options_module($options, $pre . 'appearance')
        );
        $output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'parallax_bg', esc_html__('Parallax Background', 'themify'), $pre . 'parallax_bg', $pre . 'parallax_bg', themify_options_module($options, $pre . 'parallax_bg')
        );
        $output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>', $pre . 'parallax_scroll', esc_html__('Parallax Scrolling', 'themify'), $pre . 'parallax_scroll', $pre . 'parallax_scroll', themify_options_module($options, $pre . 'parallax_scroll', true, 'mobile')
        );
        $output .= sprintf('<span class="pushlabel"><small>%s</small></span>', esc_html__('If animation is disabled, the element will appear static', 'themify')
        );

        return $output;
    }

}

/**
 * Add Builder to all themes using the themify_theme_config_setup filter.
 * @param $themify_theme_config
 * @return mixed
 * @since 1.4.2
 */
function themify_framework_theme_config_add_builder($themify_theme_config) {
    $themify_theme_config['panel']['settings']['tab']['page_builder'] = array(
        'title' => __('Themify Builder', 'themify'),
        'id' => 'themify-builder',
        'custom-module' => array(
            array(
                'title' => __('Themify Builder Options', 'themify'),
                'function' => 'themify_manage_builder_active'
            )
        )
    );
    if ('disable' !== apply_filters('themify_enable_builder', themify_get('setting-page_builder_is_active'))) {
        $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
            'title' => __('Animation Effects', 'themify'),
            'function' => 'themify_manage_builder_animation'
        );

        $themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
            'title' => __('Exclude Builder Modules', 'themify'),
            'function' => 'themify_manage_builder'
        );
    }
    return $themify_theme_config;
}

add_filter('themify_theme_config_setup', 'themify_framework_theme_config_add_builder');

