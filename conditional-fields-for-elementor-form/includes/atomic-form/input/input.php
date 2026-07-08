<?php

namespace Cool_FormKit\Includes\AtomicForm\Input;

use Cool_FormKit\Includes\AtomicForm\Field_Controls_Definition\Conditional_Input_Definition;
use Elementor\Modules\AtomicWidgets\Controls\Section;
use Elementor\Modules\AtomicWidgets\Controls\Types\Select_Control;
use Elementor\Modules\AtomicWidgets\Controls\Types\Switch_Control;
use Elementor\Modules\AtomicWidgets\Controls\Types\Text_Control;
use Elementor\Modules\AtomicWidgets\Elements\Base\Has_Template;
use Elementor\Modules\AtomicWidgets\PropTypes\Attributes_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Classes_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\Boolean_Prop_Type;
use Elementor\Modules\AtomicWidgets\PropTypes\Primitives\String_Prop_Type;
use Elementor\Modules\Components\PropTypes\Overridable_Prop_Type;
use ElementorPro\Modules\AtomicForm\Input\Input as AtomicFormInput;

if (! defined('ABSPATH')) exit;

require_once CFEF_PLUGIN_DIR . 'includes/atomic-form/field-controls-definition/conditional-input-definition.php';

if ( ! class_exists( AtomicFormInput::class ) ) {
	return;
}

class Input extends AtomicFormInput
{
    use Has_Template;

    public static $widget_description = 'Display a text input with customizable type, placeholder, default value, required, readonly, and attributes.';

    public static function get_element_type(): string
    {
        return 'e-form-input';
    }

    public function get_title(): string {
		return esc_html__( 'Input', 'conditional-fields-for-elementor-form' );
	}

    public function get_icon(): string {
		return 'eicon-atomic-input';
	}

	public function get_categories(): array {
		return [ 'atomic-form' ];
	}

	public function get_keywords() {
		return [ 'atomic', 'form', 'input', 'text', 'email', 'number', 'tel', 'password' ];
	}

    protected static function define_props_schema(): array
    {
		$schema = [
			'classes' => Classes_Prop_Type::make()->default( [] ),
			'placeholder' => String_Prop_Type::make()->default( '' ),
			'type' => String_Prop_Type::make()
				->default( 'text' )
				->enum( [ 'text', 'email', 'number', 'tel', 'password' ] ),
			'required' => Boolean_Prop_Type::make()->default( false ),
			'readonly' => Boolean_Prop_Type::make()->default( false ),
			'attributes' => Attributes_Prop_Type::make()->meta( Overridable_Prop_Type::ignore() ),
		];

		if ( Conditional_Input_Definition::is_conditional_logic_enabled() ) {
			$schema = array_merge( $schema, Conditional_Input_Definition::props_schema() );
		}

		if ( self::is_addon_active( \Mask_Form_Elementor\Includes\AtomicForm\Input\Mask_Input_Definition::class ) ) {
			$schema = array_merge(
				$schema,
				\Mask_Form_Elementor\Includes\AtomicForm\Input\Mask_Input_Definition::props_schema()
			);
		}

		if ( self::is_addon_active( \FME\Includes\AtomicForm\Input\Mask_Input_Definition::class ) ) {
			$schema = array_merge(
				$schema,
				\FME\Includes\AtomicForm\Input\Mask_Input_Definition::props_schema()
			);
		}

		return $schema;
    }

    protected function define_atomic_controls(): array
    {
		$content_items = array_merge(
			[
				Text_Control::bind_to( 'placeholder' )
					->set_placeholder( 'Enter placeholder text' )
					->set_label( __( 'Input placeholder', 'conditional-fields-for-elementor-form' ) ),
				Select_Control::bind_to( 'type' )
					->set_label( __( 'Type', 'conditional-fields-for-elementor-form' ) )
					->set_options( [
						[
							'label' => __( 'Text', 'conditional-fields-for-elementor-form' ),
							'value' => 'text',
						],
						[
							'label' => __( 'Email', 'conditional-fields-for-elementor-form' ),
							'value' => 'email',
						],
						[
							'label' => __( 'Number', 'conditional-fields-for-elementor-form' ),
							'value' => 'number',
						],
						[
							'label' => __( 'Tel', 'conditional-fields-for-elementor-form' ),
							'value' => 'tel',
						],
						[
							'label' => __( 'Password', 'conditional-fields-for-elementor-form' ),
							'value' => 'password',
						],
					] ),
				Switch_Control::bind_to( 'required' )
					->set_label( __( 'Required', 'conditional-fields-for-elementor-form' ) ),
				Switch_Control::bind_to( 'readonly' )
					->set_label( __( 'Read only', 'conditional-fields-for-elementor-form' ) ),
			],
			self::is_addon_active( \Mask_Form_Elementor\Includes\AtomicForm\Input\Mask_Input_Definition::class ) ? \Mask_Form_Elementor\Includes\AtomicForm\Input\Mask_Input_Definition::content_controls() : [],
			self::is_addon_active( \FME\Includes\AtomicForm\Input\Mask_Input_Definition::class ) ? \FME\Includes\AtomicForm\Input\Mask_Input_Definition::content_controls() : []
		);

		$sections = [
			Section::make()
				->set_label( __( 'Content', 'conditional-fields-for-elementor-form' ) )
				->set_items( $content_items ),
			Section::make()
				->set_label( __( 'Settings', 'conditional-fields-for-elementor-form' ) )
				->set_id( 'settings' )
				->set_items( $this->get_settings_controls() ),
		];

		if ( Conditional_Input_Definition::is_conditional_logic_enabled() ) {
			$sections[] = Conditional_Input_Definition::conditions_section();
		}

		return $sections;
    }

	/**
	 * @param string $field_key Option list entry from cfkef_enabled_elements.
	 */
	private static function is_cfkef_element_enabled( $field_key ): bool {
		$enabled_elements = get_option( 'cfkef_enabled_elements', array() );
		return in_array( sanitize_key( $field_key ), array_map( 'sanitize_key', (array) $enabled_elements ), true );
	}

	/**
	 * Atomic input mask extension when the addon class exists and form_input_mask is enabled.
	 *
	 * @param string $class Addon definition class name.
	 */
	private static function is_addon_active( string $class ): bool {
		return class_exists( $class ) && self::is_cfkef_element_enabled( 'form_input_mask' );
	}

    protected function get_templates(): array
    {

        return [
            'input' => __DIR__ . '/input.html.twig',
        ];
    }

}
