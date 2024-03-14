<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_Wechat_Pay extends Omise_Payment_Offsite
{
	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_wechat_pay';
		$this->has_fields         = false;
		$this->method_title       = __( 'Opn Payments WeChat Pay', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payment through <strong>WeChat Pay</strong> via Opn Payments payment gateway.', 'omise' ),
			['strong' => []]
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = [ 'TH' ];
		$this->source_type = 'wechat_pay';

		add_action( 'woocommerce_api_' . $this->id . '_callback', 'Omise_Callback::execute' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
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
				'label'   => __('Enable Opn Payments WeChat Pay', 'omise'),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __('Title', 'omise'),
				'type'        => 'text',
				'description' => __('This controls the title the user sees during checkout.', 'omise'),
				'default'     => __('WeChat Pay', 'omise'),
			),

			'description' => array(
				'title'       => __('Description', 'omise'),
				'type'        => 'textarea',
				'description' => __('This controls the description the user sees during checkout.', 'omise')
			),
		);
	}

    /**
	 * Get icons
	 *
	 * @see WC_Payment_Gateway::get_icon()
	 */
	public function get_icon()
    {
		$icon = Omise_Image::get_image([
			'file' => 'wechat_pay.svg',
			'alternate_text' => 'WeChat Pay',
		]);

		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}

	public function charge($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . "_callback"
		);
		$requestData['source']['ip'] = RequestHelper::get_client_ip();
		return OmiseCharge::create($requestData);
	}
}
