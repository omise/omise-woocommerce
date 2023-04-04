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

		$currency   = get_woocommerce_currency();
		$cart_total = WC()->cart->total;
		$capabilities = $this->backend->capabilities();
		$installmentMinLimit = $capabilities->getInstallmentLimits()['min'];

		Omise_Util::render_view(
			'templates/payment/form-installment.php',
			array(
				'installment_backends' => $this->backend->get_available_providers($currency, $cart_total),
				'is_zero_interest'     => $capabilities ? $capabilities->is_zero_interest() : false,
				'installment_min_limit' => number_format($installmentMinLimit / 100) // subunit to base unit
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$source_type = isset($_POST['source']['type']) ? $_POST['source']['type'] : '';
		$installment_terms = isset($_POST[$source_type . '_installment_terms']) ? $_POST[$source_type . '_installment_terms'] : '';
		$currency = $order->get_currency();
		$provider = $this->backend->get_provider($source_type);

		$payload = [
			'amount' => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency' => $currency,
			'description' => apply_filters('omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order),
			'source' => [
				'type' => sanitize_text_field($source_type),
				'installment_terms' => sanitize_text_field($installment_terms)
			],
			'return_uri' => $this->getRedirectUrl('omise_installment_callback', $order_id, $order),
			'metadata' => $this->getMetadata($order_id, $order)
		];

		if (isset($provider['zero_interest_installments'])) {
			$payload['zero_interest_installments'] = $provider['zero_interest_installments'];
		}

		return OmiseCharge::create($payload);
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
}
