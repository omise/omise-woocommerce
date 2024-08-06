<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

class Omise_Payment_Mobilebanking extends Omise_Payment_Offsite
{
	public function __construct()
	{
		parent::__construct();

		$this->id                 = 'omise_mobilebanking';
		$this->has_fields         = true;
		$this->method_title       = __( 'Opn Payments Mobile Banking', 'omise' );
		$this->method_description = wp_kses(
			__( 'Accept payment through <strong>Mobile Banking</strong> via Opn Payments payment gateway.', 'omise' ),
			array(
				'strong' => array()
			)
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->restricted_countries = array( 'TH' );

		$this->backend     = new Omise_Backend_Mobile_Banking;

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
				'title'   => __( 'Enable/Disable', 'omise' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Opn Payments Mobile Banking Payment', 'omise' ),
				'default' => 'no'
			),

			'title' => array(
				'title'       => __( 'Title', 'omise' ),
				'type'        => 'text',
				'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
				'default'     => __( 'Mobile Banking', 'omise' ),
			),

			'description' => array(
				'title'       => __( 'Description', 'omise' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
			),
		);
	}

	/**
	 * @see WC_Payment_Gateway::payment_fields()
	 * @see woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
	 */
	public function payment_fields() {
		$currency   = get_woocommerce_currency();
		parent::payment_fields();

		Omise_Util::render_view( 'templates/payment/form-mobilebanking.php', 
		array(
			'mobile_banking_backends' => $this->backend->get_available_providers( $currency ),
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$source_type = sanitize_text_field($_POST['omise-offsite']);
		$requestData = $this->build_charge_request(
			$order_id, $order, $source_type, $this->id . "_callback"
		);

		$requestData['source'] = array_merge($requestData['source'], [
			'platform_type' => Omise_Util::get_platform_type(wc_get_user_agent())
		]);
		return OmiseCharge::create($requestData);
	}

	/**
	 * check if payment method is support by omise capability api version 2017
	 * 
	 * @param  array of backends source_type 
	 *
	 * @return array|false
	 */
	public function is_capability_support( $available_payment_methods ) {
		return preg_grep('/^mobile_banking_/', $available_payment_methods);
	}
}
