<?php
/**
 * Plugin Name: Omise Payment Gateway
 * Plugin URI:  https://www.omise.co/woocommerce
 * Description: Omise WooCommerce Gateway Plugin is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.
 * Version:     4.10
 * Author:      Omise and contributors
 * Author URI:  https://github.com/omise/omise-woocommerce/graphs/contributors
 * Text Domain: omise
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise {
	/**
	 * Omise plugin version number.
	 *
	 * @var string
	 */
	public $version = '4.10';

	/**
	 * The Omise Instance.
	 *
	 * @since 3.0
	 *
	 * @var   \Omise
	 */
	protected static $the_instance = null;

	/**
	 * @since 3.3
	 *
	 * @var   boolean
	 */
	protected static $can_initiate = false;

	/**
	 * @since  3.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );
		add_action( 'init', array( $this, 'init' ) );

		do_action( 'omise_initiated' );
	}

	/** 
	 * Check if all dependencies are loaded
	 * properly before Omise-WooCommerce.
	 * 
	 * @since  3.2
	 */
	public function check_dependencies() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		static::$can_initiate = true;
	}

	/**
	 * @since  3.0
	 */
	public function init() {
		if ( ! static::$can_initiate ) {
			add_action( 'admin_notices', array($this, 'init_error_messages') );
			return;
		}

		$this->include_classes();
		$this->define_constants();
		$this->load_plugin_textdomain();
		$this->register_post_types();
		$this->init_admin();
		$this->init_route();
		$this->register_payment_methods();
		$this->register_hooks();
		$this->register_ajax_actions();

		prepare_omise_myaccount_panel();
	}

	/**
	 * Callback to display message about activation error
	 *
	 * @since  3.2
	 */
	public function init_error_messages(){
		?>
		<div class="error">
			<p><?php echo __( 'Omise WooCommerce plugin requires <strong>WooCommerce</strong> to be activated.', 'omise' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Define Omise necessary constants.
	 *
	 * @since 3.3
	 */
	private function define_constants() {
		global $wp_version;

		defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION', $this->version );
		defined( 'OMISE_PUBLIC_KEY' ) || define( 'OMISE_PUBLIC_KEY', $this->settings()->public_key() );
		defined( 'OMISE_SECRET_KEY' ) || define( 'OMISE_SECRET_KEY', $this->settings()->secret_key() );
		defined( 'OMISE_API_VERSION' ) || define( 'OMISE_API_VERSION', '2017-11-02' );
		defined( 'OMISE_USER_AGENT_SUFFIX' ) || define( 'OMISE_USER_AGENT_SUFFIX', sprintf( 'OmiseWooCommerce/%s WordPress/%s WooCommerce/%s', OMISE_WOOCOMMERCE_PLUGIN_VERSION, $wp_version, WC()->version ) );
	}

	/**
	 * @since 3.3
	 */
	private function include_classes() {
		defined( 'OMISE_WOOCOMMERCE_PLUGIN_PATH' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_PATH', __DIR__ );

		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-queue-runner.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-queueable.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend-installment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend-fpx.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-charge.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-card-image.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-capture.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-complete.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-create.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-offline.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-offsite.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-alipay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-billpayment-tesco.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-fpx.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-installment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-internetbanking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-konbini.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-paynow.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-promptpay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-truemoney.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-php/lib/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-plugin/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-ajax-actions.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-callback.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-capabilities.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-events.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-localization.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-money.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-payment-factory.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-rest-webhooks-controller.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-setting.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-wc-myaccount.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/omise-util.php';
	}

	/**
	 * @since  3.0
	 */
	protected function init_admin() {
		if ( is_admin() ) {
			require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-admin.php';
			Omise_Admin::get_instance()->init();
		}
	}

	/**
	 * @since  3.1
	 */
	protected function init_route() {
		add_action( 'rest_api_init', function () {
			$controllers = new Omise_Rest_Webhooks_Controller;
			$controllers->register_routes();
		} );
	}

	/**
	 * @since  3.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'omise', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * @since  3.11
	 */
	public function register_payment_methods() {
		add_filter( 'woocommerce_payment_gateways', function( $methods ) {
			return array_merge( $methods, $this->payment_methods() );
		} );
	}

	/**
	 * @since  4.0
	 */
	public function register_hooks() {
		add_action( 'omise_async_webhook_event_handler', 'Omise_Queue_Runner::execute_webhook_event_handler', 10, 3 );
	}

	/**
	 * @since  4.1
	 */
	public function register_ajax_actions() {
		add_action('wp_ajax_nopriv_fetch_order_status', 'Omise_Ajax_Actions::fetch_order_status' );
		add_action('wp_ajax_fetch_order_status', 'Omise_Ajax_Actions::fetch_order_status' );
	}

	/**
	 * Register necessary post-types
	 *
	 * @deprecated 3.0  Omise-WooCommerce was once storing Omise's charge id
	 *                  with WooCommerce's order id together in a
	 *                  customed-post-type, 'omise_charge_items'.
	 *
	 *                  Since Omise-WooCoomerce v3.0, now the plugin stores
	 *                  Omise's charge id as a 'customed-post-meta' in the
	 *                  WooCommerce's 'order' post-type instead.
	 */
	public function register_post_types() {
		register_post_type(
			'omise_charge_items',
			array(
				'supports' => array('title','custom-fields'),
				'label'    => 'Omise Charge Items',
				'labels'   => array(
					'name'          => 'Omise Charge Items',
					'singular_name' => 'Omise Charge Item'
				)
			)
		);
	}

	/**
	 * The Omise Instance.
	 *
	 * @see    Omise()
	 *
	 * @since  3.0
	 *
	 * @static
	 *
	 * @return \Omise - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}

	/**
	 * Get setting class.
	 *
	 * @since  3.4
	 *
	 * @return Omise_Setting
	 */
	public function settings() {
		return Omise_Setting::instance();
	}

	/**
	 * @since  4.0
	 *
	 * @return array of all the available payment methods
	 *               that Omise WooCommerce supported.
	 */
	public function payment_methods() {
		return Omise_Payment_Factory::$payment_methods;
	}

	/**
	 * L10n the given string.
	 *
	 * @since  4.1
	 *
	 * @return string
	 */
	public function translate( $message ) {
		return Omise_Localization::translate( $message );
	}
}

function Omise() {
	return Omise::instance();
}

Omise();
