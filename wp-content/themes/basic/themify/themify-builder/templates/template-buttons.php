<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Buttons
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache($mod_name, self::$post_id, array('ID' => $module_ID))):

    $fields_default = array(
        'mod_title_button' => '',
        'buttons_size' => '',
        'buttons_style' => 'circle',
		'fullwidth_button' => '',
		'display' => 'buttons-horizontal',
        'content_button' => array(),
        'animation_effect' => '',
        'css_button' => ''
    );


    $fields_args = wp_parse_args($mod_settings, $fields_default);
    unset($mod_settings);
    $animation_effect = self::parse_animation_effect($fields_args['animation_effect'], $fields_args);

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, $fields_args['css_button'], $animation_effect
                    ), $mod_name, $module_ID, $fields_args)
    );
    $ui_class = implode(' ', array('module-' . $mod_name, $fields_args['buttons_size'], $fields_args['buttons_style']));

    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $module_ID,
        'class' => $container_class
            ), $fields_args, $mod_name, $module_ID);
    ?>
    <!-- module buttons -->
    <div <?php echo self::get_element_attributes($container_props); ?>>

        <?php if ($fields_args['mod_title_button'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_button'], $fields_args) . $fields_args['after_title']; ?>
        <?php endif; ?>

        <div class="<?php echo $ui_class; ?>">
            <?php
            $content_button = array_filter($fields_args['content_button']);
            $units = array(
                'pixels' => 'px',
                'percents' => '%'
            );
            foreach ($content_button as $content):

                $content = wp_parse_args($content, array(
                    'label' => '',
                    'link' => '',
                    'icon' => '',
                    'link_options' => false,
                    'lightbox_width' => '',
                    'lightbox_height' => '',
                    'lightbox_size_unit_width' => '',
                    'lightbox_size_unit_height' => '',
                    'button_color_bg' => false
                ));
                $link_css_clsss = array('ui builder_button');
                $link_attr = array();

                if ($content['link_options'] === 'lightbox') {
                    $link_css_clsss[] = 'themify_lightbox';

                    if (!empty($content['lightbox_width']) || !empty($content['lightbox_height'])) {
                        $lightbox_settings = array();
                        $lightbox_settings[] = !empty($content['lightbox_width']) ? $content['lightbox_width'] . (isset($units[$content['lightbox_size_unit_width']]) ? $units[$content['lightbox_size_unit_width']] : 'px') : '';
                        $lightbox_settings[] = !empty($content['lightbox_height']) ? $content['lightbox_height'] . (isset($units[$content['lightbox_size_unit_height']]) ? $units[$content['lightbox_size_unit_height']] : 'px') : '';

                        $link_attr[] = sprintf('data-zoom-config="%s"', implode('|', $lightbox_settings));
                    }
                } elseif ($content['link_options'] === 'newtab') {
                    $link_attr[] = 'target="_blank" rel="noopener"';
                }

                if (!empty($content['button_color_bg'])) {
                    $link_css_clsss[] = $content['button_color_bg'];
                }
                ?>
				<div class="module-buttons-item <?php echo $fields_args['fullwidth_button']?> <?php echo $fields_args['display']?>">
                    <?php if ($content['link']): ?>
                        <a href="<?php echo esc_url($content['link']); ?>" class="<?php echo implode(' ', $link_css_clsss) ?>"  <?php echo implode(' ', $link_attr) ?>>
                    <?php endif; ?>
                        <?php if ($content['icon']): ?>
                            <i class="<?php echo themify_get_icon($content['icon']); ?>"></i>
                        <?php endif; ?>
                        <span><?php echo $content['label'] ?></span>
                        <?php if ($content['link']): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- /module buttons -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>