<?php

namespace Cool_FormKit\Includes;
use Cool_FormKit\Includes\AtomicForm\Checkbox\Checkbox;
use Cool_FormKit\Includes\AtomicForm\Input\Input;
use Cool_FormKit\Includes\AtomicForm\Textarea\Textarea;
use Cool_FormKit\Includes\AtomicForm\Radio_Button\Radio_Button;
use Cool_FormKit\Includes\AtomicForm\Date_Picker\Date_Picker;
use Cool_FormKit\Includes\AtomicForm\Time_Picker\Time_Picker;
use Cool_FormKit\Includes\AtomicForm\File_Upload\File_Upload;
use Cool_FormKit\Includes\AtomicForm\Select\Select;
use Elementor\Plugin as Elementor_Plugin;
use Elementor\Utils as Elementor_Utils;
use Elementor\Widgets_Manager;
use Cool_FormKit\Includes\AtomicForm\Handle_Atomic_Form_Submission;

if ( ! defined( 'ABSPATH' ) ) { exit; }
class Atomic_Form_Addon_Loader {


    private static $instance = null;

    protected $version;

    protected $error_map;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {

        if ( ! $this->are_atomic_form_experiments_active() ) {
            return;
        }

        $this->version = CFEF_VERSION;
		require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/handle-atomic-form-submission.php';

        $this->error_map =[
            __("The phone number you entered is not valid. Please check the format and try again.", "conditional-fields-for-elementor-form"),
            __("The country code you entered is not recognized. Please ensure it is correct and try again.", "conditional-fields-for-elementor-form"),
            __("The phone number you entered is too short. Please enter a complete phone number, including the country code.", "conditional-fields-for-elementor-form"),
            __("The phone number you entered is too long. Please ensure it is in the correct format and try again.", "conditional-fields-for-elementor-form"),
            __("The phone number you entered is not valid. Please check the format and try again.", "conditional-fields-for-elementor-form")
        ];

        add_filter('elementor/widgets/register', [$this, 'register_widgets'], 999);
        add_action('elementor/frontend/before_enqueue_scripts', [$this,  'enqueue_frontend_scripts']);

        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);

		if ($this->is_field_enabled( 'conditional_logic' ) ) {
			new Handle_Atomic_Form_Submission();
		}
    }

    

    /**
     * Core Atomic Widgets (`e_atomic_elements`) plus Pro Atomic Form (`e_pro_atomic_form`) must both be active.
     *
     * @see \Elementor\Modules\AtomicWidgets\Module::EXPERIMENT_NAME
     */
    private function are_atomic_form_experiments_active(): bool {

        if ( ! defined( 'ELEMENTOR_VERSION' ) || ! version_compare( ELEMENTOR_VERSION, CFEF_MIN_ELEMENTOR_ATOMIC_FORM_VERSION, '>=' ) ) {
            return false;
        }

        $experiments = Elementor_Plugin::$instance->experiments ?? null;
        if ( ! $experiments || ! method_exists( $experiments, 'is_feature_active' ) ) {
            return false;
        }

        return $experiments->is_feature_active( 'e_atomic_elements' )
            && $experiments->is_feature_active( 'e_pro_atomic_form' );
    }

    private function is_field_enabled($field_key) {
        $enabled_elements = get_option('cfkef_enabled_elements', array());
        return in_array(sanitize_key($field_key), array_map('sanitize_key', $enabled_elements));
    }

    public function enqueue_editor_scripts() {

        if ( ! $this->are_atomic_form_experiments_active() ) {
            return;
        }

        if($this->is_field_enabled('conditional_logic')){

            wp_register_script('cfl-atomic-form-handle-conditional-repeater', CFEF_PLUGIN_URL . 'assets/atomic-form/js/handle-conditional-repeater.js', array( 'jquery', 'elementor-editor'), $this->version, true);

            if (! wp_script_is('cfl-atomic-form-handle-conditional-repeater', 'enqueued') && ! wp_script_is('cfl-atomic-form-handle-conditional-repeater', 'done')) {
                wp_enqueue_script( 'cfl-atomic-form-handle-conditional-repeater' );
            }
        }

        wp_register_style('cfl-atomic-form-conditional-repeater-style', CFEF_PLUGIN_URL . 'assets/atomic-form/css/atomic-form-conditional-repeater.min.css', array(), CFEF_VERSION, 'all');
        if (! wp_style_is('cfl-atomic-form-conditional-repeater-style', 'enqueued') && ! wp_style_is('cfl-atomic-form-conditional-repeater-style', 'done')) {
            wp_enqueue_style('cfl-atomic-form-conditional-repeater-style');
        }
    }

    public function register_widgets( Widgets_Manager $widgets_manager ) {

        if ( ! $this->are_atomic_form_experiments_active() ) {
            return;
        }

        if($this->is_field_enabled('conditional_logic')){

            $widgets_manager->unregister('e-form-input');
            $widgets_manager->unregister('e-form-textarea');
            $widgets_manager->unregister('e-form-checkbox');
            $widgets_manager->unregister('e-form-radio-button');
            $widgets_manager->unregister('e-form-date-picker');
            $widgets_manager->unregister('e-form-time-picker');
            $widgets_manager->unregister('e-form-file-upload');
            $widgets_manager->unregister('e-form-select');
    
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/input/input.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/textarea/textarea.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/checkbox/checkbox.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/radio-button/radio-button.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/date-picker/date-picker.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/time-picker/time-picker.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/file-upload/file-upload.php';
            require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/select/select.php';
            $widgets_manager->register( new Input() );
            $widgets_manager->register( new Textarea() );
            $widgets_manager->register( new Checkbox() );
            $widgets_manager->register( new Radio_Button() );
            $widgets_manager->register( new Date_Picker() );
            $widgets_manager->register( new Time_Picker() );
            $widgets_manager->register( new File_Upload() );
            $widgets_manager->register( new Select() );
        }

    }


    /**
     * Atomic-form-only conditional logic handler.
     */
    public function register_atomic_form_condition_script() {
        if ( ! Elementor_Utils::has_pro() ) {
            return;
        }

        $experiments = Elementor_Plugin::$instance->experiments;
        if ( ! $experiments || ! $experiments->is_feature_active( 'e_pro_atomic_form' ) ) {
            return;
        }

        wp_register_script(
            'cfl-atomic-form-condition',
            CFEF_PLUGIN_URL . 'assets/atomic-form/js/atomic-form-condition.js',
            array( 'jquery', 'elementor-frontend' ),
            $this->version,
            true
        );

        wp_localize_script(
            'cfl-atomic-form-condition',
            'my_script_vars',
            array(
                'pluginConstant' => CFEF_PLUGIN_DIR,
            )
        );

        if (! wp_script_is('cfl-atomic-form-condition', 'enqueued') && ! wp_script_is('cfl-atomic-form-condition', 'done')) {
            wp_enqueue_script( 'cfl-atomic-form-condition' );
        }

        wp_register_style('cfl-atomic-form-conditional-style', CFEF_PLUGIN_URL . 'assets/atomic-form/css/atomic-form-conditional.min.css', array(), CFEF_VERSION, 'all');
        if (! wp_style_is('cfl-atomic-form-conditional-style', 'enqueued') && ! wp_style_is('cfl-atomic-form-conditional-style', 'done')) {
            wp_enqueue_style('cfl-atomic-form-conditional-style');
        }
    }

    public function enqueue_frontend_scripts() {

        if ( ! $this->are_atomic_form_experiments_active() ) {
            return;
        }

        if($this->is_field_enabled('conditional_logic')){

            $this->register_atomic_form_condition_script();
        }
    }

    public function get_version() {
        return $this->version;
    }
}
