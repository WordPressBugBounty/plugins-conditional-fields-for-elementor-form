<?php
/**
 * Plugin Name: Conditional Fields for Elementor Form
 * Plugin URI:https://coolplugins.net/
 * Description: The Conditional Fields for Elementor plugin add-on used to show and hide form fields based on conditional input values.
 * Version: 1.3.3
 * Author:  Cool Plugins
 * Author URI: https://coolplugins.net/
 * License:GPL2
 * Text Domain:cfef
 * Elementor tested up to: 3.24.2
 * Elementor Pro tested up to: 3.24.1
 *
 * @package cfef
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if ( ! defined( 'CFEF_VERSION' ) ) {
	define( 'CFEF_VERSION', '1.3.3' );
}
/*** Defined constent for later use */
define( 'CFEF_FILE', __FILE__ );
define( 'CFEF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CFEF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

register_activation_hook( CFEF_FILE, array( 'Conditional_Fields_For_Elementor_Form', 'cfef_activate' ) );
register_deactivation_hook( CFEF_FILE, array( 'Conditional_Fields_For_Elementor_Form', 'cfef_deactivate' ) );
if ( ! class_exists( 'Conditional_Fields_For_Elementor_Form' ) ) {
	/**
	 * Main Class start here
	 */
	final class Conditional_Fields_For_Elementor_Form {
		/**
		 * Plugin instance.
		 *
		 * @var Conditional_Fields_For_Elementor_Form
		 *
		 * @access private
		 * private static $instance = null;
		 * Function for create object of class
		 */
		private static $instance = null;
		/**
		 * Get plugin instance.
		 *
		 * @return Conditional_Fields_For_Elementor_Form
		 * @static
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		/**
		 * Constructor function check compatibe plugin before activate it
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'is_compatible' ) );
			add_action( 'plugins_loaded',array($this,'text_domain_path_set'));
			add_action( 'activated_plugin', array( $this, 'Cfef_plugin_redirection' ) );
			add_action( 'elementor_pro/forms/actions/register', array($this,'cfef_register_new_form_actions') );
		}

		public function cfef_register_new_form_actions($form_actions_registrar){
			include_once( __DIR__ .  '/includes/class-conditional-fields-redirection.php' );
			include_once( __DIR__ .  '/includes/class-conditional-fields-email.php' );
			$form_actions_registrar->register( new \Conditional_Fields_Redirection() );
			$form_actions_registrar->register( new \Conditional_Email_Action() );
		}
		/**
		 * Check if Elementor Pro is installed and activated
		 */
		public function is_compatible() {
			add_action( 'admin_init', array( $this, 'is_elementor_pro_exist' ), 5 );
			include CFEF_PLUGIN_DIR . 'includes/class-create-conditional-fields.php';
			include CFEF_PLUGIN_DIR . 'includes/class-conditional-fields-submit-button.php';
			new Conditional_Submit_Button();
			if ( is_admin() ) {
				require_once CFEF_PLUGIN_DIR . 'admin/feedback/admin-feedback-form.php';
			}
		}

		public function Cfef_pro_plugin_demo_link($links){
			$settings_link = '<a href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get-pro&utm_content=plugins-list" target="_blank">Get Pro</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		public function Cfef_plugin_redirection($plugin){
			if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php' ) ) {
				return false;
			}
			if ( $plugin == plugin_basename( __FILE__ ) ) {
				exit( wp_redirect( admin_url( 'admin.php?page=cool-formkit' ) ) );
			}	
		}

		public function text_domain_path_set(){
			load_plugin_textdomain( 'cfef', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			if ( is_plugin_active( 'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php' ) ) {
				return false;
			}
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'Cfef_pro_plugin_demo_link' ) );
			require_once CFEF_PLUGIN_DIR . '/includes/class-conditional-fields-elementor-page.php';
			new Conditional_Fields_Elementor_Page();
		}
		/**
		 * Function use for deactivate plugin if elementor pro not exist
		 */
		public function is_elementor_pro_exist() {
			if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
					add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
					return false;
			}
		}

		/**
		 * Show notice to enable elementor pro
		 */
		public function admin_notice_missing_main_plugin() {
			$message = sprintf(
				// translators: %1$s replace with Conditional Fields for Elementor Form & %2$s replace with Elementor Pro.
				esc_html__(
					'%1$s requires %2$s to be installed and activated.',
					'cfef'
				),
				esc_html__( 'Conditional Fields for Elementor Form', 'cfef' ),
				esc_html__( 'Elementor Pro', 'cfef' )
			);
			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', esc_html( $message ) );
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
		/**
		 * Add options for plugins detail
		 */
		public static function cfef_activate() {
			update_option( 'cfef-v', CFEF_VERSION );
			update_option( 'cfef-type', 'FREE' );
			update_option( 'cfef-installDate', gmdate( 'Y-m-d h:i:s' ) );
		}
		/**
		 * Function run on plugin deactivate
		 */
		public static function cfef_deactivate() {
		}

	}
}
$cfef_obj = Conditional_Fields_For_Elementor_Form::get_instance();
