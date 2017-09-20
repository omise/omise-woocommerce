<?php
/**
 * Plugin Name: Omise Payment Gateway
 * Plugin URI: https://www.omise.co/woocommerce
 * Description: Omise WooCommerce Gateway Plugin is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Omise Payment Gateway payment method to WooCommerce.
 * Version: 3.1
 * Author: Omise
 * Author URI: https://www.omise.co
 * Text Domain: omise
 *
 * Copyright: Copyright 2014-2015. Omise Co., Ltd.
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise {
	/**
	 * Omise plugin version number.
	 *
	 * @var string
	 */
	public $version = '3.1';

	/**
	 * Omise facebook bot version number.
	 *
	 * @var string
	 */
	public $facebook_bot_version = '1.0';

	/**
	 * The Omise Instance.
	 *
	 * @since 3.0
	 *
	 * @var   \Omise
	 */
	protected static $the_instance = null;

	/**
	 * @since  3.0
	 */
	public function __construct() {
		$this->initiate();

		do_action( 'omise_initiated' );
	}

	/**
	 * @since  3.0
	 */
	protected function initiate() {
		defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION', $this->version );
		defined( 'OMISE_WOOCOMMERCE_PLUGIN_PATH' ) || define( 'OMISE_WOOCOMMERCE_PLUGIN_PATH', __DIR__ );
		defined( 'OMISE_API_VERSION' ) || define( 'OMISE_API_VERSION', '2014-07-27' );
		defined( 'OMISE_FACEBOOK_BOT_VERSION' ) || define( 'OMISE_FACEBOOK_BOT_VERSION', $this->facebook_bot_version );

		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-charge.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-card-image.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-capture.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-complete.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-create.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-alipay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-fbbot.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-internetbanking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-php/lib/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/libraries/omise-plugin/Omise.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-events.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-rest-webhooks-controller.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-setting.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-wc-myaccount.php';

		// Facebook Bot
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-endpoints.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-user-service.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-request-handler.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-conversation-generator.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-fbpage-setup.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-configurator.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-message-store.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-http-service.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-payload.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-woocommerce.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-wccategory.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-wcproduct.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-entity.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/fbbot/class-omise-fbbot-handover-protocol-handler.php';
		
		// Messenger Bot Template
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/url-button-item.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/postback-button-item.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/button-template.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/element-item.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/generic-template.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/message-item.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/file-message-item.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/image-message-item.php';

		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/omise-util.php';

		add_action( 'init', 'register_omise_wc_gateway_post_type' );
		add_action( 'plugins_loaded', 'register_omise_alipay', 0 );
		add_action( 'plugins_loaded', 'register_omise_creditcard', 0 );
		add_action( 'plugins_loaded', 'register_omise_internetbanking', 0 );
		add_action( 'plugins_loaded', 'register_omise_fbbot', 0 );
		add_action( 'plugins_loaded', 'prepare_omise_myaccount_panel', 0 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'register_user_agent' ), 10 );

		// Facebook Bot Action & Filter
		add_action( 'rest_api_init', array( Omise_FBBot_Endpoints::get_instance(), 'register_bot_api_routes' ) );
		add_action( 'woocommerce_settings_saved', array( Omise_FBot_Page_Setup::get_instance(), 'facebook_page_setup' ) );

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

			add_action( 'plugins_loaded', array( Omise_Admin::get_instance(), 'register_admin_menu' ) );
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

		$user_agent = sprintf( 'OmiseWooCommerce/%s OmiseFacebookBot/%s WordPress/%s WooCommerce/%s', OMISE_WOOCOMMERCE_PLUGIN_VERSION, OMISE_FACEBOOK_BOT_VERSION, $wp_version, WC()->version );
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
