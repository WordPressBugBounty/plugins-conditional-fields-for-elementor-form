<?php
/**
 * Class Conditional_Fields_Redirection
 */
if (!defined('ABSPATH')) {
    exit;
}

// Platform detection and base class definition
$has_elementor = class_exists('ElementorPro\Modules\Forms\actions\Redirect');
$has_hello_plus = class_exists('HelloPlus\Modules\Forms\Actions\Redirect');

if ($has_elementor || $has_hello_plus) {
    // Create base class that extends the appropriate platform class
    if ($has_elementor && $has_hello_plus) {
        // If both platforms exist, extend Elementor Pro by default
        class Conditional_Fields_Redirection_Base extends \ElementorPro\Modules\Forms\actions\Redirect {
            protected $platform = 'elementor';
            
            protected function get_controls_manager() {
                return \Elementor\Controls_Manager::class;
            }
        }
    } elseif ($has_elementor) {
        // Elementor Pro only
        class Conditional_Fields_Redirection_Base extends \ElementorPro\Modules\Forms\actions\Redirect {
            protected $platform = 'elementor';
            
            protected function get_controls_manager() {
                return \Elementor\Controls_Manager::class;
            }
        }
    } else {
        // Hello Plus only
        class Conditional_Fields_Redirection_Base extends \HelloPlus\Modules\Forms\Actions\Redirect {
            protected $platform = 'hello_plus';
            
            protected function get_controls_manager() {
                return \Elementor\Controls_Manager::class;
            }
        }
    }
} else {
    // Neither platform is active
    return;
}

class Conditional_Fields_Redirection extends Conditional_Fields_Redirection_Base {

    /**
     * Get action name.
     *
     * @access public
     * @return string
     */
    public function get_name() {
        return 'conditional_fields_redirection';
    }

    /**
     * Get action label.
     *
     * @access public
     * @return string
     */
    public function get_label() {
        return esc_html__('Redirect Conditionally (Pro)', 'cfef');
    }

    /**
     * Get action Controller ID.
     *
     * @access public
     * @param string $control_id
     * @return string
     */
    public function controler_id($control_id) {
        return $control_id . '_cfef_conditional_fields';
    }

    /**
     * Register action controls.
     *
     * @access public
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section($widget) {
        $widget->start_controls_section(
            $this->controler_id('section_redirect'),
            [
                'label' => $this->get_label(),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );
        
        $cfef_conditional_logic_id = $this->controler_id('cfef_logic');
        $widget->add_control(
            $cfef_conditional_logic_id,
            [
                'label' => esc_html__('Enable Conditions', 'cfef'),
                'render_type' => 'none',
                'type' => \Elementor\Controls_Manager::SWITCHER,
            ]
        );
        
        $widget->add_control(
            $this->controler_id('cfef_logic_mode'),
            [
                'label' => esc_html__('', "cfef"),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'content_classes' => 'cfef_pro_link_button',
                'raw' => '<a target="_blank" href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=redirect-condition">Available In Conditional Fields Pro</a>',
                'condition' => [
                    $cfef_conditional_logic_id => 'yes'
                ],
            ]
        );
        
        $widget->end_controls_section();
    }

    /**
     * On export.
     *
     * @access public
     * @param array $element
     * @return array
     */
    public function on_export($element) {
        return $element;
    }

    /**
     * Run action.
     *
     * @access public
     * @param mixed $record
     * @param mixed $ajax_handler
     */
    public function run($record, $ajax_handler) {
        parent::run($record, $ajax_handler);
    }
}

class Conditional_Fields_Redirection_Two extends Conditional_Fields_Redirection {

    /**
     * Get action name.
     *
     * @access public
     * @return string
     */
    public function get_name() {
        return 'conditional_fields_redirection_two';
    }

    /**
     * Get action label.
     *
     * @access public
     * @return string
     */
    public function get_label() {
        return esc_html__('Redirect Conditionally 2 (Pro)', 'cfef');
    }
    
    /**
     * Get action Controller ID.
     *
     * @access public
     * @param string $control_id
     * @return string
     */
    public function controler_id($control_id) {
        return $control_id . '_cfef_conditional_fields_two';
    }
}