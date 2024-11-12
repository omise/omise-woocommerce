<?php
defined('ABSPATH') or die('No direct script access allowed.');

class Omise_Payment_DuitNow_OBW extends Omise_Payment_Offsite
{
	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_duitnow_obw';
		$this->has_fields         = true;
		$this->method_title       = __('Opn Payments DuitNow Online Banking/Wallets', 'omise');
		$this->method_description = __('Accept payment through <strong>DuitNow Online Banking/Wallets</strong> via Opn Payments payment gateway.', 'omise');
		$this->supports           = array('products', 'refunds');

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option('title');
		$this->description          = $this->get_option('description');
		$this->restricted_countries = array('MY');
		$this->source_type          = 'duitnow_obw';

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
				'label'   => __('Enable Opn Payments DuitNow Online Banking/Wallets Payment', 'omise'),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __('Title', 'omise'),
				'type'        => 'text',
				'description' => __('This controls the title the user sees during checkout.', 'omise'),
				'default'     => __('DuitNow Online Banking/Wallets', 'omise'),
			),

			'description' => array(
				'title'       => __('Description', 'omise'),
				'type'        => 'textarea',
				'description' => __('This controls the description the user sees during checkout.', 'omise')
			),
		);
	}

	public function get_bank_list()
	{
		return [
			'affin' => [
				'code' => 'affin',
				'name' => 'Affin Bank'
			],
			'alliance' => [
				'code' => 'alliance',
				'name' => 'Alliance Bank'
			],
			'agro' => [
				'code' => 'agro',
				'name' => 'Agrobank'
			],
			'ambank' => [
				'code' => 'ambank',
				'name' => 'AmBank'
			],
			'cimb' => [
				'code' => 'cimb',
				'name' => 'CIMB Bank'
			],
			'islam' => [
				'code' => 'islam',
				'name' => 'Bank Islam'
			],
			'rakyat' => [
				'code' => 'rakyat',
				'name' => 'Bank Rakyat'
			],
			'muamalat' => [
				'code' => 'muamalat',
				'name' => 'Bank Muamalat'
			],
			'bsn' => [
				'code' => 'bsn',
				'name' => 'Bank Simpanan Nasional'
			],
			'hongleong' => [
				'code' => 'hongleong',
				'name' => 'Hong Leong'
			],
			'hsbc' => [
				'code' => 'hsbc',
				'name' => 'HSBC Bank'
			],
			'kfh' => [
				'code' => 'kfh',
				'name' => 'Kuwait Finance House'
			],
			'maybank2u' => [
				'code' => 'maybank2u',
				'name' => 'Maybank'
			],
			'ocbc' => [
				'code' => 'ocbc',
				'name' => 'OCBC'
			],
			'public' => [
				'code' => 'public',
				'name' => 'Public Bank'
			],
			'rhb' => [
				'code' => 'rhb',
				'name' => 'RHB Bank'
			],
			'sc' => [
				'code' => 'sc',
				'name' => 'Standard Chartered'
			],
			'uob' => [
				'code' => 'uob',
				'name' => 'United Overseas Bank'
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function payment_fields()
	{
		parent::payment_fields();
		Omise_Util::render_view(
			'templates/payment/form-duitnow-obw.php',
			[ 'duitnow_obw_banklist' => $this->get_bank_list() ]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$request_data = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . "_callback"
		);

		// Prior to WC blocks, we get bank in source array. With WC blocks, bank is now a string.
		$source_bank = isset($_POST['bank'])
			? $_POST['bank']
			: (isset($_POST['source']) ? $_POST['source']['bank'] : '');
		$request_data['source'] = array_merge($request_data['source'], [
			'bank' => sanitize_text_field($source_bank),
		]);

		return OmiseCharge::create($request_data);
	}

	/**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon()
	{
		$icon = Omise_Image::get_image([
			'file' => 'duitnow-obw.png',
			'alternate_text' => 'DuitNow Online Banking/Wallets',
		]);
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}
}
