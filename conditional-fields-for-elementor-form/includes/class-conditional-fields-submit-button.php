<?php
/**
 * Class Conditional_Submit_Button
 */
if ( ! defined( 'ABSPATH' ) ){
    exit;
} 

use Elementor\Controls_Manager;

class Conditional_Submit_Button{

    private $validate_form = false;

    public function __construct(){
        add_action( 'elementor/element/form/section_buttons/after_section_start', array($this,'inject_custom_control_inside_section_buttons'), 10, 2 );
    }

    public function controler_new_id_for_submit_button($control_id){
        return $control_id . '_cfefp_submit';
    }

    public function inject_custom_control_inside_section_buttons($widget, $args){
        $cfef_conditional_logic_id_for_submit = $this->controler_new_id_for_submit_button( 'cfef_logic' );
		$widget->add_control(
			$cfef_conditional_logic_id_for_submit,
			array(
				'label' => esc_html__( 'Enable Conditions', 'cfef' ),
				'render_type' => 'none',
				'type' => Controls_Manager::SWITCHER,
            )
		);
		$widget->add_control(
			$this->controler_new_id_for_submit_button( 'cfef_logic_mode' ),
			array(
				'label' => esc_html__( '', "cfef" ),
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'cfef_pro_link_button',
				'raw'          => '<a class="cfef_custom_html" target="_blank" href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=button-condition">Available In Conditional Fields Pro</a>',
                'condition' => array(
                    $cfef_conditional_logic_id_for_submit => 'yes'
                ),
            )
		);
    }    
    
}
