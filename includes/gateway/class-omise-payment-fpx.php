<?php
defined('ABSPATH') or die('No direct script access allowed.');

class Omise_Payment_FPX extends Omise_Payment_Offsite
{

	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_fpx';
		$this->has_fields         = true;
		$this->method_title       = __('Opn Payments FPX', 'omise');
		$this->method_description = __('Accept payment through FPX', 'omise');
		$this->supports           = array('products', 'refunds');

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option('title');
		$this->description          = $this->get_option('description');
		$this->restricted_countries = array('MY');
		$this->source_type          = 'fpx';
		$this->backend              = new Omise_Backend_FPX;

		add_action('woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute');
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_order_action_' . $this->id . '_sync_payment', array($this, 'sync_payment'));
		add_action('woocommerce_after_checkout_validation', array($this, 'check_bank_selected'), null, 2);
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
				'label'   => __('Enable Opn Payments FPX Payment', 'omise'),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __('Title', 'omise'),
				'type'        => 'text',
				'description' => __('This controls the title the user sees during checkout.', 'omise'),
				'default'     => __('Online Banking (FPX)', 'omise'),
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

		Omise_Util::render_view(
			'templates/payment/form-fpx.php',
			array(
				'fpx_banklist' => $this->backend->get_available_banks()
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . "_callback"
		);
		$source_bank = isset($_POST['source']['bank']) ? $_POST['source']['bank'] : '';
		$requestData['source'] = array_merge($requestData['source'], [
			'bank' => sanitize_text_field($source_bank),
		]);
		return OmiseCharge::create($requestData);
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon()
	{
		$icon = Omise_Image::get_image([
			'file' => 'fpx.svg',
			'alternate_text' => 'FPX',
			'width' => 60,
			'height' => 60,
		]);
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}
}
