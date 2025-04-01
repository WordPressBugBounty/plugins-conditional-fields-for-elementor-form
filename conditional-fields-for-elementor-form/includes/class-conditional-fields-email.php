<?php
/**
 * Class Conditional_Email_Action
 */
if (!defined('ABSPATH')) {
    exit;
}

// Import common namespace
use Elementor\Controls_Manager;

// Platform-specific base class definition
if (class_exists('ElementorPro\Modules\Forms\actions\Email2') && class_exists('HelloPlus\Modules\Forms\Actions\Email')) {
    // Both platforms are active
    class Conditional_Email_Action_Base extends \ElementorPro\Modules\Forms\actions\Email2 {
        protected $platform = 'both';
        protected $hello_plus_active = true;
    }
} elseif (class_exists('ElementorPro\Modules\Forms\actions\Email2')) {
    // Only Elementor Pro
    class Conditional_Email_Action_Base extends \ElementorPro\Modules\Forms\actions\Email2 {
        protected $platform = 'elementor';
        protected $hello_plus_active = false;
    }
} elseif (class_exists('HelloPlus\Modules\Forms\Actions\Email')) {
    // Only Hello Plus
    class Conditional_Email_Action_Base extends \HelloPlus\Modules\Forms\Classes\Action_Base {
        protected $platform = 'hello_plus';
        protected $hello_plus_active = true;
    }
} else {
    // Neither platform is active
    return;
}

class Conditional_Email_Action extends Conditional_Email_Action_Base {

    /**
     * Get action name.
     *
     * @access public
     * @return string
     */
    public function get_name() {
        return 'Conditional_Email_Action';
    }

    /**
     * Get action label.
     *
     * @access public
     * @return string
     */
    public function get_label() {
        return esc_html__('Email Conditionally (Pro)', 'cfef');
    }

    /**
     * Get action Controller ID.
     *
     * @access public
     * @param string $control_id
     * @return string
     */
    public function controler_id($control_id) {
        return $control_id . '_cfef_email_action';
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
                'type' => Controls_Manager::SWITCHER,
            ]
        );
        
        $widget->add_control(
            $this->controler_id('cfef_logic_mode'),
            [
                'label' => esc_html__('', "cfef"),
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'cfef_pro_link_button',
                'raw' => '<a target="_blank" href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=email-condition">Available In Conditional Fields Pro</a>',
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
        if ($this->platform === 'both') {
            // Run both platform actions
            parent::run($record, $ajax_handler);
            if ($this->hello_plus_active) {
                \HelloPlus\Modules\Forms\Actions\Email::run($record, $ajax_handler);
            }
        } elseif ($this->platform === 'elementor') {
            parent::run($record, $ajax_handler);
        } elseif ($this->platform === 'hello_plus') {
            parent::run($record, $ajax_handler);
        }
    }
}

class Conditional_Email_Action_Two extends Conditional_Email_Action {

    /**
     * Get action name.
     *
     * @access public
     * @return string
     */
    public function get_name() {
        return 'conditional_email_action_two';
    }

    /**
     * Get action label.
     *
     * @access public
     * @return string
     */
    public function get_label() {
        return esc_html__('Email Conditionally 2 (Pro)', 'cfef');
    }
    
    /**
     * Get action Controller ID.
     *
     * @access public
     * @param string $control_id
     * @return string
     */
    public function controler_id($control_id) {
        return $control_id . '_cfef_email_action_two';
    }
}