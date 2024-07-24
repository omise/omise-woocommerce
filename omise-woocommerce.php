<?php

/**
 * Plugin Name: Opn Payments
 * Plugin URI:  https://www.omise.co/woocommerce
 * Description: Opn Payments is a WordPress plugin designed specifically for WooCommerce. The plugin adds support for Opn Payments Payment Gateway's payment methods to WooCommerce.
 * Version:     5.9.0
 * Author:      Opn Payments and contributors
 * Author URI:  https://github.com/omise/omise-woocommerce/graphs/contributors
 * Text Domain: omise
 * WC requires at least: 3.3.4
 * WC tested up to: 8.1.1
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */
defined('ABSPATH') or die('No direct script access allowed.');

#[AllowDynamicProperties]
class Omise
{
	/**
	 * Omise plugin version number.
	 *
	 * @var string
	 */
	public $version = '5.9.0';

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

	CONST OMISE_JS_LINK = 'https://cdn.omise.co/omise.js';

	/**
	 * @since  3.0
	 */
	public function __construct()
	{
		add_action('before_woocommerce_init', [$this, 'enable_hpos']);
		add_action('plugins_loaded', array($this, 'check_dependencies'));
		add_action('woocommerce_init', array($this, 'init'));
		do_action('omise_initiated');
	}

	/**
	 * enable high performance order storage(HPOS) feature
	 */
	public function enable_hpos() {
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables', 
				__FILE__, 
				true
			);
		}
	}

	/**
	 * Notice for users informing about embedded form
	 */
	public function embedded_form_notice()
	{
		$this->omiseCardGateway = new Omise_Payment_Creditcard();
		$secure_form_enabled = $this->omiseCardGateway->get_option('secure_form_enabled');

		// hide if user enables the embedded form.
		if (!(bool)$secure_form_enabled) {
			$translation = __('Update your plugin to the latest version to enable Secure Form and maximize the security of your customers’ information. You will need to re-customize the credit card checkout form after the upgrade. <a target="_blank" href="https://www.omise.co/woocommerce-plugin">Learn how to enable Secure Form</a>.', 'omise');
			echo "<div class='notice notice-warning is-dismissible'><p><strong>Opn Payments:</strong> $translation</p></div>";
		}
	}

	/**
	 * get plugin assess url
	 */
	public static function get_assets_url() {
		return plugins_url('assets' , __FILE__);
	}

	/**
	 * Check if all dependencies are loaded
	 * properly before Omise WooCommerce.
	 *
	 * @since  3.2
	 */
	public function check_dependencies()
	{
		if (!function_exists('WC')) {
			return;
		}

		static::$can_initiate = true;
	}

	/**
	 * @since  3.0
	 */
	public function init()
	{
		if (!static::$can_initiate) {
			add_action('admin_notices', array($this, 'init_error_messages'));
			return;
		}

		$this->load_plugin_textdomain();
		$this->include_classes();
		$this->define_constants();
		$this->register_post_types();
		$this->init_admin();
		$this->init_route();
		$this->register_payment_methods();
		$this->register_hooks();
		$this->register_ajax_actions();

		prepare_omise_myaccount_panel();

		// adding action after all dependencies are loaded.
		if (static::$can_initiate) {
			// Moving here because the class used in the function could not be found on uninstall
			add_action('admin_notices', [$this, 'embedded_form_notice']);
			return;
		}
	}

	/**
	 * Callback to display message about activation error
	 *
	 * @since  3.2
	 */
	public function init_error_messages()
	{
?>
		<div class="error">
			<p><?php echo __('Opn Payments WooCommerce plugin requires <strong>WooCommerce</strong> to be activated.', 'omise'); ?></p>
		</div>
<?php
	}

	/**
	 * Define Omise necessary constants.
	 *
	 * @since 3.3
	 */
	private function define_constants()
	{
		global $wp_version;

		defined('OMISE_WOOCOMMERCE_PLUGIN_VERSION') || define('OMISE_WOOCOMMERCE_PLUGIN_VERSION', $this->version);
		defined('OMISE_PUBLIC_KEY') || define('OMISE_PUBLIC_KEY', $this->settings()->public_key());
		defined('OMISE_SECRET_KEY') || define('OMISE_SECRET_KEY', $this->settings()->secret_key());
		defined('OMISE_API_VERSION') || define('OMISE_API_VERSION', '2017-11-02');
		defined('OMISE_USER_AGENT_SUFFIX') || define(
			'OMISE_USER_AGENT_SUFFIX', 
			sprintf(
				'OmiseWooCommerce/%s WordPress/%s WooCommerce/%s', 
				OMISE_WOOCOMMERCE_PLUGIN_VERSION, 
				$wp_version, 
				WC()->version
			)
		);
	}

	/**
	 * @since 3.3
	 */
	private function include_classes()
	{
		defined('OMISE_WOOCOMMERCE_PLUGIN_PATH') || define('OMISE_WOOCOMMERCE_PLUGIN_PATH', __DIR__);

		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-queue-runner.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-queueable.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend-installment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend-mobile-banking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/backends/class-omise-backend-fpx.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-charge.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-card-image.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-image.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-customer.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/classes/class-omise-customer-card.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-capture.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-complete.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/events/class-omise-event-charge-create.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/traits/sync-order-trait.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/traits/charge-request-builder-trait.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-offline.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-offsite.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-base-card.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-alipay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-billpayment-tesco.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-fpx.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-installment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-internetbanking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-mobilebanking.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-konbini.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-paynow.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-promptpay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-truemoney.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-alipayplus.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-rabbit-linepay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-googlepay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-grabpay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-ocbc-digital.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-boost.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-duitnow-obw.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-duitnow-qr.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-maybank-qr.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-shopeepay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-touch-n-go.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-atome.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-paypay.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/gateway/class-omise-payment-wechat-pay.php';
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
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/admin/class-omise-admin-page.php';
		require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/admin/class-omise-page-card-form-customization.php';
	}

	/**
	 * @since  3.0
	 */
	protected function init_admin()
	{
		if (is_admin()) {
			require_once OMISE_WOOCOMMERCE_PLUGIN_PATH . '/includes/class-omise-admin.php';
			Omise_Admin::get_instance()->init();
		}
	}

	/**
	 * @since  3.1
	 */
	protected function init_route()
	{
		add_action('rest_api_init', function () {
			$controllers = new Omise_Rest_Webhooks_Controller;
			$controllers->register_routes();
		});
	}

	/**
	 * @since  3.0
	 */
	public function load_plugin_textdomain()
	{
		load_plugin_textdomain('omise', false, plugin_basename(dirname(__FILE__)) . '/languages/');
	}

	/**
	 * @since  3.11
	 */
	public function register_payment_methods()
	{
		add_filter('woocommerce_payment_gateways', function ($methods) {
			return array_merge($methods, $this->payment_methods());
		});
	}

	/**
	 * @since  4.0
	 */
	public function register_hooks()
	{
		add_action('omise_async_webhook_event_handler', 'Omise_Queue_Runner::execute_webhook_event_handler', 10, 3);
	}

	/**
	 * @since  4.1
	 */
	public function register_ajax_actions()
	{
		add_action('wp_ajax_nopriv_fetch_order_status', 'Omise_Ajax_Actions::fetch_order_status');
		add_action('wp_ajax_fetch_order_status', 'Omise_Ajax_Actions::fetch_order_status');
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
	public function register_post_types()
	{
		register_post_type(
			'omise_charge_items',
			array(
				'supports' => array('title', 'custom-fields'),
				'label'    => 'Opn Payments Charge Items',
				'labels'   => array(
					'name'          => 'Opn Payments Charge Items',
					'singular_name' => 'Opn Payments Charge Item'
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
	public static function instance()
	{
		if (is_null(self::$the_instance)) {
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
	public function settings()
	{
		return Omise_Setting::instance();
	}

	/**
	 * @since  4.0
	 *
	 * @return array of all the available payment methods
	 *               that Omise WooCommerce supported.
	 */
	public function payment_methods()
	{
		return Omise_Payment_Factory::$payment_methods;
	}

	/**
	 * L10n the given string.
	 *
	 * @since  4.1
	 *
	 * @return string
	 */
	public function translate($message)
	{
		return Omise_Localization::translate($message);
	}
}

function Omise()
{
	return Omise::instance();
}

Omise();
