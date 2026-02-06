<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('HelloPlus_Widget_Loader')) {
    class HelloPlus_Widget_Loader
    {

        public function __construct()
        {

            add_action('elementor/element/ehp-form/section_integration/after_section_end', array($this, 'show_actions_on_editor_side'), 10, 2);
        }

        public function show_actions_on_editor_side($element, $args)
        {

            require_once CFEF_PLUGIN_DIR . 'includes/helloplus-form-to-sheet.php';
            $instance = new Sheet_HelloPlus_Action();
            
            $custom_actions[$instance->get_name()] = $instance->get_label();
            $action_instances[] = $instance;

            $element->start_controls_section(
                'cool_formkit_conditional_actions_section',
                [
                    'label' => esc_html__('Cool Actions After Submit','conditional-fields-for-elementor-form'),
                ]
            );

            $element->add_control('cool_formkit_submit_actions', [
                'label'       => __('Actions After Submit','conditional-fields-for-elementor-form'),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'label_block' => true,
                'options'     => $custom_actions,
                'default'     => [],
                'render_type' => 'template',
            ]);

            $element->end_controls_section();

            // === 4. Register All Controls with Condition
            foreach ($action_instances as $instance) {
                if (method_exists($instance, 'register_settings_section')) {
                    $instance->register_settings_section($element);
                }
            }
        }

    }
}
