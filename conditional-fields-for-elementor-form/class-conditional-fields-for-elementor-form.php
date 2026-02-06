<?php
/**
 * Plugin Name: Conditional Fields for Elementor Form
 * Plugin URI:https://coolplugins.net/
 * Description: The Conditional Fields for Elementor plugin add-on used to show and hide form fields based on conditional input values.
 * Version: 1.6.1
 * Author:  Cool Plugins
 * Author URI: https://coolplugins.net/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=plugins_list
 * License:GPL2
 * Text Domain:conditional-fields-for-elementor-form
 * Elementor tested up to:  3.35.0
 * Elementor Pro tested up to:  3.35.0
 *
 * @package cfef
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
if ( ! defined( 'CFEF_VERSION' ) ) {
	define( 'CFEF_VERSION', '1.6.1' );
}
/*** Defined constent for later use */
define( 'CFEF_FILE', __FILE__ );
define( 'CFEF_PLUGIN_BASE', plugin_basename( CFEF_FILE ) );
define( 'CFEF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CFEF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('CFEF_FEEDBACK_URL', 'https://feedback.coolplugins.net/');


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
			add_action('init', array($this, 'formdb_marketing_hello_plus'));
			add_action( 'plugins_loaded',array($this,'compatibilityCheck'));
			add_action( 'activated_plugin', array( $this, 'Cfef_plugin_redirection' ) );
			add_action( 'elementor_pro/forms/actions/register', array($this,'cfef_register_new_form_actions') );
			add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
			add_action('wp_head', array( $this, 'stop_format_detection_in_safari' ));
			$this->includes();
		}

		public function stop_format_detection_in_safari() {

			if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				return;
			}

			$ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

			$is_safari =
				false !== strpos( $ua, 'Safari' ) &&
				false !== strpos( $ua, 'Mobile' ) && // ensures mobile Safari
				(
					false !== strpos( $ua, 'iPhone' ) ||
					false !== strpos( $ua, 'iPad' ) ||
					false !== strpos( $ua, 'iPod' )
				) &&
				! preg_match( '/Chrome|CriOS|Chromium|OPR|Edg/i', $ua );

			if ( $is_safari ) {
				echo '<meta name="format-detection" content="telephone=no">' . "\n";
			}
		}


		private function is_field_enabled($field_key) {
			$enabled_elements = get_option('cfkef_enabled_elements', array());
			return in_array(sanitize_key($field_key), array_map('sanitize_key', $enabled_elements));
		}

		private function includes() {

			require_once CFEF_PLUGIN_DIR . 'admin/feedback/cron/cfef-class-cron.php';
		
		}

		public function formdb_marketing_hello_plus(){

			if ( !is_plugin_active( 'sb-elementor-contact-form-db/sb_elementor_contact_form_db.php' ) && !defined("formdb_hello_plus_marketing_editor")){

				define("formdb_hello_plus_marketing_editor", true);

				require_once CFEF_PLUGIN_DIR . 'includes/helloplus_loader.php';
				new HelloPlus_Widget_Loader();
			}
			
		}


		public function cfef_register_new_form_actions($form_actions_registrar){

			if($this->is_field_enabled('conditional_logic')){

				include_once( __DIR__ .  '/includes/class-conditional-fields-redirection.php' );
				include_once( __DIR__ .  '/includes/class-conditional-fields-email.php' );
				$form_actions_registrar->register( new \Conditional_Fields_Redirection() );
				$form_actions_registrar->register( new \Conditional_Email_Action() );

				if ( !is_plugin_active( 'sb-elementor-contact-form-db/sb_elementor_contact_form_db.php' ) && !defined("formdb_elementor_marketing_editor")){

					define("formdb_elementor_marketing_editor", true);

					include_once( __DIR__ .  '/includes/class-form-to-sheet.php' );
					$form_actions_registrar->register( new \Sheet_Action() );

				}
			}
		}
		/**
		 * Check if Elementor Pro is installed and activated
		 */
		public function is_compatible() {
			// add_action( 'admin_init', array( $this, 'is_elementor_pro_exist' ), 5 );

			if($this->is_field_enabled('conditional_logic')){


				include CFEF_PLUGIN_DIR . 'includes/class-create-conditional-fields.php';
				include CFEF_PLUGIN_DIR . 'includes/class-conditional-fields-submit-button.php';
				new Conditional_Submit_Button();
			}

			if ( is_admin() ) {
				require_once CFEF_PLUGIN_DIR . 'admin/feedback/admin-feedback-form.php';
			}
		}

		public function Cfef_pro_plugin_demo_link($links){
			$get_pro_link = '<a href="https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugins_list#pricing" style="font-weight: bold; color: green;" target="_blank">Get Pro</a>';
			array_unshift( $links, $get_pro_link );
			return $links;
		}

		public function Cfef_plugin_redirection($plugin){
			if ( ! is_plugin_active( 'elementor-pro/elementor-pro.php' ) && ! is_plugin_active( 'pro-elements/pro-elements.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php' ) ) {
				return false;
			}
			if ( $plugin == plugin_basename( __FILE__ ) ) {

				if ( current_user_can( 'activate_plugins' ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=cool-formkit' ) );
					exit;
				}
			}	
		}

		public function compatibilityCheck(){
			if ( is_plugin_active( 'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php' ) ) {
				return false;
			}
			if ( is_plugin_active( 'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php' ) ) {
				return false;
			}
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'Cfef_pro_plugin_demo_link' ) );
			require_once CFEF_PLUGIN_DIR . '/includes/class-conditional-fields-elementor-page.php';
			new Conditional_Fields_Elementor_Page();

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'cfef_plugin_settings_link' ) );

			if(!class_exists('CPFM_Feedback_Notice')){
				require_once CFEF_PLUGIN_DIR . 'admin/feedback/cpfm-common-notice.php';
			}

			if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {

				require_once CFEF_PLUGIN_DIR . '/admin/marketing/cfef-marketing-common.php';
			}


		}


		function cfef_plugin_settings_link( $links ) {

			$settings_link = '<a href="' . admin_url( 'admin.php?page=cool-formkit' ) . '">Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}


		/**
		 * Function use for deactivate plugin if elementor pro or pro elements not exist
		 */
		public function is_elementor_pro_exist() {
			if (
				is_plugin_active('pro-elements/pro-elements.php') || 
				is_plugin_active('elementor-pro/elementor-pro.php')||
				is_plugin_active('hello-plus/hello-plus.php')
			) {
				return true; // At least one plugin is active, the conditional plugin can run.
			}
		
			// If neither plugin is active, show an admin notice.
			add_action('admin_notices', array($this, 'admin_notice_missing_main_plugin'));
			return false;
		}

		/**
		 * Show notice to enable elementor pro
		 */
		public function admin_notice_missing_main_plugin() {
			$message = sprintf(
				// translators: %1$s replace with Conditional Fields for Elementor Form & %2$s replace with Elementor Pro.
				esc_html__(
					'%1$s requires %2$s to be installed and activated.','conditional-fields-for-elementor-form'
				),
				esc_html__( 'Conditional Fields for Elementor Form','conditional-fields-for-elementor-form' ),
				esc_html__( 'Elementor Pro','conditional-fields-for-elementor-form' ),
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

			if (!get_option( 'conditional_fields_initial_version' ) ) {
                add_option( 'conditional_fields_initial_version', CFEF_VERSION );
            }

			if(!get_option( 'cfef-install-date' ) ) {
				add_option( 'cfef-install-date', gmdate('Y-m-d h:i:s') );
        	}


			$settings       = get_option('cfef_usage_share_data');

			
			if (!empty($settings) || $settings === 'on'){
				
				static::cfef_cron_job_init();
			}
		}

		public static function cfef_cron_job_init()
		{
			if (!wp_next_scheduled('cfef_extra_data_update')) {
				wp_schedule_event(time(), 'every_30_days', 'cfef_extra_data_update');
			}
		}


		/**
		 * Function run on plugin deactivate
		 */
		public static function cfef_deactivate() {

			if (wp_next_scheduled('cfef_extra_data_update')) {
            	wp_clear_scheduled_hook('cfef_extra_data_update');
        	}
		}


		public function plugin_row_meta( $plugin_meta, $plugin_file ) {


			
			if ( CFEF_PLUGIN_BASE === $plugin_file ) {
				$row_meta = [
					'docs' => '<a href="https://docs.coolplugins.net/plugin/conditional-fields-for-elementor-form/?utm_source=cfef_plugin&utm_medium=inside&utm_campaign=docs&utm_content=plugins_list" aria-label="' . esc_attr( esc_html__( 'Country Code Documentation','conditional-fields-for-elementor-form' ) ) . '" target="_blank">' . esc_html__( 'Docs & FAQs','conditional-fields-for-elementor-form' ) . '</a>'
				];

				$plugin_meta = array_merge( $plugin_meta, $row_meta );
			}

			return $plugin_meta;

		}


	
}

}
$cfef_obj = Conditional_Fields_For_Elementor_Form::get_instance();
