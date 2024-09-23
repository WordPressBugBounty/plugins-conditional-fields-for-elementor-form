<?php
/**
 * Class Conditional_Fields_Redirection
 */
if ( ! defined( 'ABSPATH' ) ){
    exit;
} 

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\actions\Redirect;
use Elementor\Modules\DynamicTags\Module;

class Conditional_Fields_Redirection extends Redirect {

    /**
	 * Get action name.
	 *
	 * Retrieve conditional_fields_redirection action name.
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
	 * Retrieve Conditional Fields Redirection action label.
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'Redirect Conditionally (Pro)', 'cfef' );
	}

    /**
	 * Get action Controler ID.
	 *
	 * Retrieve Conditional Fields Controler ID.
	 *
	 * @access public
     * @param $control_id
	 * @return string
	 */
	public function controler_id( $control_id ) {
		return $control_id . '_cfef_conditional_fields';
	}

    /**
	 * Register action controls.
	 *
	 * Method to register action controls.
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			$this->controler_id( 'section_redirect' ),
			array(
				'label' => $this->get_label(),
				'condition' => array(
					'submit_actions' => $this->get_name(),
                ),
            )
		);
		$cfef_conditional_logic_id = $this->controler_id( 'cfef_logic' );
		$widget->add_control(
			$cfef_conditional_logic_id,
			array(
				'label' => esc_html__( 'Enable Conditions', 'cfef' ),
				'render_type' => 'none',
				'type' => Controls_Manager::SWITCHER,
            )
		);
        $widget->add_control(
			$this->controler_id( 'cfef_logic_mode' ),
			array(
				'label' => esc_html__( '', "cfef" ),
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'cfef_pro_link_button',
				'raw'          => '<a target="_blank" href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=redirect-condition">Available In Conditional Fields Pro</a>',
                'condition' => array(
                    $cfef_conditional_logic_id => 'yes'
                ),
            )
		);
		$widget->end_controls_section();
	}

    /**
	 * On export.
	 *
	 * method to clear fields when exporting.
	 *
	 * @access public
	 * @param array $element
	 */
	public function on_export( $element ) {

	}
    /**
	 * Run action.
	 *
	 * Method for form redirection after form submission.
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		
	}
    
}

/**
 * Class Conditional_Fields_Redirection_Two
 */
class Conditional_Fields_Redirection_Two extends Conditional_Fields_Redirection {
	public function get_name() {
		return 'conditional_fields_redirection_two';
	}

	public function get_label() {
		return esc_html__( 'Redirect Conditionally 2 (Pro)', 'cfef' );
	}
	public function controler_id( $control_id ) {
		return $control_id . '_cfef_conditional_fields_two';
	}
}
