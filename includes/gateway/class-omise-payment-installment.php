<?php
defined('ABSPATH') or die('No direct script access allowed.');

/**
 * @since 3.4
 */
class Omise_Payment_Installment extends Omise_Payment_Offsite
{
	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_installment';
		$this->has_fields         = true;
		$this->method_title       = __('Opn Payments Installments', 'omise');
		$this->method_description = wp_kses(
			__('Accept <strong>installment payments</strong> via Opn Payments payment gateway.', 'omise'),
			array('strong' => array())
		);
		$this->supports           = array('products', 'refunds');

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option('title');
		$this->description          = $this->get_option('description');
		$this->restricted_countries = array('TH', 'MY');

		$this->backend     = new Omise_Backend_Installment;

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_order_action_' . $this->id . '_sync_payment', array($this, 'sync_payment'));
		add_action('woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute');
		add_action('wp_enqueue_scripts', array( $this, 'omise_scripts' ));
	}

	/**
	 * @see WC_Settings_API::init_form_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __('Enable/Disable', 'omise'),
				'type'    => 'checkbox',
				'label'   => __('Enable Opn Payments Installment Payments', 'omise'),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __('Title', 'omise'),
				'type'        => 'text',
				'description' => __('This controls the title the user sees during checkout.', 'omise'),
				'default'     => __('Installments', 'omise'),
			),

			'description' => array(
				'title'       => __('Description', 'omise'),
				'type'        => 'textarea',
				'description' => __('This controls the description the user sees during checkout.', 'omise')
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function payment_fields()
	{
		parent::payment_fields();

		Omise_Util::render_view('templates/payment/form-installment.php', $this->get_view_data());
	}

	public function get_view_data()
	{
		$currency   = get_woocommerce_currency();
		$cart_total = $this->get_total_amount();

		$capabilities = $this->backend->capabilities();
		$installmentMinLimit = $capabilities->getInstallmentMinLimit();

		return [
			'installments_enabled' => $this->backend->get_available_providers($currency, $cart_total),
			'is_zero_interest'     => $capabilities ? $capabilities->is_zero_interest() : false,
			'installment_min_limit' => Omise_Money::convert_currency_unit($installmentMinLimit, $currency),
			'currency' => $currency,
			'total_amount' => Omise_Money::to_subunit($cart_total, $currency),
		];
	}

	/**
	 * Get the total amount of an order
	 */
	public function get_total_amount()
	{
		global $wp;

		if (
			isset($wp->query_vars['order-pay']) &&
			(int)$wp->query_vars['order-pay'] > 0
		) {
			$order_id = (int)$wp->query_vars['order-pay'];
			$order = wc_get_order( $order_id );
			return $order->get_total();
		}

		// if not an order page then get total from the cart
		return WC()->cart->total;
	}

	/**
	 * Get the total amount of an order in cents
	 */
	public function convert_to_cents($amount)
	{
			return intval(floatval($amount) * 100);
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id,
			$order,
			null,
			$this->id . "_callback"
		);
		$requestData['description'] = 'staging';
		$requestData['source'] = isset($_POST['omise_source']) ? wc_clean($_POST['omise_source']) : '';
		$requestData['card'] = isset($_POST['omise_token']) ? wc_clean($_POST['omise_token']) : '';
		return OmiseCharge::create($requestData);
	}

	/**
	 * check if payment method is support by omise capability api version 2017
	 * 
	 * @param  array of backends source_type 
	 *
	 * @return array|false
	 */
	public function is_capability_support($available_payment_methods)
	{
		return preg_grep('/^installment_/', $available_payment_methods);
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function omise_scripts() {
		if ( is_checkout()) {
			wp_enqueue_script(
				'omise-js',
				Omise::OMISE_JS_LINK,
				[ 'jquery' ],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_enqueue_script(
				'omise-installment-form',
				plugins_url( '../../assets/javascripts/omise-installment-form.js', __FILE__ ),
				[ 'omise-js' ],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_enqueue_script(
				'omise-payment-form-handler',
				plugins_url( '../../assets/javascripts/omise-payment-form-handler.js', __FILE__ ),
				[ 'omise-js' ],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_localize_script(
				'omise-payment-form-handler',
				'omise_installment_params',
				$this->getParamsForJS()
			);
		}
	}

	public function getParamsForJS()
	{
		return [
			'key' => $this->public_key(),
			'amount' => $this->convert_to_cents($this->get_total_amount()),
		];
	}
}
