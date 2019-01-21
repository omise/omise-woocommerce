<?php
/**
 * Plugin Name: Omise Payment Gateway
 * Plugin URI:  https://www.omise.co/woocommerce
 * Description: Omise WooCommerce Gateway Plugin is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.
 * Version:     3.2
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
	public $version = '3.2';

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
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce_active' ) );
		add_action( 'init', array( $this, 'initiate' ) );

		do_action( 'omise_initiated' );
	}

	/** 
	 * Callback to display message about activation error
	 * 
	 * @since  3.2
	 */
	public function woocommerce_plugin_notice(){
		?>
		<div class="error">
			<p><?php echo __( 'The Omise WooCommerce plugin requires <strong>WooCommerce</strong> to be activated.', 'omise' ); ?></p>
		</div>
		<?php
	}

	/** 
	 * Callback checking if WooCommerce is active
	 * 
	 * @since  3.2
	 */
	public function check_woocommerce_active() {
		if ( function_exists( 'WC' ) ) {
			static::$can_initiate = true;
			return;
		}
	}

	/**
	 * @since  3.0
	 */
	public function initiate() {
		if ( ! static::$can_initiate ) {
			add_action( 'admin_notices', array($this, 'woocommerce_plugin_notice') );
			return;
		}

		defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION', $this->version );
		defined( 'OMISE_WOOCOMMERCE_PLUGIN_PATH' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_PATH', __DIR__ );
		defined( 'OMISE_API_VERSION' ) || define( 'OMISE_API_VERSION', '2014-07-27' );

		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-charge.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-card-image.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-capture.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-complete.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-create.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-alipay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-internetbanking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-php/lib/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-plugin/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-events.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-rest-webhooks-controller.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-setting.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-wc-myaccount.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/omise-util.php';		

		register_omise_wc_gateway_post_type();
		register_omise_alipay();
		register_omise_creditcard();
		register_omise_internetbanking();
		prepare_omise_myaccount_panel();
		$this->load_plugin_textdomain();
		$this->register_user_agent();

		$this->init_admin();
		$this->init_route();
	}

	/**
	 * @since  3.0
	 */
	protected function init_admin() {
		if ( is_admin() ) {
			require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/admin/class-omise-page-settings.php';
			require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-admin.php';

			Omise_Admin::get_instance()->register_admin_menu();
			add_filter( 'woocommerce_order_actions', array( $this, 'register_order_actions' ) );
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
	 * @since  3.0
	 */
	public function register_user_agent() {
		global $wp_version;

		$user_agent = sprintf( 'OmiseWooCommerce/%s WordPress/%s WooCommerce/%s', OMISE_WOOCOMMERCE_PLUGIN_VERSION, $wp_version, function_exists('WC') ? WC()->version : '' );
		defined( 'OMISE_USER_AGENT_SUFFIX' ) || define( 'OMISE_USER_AGENT_SUFFIX', $user_agent );
	}

	/**
	 * @param  array $order_actions
	 *
	 * @return array
	 */
	public function register_order_actions( $order_actions ) {
		global $theorder;

		/** backward compatible with WooCommerce v2.x series **/
		$payment_method = version_compare( WC()->version, '3.0.0', '>=' ) ? $theorder->get_payment_method() : $theorder->payment_method;

		if ( 'omise' === $payment_method ) {
			$order_actions[ $payment_method . '_charge_capture'] = __( 'Omise: Capture this order', 'omise' );
		}

		$order_actions[ $payment_method . '_sync_payment'] = __( 'Omise: Manual sync payment status', 'omise' );

		return $order_actions;
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
}

function Omise() {
	return Omise::instance();
}

Omise();
